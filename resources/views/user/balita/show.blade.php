@extends('layouts.user')

@section('title', 'Detail Balita')
@section('page_title', 'Detail Balita')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $dataBalita = $balita ?? $dataBalita ?? null;

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
    $growthMetrics = collect($growthMetrics ?? []);
    $trend = collect($trend ?? []);
    $riwayatCards = collect($riwayatCards ?? []);
    $imunisasiCards = collect($imunisasiCards ?? []);

    $growthAnalysis = $growthAnalysis ?? [
        'label' => 'Belum Ada Data',
        'message' => 'Data pengukuran balita belum tersedia.',
        'suggestion' => 'Lakukan pengukuran BB, TB, lingkar kepala, dan LILA di Posyandu.',
        'tone' => 'slate',
        'icon' => 'fa-circle-info',
    ];

    $genderLabel = match ($dataBalita->jenis_kelamin ?? null) {
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        default => 'Belum diisi',
    };

    $initial = strtoupper(substr(trim((string) ($dataBalita->nama_lengkap ?? 'B')), 0, 1)) ?: 'B';

    $formatDate = fn ($value, $format = 'd F Y') => $value
        ? Carbon::parse($value)->translatedFormat($format)
        : '-';

    $analysisTone = match ($growthAnalysis['tone'] ?? 'slate') {
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'amber' => 'border-amber-200 bg-amber-50 text-amber-700',
        'rose' => 'border-rose-200 bg-rose-50 text-rose-700',
        'sky' => 'border-sky-200 bg-sky-50 text-sky-700',
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
@endphp

@push('styles')
<style>
    .balita-page {
        background:
            radial-gradient(circle at 8% 8%, rgba(244, 63, 94, .11), transparent 28%),
            radial-gradient(circle at 92% 12%, rgba(16, 185, 129, .13), transparent 26%),
            radial-gradient(circle at 78% 88%, rgba(14, 165, 233, .10), transparent 28%),
            linear-gradient(135deg, #f8fafc 0%, #fff1f2 38%, #ecfdf5 100%);
    }

    .bt-enter {
        opacity: 0;
        animation: btEnter .36s cubic-bezier(.16, 1, .3, 1) forwards;
    }

    .bt-enter-2 {
        opacity: 0;
        animation: btEnter .36s cubic-bezier(.16, 1, .3, 1) .07s forwards;
    }

    @keyframes btEnter {
        from {
            opacity: 0;
            transform: translateY(12px) scale(.99);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .bt-glass {
        border: 1px solid rgba(255, 255, 255, .76);
        background: rgba(255, 255, 255, .70);
        backdrop-filter: blur(20px);
        box-shadow: 0 16px 46px rgba(15, 23, 42, .055);
    }

    .bt-card {
        border: 1px solid rgba(226, 232, 240, .78);
        background: rgba(255, 255, 255, .74);
        backdrop-filter: blur(16px);
        box-shadow: 0 10px 28px rgba(15, 23, 42, .04);
    }

    .bt-scroll::-webkit-scrollbar {
        width: 4px;
    }

    .bt-scroll::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, .55);
        border-radius: 999px;
    }

    @media (prefers-reduced-motion: reduce) {
        .bt-enter,
        .bt-enter-2 {
            animation: none;
            opacity: 1;
        }
    }
</style>
@endpush

@section('content')
<div class="balita-page -mx-4 -my-4 min-h-[calc(100vh-96px)] px-4 py-5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-5">

        {{-- HERO --}}
        <section class="bt-enter grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="bt-glass relative overflow-hidden rounded-[30px] p-5 sm:p-6">
                <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-rose-300/18 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-24 left-8 h-56 w-56 rounded-full bg-emerald-300/16 blur-3xl"></div>

                <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center">
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-[26px] bg-gradient-to-br from-rose-400 to-emerald-500 text-3xl font-black text-white shadow-[0_18px_42px_rgba(244,63,94,.18)]">
                        {{ $initial }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.11em] text-rose-700">
                                Balita
                            </span>

                            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.11em] text-emerald-700">
                                {{ $genderLabel }}
                            </span>

                            <span class="rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.11em] text-sky-700">
                                {{ $usia['kategori'] ?? 'Balita' }}
                            </span>
                        </div>

                        <h1 class="mt-3 line-clamp-2 text-3xl font-black tracking-tight text-slate-800 sm:text-4xl">
                            {{ $dataBalita->nama_lengkap ?? '-' }}
                        </h1>

                        <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                            NIK {{ $dataBalita->nik ?? '-' }} •
                            {{ $formatDate($dataBalita->tanggal_lahir ?? null) }}
                        </p>

                        <div class="mt-4 flex flex-wrap gap-3">
                            <a href="{{ $backRoute }}"
                               class="smooth-route inline-flex h-10 items-center justify-center rounded-2xl border border-rose-100 bg-white/82 px-4 text-xs font-black uppercase tracking-[0.12em] text-rose-700 transition hover:bg-rose-50">
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

            {{-- GROWTH ANALYSIS --}}
            <div class="bt-glass rounded-[30px] p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-rose-700">
                    Analisis Pertumbuhan
                </p>

                <div class="mt-4 rounded-[24px] border p-4 {{ $analysisTone }}">
                    <div class="flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[17px] bg-white/75 text-lg shadow-sm">
                            <i class="fas {{ $growthAnalysis['icon'] ?? 'fa-circle-info' }}"></i>
                        </div>

                        <div>
                            <h2 class="text-xl font-black">
                                {{ $growthAnalysis['label'] ?? 'Belum Ada Data' }}
                            </h2>

                            <p class="mt-2 text-sm font-bold leading-6 opacity-80">
                                {{ $growthAnalysis['message'] ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <p class="mt-4 text-sm font-semibold leading-6 text-slate-600">
                    {{ $growthAnalysis['suggestion'] ?? 'Lakukan pemantauan pertumbuhan secara rutin di Posyandu.' }}
                </p>
            </div>
        </section>

        {{-- PRIMARY METRICS --}}
        <section class="bt-enter-2 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
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

        {{-- SECONDARY GROWTH METRICS --}}
        <section class="bt-enter-2 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
            @foreach($growthMetrics as $metric)
                <div class="bt-card rounded-[22px] p-4 {{ $metricTone($metric['tone'] ?? 'slate') }}">
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

        <section class="bt-enter-2 grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_380px]">

            <div class="space-y-5">

                {{-- TREND --}}
                <div class="bt-glass overflow-hidden rounded-[30px]">
                    <div class="border-b border-rose-100/80 bg-gradient-to-r from-white/86 via-rose-50/72 to-emerald-50/62 px-5 py-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.20em] text-rose-700">
                            Tren Pertumbuhan
                        </p>
                        <h2 class="mt-1 text-xl font-black text-slate-800">
                            Grafik Ringkas Berat Badan
                        </h2>
                    </div>

                    <div class="p-5">
                        @if($trend->isNotEmpty())
                            <div class="flex h-48 items-end gap-3 rounded-[26px] border border-rose-100 bg-gradient-to-br from-white/82 via-rose-50/48 to-emerald-50/45 p-4">
                                @foreach($trend as $point)
                                    <div class="flex min-w-0 flex-1 flex-col items-center justify-end gap-2">
                                        <div class="flex h-32 w-full items-end justify-center">
                                            <div class="w-full max-w-[34px] rounded-t-2xl bg-gradient-to-t from-rose-500 to-emerald-400 shadow-[0_10px_24px_rgba(244,63,94,.16)]"
                                                 style="height: {{ $point['height'] ?? 14 }}%;"></div>
                                        </div>

                                        <p class="truncate text-[10px] font-black text-slate-500">
                                            {{ $point['label'] ?? '-' }}
                                        </p>

                                        <p class="text-[10px] font-black text-rose-700">
                                            {{ filled($point['berat'] ?? null) ? $point['berat'] . ' kg' : '-' }}
                                        </p>

                                        <p class="text-[9px] font-black text-slate-400">
                                            {{ $point['tinggi'] ?? '-' }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-[26px] border border-dashed border-rose-300 bg-rose-50/55 p-8 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-[22px] bg-white text-xl text-rose-500 shadow-sm">
                                    <i class="fas fa-chart-column"></i>
                                </div>

                                <h3 class="mt-4 text-base font-black text-slate-800">
                                    Tren Belum Tersedia
                                </h3>

                                <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                                    Grafik akan muncul setelah ada riwayat pengukuran berat badan.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- PROFILE --}}
                <div class="bt-glass overflow-hidden rounded-[30px]">
                    <div class="border-b border-emerald-100/80 bg-gradient-to-r from-white/86 via-emerald-50/72 to-rose-50/62 px-5 py-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.20em] text-emerald-700">
                            Profil Balita
                        </p>
                        <h2 class="mt-1 text-xl font-black text-slate-800">
                            Identitas dan Data Dasar
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2">
                        <div class="bt-card rounded-[22px] p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Nama</p>
                            <p class="mt-2 text-sm font-black text-slate-800">{{ $dataBalita->nama_lengkap ?? '-' }}</p>
                        </div>

                        <div class="bt-card rounded-[22px] p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">NIK</p>
                            <p class="mt-2 text-sm font-black text-slate-800">{{ $dataBalita->nik ?? '-' }}</p>
                        </div>

                        <div class="bt-card rounded-[22px] p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Tempat Lahir</p>
                            <p class="mt-2 text-sm font-black text-slate-800">{{ $dataBalita->tempat_lahir ?? '-' }}</p>
                        </div>

                        <div class="bt-card rounded-[22px] p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Tanggal Lahir</p>
                            <p class="mt-2 text-sm font-black text-slate-800">{{ $formatDate($dataBalita->tanggal_lahir ?? null) }}</p>
                        </div>

                        <div class="bt-card rounded-[22px] p-4 sm:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Alamat</p>
                            <p class="mt-2 text-sm font-black leading-6 text-slate-800">{{ $dataBalita->alamat ?? '-' }}</p>
                        </div>

                        <div class="bt-card rounded-[22px] p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Nama Orang Tua</p>
                            <p class="mt-2 text-sm font-black text-slate-800">{{ $dataBalita->nama_orangtua ?? $dataBalita->nama_ibu ?? '-' }}</p>
                        </div>

                        <div class="bt-card rounded-[22px] p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Usia</p>
                            <p class="mt-2 text-sm font-black text-slate-800">{{ $usia['label'] ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIWAYAT --}}
            <aside class="bt-glass flex max-h-[820px] min-h-[520px] flex-col overflow-hidden rounded-[30px]">
                <div class="border-b border-rose-100/80 bg-gradient-to-r from-white/86 via-rose-50/72 to-emerald-50/62 px-5 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.20em] text-rose-700">
                        Riwayat Klinis
                    </p>
                    <h2 class="mt-1 text-xl font-black text-slate-800">
                        Pengukuran Balita
                    </h2>
                </div>

                <div class="bt-scroll flex-1 space-y-3 overflow-y-auto p-4">
                    @forelse($riwayatCards as $item)
                        <article class="rounded-[24px] border border-rose-100 bg-white/72 p-4 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-black text-slate-800">
                                        {{ $item['tanggal'] ?? '-' }}
                                    </p>

                                    <p class="mt-1 text-xs font-bold text-slate-500">
                                        Usia saat periksa {{ $item['usia'] ?? '-' }}
                                    </p>
                                </div>

                                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.10em] text-emerald-700">
                                    {{ $item['status'] ?? 'Tervalidasi' }}
                                </span>
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-2">
                                <div class="rounded-[18px] border border-slate-100 bg-slate-50/70 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">BB</p>
                                    <p class="mt-1 truncate text-sm font-black text-slate-800">{{ $item['berat'] ?? '-' }}</p>
                                </div>

                                <div class="rounded-[18px] border border-slate-100 bg-slate-50/70 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">TB</p>
                                    <p class="mt-1 truncate text-sm font-black text-slate-800">{{ $item['tinggi'] ?? '-' }}</p>
                                </div>

                                <div class="rounded-[18px] border border-slate-100 bg-slate-50/70 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">LK</p>
                                    <p class="mt-1 truncate text-sm font-black text-slate-800">{{ $item['lingkar_kepala'] ?? '-' }}</p>
                                </div>
                            </div>

                            <div class="mt-2 grid grid-cols-3 gap-2">
                                <div class="rounded-[18px] border border-slate-100 bg-slate-50/70 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">LILA</p>
                                    <p class="mt-1 truncate text-sm font-black text-slate-800">{{ $item['lila'] ?? '-' }}</p>
                                </div>

                                <div class="rounded-[18px] border border-slate-100 bg-slate-50/70 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">BB/U</p>
                                    <p class="mt-1 truncate text-sm font-black text-slate-800">{{ $item['status_bbu'] ?? '-' }}</p>
                                </div>

                                <div class="rounded-[18px] border border-slate-100 bg-slate-50/70 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">BB/TB</p>
                                    <p class="mt-1 truncate text-sm font-black text-slate-800">{{ $item['status_bbtb'] ?? '-' }}</p>
                                </div>
                            </div>

                            <div class="mt-3 rounded-[18px] border border-rose-100 bg-rose-50/55 px-3 py-3 text-sm font-semibold leading-6 text-rose-700">
                                <span class="font-black">Status Gizi:</span>
                                {{ $item['status_gizi'] ?? 'Belum Dinilai' }}
                            </div>

                            <p class="mt-3 line-clamp-2 text-sm font-semibold leading-6 text-slate-500">
                                {{ $item['keluhan'] ?? 'Tidak ada catatan keluhan.' }}
                            </p>
                        </article>
                    @empty
                        <div class="flex h-full min-h-[260px] flex-col items-center justify-center rounded-[24px] border border-dashed border-rose-300 bg-rose-50/55 p-8 text-center">
                            <div class="flex h-14 w-14 items-center justify-center rounded-[22px] bg-white text-xl text-rose-500 shadow-sm">
                                <i class="fas fa-notes-medical"></i>
                            </div>

                            <h3 class="mt-4 text-base font-black text-slate-800">
                                Riwayat Masih Kosong
                            </h3>

                            <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                                Riwayat pengukuran akan muncul setelah data divalidasi oleh Bidan.
                            </p>
                        </div>
                    @endforelse
                </div>
            </aside>
        </section>

        {{-- IMUNISASI --}}
        <section class="bt-enter-2 bt-glass overflow-hidden rounded-[30px]">
            <div class="border-b border-emerald-100/80 bg-gradient-to-r from-white/86 via-emerald-50/72 to-sky-50/62 px-5 py-4">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.20em] text-emerald-700">
                            Riwayat Imunisasi
                        </p>
                        <h2 class="mt-1 text-xl font-black text-slate-800">
                            Vaksinasi dan Imunisasi Balita
                        </h2>
                    </div>

                    <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.10em] text-emerald-700">
                        {{ $imunisasiCards->count() }} Data
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 p-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse($imunisasiCards as $item)
                    <article class="bt-card rounded-[24px] p-4">
                        <div class="flex items-start gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[17px] border border-emerald-100 bg-emerald-50 text-emerald-600">
                                <i class="fas fa-syringe"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="line-clamp-1 text-base font-black text-slate-800">
                                    {{ $item['nama'] ?? 'Imunisasi' }}
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-500">
                                    {{ $item['tanggal'] ?? '-' }}
                                </p>

                                <div class="mt-3 grid grid-cols-2 gap-2">
                                    <div class="rounded-[16px] border border-slate-100 bg-slate-50/70 px-3 py-2">
                                        <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">Batch</p>
                                        <p class="mt-1 truncate text-xs font-black text-slate-700">{{ $item['batch'] ?? '-' }}</p>
                                    </div>

                                    <div class="rounded-[16px] border border-slate-100 bg-slate-50/70 px-3 py-2">
                                        <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">Petugas</p>
                                        <p class="mt-1 truncate text-xs font-black text-slate-700">{{ $item['petugas'] ?? '-' }}</p>
                                    </div>
                                </div>

                                <p class="mt-3 line-clamp-2 text-sm font-semibold leading-6 text-slate-500">
                                    {{ $item['catatan'] ?? 'Tidak ada catatan tambahan.' }}
                                </p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-[26px] border border-dashed border-emerald-300 bg-emerald-50/55 p-8 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-[22px] bg-white text-xl text-emerald-500 shadow-sm">
                            <i class="fas fa-syringe"></i>
                        </div>

                        <h3 class="mt-4 text-base font-black text-slate-800">
                            Riwayat Imunisasi Belum Tersedia
                        </h3>

                        <p class="mx-auto mt-1 max-w-md text-sm font-semibold leading-6 text-slate-500">
                            Riwayat imunisasi akan muncul setelah dicatat oleh petugas Posyandu.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection