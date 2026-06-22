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

    $chartBidan   = $normalizeSeries('bidan');
    $chartKader   = $normalizeSeries('kader');
    $chartBalita  = $normalizeSeries('balita');
    $chartRemaja  = $normalizeSeries('remaja');
    $chartLansia  = $normalizeSeries('lansia');

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

    $sasaranTotal = max(1, $sasaranStats['total'] ?? 1);
    $balitaPercent = (($sasaranStats['balita'] ?? 0) / $sasaranTotal) * 100;
    $remajaPercent = (($sasaranStats['remaja'] ?? 0) / $sasaranTotal) * 100;
    $lansiaPercent = (($sasaranStats['lansia'] ?? 0) / $sasaranTotal) * 100;

    // Warna untuk chart
    $chartColors = ['#f59e0b', '#10b981', '#38bdf8', '#2563eb', '#14b8a6'];
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

    .premium-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
    .premium-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .premium-scrollbar::-webkit-scrollbar-thumb { background-color: #E2E8F0; border-radius: 10px; }
    .premium-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #CBD5E1; }

    /* ApexCharts custom */
    #trendChart {
        min-height: 280px;
        width: 100%;
    }
    .apexcharts-canvas {
        border-radius: 12px;
    }
    .apexcharts-tooltip {
        border-radius: 12px !important;
        box-shadow: 0 12px 30px rgba(15,23,42,.12) !important;
        border: 1px solid rgba(226,232,240,.8) !important;
        background: rgba(255,255,255,.98) !important;
        backdrop-filter: blur(4px) !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }
    .apexcharts-tooltip-title {
        font-weight: 800 !important;
        color: #0f172a !important;
    }
    .apexcharts-tooltip-text-y-label {
        font-weight: 700 !important;
        color: #64748b !important;
    }
    .apexcharts-tooltip-text-y-value {
        font-weight: 900 !important;
        color: #0f172a !important;
    }
    .apexcharts-legend-text {
        font-weight: 700 !important;
        color: #475569 !important;
        font-size: 12px !important;
    }

    /* Stat card icons */
    .stat-icon {
        transition: transform .2s cubic-bezier(.16, 1, .3, 1);
    }
    .group:hover .stat-icon {
        transform: scale(1.08) rotate(-2deg);
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
        #trendChart {
            min-height: 220px;
        }
    }
    @media (max-width: 640px) {
        #trendChart {
            min-height: 180px;
        }
        .premium-glass-card {
            border-radius: 18px;
        }
    }
</style>
@endpush

@section('content')
<div class="px-4 py-6 sm:px-6 lg:px-8 max-w-[1360px] mx-auto space-y-6">

    {{-- HEADER --}}
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

    {{-- STATISTIK CARD (6 kartu) --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 sm:gap-5 nexus-animate-up d-1">

        <div class="premium-glass-card p-5 flex flex-col justify-start group">
            <div class="w-12 h-12 rounded-[14px] bg-blue-50 text-blue-600 flex items-center justify-center text-xl mb-3 stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Akun Warga</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tight mt-1">{{ number_format($roleStats['user'] ?? 0) }}</h3>
        </div>

        <div class="premium-glass-card p-5 flex flex-col justify-start group">
            <div class="w-12 h-12 rounded-[14px] bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl mb-3 stat-icon">
                <i class="fas fa-user-nurse"></i>
            </div>
            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Akun Kader</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tight mt-1">{{ number_format($roleStats['kader'] ?? 0) }}</h3>
        </div>

        <div class="premium-glass-card p-5 flex flex-col justify-start group">
            <div class="w-12 h-12 rounded-[14px] bg-amber-50 text-amber-500 flex items-center justify-center text-xl mb-3 stat-icon">
                <i class="fas fa-user-md"></i>
            </div>
            <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest">Akun Bidan</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tight mt-1">{{ number_format($roleStats['bidan'] ?? 0) }}</h3>
        </div>

        <div class="premium-glass-card p-5 flex flex-col justify-start group">
            <div class="w-12 h-12 rounded-[14px] bg-emerald-500 text-white flex items-center justify-center text-xl mb-3 stat-icon shadow-lg shadow-emerald-500/30">
                <i class="fas fa-user-check"></i>
            </div>
            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Terverifikasi</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tight mt-1">{{ number_format($accountStats['aktif'] ?? 0) }}</h3>
        </div>

        <div class="premium-glass-card p-5 flex flex-col justify-start group">
            <div class="w-12 h-12 rounded-[14px] bg-rose-50 text-rose-500 flex items-center justify-center text-xl mb-3 stat-icon">
                <i class="fas fa-user-lock"></i>
            </div>
            <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Nonaktif</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tight mt-1">{{ number_format($accountStats['nonaktif'] ?? 0) }}</h3>
        </div>

        <div class="premium-glass-card p-5 flex flex-col justify-start group">
            <div class="w-12 h-12 rounded-[14px] bg-sky-50 text-sky-500 flex items-center justify-center text-xl mb-3 stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <p class="text-[10px] font-black text-sky-600 uppercase tracking-widest">Total Sasaran</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tight mt-1">{{ number_format($sasaranStats['total'] ?? 0) }}</h3>
        </div>

    </div>

    {{-- CHART + DEMOGRAFI --}}
    <div class="grid grid-cols-1 xl:grid-cols-[2fr_1fr] gap-6 nexus-animate-up d-2">

        {{-- CHART (tanpa data label) --}}
        <div class="premium-glass-card p-6 md:p-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <div>
                    <h2 class="text-lg font-black text-slate-800 tracking-tight">Tren Pertumbuhan Pengguna</h2>
                    <p class="text-xs font-semibold text-slate-400 mt-1">
                        Komparasi data registrasi per peran dalam 6 bulan terakhir
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 bg-slate-50/80 rounded-2xl px-4 py-2 border border-slate-100 shadow-sm">
                    <span class="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest text-slate-500"><span class="w-2.5 h-2.5 rounded-sm bg-amber-400"></span> Bidan</span>
                    <span class="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest text-slate-500"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span> Kader</span>
                    <span class="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest text-slate-500"><span class="w-2.5 h-2.5 rounded-sm bg-sky-400"></span> Balita</span>
                    <span class="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest text-slate-500"><span class="w-2.5 h-2.5 rounded-sm bg-blue-600"></span> Remaja</span>
                    <span class="flex items-center gap-1.5 text-[9px] font-black uppercase tracking-widest text-slate-500"><span class="w-2.5 h-2.5 rounded-sm bg-teal-500"></span> Lansia</span>
                </div>
            </div>

            <div id="trendChart"></div>
        </div>

        {{-- DEMOGRAFI (dengan progress bar) --}}
        <div class="premium-glass-card p-6 md:p-8 flex flex-col">
            <div class="mb-6">
                <h2 class="text-lg font-black text-slate-800 tracking-tight">Kategori Demografi</h2>
                <p class="text-xs font-semibold text-slate-400 mt-1">Penyebaran sasaran posyandu aktif</p>
            </div>

            <div class="flex-1 space-y-6 flex flex-col justify-center">
                <div>
                    <div class="flex justify-between items-end mb-2">
                        <span class="font-bold text-slate-600 text-sm flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-sky-500"></span>
                            Balita
                        </span>
                        <span class="font-black text-slate-800 text-lg">
                            {{ number_format($sasaranStats['balita'] ?? 0) }}
                            <span class="text-[10px] text-slate-400">jiwa</span>
                        </span>
                    </div>
                    <div class="w-full bg-slate-100/80 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-sky-500 h-full rounded-full transition-all duration-700" style="width: {{ $balitaPercent }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-end mb-2">
                        <span class="font-bold text-slate-600 text-sm flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-blue-600"></span>
                            Remaja
                        </span>
                        <span class="font-black text-slate-800 text-lg">
                            {{ number_format($sasaranStats['remaja'] ?? 0) }}
                            <span class="text-[10px] text-slate-400">jiwa</span>
                        </span>
                    </div>
                    <div class="w-full bg-slate-100/80 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-blue-600 h-full rounded-full transition-all duration-700" style="width: {{ $remajaPercent }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-end mb-2">
                        <span class="font-bold text-slate-600 text-sm flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-teal-500"></span>
                            Lansia
                        </span>
                        <span class="font-black text-slate-800 text-lg">
                            {{ number_format($sasaranStats['lansia'] ?? 0) }}
                            <span class="text-[10px] text-slate-400">jiwa</span>
                        </span>
                    </div>
                    <div class="w-full bg-slate-100/80 rounded-full h-2.5 overflow-hidden">
                        <div class="bg-teal-500 h-full rounded-full transition-all duration-700" style="width: {{ $lansiaPercent }}%"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- TABLE --}}
    <div class="premium-glass-card flex flex-col max-h-[480px] nexus-animate-up d-3">
        <div class="p-6 md:p-8 border-b border-slate-100/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-black text-slate-800 tracking-tight">Log Registrasi Akun Baru</h2>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">
                    Pemantauan masuknya entitas bidan, kader, dan warga
                </p>
            </div>

            <a href="{{ route('admin.users.index') }}" class="text-[10px] font-black text-white hover:bg-emerald-700 uppercase tracking-widest bg-emerald-600 px-5 py-2.5 rounded-xl transition-all shadow-md inline-flex items-center gap-2 flex-shrink-0">
                Kelola Akses <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="flex-1 overflow-auto premium-scrollbar p-0">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50 sticky top-0 z-10">
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
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Clock & Greeting ---
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

    // --- ApexCharts ---
    const chartLabels = @json($chartLabels);
    const chartBidan   = @json($chartBidan);
    const chartKader   = @json($chartKader);
    const chartBalita  = @json($chartBalita);
    const chartRemaja  = @json($chartRemaja);
    const chartLansia  = @json($chartLansia);

    const colors = ['#f59e0b', '#10b981', '#38bdf8', '#2563eb', '#14b8a6'];
    const strokeColors = ['#f59e0b', '#10b981', '#38bdf8', '#2563eb', '#14b8a6'];

    const options = {
        series: [
            { name: 'Bidan', data: chartBidan },
            { name: 'Kader', data: chartKader },
            { name: 'Balita', data: chartBalita },
            { name: 'Remaja', data: chartRemaja },
            { name: 'Lansia', data: chartLansia }
        ],
        chart: {
            type: 'area',
            height: 300,
            fontFamily: 'Plus Jakarta Sans, system-ui, sans-serif',
            toolbar: { show: false },
            zoom: { enabled: false },
            background: 'transparent',
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 600,
                dynamicAnimation: { speed: 300 }
            }
        },
        colors: colors,
        dataLabels: { enabled: false }, // <-- HAPUS ANGKA DI ATAS CHART
        stroke: {
            curve: 'smooth',
            width: 2.5
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.35,
                opacityTo: 0.02,
                stops: [0, 90, 100]
            }
        },
        grid: {
            borderColor: 'rgba(226, 232, 240, 0.5)',
            strokeDashArray: 5,
            xaxis: { lines: { show: true } },
            yaxis: { lines: { show: true } }
        },
        xaxis: {
            categories: chartLabels,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px',
                    fontWeight: 700
                },
                rotate: 0,
                trim: false
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#64748b',
                    fontSize: '12px',
                    fontWeight: 700
                },
                formatter: function (val) {
                    return Math.round(val);
                }
            },
            forceNiceScale: true,
            min: 0
        },
        legend: {
            position: 'top',
            horizontalAlign: 'center',
            fontSize: '12px',
            fontWeight: 700,
            markers: {
                radius: 4,
                width: 10,
                height: 10
            },
            itemMargin: {
                horizontal: 12,
                vertical: 4
            },
            offsetY: 8
        },
        tooltip: {
            theme: 'light',
            x: {
                show: true,
                format: 'dd MMM yyyy'
            },
            y: {
                formatter: function (val, { series, seriesIndex, dataPointIndex, w }) {
                    return val + ' registrasi';
                }
            },
            marker: { show: true },
            style: {
                fontSize: '13px',
                fontFamily: 'Plus Jakarta Sans, sans-serif'
            }
        },
        markers: {
            size: 4,
            strokeWidth: 2,
            strokeColors: strokeColors,
            hover: {
                size: 6,
                sizeOffset: 2
            }
        },
        responsive: [
            {
                breakpoint: 640,
                options: {
                    chart: { height: 220 },
                    legend: { position: 'bottom', horizontalAlign: 'center' },
                    xaxis: { labels: { fontSize: '10px' } },
                    yaxis: { labels: { fontSize: '10px' } }
                }
            }
        ]
    };

    const chart = new ApexCharts(document.querySelector("#trendChart"), options);
    chart.render();

    // Resize observer
    let resizeTimer;
    const resizeObserver = new ResizeObserver(() => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            const w = window.innerWidth;
            const newHeight = w < 640 ? 220 : 300;
            chart.updateOptions({ chart: { height: newHeight } });
        }, 100);
    });
    const chartContainer = document.querySelector("#trendChart");
    if (chartContainer) resizeObserver.observe(chartContainer);

    window.addEventListener('beforeunload', () => resizeObserver.disconnect());
});
</script>
@endpush