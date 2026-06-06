@extends('layouts.user')

@section('title', 'Detail Remaja')
@section('page_title', 'Detail Remaja')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

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

    $genderLabel = match ($remaja->jenis_kelamin ?? null) {
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        default => 'Belum diisi',
    };

    $initial = strtoupper(substr(trim((string) ($remaja->nama_lengkap ?? 'R')), 0, 1)) ?: 'R';

    $formatDate = fn ($value, $format = 'd F Y') => $value
        ? Carbon::parse($value)->translatedFormat($format)
        : '-';

    $numberValue = function ($value, $unit = '') {
        if (blank($value)) {
            return '-';
        }

        $value = rtrim(rtrim((string) $value, '0'), '.');

        return trim($value . ' ' . $unit);
    };
    $heightValue = function ($value) use ($numberValue) {
    if (blank($value)) {
        return '-';
    }

    $height = (float) $value;

    if ($height >= 10 && $height < 50) {
        $height *= 10;
    }

    if ($height < 50 || $height > 250) {
        return '-';
    }

    return $numberValue($height, 'cm');
};

    $analysisTone = match ($imtAnalysis['tone'] ?? 'slate') {
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
@endphp

@push('styles')
<style>
    .remaja-page {
        background:
            radial-gradient(circle at 8% 8%, rgba(14, 165, 233, .13), transparent 28%),
            radial-gradient(circle at 92% 12%, rgba(16, 185, 129, .13), transparent 26%),
            radial-gradient(circle at 78% 88%, rgba(245, 158, 11, .10), transparent 28%),
            linear-gradient(135deg, #f8fafc 0%, #eff6ff 42%, #ecfdf5 100%);
    }

    .remaja-enter {
        opacity: 0;
        animation: remajaEnter .36s cubic-bezier(.16, 1, .3, 1) forwards;
    }

    .remaja-enter-2 {
        opacity: 0;
        animation: remajaEnter .36s cubic-bezier(.16, 1, .3, 1) .07s forwards;
    }

    @keyframes remajaEnter {
        from {
            opacity: 0;
            transform: translateY(12px) scale(.99);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .rm-glass {
        border: 1px solid rgba(255, 255, 255, .76);
        background: rgba(255, 255, 255, .70);
        backdrop-filter: blur(20px);
        box-shadow: 0 16px 46px rgba(15, 23, 42, .055);
    }

    .rm-card {
        border: 1px solid rgba(226, 232, 240, .78);
        background: rgba(255, 255, 255, .74);
        backdrop-filter: blur(16px);
        box-shadow: 0 10px 28px rgba(15, 23, 42, .04);
    }

    @media (prefers-reduced-motion: reduce) {
        .remaja-enter,
        .remaja-enter-2 {
            animation: none;
            opacity: 1;
        }
    }
</style>
@endpush

@section('content')
<div class="remaja-page -mx-4 -my-4 min-h-[calc(100vh-96px)] px-4 py-5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-5">

        <section class="remaja-enter grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="rm-glass relative overflow-hidden rounded-[30px] p-5 sm:p-6">
                <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-sky-300/20 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-24 left-8 h-56 w-56 rounded-full bg-emerald-300/16 blur-3xl"></div>

                <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center">
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-[26px] bg-gradient-to-br from-sky-400 to-emerald-500 text-3xl font-black text-white shadow-[0_18px_42px_rgba(14,165,233,.20)]">
                        {{ $initial }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.11em] text-sky-700">
                                Remaja
                            </span>

                            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.11em] text-emerald-700">
                                {{ $genderLabel }}
                            </span>
                        </div>

                        <h1 class="mt-3 line-clamp-2 text-3xl font-black tracking-tight text-slate-800 sm:text-4xl">
                            {{ $remaja->nama_lengkap ?? '-' }}
                        </h1>

                        <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                            NIK {{ $remaja->nik ?? '-' }} • {{ $remaja->sekolah ?: 'Sekolah belum diisi' }}
                        </p>

                        <div class="mt-4 flex flex-wrap gap-3">
                            <a href="{{ $backRoute }}"
                               class="smooth-route inline-flex h-10 items-center justify-center rounded-2xl border border-sky-100 bg-white/82 px-4 text-xs font-black uppercase tracking-[0.12em] text-sky-700 transition hover:bg-sky-50">
                                Kembali
                            </a>

                            <a href="{{ $dashboardRoute }}"
                               class="smooth-route inline-flex h-10 items-center justify-center rounded-2xl bg-sky-500 px-4 text-xs font-black uppercase tracking-[0.12em] text-white shadow-[0_12px_26px_rgba(14,165,233,.20)] transition hover:bg-sky-600">
                                Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rm-glass rounded-[30px] p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-sky-700">
                    Analisis IMT
                </p>

                <div class="mt-4 rounded-[24px] border p-4 {{ $analysisTone }}">
                    <div class="flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[17px] bg-white/75 text-lg shadow-sm">
                            <i class="fas {{ $imtAnalysis['icon'] ?? 'fa-circle-info' }}"></i>
                        </div>

                        <div>
                            <h2 class="text-xl font-black">
                                {{ $imtAnalysis['label'] ?? 'Belum Ada Data' }}
                            </h2>

                            <p class="mt-2 text-sm font-bold leading-6 opacity-80">
                                {{ $imtAnalysis['message'] ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <p class="mt-4 text-sm font-semibold leading-6 text-slate-600">
                    {{ $imtAnalysis['suggestion'] ?? 'Lakukan pemeriksaan di Posyandu secara berkala.' }}
                </p>
            </div>
        </section>

        <section class="remaja-enter-2 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($metrics as $metric)
                <div class="rounded-[24px] border p-5 shadow-[0_10px_28px_rgba(15,23,42,.04)] backdrop-blur-xl {{ $metricTone($metric['tone'] ?? 'slate') }}">
                    <p class="text-[10px] font-black uppercase tracking-[0.16em] opacity-70">
                        {{ $metric['label'] }}
                    </p>

                    <p class="mt-3 text-2xl font-black leading-tight">
                        {{ $metric['value'] }}
                    </p>

                    <p class="mt-1 text-sm font-bold opacity-70">
                        {{ $metric['caption'] }}
                    </p>
                </div>
            @endforeach
        </section>

        <section class="remaja-enter-2 grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_340px]">

            <div class="space-y-5">
                <div class="rm-glass overflow-hidden rounded-[30px]">
                    <div class="border-b border-sky-100/80 bg-gradient-to-r from-white/86 via-sky-50/72 to-emerald-50/62 px-5 py-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.20em] text-sky-700">
                            Tren Kesehatan
                        </p>
                        <h2 class="mt-1 text-xl font-black text-slate-800">
                            Grafik Ringkas IMT
                        </h2>
                    </div>

                    <div class="p-5">
                        @if(count($trend) > 0)
                            <div class="flex h-48 items-end gap-3 rounded-[26px] border border-sky-100 bg-gradient-to-br from-white/82 via-sky-50/55 to-emerald-50/45 p-4">
                                @foreach($trend as $point)
                                    <div class="flex min-w-0 flex-1 flex-col items-center justify-end gap-2">
                                        <div class="flex h-32 w-full items-end justify-center">
                                            <div class="w-full max-w-[34px] rounded-t-2xl bg-gradient-to-t from-sky-500 to-emerald-400 shadow-[0_10px_24px_rgba(14,165,233,.18)]"
                                                 style="height: {{ $point['height'] }}%;"></div>
                                        </div>

                                        <p class="truncate text-[10px] font-black text-slate-500">
                                            {{ $point['label'] }}
                                        </p>

                                        <p class="text-[10px] font-black text-sky-700">
                                            {{ $point['imt'] ?? '-' }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-[26px] border border-dashed border-sky-300 bg-sky-50/55 p-8 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-[22px] bg-white text-xl text-sky-500 shadow-sm">
                                    <i class="fas fa-chart-column"></i>
                                </div>

                                <h3 class="mt-4 text-base font-black text-slate-800">
                                    Tren Belum Tersedia
                                </h3>

                                <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                                    Grafik akan muncul setelah ada riwayat pemeriksaan BB dan TB.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="rm-glass overflow-hidden rounded-[30px]">
                    <div class="border-b border-emerald-100/80 bg-gradient-to-r from-white/86 via-emerald-50/72 to-sky-50/62 px-5 py-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.20em] text-emerald-700">
                            Profil Remaja
                        </p>
                        <h2 class="mt-1 text-xl font-black text-slate-800">
                            Identitas dan Data Dasar
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2">
                        <div class="rm-card rounded-[22px] p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Nama</p>
                            <p class="mt-2 text-sm font-black text-slate-800">{{ $remaja->nama_lengkap ?? '-' }}</p>
                        </div>

                        <div class="rm-card rounded-[22px] p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">NIK</p>
                            <p class="mt-2 text-sm font-black text-slate-800">{{ $remaja->nik ?? '-' }}</p>
                        </div>

                        <div class="rm-card rounded-[22px] p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Tanggal Lahir</p>
                            <p class="mt-2 text-sm font-black text-slate-800">{{ $formatDate($remaja->tanggal_lahir ?? null) }}</p>
                        </div>

                        <div class="rm-card rounded-[22px] p-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Sekolah</p>
                            <p class="mt-2 text-sm font-black text-slate-800">{{ $remaja->sekolah ?: 'Belum diisi' }}</p>
                        </div>

                        <div class="rm-card rounded-[22px] p-4 sm:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Alamat</p>
                            <p class="mt-2 text-sm font-black leading-6 text-slate-800">{{ $remaja->alamat ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="rm-glass flex max-h-[780px] min-h-[480px] flex-col overflow-hidden rounded-[30px]">
                <div class="border-b border-amber-100/80 bg-gradient-to-r from-white/86 via-amber-50/72 to-sky-50/62 px-5 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.20em] text-amber-700">
                        Riwayat Klinis
                    </p>
                    <h2 class="mt-1 text-xl font-black text-slate-800">
                        Pemeriksaan Terakhir
                    </h2>
                </div>

                <div class="flex-1 space-y-3 overflow-y-auto p-4">
                    @forelse($riwayat as $kunjungan)
                        @php
                            $pem = $kunjungan->pemeriksaan;
                            $berat = $numberValue($pem->berat_badan ?? null, 'kg');
                            $tinggi = $numberValue($pem->tinggi_badan ?? null, 'cm');

                            $imtRiwayat = $pem->imt ?? null;

                            if (blank($imtRiwayat) && filled($pem->berat_badan ?? null) && filled($pem->tinggi_badan ?? null)) {
                                $meter = ((float) $pem->tinggi_badan) / 100;
                                $imtRiwayat = $meter > 0
                                    ? round(((float) $pem->berat_badan) / ($meter * $meter), 1)
                                    : null;
                            }
                        @endphp

                        <article class="rounded-[24px] border border-amber-100 bg-white/72 p-4 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-black text-slate-800">
                                        {{ $formatDate($kunjungan->tanggal_kunjungan ?? $kunjungan->created_at, 'd M Y') }}
                                    </p>

                                    <p class="mt-1 text-xs font-bold text-slate-500">
                                        Pemeriksaan Posyandu
                                    </p>
                                </div>

                                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.10em] text-emerald-700">
                                    Tervalidasi
                                </span>
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-2">
                                <div class="rounded-[18px] border border-slate-100 bg-slate-50/70 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">BB</p>
                                    <p class="mt-1 truncate text-sm font-black text-slate-800">{{ $berat }}</p>
                                </div>

                                <div class="rounded-[18px] border border-slate-100 bg-slate-50/70 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">TB</p>
                                    <p class="mt-1 truncate text-sm font-black text-slate-800">{{ $tinggi }}</p>
                                </div>

                                <div class="rounded-[18px] border border-slate-100 bg-slate-50/70 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">IMT</p>
                                    <p class="mt-1 truncate text-sm font-black text-slate-800">{{ $imtRiwayat ?: '-' }}</p>
                                </div>
                            </div>

                            <p class="mt-3 line-clamp-2 text-sm font-semibold leading-6 text-slate-500">
                                {{ $kunjungan->keluhan ?: 'Tidak ada catatan keluhan.' }}
                            </p>
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