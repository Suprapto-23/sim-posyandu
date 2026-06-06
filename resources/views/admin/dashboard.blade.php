@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page-name', 'Dashboard')
@section('page-title', 'Dashboard Admin')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    $roleStats = $roleStats ?? [
        'admin' => 0,
        'bidan' => 0,
        'kader' => 0,
        'user' => 0,
    ];

    $accountStats = $accountStats ?? [
        'total' => 0,
        'aktif' => 0,
        'nonaktif' => 0,
    ];

    $sasaranStats = $sasaranStats ?? [
        'balita' => 0,
        'remaja' => 0,
        'lansia' => 0,
        'total' => 0,
    ];

    $serviceStats = $serviceStats ?? [
        'jadwal' => 0,
        'jadwal_aktif' => 0,
        'pemeriksaan' => 0,
        'imunisasi' => 0,
        'absensi' => 0,
        'pengukuran' => 0,
        'laporan' => 0,
    ];

    $monthlySeries = $monthlySeries ?? [
        'labels' => [],
        'akun' => [],
        'pemeriksaan' => [],
        'jadwal' => [],
        'max' => 1,
    ];

    $formatDate = function ($date) {
        if (! $date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d M Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatDateTime = function ($date) {
        if (! $date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d M Y, H:i');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $roleLabel = function ($role) {
        return match ($role) {
            'admin' => 'Admin',
            'bidan' => 'Bidan',
            'kader' => 'Kader',
            'user' => 'Warga',
            default => Str::title((string) $role),
        };
    };

    $statusLabel = function ($status) {
        return $status === 'active' ? 'Aktif' : 'Nonaktif';
    };

    $statusClass = function ($status) {
        return $status === 'active'
            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
            : 'bg-rose-50 text-rose-700 border-rose-200';
    };

    $initial = function ($name) {
        return Str::upper(Str::substr(trim((string) $name), 0, 1)) ?: 'U';
    };

    $barHeight = function ($value) use ($monthlySeries) {
        $max = max((int) ($monthlySeries['max'] ?? 1), 1);
        $percent = ((int) $value / $max) * 100;

        return max(8, min(100, $percent));
    };
@endphp

@push('styles')
<style>
    .admin-dashboard-page {
        background:
            radial-gradient(circle at 8% 6%, rgba(16, 185, 129, .13), transparent 28%),
            radial-gradient(circle at 96% 4%, rgba(14, 165, 233, .12), transparent 26%),
            radial-gradient(circle at 50% 100%, rgba(245, 158, 11, .08), transparent 30%),
            linear-gradient(135deg, #f4fff9 0%, #eef9ff 48%, #f8fafc 100%);
    }

    .admin-dashboard-grid {
        background-image:
            linear-gradient(rgba(15, 23, 42, .035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, .035) 1px, transparent 1px);
        background-size: 30px 30px;
    }

    .admin-dashboard-glass {
        background: rgba(255, 255, 255, .86);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .055);
    }

    .admin-dashboard-card {
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .admin-dashboard-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 36px rgba(15, 23, 42, .075);
    }

    .admin-dashboard-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .38rem;
        border-width: 1px;
        border-radius: 999px;
        padding: .35rem .7rem;
        font-size: .68rem;
        font-weight: 950;
        letter-spacing: .06em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .admin-chart-column {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        align-items: end;
        gap: 4px;
        height: 150px;
    }

    .admin-chart-bar {
        min-height: 8px;
        border-radius: 999px 999px 6px 6px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.35);
    }

    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
            scroll-behavior: auto !important;
        }
    }
</style>
@endpush

@section('content')
<div class="admin-dashboard-page relative min-h-screen overflow-hidden px-4 py-5 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute inset-0 admin-dashboard-grid opacity-70"></div>

    <div class="relative z-10 mx-auto max-w-[1320px] space-y-5">
        <section class="admin-dashboard-glass overflow-hidden rounded-[1.75rem] border border-white/80 p-5 sm:p-6">
            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700">
                        <i class="fas fa-shield-heart"></i>
                        Ringkasan Sistem
                    </div>

                    <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                        Dashboard Admin
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm font-semibold leading-6 text-slate-600">
                        Pantau akun pengguna, data sasaran, dan aktivitas layanan utama PosyanduCare dari satu halaman ringkas.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:min-w-[360px]">
                    <a href="{{ route('admin.users.index') }}"
                       class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 transition hover:-translate-y-0.5 hover:bg-emerald-100">
                        <p class="text-[11px] font-black uppercase tracking-[.15em] text-emerald-700">
                            Akun Warga
                        </p>
                        <p class="mt-1 text-2xl font-black text-slate-950">
                            {{ number_format($roleStats['user'] ?? 0) }}
                        </p>
                    </a>

                    <a href="{{ route('admin.kaders.index') }}"
                       class="rounded-2xl border border-sky-200 bg-sky-50/80 p-4 transition hover:-translate-y-0.5 hover:bg-sky-100">
                        <p class="text-[11px] font-black uppercase tracking-[.15em] text-sky-700">
                            Akun Kader
                        </p>
                        <p class="mt-1 text-2xl font-black text-slate-950">
                            {{ number_format($roleStats['kader'] ?? 0) }}
                        </p>
                    </a>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="admin-dashboard-card admin-dashboard-glass rounded-[1.4rem] border border-emerald-200 p-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.15em] text-emerald-700">Total Akun</p>
                        <p class="mt-1 text-3xl font-black text-slate-950">{{ number_format($accountStats['total'] ?? 0) }}</p>
                        <p class="text-sm font-semibold text-slate-500">Semua pengguna sistem</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/20">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="admin-dashboard-card admin-dashboard-glass rounded-[1.4rem] border border-sky-200 p-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.15em] text-sky-700">Akun Aktif</p>
                        <p class="mt-1 text-3xl font-black text-slate-950">{{ number_format($accountStats['aktif'] ?? 0) }}</p>
                        <p class="text-sm font-semibold text-slate-500">Dapat login</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-500 text-white shadow-lg shadow-sky-500/20">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>

            <div class="admin-dashboard-card admin-dashboard-glass rounded-[1.4rem] border border-amber-200 p-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.15em] text-amber-700">Total Sasaran</p>
                        <p class="mt-1 text-3xl font-black text-slate-950">{{ number_format($sasaranStats['total'] ?? 0) }}</p>
                        <p class="text-sm font-semibold text-slate-500">Balita, Remaja, Lansia</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-lg shadow-amber-500/20">
                        <i class="fas fa-people-group"></i>
                    </div>
                </div>
            </div>

            <div class="admin-dashboard-card admin-dashboard-glass rounded-[1.4rem] border border-violet-200 p-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.15em] text-violet-700">Pemeriksaan</p>
                        <p class="mt-1 text-3xl font-black text-slate-950">{{ number_format($serviceStats['pemeriksaan'] ?? 0) }}</p>
                        <p class="text-sm font-semibold text-slate-500">Data klinis tercatat</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-500 text-white shadow-lg shadow-violet-500/20">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-5 xl:grid-cols-[minmax(0,1.15fr)_minmax(360px,.85fr)]">
            <div class="admin-dashboard-glass rounded-[1.5rem] border border-white/80 p-5">
                <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">
                            Tren 6 Bulan
                        </p>
                        <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                            Aktivitas Sistem
                        </h2>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span class="admin-dashboard-chip border-emerald-200 bg-emerald-50 text-emerald-700">Akun</span>
                        <span class="admin-dashboard-chip border-sky-200 bg-sky-50 text-sky-700">Jadwal</span>
                        <span class="admin-dashboard-chip border-violet-200 bg-violet-50 text-violet-700">Pemeriksaan</span>
                    </div>
                </div>

                <div class="grid grid-cols-6 gap-3">
                    @foreach($monthlySeries['labels'] ?? [] as $index => $label)
                        <div class="min-w-0">
                            <div class="admin-chart-column rounded-2xl border border-slate-200 bg-white/70 px-2 py-3">
                                <div class="admin-chart-bar bg-emerald-500"
                                     style="height: {{ $barHeight($monthlySeries['akun'][$index] ?? 0) }}%;"></div>
                                <div class="admin-chart-bar bg-sky-500"
                                     style="height: {{ $barHeight($monthlySeries['jadwal'][$index] ?? 0) }}%;"></div>
                                <div class="admin-chart-bar bg-violet-500"
                                     style="height: {{ $barHeight($monthlySeries['pemeriksaan'][$index] ?? 0) }}%;"></div>
                            </div>

                            <p class="mt-2 text-center text-xs font-black text-slate-500">
                                {{ $label }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="admin-dashboard-glass rounded-[1.5rem] border border-white/80 p-5">
                <div class="mb-5">
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">
                        Sasaran
                    </p>
                    <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                        Komposisi Data Sasaran
                    </h2>
                </div>

                <div class="space-y-3">
                    <div class="rounded-2xl border border-sky-200 bg-sky-50/80 p-4">
                        <div class="flex items-center justify-between">
                            <span class="font-black text-sky-800">Balita</span>
                            <span class="text-2xl font-black text-slate-950">{{ number_format($sasaranStats['balita'] ?? 0) }}</span>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-indigo-200 bg-indigo-50/80 p-4">
                        <div class="flex items-center justify-between">
                            <span class="font-black text-indigo-800">Remaja</span>
                            <span class="text-2xl font-black text-slate-950">{{ number_format($sasaranStats['remaja'] ?? 0) }}</span>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4">
                        <div class="flex items-center justify-between">
                            <span class="font-black text-emerald-800">Lansia</span>
                            <span class="text-2xl font-black text-slate-950">{{ number_format($sasaranStats['lansia'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-5 xl:grid-cols-2">
            <div class="admin-dashboard-glass rounded-[1.5rem] border border-white/80 p-5">
                <div class="mb-4 flex items-end justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">
                            Akun Terbaru
                        </p>
                        <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                            Pengguna Baru
                        </h2>
                    </div>

                    <a href="{{ route('admin.users.index') }}"
                       class="text-xs font-black uppercase tracking-[.12em] text-emerald-700 hover:text-emerald-800">
                        Kelola
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($recentUsers ?? [] as $user)
                        <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white/75 p-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-sm font-black text-white">
                                {{ $initial($user->name ?? 'U') }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-black text-slate-950">
                                    {{ $user->name ?? '-' }}
                                </p>
                                <p class="truncate text-xs font-bold text-slate-500">
                                    {{ $user->email ?? '-' }}
                                </p>
                            </div>

                            <div class="text-right">
                                <span class="admin-dashboard-chip {{ $statusClass($user->status ?? 'inactive') }}">
                                    {{ $statusLabel($user->status ?? 'inactive') }}
                                </span>
                                <p class="mt-1 text-xs font-bold text-slate-400">
                                    {{ $roleLabel($user->role ?? '-') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-white/60 p-8 text-center">
                            <p class="text-sm font-bold text-slate-500">
                                Belum ada akun terbaru.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="admin-dashboard-glass rounded-[1.5rem] border border-white/80 p-5">
                <div class="mb-4 flex items-end justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">
                            Jadwal Terbaru
                        </p>
                        <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                            Agenda Posyandu
                        </h2>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($recentJadwals ?? [] as $jadwal)
                        <div class="rounded-2xl border border-slate-200 bg-white/75 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-slate-950">
                                        {{ $jadwal->judul ?? 'Jadwal Posyandu' }}
                                    </p>

                                    <p class="mt-1 text-xs font-bold text-slate-500">
                                        {{ $formatDate($jadwal->tanggal ?? null) }}
                                        @if(! empty($jadwal->lokasi))
                                            <span class="mx-1 text-slate-300">•</span>
                                            {{ $jadwal->lokasi }}
                                        @endif
                                    </p>
                                </div>

                                <span class="admin-dashboard-chip border-sky-200 bg-sky-50 text-sky-700">
                                    {{ Str::title($jadwal->status ?? 'aktif') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-white/60 p-8 text-center">
                            <p class="text-sm font-bold text-slate-500">
                                Belum ada jadwal terbaru.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[1.4rem] border border-white/80 bg-white/75 p-4 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[.15em] text-slate-400">Jadwal</p>
                <p class="mt-1 text-2xl font-black text-slate-950">{{ number_format($serviceStats['jadwal'] ?? 0) }}</p>
                <p class="text-sm font-semibold text-slate-500">Total agenda</p>
            </div>

            <div class="rounded-[1.4rem] border border-white/80 bg-white/75 p-4 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[.15em] text-slate-400">Imunisasi</p>
                <p class="mt-1 text-2xl font-black text-slate-950">{{ number_format($serviceStats['imunisasi'] ?? 0) }}</p>
                <p class="text-sm font-semibold text-slate-500">Catatan layanan</p>
            </div>

            <div class="rounded-[1.4rem] border border-white/80 bg-white/75 p-4 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[.15em] text-slate-400">Absensi</p>
                <p class="mt-1 text-2xl font-black text-slate-950">{{ number_format($serviceStats['absensi'] ?? 0) }}</p>
                <p class="text-sm font-semibold text-slate-500">Agenda tercatat</p>
            </div>

            <div class="rounded-[1.4rem] border border-white/80 bg-white/75 p-4 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[.15em] text-slate-400">Laporan</p>
                <p class="mt-1 text-2xl font-black text-slate-950">{{ number_format($serviceStats['laporan'] ?? 0) }}</p>
                <p class="text-sm font-semibold text-slate-500">Rekap bulanan</p>
            </div>
        </section>
    </div>
</div>
@endsection