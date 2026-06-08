@extends('layouts.admin')

@section('title', 'Dashboard Analytics | Executive Control')
@section('page-name', 'Dashboard')
@section('page-title', 'Dashboard Analytics')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    $adminName = auth()->user()->name ?? 'Admin';

    $roleStats = $roleStats ?? ['admin' => 0, 'bidan' => 0, 'kader' => 0, 'user' => 0];
    $accountStats = $accountStats ?? ['total' => 0, 'aktif' => 0, 'nonaktif' => 0];
    $sasaranStats = $sasaranStats ?? ['balita' => 0, 'remaja' => 0, 'lansia' => 0, 'total' => 0];

    $monthlySeries = $monthlySeries ?? [];
    $chartLabels = array_values($monthlySeries['labels'] ?? []);

    if (empty($chartLabels)) {
        $chartLabels = collect(range(5, 0))
            ->map(fn ($i) => Carbon::now()->subMonths($i)->translatedFormat('M'))
            ->values()
            ->all();
    }

    $chartCount = count($chartLabels);

    $normalizeSeries = function (string $key) use ($monthlySeries, $chartCount) {
        $data = array_values($monthlySeries[$key] ?? []);
        return array_pad(array_slice($data, 0, $chartCount), $chartCount, 0);
    };

    $chartBidan = $normalizeSeries('bidan');
    $chartKader = $normalizeSeries('kader');
    $chartBalita = $normalizeSeries('balita');
    $chartRemaja = $normalizeSeries('remaja');
    $chartLansia = $normalizeSeries('lansia');
    $chartMax = max(array_merge($chartBidan, $chartKader, $chartBalita, $chartRemaja, $chartLansia, [1]));

    $formatDate = fn ($date) => $date ? Carbon::parse($date)->translatedFormat('d M Y, H:i') : '-';

    $roleBadgeClasses = fn ($role) => match ($role) {
        'admin' => 'bg-slate-900 text-white border-slate-950',
        'bidan' => 'bg-amber-50 text-amber-700 border-amber-200/60 shadow-sm',
        'kader' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60 shadow-sm',
        'user' => 'bg-sky-50 text-sky-700 border-sky-200/60 shadow-sm',
        default => 'bg-slate-50 text-slate-600 border-slate-200',
    };

    $roleLabel = fn ($role) => match ($role) {
        'admin' => 'Administrator',
        'bidan' => 'Bidan Desa',
        'kader' => 'Kader Posyandu',
        'user' => 'Warga',
        default => Str::title((string) $role),
    };

    $statusLabel = fn ($status) => $status === 'active' ? 'Aktif' : 'Nonaktif';
    $statusClass = fn ($status) => $status === 'active' ? 'bg-emerald-500' : 'bg-slate-300';
    $initial = fn ($name) => Str::upper(Str::substr(trim((string) $name), 0, 1)) ?: 'U';
    $barHeight = fn ($value) => max(2, min(100, (((int) $value / $chartMax) * 100)));

    $sasaranTotal = max(1, $sasaranStats['total'] ?? 1);
    $balitaPercent = (($sasaranStats['balita'] ?? 0) / $sasaranTotal) * 100;
    $remajaPercent = (($sasaranStats['remaja'] ?? 0) / $sasaranTotal) * 100;
    $lansiaPercent = (($sasaranStats['lansia'] ?? 0) / $sasaranTotal) * 100;
@endphp

@push('styles')
<style>
    body { background-color: #F8FAFC; }

    .nexus-animate-up {
        animation: nexusFadeUp .5s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }

    @keyframes nexusFadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .d-1 { animation-delay: 70ms; }
    .d-2 { animation-delay: 120ms; }
    .d-3 { animation-delay: 170ms; }

    .executive-header {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        padding: 34px 42px;
        background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 48%, #10b981 100%);
        border: 1px solid rgba(255,255,255,.22);
        box-shadow: 0 24px 55px -24px rgba(14,165,233,.65);
    }

    .executive-header::after {
        content: "";
        position: absolute;
        right: -70px;
        top: -80px;
        width: 260px;
        height: 260px;
        border-radius: 999px;
        background: rgba(255,255,255,.16);
        filter: blur(55px);
        pointer-events: none;
    }

    .executive-mesh {
        position: absolute;
        inset: 0;
        opacity: .16;
        pointer-events: none;
        background-image: radial-gradient(rgba(255,255,255,.55) 1px, transparent 1px);
        background-size: 24px 24px;
    }

    .executive-content {
        position: relative;
        z-index: 2;
        max-width: 850px;
    }

    .executive-chip,
    .clock-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 30px;
        padding: 0 14px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.26);
        background: rgba(255,255,255,.16);
        color: rgba(255,255,255,.94);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.14);
        backdrop-filter: blur(12px);
        font-weight: 900;
        white-space: nowrap;
    }

    .executive-chip {
        font-size: 10px;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .clock-chip {
        font-size: 11px;
        font-weight: 800;
    }

    .executive-chip i,
    .clock-chip i {
        color: #ffffff !important;
        opacity: .92;
    }

    .executive-title {
        margin: 22px 0 0;
        color: #ffffff;
        font-size: clamp(30px, 3.6vw, 48px);
        font-weight: 900;
        line-height: 1.08;
        letter-spacing: -.045em;
        text-shadow: 0 8px 22px rgba(15,23,42,.12);
        font-family: "Poppins", sans-serif;
    }

    .executive-desc {
        margin-top: 16px;
        max-width: 780px;
        color: rgba(255,255,255,.90);
        font-size: 15px;
        font-weight: 650;
        line-height: 1.75;
    }

    .premium-glass-card {
        background: rgba(255,255,255,.95);
        backdrop-filter: blur(16px);
        border-radius: 24px;
        box-shadow: 0 4px 25px -10px rgba(15,23,42,.05);
        transition: all .22s cubic-bezier(.16, 1, .3, 1);
    }

    .premium-glass-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 35px -8px rgba(15,23,42,.08);
    }

    .chart-panel {
        position: relative;
        height: 220px;
        display: flex;
        align-items: flex-end;
        gap: 8px;
        padding-bottom: 4px;
        border-bottom: 2px solid #E2E8F0;
    }

    .chart-grid-line {
        position: absolute;
        left: 0;
        right: 0;
        height: 1px;
        background-color: rgba(226,232,240,.8);
        z-index: 0;
    }

    .chart-bar-column {
        flex: 1;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: flex-end;
        gap: 3px;
        z-index: 10;
    }

    .bar-pill {
        width: 8px;
        border-radius: 3px 3px 0 0;
        transition: all .28s cubic-bezier(.16, 1, .3, 1);
    }

    .bar-pill:hover {
        filter: brightness(1.12);
        transform: scaleY(1.05);
        transform-origin: bottom;
    }

    .premium-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
    .premium-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .premium-scrollbar::-webkit-scrollbar-thumb { background-color: #E2E8F0; border-radius: 10px; }
    .premium-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #CBD5E1; }

    @media (min-width: 1024px) {
        .bar-pill { width: 10px; }
        .chart-bar-column { gap: 4px; }
    }

    @media (max-width: 768px) {
        .executive-header {
            padding: 28px 24px;
            border-radius: 22px;
        }

        .executive-title {
            margin-top: 18px;
            font-size: 30px;
        }

        .executive-desc {
            font-size: 13px;
            line-height: 1.65;
        }

        .clock-chip {
            margin-top: 8px;
        }
    }
</style>
@endpush

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8 max-w-[1360px] mx-auto space-y-6">

    <div class="executive-header nexus-animate-up">
        <div class="executive-mesh"></div>
        <div class="absolute -right-12 -bottom-12 w-72 h-72 bg-emerald-500/10 rounded-full filter blur-[80px] pointer-events-none"></div>

        <div class="executive-content">
            <div class="flex flex-wrap items-center gap-3">
                <span class="executive-chip">
                    <i class="fas fa-crown"></i>
                    Mode Administrator
                </span>

                <span id="realtime-clock" class="clock-chip">
                    <i class="far fa-clock"></i>
                    Memuat waktu...
                </span>
            </div>

            <h1 class="executive-title">
                <span id="dynamic-greeting">Halo</span>, {{ $adminName }}!
                <span id="dynamic-emoji">👋</span>
            </h1>

            <p class="executive-desc">
                Pusat Kendali Akses & Pengguna PosyanduCare. Pantau secara menyeluruh statistik pendaftaran akun warga,
                verifikasi hak akses tenaga kesehatan, serta analisis demografi pengguna.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 sm:gap-5 nexus-animate-up d-1">

        <div class="premium-glass-card p-5 flex flex-col justify-start group">
            <div class="w-12 h-12 rounded-[14px] bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-users"></i>
            </div>
            <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Akun Warga</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tight mt-1">{{ number_format($roleStats['user'] ?? 0) }}</h3>
        </div>

        <div class="premium-glass-card p-5 flex flex-col justify-start group">
            <div class="w-12 h-12 rounded-[14px] bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-user-nurse"></i>
            </div>
            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Akun Kader</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tight mt-1">{{ number_format($roleStats['kader'] ?? 0) }}</h3>
        </div>

        <div class="premium-glass-card p-5 flex flex-col justify-start group">
            <div class="w-12 h-12 rounded-[14px] bg-amber-50 text-amber-500 flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-user-md"></i>
            </div>
            <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest">Akun Bidan</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tight mt-1">{{ number_format($roleStats['bidan'] ?? 0) }}</h3>
        </div>

        <div class="premium-glass-card p-5 flex flex-col justify-start group">
            <div class="w-12 h-12 rounded-[14px] bg-emerald-500 text-white flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform shadow-lg shadow-emerald-500/30">
                <i class="fas fa-user-check"></i>
            </div>
            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Terverifikasi</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tight mt-1">{{ number_format($accountStats['aktif'] ?? 0) }}</h3>
        </div>

        <div class="premium-glass-card p-5 flex flex-col justify-start group">
            <div class="w-12 h-12 rounded-[14px] bg-rose-50 text-rose-500 flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-user-lock"></i>
            </div>
            <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Nonaktif</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tight mt-1">{{ number_format($accountStats['nonaktif'] ?? 0) }}</h3>
        </div>

        <div class="premium-glass-card p-5 flex flex-col justify-start group">
            <div class="w-12 h-12 rounded-[14px] bg-sky-50 text-sky-500 flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-users"></i>
            </div>
            <p class="text-[10px] font-black text-sky-600 uppercase tracking-widest">Total Sasaran</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tight mt-1">{{ number_format($sasaranStats['total'] ?? 0) }}</h3>
        </div>

    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[2fr_1fr] gap-6 nexus-animate-up d-2">

        <div class="premium-glass-card p-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-lg font-black text-slate-800 tracking-tight">Tren Pertumbuhan Pengguna</h2>
                    <p class="text-xs font-semibold text-slate-400 mt-1">
                        Komparasi data registrasi per peran dalam 6 bulan terakhir.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 bg-slate-50/80 rounded-2xl px-4 py-2 border border-slate-100 shadow-sm">
                    <span class="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest text-slate-500"><div class="w-2.5 h-2.5 rounded-sm bg-amber-400"></div> Bidan</span>
                    <span class="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest text-slate-500"><div class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></div> Kader</span>
                    <span class="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest text-slate-500"><div class="w-2.5 h-2.5 rounded-sm bg-sky-400"></div> Balita</span>
                    <span class="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest text-slate-500"><div class="w-2.5 h-2.5 rounded-sm bg-blue-600"></div> Remaja</span>
                    <span class="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest text-slate-500"><div class="w-2.5 h-2.5 rounded-sm bg-teal-500"></div> Lansia</span>
                </div>
            </div>

            <div class="chart-panel">
                <div class="chart-grid-line" style="bottom: 25%"></div>
                <div class="chart-grid-line" style="bottom: 50%"></div>
                <div class="chart-grid-line" style="bottom: 75%"></div>
                <div class="chart-grid-line" style="bottom: 100%"></div>

                @foreach($chartLabels as $index => $label)
                    @php
                        $tooltipPosition = 'left-1/2 -translate-x-1/2';
                        if ($loop->first) $tooltipPosition = 'left-0 translate-x-0';
                        elseif ($loop->last) $tooltipPosition = 'right-0 translate-x-0';
                    @endphp

                    <div class="chart-bar-column group relative">
                        <div class="absolute -top-24 {{ $tooltipPosition }} opacity-0 group-hover:opacity-100 transition-all duration-300 bg-white border border-slate-100 text-slate-700 text-[10px] py-2.5 px-3.5 rounded-xl font-black z-30 shadow-[0_10px_25px_rgba(0,0,0,0.15)] pointer-events-none text-left min-w-[170px]">
                            <span class="text-slate-400 border-b border-slate-100 pb-1.5 mb-1.5 block">{{ $label }}</span>
                            <div class="grid grid-cols-2 gap-x-2 gap-y-1.5">
                                <div><span class="text-amber-500 mr-1">■</span> Bidan: {{ $chartBidan[$index] ?? 0 }}</div>
                                <div><span class="text-emerald-500 mr-1">■</span> Kader: {{ $chartKader[$index] ?? 0 }}</div>
                                <div><span class="text-sky-500 mr-1">■</span> Balita: {{ $chartBalita[$index] ?? 0 }}</div>
                                <div><span class="text-blue-600 mr-1">■</span> Remaja: {{ $chartRemaja[$index] ?? 0 }}</div>
                                <div class="col-span-2 pt-0.5"><span class="text-teal-500 mr-1">■</span> Lansia: {{ $chartLansia[$index] ?? 0 }}</div>
                            </div>
                        </div>

                        <div class="bar-pill bg-gradient-to-t from-amber-500 to-amber-300" style="height: {{ $barHeight($chartBidan[$index] ?? 0) }}%;"></div>
                        <div class="bar-pill bg-gradient-to-t from-emerald-600 to-emerald-400" style="height: {{ $barHeight($chartKader[$index] ?? 0) }}%;"></div>
                        <div class="bar-pill bg-gradient-to-t from-sky-500 to-sky-300" style="height: {{ $barHeight($chartBalita[$index] ?? 0) }}%;"></div>
                        <div class="bar-pill bg-gradient-to-t from-blue-700 to-blue-500" style="height: {{ $barHeight($chartRemaja[$index] ?? 0) }}%;"></div>
                        <div class="bar-pill bg-gradient-to-t from-teal-600 to-teal-400" style="height: {{ $barHeight($chartLansia[$index] ?? 0) }}%;"></div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-between items-center mt-3 px-1">
                @foreach($chartLabels as $label)
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex-1 text-center">{{ $label }}</div>
                @endforeach
            </div>
        </div>

        <div class="premium-glass-card p-8 flex flex-col">
            <div class="mb-6">
                <h2 class="text-lg font-black text-slate-800 tracking-tight">Kategori Demografi</h2>
                <p class="text-xs font-semibold text-slate-400 mt-1">Penyebaran sasaran posyandu aktif</p>
            </div>

            <div class="flex-1 space-y-6 flex flex-col justify-center">
                <div>
                    <div class="flex justify-between items-end mb-2">
                        <span class="font-bold text-slate-600 text-sm flex items-center gap-2">
                            <i class="fas fa-child text-sky-500"></i>
                            Balita
                        </span>
                        <span class="font-black text-slate-800 text-lg">
                            {{ number_format($sasaranStats['balita'] ?? 0) }}
                            <span class="text-[10px] text-slate-400">Jiwa</span>
                        </span>
                    </div>
                    <div class="w-full bg-slate-100/80 rounded-full h-2">
                        <div class="bg-sky-500 h-full rounded-full" style="width: {{ $balitaPercent }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-end mb-2">
                        <span class="font-bold text-slate-600 text-sm flex items-center gap-2">
                            <i class="fas fa-user-graduate text-blue-600"></i>
                            Remaja
                        </span>
                        <span class="font-black text-slate-800 text-lg">
                            {{ number_format($sasaranStats['remaja'] ?? 0) }}
                            <span class="text-[10px] text-slate-400">Jiwa</span>
                        </span>
                    </div>
                    <div class="w-full bg-slate-100/80 rounded-full h-2">
                        <div class="bg-blue-600 h-full rounded-full" style="width: {{ $remajaPercent }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-end mb-2">
                        <span class="font-bold text-slate-600 text-sm flex items-center gap-2">
                            <i class="fas fa-person-cane text-teal-500"></i>
                            Lansia
                        </span>
                        <span class="font-black text-slate-800 text-lg">
                            {{ number_format($sasaranStats['lansia'] ?? 0) }}
                            <span class="text-[10px] text-slate-400">Jiwa</span>
                        </span>
                    </div>
                    <div class="w-full bg-slate-100/80 rounded-full h-2">
                        <div class="bg-teal-500 h-full rounded-full" style="width: {{ $lansiaPercent }}%"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="premium-glass-card flex flex-col h-[450px] nexus-animate-up d-3">
        <div class="p-6 md:p-8 border-b border-slate-100/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-black text-slate-800 tracking-tight">Log Registrasi Akun Baru</h2>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">
                    Pemantauan masuknya entitas bidan, kader, dan warga
                </p>
            </div>

            <a href="{{ route('admin.users.index') }}" class="text-[10px] font-black text-white hover:bg-emerald-700 uppercase tracking-widest bg-emerald-600 px-5 py-2.5 rounded-xl transition-all shadow-md inline-flex items-center gap-2">
                Kelola Akses <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="flex-1 overflow-auto premium-scrollbar p-0">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="py-3 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Profil Pengguna</th>
                        <th class="py-3 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Kontak</th>
                        <th class="py-3 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Peran Akses</th>
                        <th class="py-3 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Status</th>
                        <th class="py-3 px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Waktu Daftar</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100/50">
                    @forelse($recentUsers ?? [] as $user)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 font-black flex items-center justify-center shrink-0 shadow-inner text-sm group-hover:bg-blue-50 group-hover:text-blue-600 transition-colors">
                                        {{ $initial($user->name ?? 'U') }}
                                    </div>

                                    <p class="text-sm font-black text-slate-800">{{ $user->name ?? '-' }}</p>
                                </div>
                            </td>

                            <td class="py-4 px-6">
                                <p class="text-xs font-semibold text-slate-500">{{ $user->email ?? '-' }}</p>
                            </td>

                            <td class="py-4 px-6">
                                <span class="px-3 py-1 text-[9px] font-black uppercase tracking-wider rounded-md border {{ $roleBadgeClasses($user->role ?? 'user') }}">
                                    {{ $roleLabel($user->role ?? 'user') }}
                                </span>
                            </td>

                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $statusClass($user->status ?? 'inactive') }}"></span>
                                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                        {{ $statusLabel($user->status ?? 'inactive') }}
                                    </p>
                                </div>
                            </td>

                            <td class="py-4 px-6">
                                <p class="text-xs font-semibold text-slate-400">
                                    {{ $formatDate($user->created_at ?? null) }}
                                </p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400 opacity-60">
                                    <i class="fas fa-user-shield text-3xl mb-3"></i>
                                    <p class="text-xs font-bold uppercase tracking-widest mt-2">
                                        Belum ada registrasi baru
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const clockEl = document.getElementById('realtime-clock');
    const greetingEl = document.getElementById('dynamic-greeting');
    const emojiEl = document.getElementById('dynamic-emoji');

    function updateClockAndGreeting() {
        const now = new Date();

        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        const dayName = days[now.getDay()];
        const date = String(now.getDate()).padStart(2, '0');
        const month = months[now.getMonth()];
        const year = now.getFullYear();

        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        if (clockEl) {
            clockEl.innerHTML = `<i class="far fa-clock"></i> ${dayName}, ${date} ${month} ${year} • ${hours}:${minutes}:${seconds} WIB`;
        }

        const h = now.getHours();
        let greeting = 'Selamat Malam';
        let emoji = '🌙';

        if (h >= 5 && h < 11) {
            greeting = 'Selamat Pagi';
            emoji = '🌅';
        } else if (h >= 11 && h < 15) {
            greeting = 'Selamat Siang';
            emoji = '☀️';
        } else if (h >= 15 && h < 18) {
            greeting = 'Selamat Sore';
            emoji = '🌇';
        }

        if (greetingEl) greetingEl.innerText = greeting;
        if (emojiEl) emojiEl.innerText = emoji;
    }

    updateClockAndGreeting();
    setInterval(updateClockAndGreeting, 1000);
});
</script>
@endpush