@extends('layouts.bidan')

@section('title', 'Pemeriksaan Klinis')
@section('page-name', 'Pemeriksaan Klinis')
@section('page-title', 'Pemeriksaan Klinis')

@php
    use Carbon\Carbon;

    // --- LOGIKA DATA (TIDAK DIUBAH) ---
    $pemeriksaans = $pemeriksaans ?? collect();
    $tab = $tab ?? request('tab', 'pending');
    $kategori = $kategori ?? request('kategori', 'semua');
    $search = $search ?? request('search', '');

    $kategoriOptions = $kategoriOptions ?? [
        'balita' => ['label' => 'Balita', 'icon' => 'ph-baby', 'desc' => 'Pemeriksaan pertumbuhan Balita.'],
        'remaja' => ['label' => 'Remaja', 'icon' => 'ph-user-focus', 'desc' => 'Pemeriksaan kesehatan Remaja.'],
        'lansia' => ['label' => 'Lansia', 'icon' => 'ph-heartbeat', 'desc' => 'Pemeriksaan kesehatan Lansia.'],
    ];

    $stats = $stats ?? [
        'total' => 0, 'pending' => 0, 'verified' => 0, 'bulan_ini' => 0,
        'kategori' => ['balita' => 0, 'remaja' => 0, 'lansia' => 0],
    ];

    $getValue = function ($item, array $keys, mixed $default = '-') {
        foreach ($keys as $key) {
            $value = data_get($item, $key);
            if ($value !== null && $value !== '') return $value;
        }
        return $default;
    };

    $formatDate = function ($date) {
        if (!$date || $date === '-') return '-';
        try { return Carbon::parse($date)->translatedFormat('d M Y'); } catch (\Throwable $e) { return '-'; }
    };

    $formatDateTime = function ($date) {
        if (!$date || $date === '-') return '-';
        try { return Carbon::parse($date)->translatedFormat('d M Y, H:i') . ' WIB'; } catch (\Throwable $e) { return '-'; }
    };

    $displayValue = function (mixed $value, string $suffix = '') {
        if ($value === null || $value === '' || $value === '-') return '-';
        return trim((string) $value . ' ' . $suffix);
    };

    $getPasien = function ($item) {
        return data_get($item, 'kunjungan.pasien') ?? data_get($item, 'balita') ?? data_get($item, 'remaja') ?? data_get($item, 'lansia') ?? null;
    };

    $getKategori = function ($item) use ($getValue) {
        $raw = strtolower((string) $getValue($item, ['kategori_pasien'], ''));
        if (in_array($raw, ['balita', 'remaja', 'lansia'], true)) return $raw;
        if (data_get($item, 'balita')) return 'balita';
        if (data_get($item, 'remaja')) return 'remaja';
        if (data_get($item, 'lansia')) return 'lansia';
        $pasien = data_get($item, 'kunjungan.pasien');
        if ($pasien) {
            $class = strtolower(class_basename($pasien));
            if (in_array($class, ['balita', 'remaja', 'lansia'], true)) return $class;
        }
        return 'balita';
    };

    $getNamaPasien = function ($item) use ($getPasien, $getValue) {
        $pasien = $getPasien($item);
        if ($pasien) return $getValue($pasien, ['nama_lengkap', 'nama', 'nama_balita', 'nama_remaja', 'nama_lansia'], 'Nama tidak tersedia');
        return $getValue($item, ['nama_pasien', 'nama'], 'Nama tidak tersedia');
    };

    $getNikPasien = function ($item) use ($getPasien, $getValue) {
        $pasien = $getPasien($item);
        if ($pasien) return $getValue($pasien, ['nik', 'nik_anak'], '-');
        return $getValue($item, ['nik', 'nik_pasien', 'nik_anak'], '-');
    };

    $getPetugas = function ($item) {
        return data_get($item, 'pemeriksa.name') ?? data_get($item, 'pemeriksa.nama') ?? data_get($item, 'kunjungan.petugas.name') ?? data_get($item, 'kunjungan.petugas.nama') ?? '-';
    };

    $getVerifikator = function ($item) {
        return data_get($item, 'verifikator.name') ?? data_get($item, 'verifikator.nama') ?? data_get($item, 'verifikatorLegacy.name') ?? data_get($item, 'verifikatorLegacy.nama') ?? '-';
    };

    $isVerified = function ($item) use ($getValue) {
        $status = strtolower((string) $getValue($item, ['status_verifikasi'], ''));
        return in_array($status, ['verified', 'tervalidasi', 'approved'], true);
    };

    $statusLabel = function ($item) use ($isVerified, $getValue) {
        $status = strtolower((string) $getValue($item, ['status_verifikasi'], ''));
        if ($isVerified($item)) return 'Tervalidasi';
        if (in_array($status, ['rejected', 'ditolak'], true)) return 'Perlu Revisi';
        return 'Menunggu Validasi';
    };

    // Penyesuaian Warna Soft Healthcare (Emerald & Gold)
    $statusTheme = function ($item) use ($isVerified, $getValue) {
        $status = strtolower((string) $getValue($item, ['status_verifikasi'], ''));
        if ($isVerified($item)) return 'bg-emerald-50 text-emerald-700 ring-emerald-200 border-emerald-100 shadow-sm shadow-emerald-500/10';
        if (in_array($status, ['rejected', 'ditolak'], true)) return 'bg-rose-50 text-rose-700 ring-rose-200 border-rose-100 shadow-sm shadow-rose-500/10';
        // GOLD ACCENT
        return 'bg-amber-50/80 text-amber-700 ring-amber-200 border-amber-100 shadow-sm shadow-amber-500/10';
    };

    $kategoriTheme = function ($key) {
        return match ($key) {
            'remaja' => [
                'badge' => 'bg-indigo-50/80 text-indigo-700 border-indigo-100',
                'iconBox' => 'bg-indigo-50/80 text-indigo-600 border-indigo-100',
                'active' => 'border-indigo-200 bg-indigo-100/50 text-indigo-800 shadow-sm shadow-indigo-500/10',
            ],
            'lansia' => [
                'badge' => 'bg-teal-50/80 text-teal-700 border-teal-100',
                'iconBox' => 'bg-teal-50/80 text-teal-600 border-teal-100',
                'active' => 'border-teal-200 bg-teal-100/50 text-teal-800 shadow-sm shadow-teal-500/10',
            ],
            default => [
                'badge' => 'bg-sky-50/80 text-sky-700 border-sky-100',
                'iconBox' => 'bg-sky-50/80 text-sky-600 border-sky-100',
                'active' => 'border-sky-200 bg-sky-100/50 text-sky-800 shadow-sm shadow-sky-500/10',
            ],
        };
    };

    $getParameterUtama = function ($item, string $kategori) use ($displayValue) {
        if ($kategori === 'lansia') return ['Tensi' => $displayValue(data_get($item, 'tekanan_darah')), 'Gula' => $displayValue(data_get($item, 'gula_darah'), 'mg/dL'), 'Koles.' => $displayValue(data_get($item, 'kolesterol'), 'mg/dL')];
        if ($kategori === 'remaja') return ['BB' => $displayValue(data_get($item, 'berat_badan'), 'kg'), 'TB' => $displayValue(data_get($item, 'tinggi_badan'), 'cm'), 'Tensi' => $displayValue(data_get($item, 'tekanan_darah'))];
        return ['BB' => $displayValue(data_get($item, 'berat_badan'), 'kg'), 'TB' => $displayValue(data_get($item, 'tinggi_badan'), 'cm'), 'Gizi' => $displayValue(data_get($item, 'status_gizi'))];
    };

    $buildUrl = function (array $overrides = []) use ($tab, $kategori, $search) {
        $query = array_merge(['tab' => $tab, 'kategori' => $kategori, 'search' => $search], $overrides);
        $query = collect($query)->filter(fn ($value) => $value !== null && $value !== '' && $value !== 'semua')->all();
        return route('bidan.pemeriksaan.index', $query);
    };

    $visibleCount = method_exists($pemeriksaans, 'count') ? $pemeriksaans->count() : count($pemeriksaans);
    $totalData = method_exists($pemeriksaans, 'total') ? $pemeriksaans->total() : $visibleCount;

    $pageTitle = $tab === 'verified' ? 'Data Tervalidasi' : 'Menunggu Validasi';
    $pageSubtitle = $tab === 'verified' ? 'Arsip rekam medis yang telah disahkan.' : 'Tinjau dan validasi segera.';

    $summaryCards = [
        ['label' => 'Total Data', 'value' => $stats['total'] ?? 0, 'icon' => 'ph-stethoscope', 'class' => 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-emerald-500/10'],
        ['label' => 'Menunggu', 'value' => $stats['pending'] ?? 0, 'icon' => 'ph-clock-countdown', 'class' => 'bg-amber-50 text-amber-600 border-amber-100 shadow-amber-500/10'], // GOLD
        ['label' => 'Tervalidasi', 'value' => $stats['verified'] ?? 0, 'icon' => 'ph-check-circle', 'class' => 'bg-teal-50 text-teal-600 border-teal-100 shadow-teal-500/10'],
        ['label' => 'Bulan Ini', 'value' => $stats['bulan_ini'] ?? 0, 'icon' => 'ph-calendar-check', 'class' => 'bg-sky-50 text-sky-600 border-sky-100 shadow-sky-500/10'],
    ];
@endphp

@push('styles')
<style>
    /* =========================================
       SOFT HEALTHCARE & GLASSMORPHISM THEME 
       ========================================= */
    .premium-dashboard {
        font-family: 'Inter', system-ui, sans-serif;
        background-color: #f8fafc;
        position: relative;
        min-height: 100vh;
        /* Soft Emerald & Gold Mesh Gradient Background */
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 84%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(43, 100%, 92%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(160, 84%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 0% 100%, hsla(199, 92%, 94%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }
    
    /* Perfect Glassmorphism Card */
    .glass-card {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: 1.5rem;
        box-shadow: 
            0 10px 40px -10px rgba(15, 23, 42, 0.05), 
            inset 0 1px 0 rgba(255, 255, 255, 1);
        position: relative;
        z-index: 10;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .glass-card:hover {
        box-shadow: 
            0 15px 50px -10px rgba(16, 185, 129, 0.08), 
            inset 0 1px 0 rgba(255, 255, 255, 1);
    }

    /* Animations */
    .nexus-page-enter { animation: nexusMainIn .3s ease-out both; }
    .nexus-panel-enter { animation: nexusPanelIn .3s ease-out both; }
    @keyframes nexusMainIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes nexusPanelIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

    /* Custom Scrollbar for Glass UI */
    .nexus-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .nexus-scroll::-webkit-scrollbar-track { background: transparent; }
    .nexus-scroll::-webkit-scrollbar-thumb { background: rgba(16, 185, 129, 0.2); border-radius: 999px; }
    .nexus-scroll::-webkit-scrollbar-thumb:hover { background: rgba(16, 185, 129, 0.4); }

    .nexus-live-hidden { display: none !important; }
    .nexus-list-stable { min-height: 400px; }

    /* Soft Chip Parameter */
    .parameter-chip {
        display: inline-flex; align-items: center; gap: 4px; border-radius: 8px;
        background: rgba(255, 255, 255, 0.6); border: 1px solid rgba(255, 255, 255, 0.8);
        padding: 4px 10px; font-size: 11px; font-weight: 600; color: #64748b; white-space: nowrap;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .parameter-chip strong { color: #0f172a; font-weight: 800; }

    /* Clean Glass Table */
    .premium-table tr { transition: all 0.2s ease; border-bottom: 1px solid rgba(226, 232, 240, 0.5); }
    .premium-table tr:hover td { background-color: rgba(255, 255, 255, 0.6); }

    /* Pulse Dots (Gold for pending, Emerald for real-time) */
    .live-pulse { position: relative; display: flex; width: 8px; height: 8px; }
    .live-pulse span { position: absolute; display: inline-flex; height: 100%; width: 100%; border-radius: 999px; background-color: #f59e0b; opacity: 0.75; animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite; }
    .live-pulse .dot { position: relative; display: inline-flex; border-radius: 999px; height: 8px; width: 8px; background-color: #d97706; }
    @keyframes ping { 75%, 100% { transform: scale(2.5); opacity: 0; } }

    /* Soft Premium Buttons */
    .btn-emerald { 
        background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2), inset 0 1px 0 rgba(255,255,255,0.2); 
        transition: all 0.3s ease; 
        color: white; border: none;
    }
    .btn-emerald:hover { 
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3), inset 0 1px 0 rgba(255,255,255,0.2); 
        transform: translateY(-1px); 
    }
</style>
@endpush

@section('content')
<div class="premium-dashboard nexus-page-enter space-y-5 pb-8 text-slate-800 p-2 md:p-4">

    {{-- HEADER GLASSMORPHISM (CLEAN & SOFT) --}}
    <section class="nexus-panel-enter glass-card p-6 md:p-8 overflow-hidden relative">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-emerald-200/40 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-40 -bottom-20 w-48 h-48 bg-amber-200/30 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between relative z-10">
            <div class="min-w-0">
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/80 backdrop-blur-sm px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-wider text-emerald-700 shadow-sm">
                    <i class="ph-bold ph-stethoscope text-sm"></i>
                    Manajemen Layanan Medis
                </div>

                <h1 class="mt-4 text-3xl font-black leading-tight tracking-tight md:text-4xl">
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-800 to-teal-600">Pemeriksaan Klinis</span>
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-500 font-medium">
                    Tinjau data pemeriksaan dari Kader, berikan catatan medis, dan validasi hasil pemeriksaan untuk dimasukkan ke dalam sistem Rekam Medis terpadu.
                </p>
            </div>

            <div class="flex flex-wrap gap-3 relative z-10">
                <div class="inline-flex items-center gap-4 rounded-2xl border border-amber-100/60 bg-gradient-to-br from-amber-50 to-orange-50/50 backdrop-blur-md px-5 py-3 shadow-sm">
                    <div>
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-amber-700/70">Total Tampil</span>
                        <span class="block text-2xl font-black text-amber-700 mt-0.5" id="pemeriksaanVisibleCount">{{ $visibleCount }}</span>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-amber-500 shadow-sm">
                        <i class="ph-bold ph-database text-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SUMMARY CARDS (SOFT HEALTHCARE) --}}
    <section class="nexus-panel-enter grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach($summaryCards as $card)
            <div class="glass-card p-5 group hover:-translate-y-1 transition-transform duration-300">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-slate-600 transition-colors">
                            {{ $card['label'] }}
                        </p>
                        <h2 class="mt-1 text-3xl font-black tracking-tight text-slate-800">
                            {{ $card['value'] }}
                        </h2>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border bg-white/60 backdrop-blur-sm shadow-sm {{ $card['class'] }} group-hover:scale-110 transition-transform">
                        <i class="ph-fill {{ $card['icon'] }} text-2xl"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </section>

    {{-- FILTER BAR (CLEAN PREMIUM UI) --}}
    <section class="nexus-panel-enter glass-card p-4 md:p-5">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="inline-flex w-full rounded-2xl border border-white/60 bg-slate-100/50 p-1.5 sm:w-fit shadow-inner">
                    <a href="{{ $buildUrl(['tab' => 'pending']) }}"
                       class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-xs font-bold transition-all sm:flex-none {{ $tab === 'pending' ? 'bg-white text-amber-700 shadow-sm border border-slate-100' : 'text-slate-500 hover:text-slate-800 hover:bg-white/60' }}">
                        <i class="ph-bold ph-clock-countdown text-sm"></i>
                        Menunggu
                        <span class="rounded-full bg-amber-100/80 px-2 py-0.5 text-[10px] text-amber-800 ml-1">{{ $stats['pending'] ?? 0 }}</span>
                    </a>

                    <a href="{{ $buildUrl(['tab' => 'verified']) }}"
                       class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-xs font-bold transition-all sm:flex-none {{ $tab === 'verified' ? 'bg-white text-emerald-700 shadow-sm border border-slate-100' : 'text-slate-500 hover:text-slate-800 hover:bg-white/60' }}">
                        <i class="ph-bold ph-check-circle text-sm"></i>
                        Tervalidasi
                        <span class="rounded-full bg-emerald-100/80 px-2 py-0.5 text-[10px] text-emerald-800 ml-1">{{ $stats['verified'] ?? 0 }}</span>
                    </a>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ $buildUrl(['kategori' => 'semua']) }}"
                       class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-bold transition-all {{ $kategori === 'semua' ? 'border-emerald-200 bg-emerald-50 text-emerald-800 shadow-sm' : 'border-white/60 bg-white/60 text-slate-500 hover:bg-white hover:text-slate-800' }}">
                        <i class="ph-bold ph-users-three"></i> Semua
                    </a>

                    @foreach($kategoriOptions as $key => $option)
                        @php $theme = $kategoriTheme($key); $active = $kategori === $key; @endphp
                        <a href="{{ $buildUrl(['kategori' => $key]) }}"
                           class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-bold transition-all {{ $active ? $theme['active'] : 'border-white/60 bg-white/60 text-slate-500 hover:bg-white hover:text-slate-800' }}">
                            <i class="ph-bold {{ $option['icon'] }}"></i> {{ $option['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- MAIN CONTENT: TABLE & SEARCH --}}
    <section class="nexus-panel-enter glass-card p-0 overflow-hidden flex flex-col">
        <div class="p-5 md:p-6 border-b border-white/60 bg-white/40 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <div class="h-8 w-1.5 bg-gradient-to-b from-emerald-400 to-teal-500 rounded-full"></div>
                    <h2 class="text-lg font-black tracking-tight text-slate-900">{{ $pageTitle }}</h2>
                </div>
                <p class="mt-1 text-xs font-medium text-slate-500 ml-3.5">{{ $pageSubtitle }}</p>
            </div>

            <form method="GET" action="{{ route('bidan.pemeriksaan.index') }}" class="flex w-full gap-2 xl:max-w-md">
                <input type="hidden" name="tab" value="{{ $tab }}">
                @if($kategori !== 'semua') <input type="hidden" name="kategori" value="{{ $kategori }}"> @endif

                <div class="relative w-full">
                    <i class="ph-bold ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="pemeriksaanLiveSearch" name="search" value="{{ $search }}" autocomplete="off" placeholder="Cari nama atau NIK sasaran..."
                           class="w-full rounded-2xl border border-white/80 bg-white/60 backdrop-blur-sm py-2.5 pl-10 pr-10 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 shadow-inner">
                    <button type="button" id="pemeriksaanClearSearch" class="absolute right-2 top-1/2 hidden h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-slate-400 hover:bg-slate-200 hover:text-slate-700 transition">
                        <i class="ph-bold ph-x text-sm"></i>
                    </button>
                </div>

                @if($search)
                    <a href="{{ $buildUrl(['search' => null]) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 transition shadow-sm">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="nexus-list-stable p-2">
            {{-- DESKTOP TABLE --}}
            <div class="nexus-scroll hidden max-h-[600px] overflow-auto lg:block">
                <table class="w-full text-left premium-table border-collapse">
                    <thead class="sticky top-0 z-10 bg-white/80 backdrop-blur-xl">
                        <tr>
                            <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-200/60">Sasaran & NIK</th>
                            <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-200/60">Kategori</th>
                            <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-200/60">Status</th>
                            <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-200/60">Parameter Kunci</th>
                            <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-200/60">Waktu & Petugas</th>
                            <th class="px-5 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-200/60 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody id="pemeriksaanTableBody">
                        @forelse($pemeriksaans as $pemeriksaan)
                            @php
                                $itemKategori = $getKategori($pemeriksaan);
                                $itemMeta = $kategoriOptions[$itemKategori] ?? $kategoriOptions['balita'];
                                $itemTheme = $kategoriTheme($itemKategori);
                                $namaPasien = $getNamaPasien($pemeriksaan);
                                $nikPasien = $getNikPasien($pemeriksaan);
                                $tanggal = $getValue($pemeriksaan, ['tanggal_periksa', 'kunjungan.tanggal_kunjungan', 'created_at'], null);
                                $parameterUtama = $getParameterUtama($pemeriksaan, $itemKategori);
                                $petugas = $getPetugas($pemeriksaan);
                                $verified = $isVerified($pemeriksaan);
                                $searchName = mb_strtolower(trim((string) $namaPasien), 'UTF-8');
                                $searchNik = mb_strtolower(trim((string) $nikPasien), 'UTF-8');
                            @endphp

                            <tr class="js-pemeriksaan-row group" data-name="{{ $searchName }}" data-nik="{{ $searchNik }}" data-order="{{ $loop->index }}">
                                <td class="px-5 py-4 align-middle">
                                    <p class="text-sm font-bold text-slate-800 group-hover:text-emerald-700 transition-colors">{{ $namaPasien }}</p>
                                    <p class="text-[11px] font-semibold text-slate-400 font-mono mt-0.5">{{ $nikPasien }}</p>
                                </td>

                                <td class="px-5 py-4 align-middle">
                                    <span class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-[10px] font-bold bg-white shadow-sm {{ $itemTheme['badge'] }}">
                                        {{ $itemMeta['label'] }}
                                    </span>
                                </td>
                                
                                <td class="px-5 py-4 align-middle">
                                    <div class="inline-flex items-center gap-2 rounded-md border px-3 py-1.5 text-[11px] font-bold bg-white {{ $statusTheme($pemeriksaan) }}">
                                        @if(!$verified)
                                            <div class="live-pulse"><span class=""></span><div class="dot"></div></div>
                                        @else
                                            <i class="ph-fill ph-check-circle text-emerald-500"></i>
                                        @endif
                                        {{ $statusLabel($pemeriksaan) }}
                                    </div>
                                </td>

                                <td class="px-5 py-4 align-middle">
                                    <div class="flex flex-wrap gap-1.5 max-w-[280px]">
                                        @foreach($parameterUtama as $label => $value)
                                            <span class="parameter-chip">{{ $label }}: <strong>{{ $value }}</strong></span>
                                        @endforeach
                                    </div>
                                </td>

                                <td class="px-5 py-4 align-middle">
                                    <p class="text-[11px] font-bold text-slate-600"><i class="ph-bold ph-calendar-blank text-slate-400 mr-1"></i> {{ $formatDate($tanggal) }}</p>
                                    <p class="text-[10px] font-semibold text-slate-400 mt-1 uppercase tracking-wide"><i class="ph-bold ph-user mr-0.5"></i> {{ $petugas }}</p>
                                </td>

                                <td class="px-5 py-4 align-middle text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('bidan.pemeriksaan.show', $pemeriksaan->id) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 hover:border-emerald-300 hover:text-emerald-600 hover:bg-emerald-50 transition shadow-sm" title="Detail">
                                            <i class="ph-bold ph-eye text-base"></i>
                                        </a>

                                        @unless($verified)
                                            <a href="{{ route('bidan.pemeriksaan.validasi', $pemeriksaan->id) }}" class="btn-emerald inline-flex items-center gap-1.5 rounded-xl px-4 py-1.5 text-xs font-bold">
                                                Validasi
                                            </a>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-14 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white border border-slate-100 shadow-sm text-slate-300 mb-4">
                                        <i class="ph-fill ph-folder-open text-3xl"></i>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-700">Pemeriksaan belum ada</h3>
                                    <p class="text-xs text-slate-400 mt-1">Gunakan tab atau filter lain untuk mencari data.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE VIEW --}}
            <div id="pemeriksaanCardContainer" class="space-y-3 lg:hidden mt-2 p-2">
                @forelse($pemeriksaans as $pemeriksaan)
                    @php
                        $itemKategori = $getKategori($pemeriksaan);
                        $itemMeta = $kategoriOptions[$itemKategori] ?? $kategoriOptions['balita'];
                        $itemTheme = $kategoriTheme($itemKategori);
                        $namaPasien = $getNamaPasien($pemeriksaan);
                        $nikPasien = $getNikPasien($pemeriksaan);
                        $tanggal = $getValue($pemeriksaan, ['tanggal_periksa', 'kunjungan.tanggal_kunjungan', 'created_at'], null);
                        $petugas = $getPetugas($pemeriksaan);
                        $verified = $isVerified($pemeriksaan);
                        $searchName = mb_strtolower(trim((string) $namaPasien), 'UTF-8');
                        $searchNik = mb_strtolower(trim((string) $nikPasien), 'UTF-8');
                    @endphp

                    <article class="js-pemeriksaan-card rounded-2xl border border-white/60 bg-white/70 backdrop-blur-md p-4 shadow-sm" data-name="{{ $searchName }}" data-nik="{{ $searchNik }}" data-order="{{ $loop->index }}">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $namaPasien }}</h3>
                                <p class="text-xs text-slate-500 font-mono">{{ $nikPasien }}</p>
                            </div>
                            <span class="inline-flex rounded-lg border bg-white px-2 py-1 text-[10px] font-bold shadow-sm {{ $itemTheme['badge'] }}">{{ $itemMeta['label'] }}</span>
                        </div>
                        
                        <div class="flex items-center gap-2 mb-4">
                             <div class="inline-flex items-center gap-1.5 rounded-md border bg-white px-2.5 py-1 text-[10px] font-bold shadow-sm {{ $statusTheme($pemeriksaan) }}">
                                {{ $statusLabel($pemeriksaan) }}
                            </div>
                            <span class="text-[11px] font-bold text-slate-500"><i class="ph-bold ph-calendar-blank"></i> {{ $formatDate($tanggal) }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-slate-200/60">
                            <a href="{{ route('bidan.pemeriksaan.show', $pemeriksaan->id) }}" class="inline-flex justify-center items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 shadow-sm">Detail</a>
                            @unless($verified)
                                <a href="{{ route('bidan.pemeriksaan.validasi', $pemeriksaan->id) }}" class="btn-emerald inline-flex justify-center items-center rounded-xl px-3 py-2 text-xs font-bold text-white">Validasi</a>
                            @endunless
                        </div>
                    </article>
                @empty
                    <div class="p-8 text-center bg-white/50 backdrop-blur-sm rounded-2xl border border-white/80 border-dashed">
                        <p class="text-sm font-bold text-slate-500">Kosong</p>
                    </div>
                @endforelse
            </div>

            {{-- LIVE EMPTY STATE --}}
            <div id="pemeriksaanLiveEmpty" class="hidden rounded-2xl border border-dashed border-slate-300 bg-white/50 backdrop-blur-sm p-10 text-center m-4">
                <i class="ph-fill ph-magnifying-glass text-4xl text-slate-300 mb-3"></i>
                <h3 class="text-base font-bold text-slate-700">Data Tidak Cocok</h3>
                <p class="text-xs text-slate-500 mt-1">Pencarian untuk NIK/Nama tersebut tidak ditemukan.</p>
            </div>

            {{-- PAGINATION --}}
            @if(method_exists($pemeriksaans, 'hasPages') && $pemeriksaans->hasPages())
                <div id="pemeriksaanPagination" class="bg-white/40 backdrop-blur-sm p-5 border-t border-white/60 flex flex-col md:flex-row md:items-center md:justify-between gap-4 mt-2">
                    <p class="text-xs font-semibold text-slate-500">Menampilkan {{ $pemeriksaans->firstItem() }} - {{ $pemeriksaans->lastItem() }} dari <span class="font-bold text-slate-700">{{ $pemeriksaans->total() }}</span></p>
                    <div>{{ $pemeriksaans->links() }}</div>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    // JS Logic Tidak Diubah
    (() => {
        const searchInput = document.getElementById('pemeriksaanLiveSearch');
        const clearButton = document.getElementById('pemeriksaanClearSearch');
        const visibleCountText = document.getElementById('pemeriksaanVisibleCount');
        const liveEmpty = document.getElementById('pemeriksaanLiveEmpty');
        const pagination = document.getElementById('pemeriksaanPagination');
        const tableBody = document.getElementById('pemeriksaanTableBody');
        const cardContainer = document.getElementById('pemeriksaanCardContainer');
        const rows = Array.from(document.querySelectorAll('.js-pemeriksaan-row'));
        const cards = Array.from(document.querySelectorAll('.js-pemeriksaan-card'));

        const normalize = (value) => String(value || '').toLowerCase().trim();
        const isNumericKeyword = (keyword) => /^[0-9]+$/.test(keyword);

        const getMatchRank = (item, keyword) => {
            if (keyword === '') return 10 + Number(item.dataset.order || 0);
            const name = normalize(item.dataset.name);
            const nik = normalize(item.dataset.nik);
            if (isNumericKeyword(keyword)) {
                if (nik.startsWith(keyword)) return 0;
                if (nik.includes(keyword)) return 1;
                return 999;
            }
            if (name.startsWith(keyword)) return 0;
            if (name.includes(keyword)) return 1;
            return 999;
        };

        const sortAndRender = (items, keyword, container) => {
            let visible = 0;
            items.map(item => ({ item, rank: getMatchRank(item, keyword), name: normalize(item.dataset.name), order: Number(item.dataset.order || 0) }))
                .sort((a, b) => a.rank !== b.rank ? a.rank - b.rank : (a.name !== b.name ? a.name.localeCompare(b.name) : a.order - b.order))
                .forEach(entry => {
                    const matched = entry.rank < 999;
                    entry.item.classList.toggle('nexus-live-hidden', !matched);
                    if (matched) { visible += 1; if (container) container.appendChild(entry.item); }
                });
            return visible;
        };

        let frameId = null;
        const applyFilter = () => {
            if (frameId) cancelAnimationFrame(frameId);
            frameId = requestAnimationFrame(() => {
                const keyword = normalize(searchInput?.value);
                const visibleRows = sortAndRender(rows, keyword, tableBody);
                const visibleCards = sortAndRender(cards, keyword, cardContainer);
                const visibleCount = rows.length > 0 ? visibleRows : visibleCards;
                const hasData = rows.length > 0 || cards.length > 0;

                if (visibleCountText) visibleCountText.textContent = String(visibleCount);
                if (liveEmpty) liveEmpty.classList.toggle('hidden', !hasData || visibleCount > 0);
                if (clearButton) {
                    const hasKeyword = keyword !== '';
                    clearButton.classList.toggle('hidden', !hasKeyword);
                    clearButton.classList.toggle('inline-flex', hasKeyword);
                }
                if (pagination) pagination.classList.toggle('hidden', keyword !== '');
            });
        };

        searchInput?.addEventListener('input', applyFilter, { passive: true });
        searchInput?.addEventListener('keyup', applyFilter, { passive: true });
        searchInput?.addEventListener('search', applyFilter, { passive: true });
        clearButton?.addEventListener('click', () => { if (searchInput) { searchInput.value = ''; searchInput.focus(); applyFilter(); } });
        applyFilter();
    })();
</script>
@endpush