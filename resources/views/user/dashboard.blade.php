@extends('layouts.user')

@section('title', 'Beranda Saya')
@section('page_title', 'Beranda Saya')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $user = Auth::user();
    $userName = $user->name ?? 'Warga';
    $firstName = trim(explode(' ', $userName)[0]) ?: 'Warga';

    $routeTo = function ($names, $params = []) {
        foreach ((array) $names as $name) {
            if (Route::has($name)) return route($name, $params);
        }
        return '#';
    };

    $anakList = collect($dataAnak ?? []);
    $remajaList = collect($dataRemaja ?? []);
    $lansiaList = collect($dataLansia ?? []);

    $jadwalList = collect($jadwalTerdekat ?? []);
    $jumlahNotif = $totalNotifikasiBelumDibaca ?? collect($notifikasiTerbaru ?? [])->where('is_read', false)->count();
    $totalSasaran = $anakList->count() + $remajaList->count() + $lansiaList->count();

    // Routes
    $monitoringRoute = $routeTo('user.monitoring.index');
    $jadwalRoute = $routeTo('user.jadwal.index');
    $notifikasiRoute = $routeTo('user.notifikasi.index');
    $profileRoute = $routeTo('user.profile.edit');

    $balitaShowRoute = fn ($id) => $routeTo(['user.balita.show', 'user.monitoring.balita.show'], [$id]);
    $remajaShowRoute = fn ($id) => $routeTo(['user.remaja.show', 'user.monitoring.remaja.show'], [$id]);
    $lansiaShowRoute = fn ($id) => $routeTo(['user.lansia.show', 'user.monitoring.lansia.show'], [$id]);

    $formatDate = fn ($value, $format = 'd M Y') => filled($value) && $value !== '-' ? Carbon::parse($value)->translatedFormat($format) : '-';
    $formatTime = fn ($value) => filled($value) && $value !== '-' ? Carbon::parse($value)->format('H:i') : '-';

    // Ekstraktor Data Sasaran
    $getName = fn($item) => data_get($item, 'nama_lengkap') ?: data_get($item, 'nama_remaja') ?: data_get($item, 'nama_balita') ?: data_get($item, 'nama_lansia') ?: data_get($item, 'nama') ?: 'Tanpa Nama';
    $getDob = fn($item) => data_get($item, 'tanggal_lahir') ?: data_get($item, 'tgl_lahir');
    
    $ageText = function ($dob) {
        if (blank($dob)) return '-';
        try {
            $diff = Carbon::parse($dob)->diff(now());
            return $diff->y > 0 ? $diff->y . ' thn' : $diff->m . ' bln';
        } catch (\Throwable $e) { return '-'; }
    };

    $healthItems = collect();
    foreach ($anakList as $anak) {
        $healthItems->push([
            'type' => 'Balita',
            'name' => $getName($anak),
            'meta' => $ageText($getDob($anak)),
            'href' => $balitaShowRoute(data_get($anak, 'id')),
            'icon' => 'fa-child-reaching',
            'badge_bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200'
        ]);
    }
    foreach ($remajaList as $remaja) {
        $healthItems->push([
            'type' => 'Remaja',
            'name' => $getName($remaja),
            'meta' => $ageText($getDob($remaja)),
            'href' => $remajaShowRoute(data_get($remaja, 'id')),
            'icon' => 'fa-user-graduate',
            'badge_bg' => 'bg-amber-50 text-amber-700 border-amber-200'
        ]);
    }
    foreach ($lansiaList as $lansia) {
        $healthItems->push([
            'type' => 'Lansia',
            'name' => $getName($lansia),
            'meta' => $ageText($getDob($lansia)),
            'href' => $lansiaShowRoute(data_get($lansia, 'id')),
            'icon' => 'fa-person-cane',
            'badge_bg' => 'bg-blue-50 text-blue-700 border-blue-200'
        ]);
    }

    $statCards = [
        [
            'id' => 'stat-keluarga',
            'label' => 'Total Anggota',
            'value' => $totalSasaran,
            'desc' => 'Terdaftar di KK',
            'icon' => 'fa-users',
            'icon_bg' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
            'url' => $profileRoute
        ],
        [
            'id' => 'stat-riwayat',
            'label' => 'Rekam Medis',
            'value' => $healthItems->count(),
            'desc' => 'Buku KIA & Lansia',
            'icon' => 'fa-notes-medical',
            'icon_bg' => 'bg-teal-50 text-teal-600 border-teal-100',
            'url' => $monitoringRoute
        ],
        [
            'id' => 'stat-jadwal',
            'label' => 'Agenda Posyandu',
            'value' => $jadwalList->count(),
            'desc' => 'Jadwal Terdekat',
            'icon' => 'fa-calendar-check',
            'icon_bg' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
            'url' => $jadwalRoute
        ],
        [
            'id' => 'stat-notif',
            'label' => 'Pemberitahuan',
            'value' => $jumlahNotif,
            'desc' => 'Pesan Belum Dibaca',
            'icon' => 'fa-bell',
            'icon_bg' => 'bg-rose-50 text-rose-600 border-rose-100',
            'url' => $notifikasiRoute
        ],
    ];

    $sasaranList = collect($sasaranList ?? []);
    $sasaranAktif = $sasaranAktif ?? $sasaranList->first();
    $grafikPeriodeAktif = $grafikPeriode ?? 'bulanan';
    $grafikRingkasan = $grafikData['ringkasan'] ?? null;
    $grafikTahunAktif = $grafikData['tahun'] ?? null;
    $grafikTahunTersedia = collect($grafikData['available_years'] ?? []);
@endphp

@push('styles')
<style>
    body {
        background-color: #f8fafc;
        background-image: 
            radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.08) 0px, transparent 50%),
            radial-gradient(at 100% 0%, rgba(13, 148, 136, 0.07) 0px, transparent 50%),
            radial-gradient(at 50% 100%, rgba(241, 245, 249, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }

    .premium-card {
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 1.5rem;
        box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.04), 0 2px 6px -1px rgba(15, 23, 42, 0.02);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .premium-card:hover {
        border-color: rgba(203, 213, 225, 1);
        box-shadow: 0 12px 28px -6px rgba(15, 23, 42, 0.07);
    }

    .slim-scroll::-webkit-scrollbar { width: 5px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    .slim-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    .apexcharts-tooltip {
        border-radius: 1rem !important;
        box-shadow: 0 12px 28px -4px rgba(0, 0, 0, 0.12) !important;
        border: 1px solid #e2e8f0 !important;
        font-family: inherit !important;
    }
</style>
@endpush

@section('content')
<div class="px-3 py-4 sm:px-6 sm:py-6 lg:px-8 max-w-[1400px] mx-auto space-y-5 sm:space-y-6">

    {{-- 1. HERO BANNER TERPADU DENGAN DETIK REAL-TIME --}}
    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-400 p-6 sm:p-8 lg:p-10 shadow-xl shadow-emerald-500/15 border-[4px] border-white/50">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                {{-- Badge Portal & Jam Detik Real-Time --}}
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 border border-white/30 text-white text-[11px] font-black uppercase tracking-wider backdrop-blur-md">
                        <span class="w-2 h-2 rounded-full bg-emerald-100 animate-pulse"></span>
                        Portal Warga
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-black/15 border border-white/25 text-emerald-50 text-[11px] font-bold backdrop-blur-md shadow-sm">
                        <i class="fa-regular fa-clock text-emerald-200"></i>
                        <span id="hero-live-datetime" class="tabular-nums">Memuat waktu...</span>
                    </span>
                </div>

                {{-- Salam & Deskripsi --}}
                <div>
                    <h1 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                        <span id="dynamic-greeting">Selamat Datang</span>, {{ $firstName }} 🏡
                    </h1>
                    <p class="text-teal-50 text-xs sm:text-sm font-medium leading-relaxed mt-1">
                        Pusat informasi kesehatan Posyandu keluarga Anda. Pantau jadwal, riwayat imunisasi, dan perkembangan fisik secara real-time.
                    </p>
                </div>
            </div>

            {{-- Tombol Aksi Cepat --}}
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a href="{{ $monitoringRoute }}" class="bg-white text-emerald-700 hover:bg-teal-50 px-5 sm:px-6 py-2.5 rounded-full text-xs sm:text-sm font-bold shadow-md hover:shadow-lg active:scale-95 transition-all flex items-center gap-2">
                    <i class="fa-solid fa-notes-medical"></i> <span>Buku Rekam Medis</span>
                </a>
                <a href="{{ $jadwalRoute }}" class="bg-emerald-700/30 hover:bg-emerald-700/50 border border-white/30 text-white px-5 sm:px-6 py-2.5 rounded-full text-xs sm:text-sm font-bold backdrop-blur-md active:scale-95 transition-all flex items-center gap-2">
                    <i class="fa-regular fa-calendar-days"></i> <span>Lihat Jadwal</span>
                </a>
            </div>
        </div>
    </div>

    {{-- 2. METRIK UTAMA --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
        @foreach($statCards as $card)
            <a href="{{ $card['url'] }}" class="premium-card p-4 sm:p-5 flex flex-col justify-between group">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-lg border {{ $card['icon_bg'] }} group-hover:scale-105 transition-transform">
                        <i class="fa-solid {{ $card['icon'] }}"></i>
                    </div>
                    <span class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight" id="{{ $card['id'] }}">
                        {{ $card['value'] }}
                    </span>
                </div>
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-700 truncate">{{ $card['label'] }}</h3>
                    <p class="text-[11px] font-medium text-slate-400 mt-0.5 truncate">{{ $card['desc'] }}</p>
                </div>
            </a>
        @endforeach
    </div>

    {{-- 3. LAYOUT UTAMA (GRID 8 : 4) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">
        
        {{-- KOLOM KIRI (GRAFIK PERKEMBANGAN FISIK) --}}
        <div class="lg:col-span-8 space-y-5 sm:space-y-6">
            
            <div class="premium-card p-4 sm:p-6" id="widget-grafik">
                <div class="flex flex-col gap-4 mb-4">
                    {{-- Header Grafik & Switcher Periode --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-sm">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-bold text-slate-800">Grafik Perkembangan Fisik</h2>
                                <p class="text-xs text-slate-400">Tren kenaikan & pertumbuhan berat dan tinggi badan</p>
                            </div>
                        </div>

                        {{-- Navigasi Tahun & Switcher Periode --}}
                        <div class="flex items-center gap-2">
                            <div id="grafik-year-nav" class="flex items-center bg-slate-50 border border-slate-200 rounded-xl px-1 py-0.5 {{ $grafikPeriodeAktif === 'tahunan' ? 'hidden' : '' }}">
                                <button type="button" id="grafik-year-prev" class="w-7 h-7 flex items-center justify-center text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-200/60 transition" title="Tahun sebelumnya">
                                    <i class="fas fa-chevron-left text-[10px]"></i>
                                </button>
                                <select id="grafik-year-select" class="bg-transparent text-xs font-bold text-slate-700 px-1 py-1 focus:outline-none cursor-pointer">
                                    @forelse($grafikTahunTersedia as $th)
                                        <option value="{{ $th }}" @selected($th == $grafikTahunAktif)>{{ $th }}</option>
                                    @empty
                                        <option value="{{ $grafikTahunAktif ?? now()->year }}">{{ $grafikTahunAktif ?? now()->year }}</option>
                                    @endforelse
                                </select>
                                <button type="button" id="grafik-year-next" class="w-7 h-7 flex items-center justify-center text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-200/60 transition" title="Tahun berikutnya">
                                    <i class="fas fa-chevron-right text-[10px]"></i>
                                </button>
                            </div>

                            <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200/60">
                                <button type="button" class="filter-pill-btn px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $grafikPeriodeAktif === 'bulanan' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}" data-periode="bulanan">
                                    Bulanan
                                </button>
                                <button type="button" class="filter-pill-btn px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $grafikPeriodeAktif === 'tahunan' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}" data-periode="tahunan">
                                    Tahunan
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Bar Sasaran Aktif & Ringkasan KPI --}}
                    @if($sasaranAktif)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            @if($sasaranList->count() > 1)
                                <div class="min-w-[200px]">
                                    <select id="grafik-sasaran-select" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none cursor-pointer">
                                        @foreach($sasaranList as $s)
                                            <option value="{{ $s['kategori'] }}|{{ $s['id'] }}" @selected($sasaranAktif['id'] == $s['id'] && $sasaranAktif['kategori'] == $s['kategori'])>
                                                {{ $s['nama'] }} ({{ $s['label'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="text-xs font-bold text-slate-700 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    <span>{{ $sasaranAktif['nama'] ?? 'Anggota Keluarga' }} ({{ $sasaranAktif['label'] ?? 'Sasaran' }})</span>
                                </div>
                            @endif

                            {{-- Badges Nilai Terakhir --}}
                            <div class="grid grid-cols-3 sm:flex sm:items-center gap-2">
                                <div class="bg-emerald-50/80 border border-emerald-200/80 rounded-xl px-3 py-1.5 text-center sm:min-w-[90px]">
                                    <span class="text-[9px] font-extrabold text-emerald-700 uppercase tracking-wider block">BERAT BADAN</span>
                                    <div class="flex items-baseline justify-center gap-0.5">
                                        <span class="text-sm font-black text-slate-800" id="ringkasan-berat">{{ data_get($grafikRingkasan, 'berat_terakhir', '-') }}</span>
                                        <span class="text-[10px] font-bold text-slate-500">kg</span>
                                    </div>
                                </div>
                                <div class="bg-indigo-50/80 border border-indigo-200/80 rounded-xl px-3 py-1.5 text-center sm:min-w-[90px]">
                                    <span class="text-[9px] font-extrabold text-indigo-700 uppercase tracking-wider block">TINGGI BADAN</span>
                                    <div class="flex items-baseline justify-center gap-0.5">
                                        <span class="text-sm font-black text-slate-800" id="ringkasan-tinggi">{{ data_get($grafikRingkasan, 'tinggi_terakhir', '-') }}</span>
                                        <span class="text-[10px] font-bold text-slate-500">cm</span>
                                    </div>
                                </div>
                                <div class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-center sm:min-w-[105px]">
                                    <span class="text-[9px] font-extrabold text-slate-500 uppercase tracking-wider block">CEK TERAKHIR</span>
                                    <span class="text-xs font-bold text-slate-700 block truncate" id="ringkasan-tanggal">{{ data_get($grafikRingkasan, 'tanggal_terakhir', '-') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Legenda Indikator --}}
                        <div class="flex items-center gap-4 text-xs font-bold text-slate-600 pt-1 border-t border-slate-100">
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-sm"></span> Berat Badan (Kg)
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-indigo-500 shadow-sm"></span> Tinggi Badan (Cm)
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Area Canvas Grafik ApexCharts --}}
                <div class="relative w-full h-[350px]">
                    <div id="apexGrowthChart" class="w-full h-full {{ empty($grafikData['labels'] ?? []) ? 'hidden' : '' }}"></div>

                    <div id="grafik-empty" class="{{ empty($grafikData['labels'] ?? []) ? '' : 'hidden' }} flex flex-col items-center justify-center h-full bg-slate-50/60 rounded-2xl border border-dashed border-slate-200 p-6 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-white shadow-sm border border-slate-200 flex items-center justify-center text-slate-400 mb-3">
                            <i class="fas fa-chart-area text-xl"></i>
                        </div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-700">Belum Ada Riwayat Antropometri</h4>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs">Data pemeriksaan berat dan tinggi badan belum tercatat pada periode ini.</p>
                    </div>
                </div>

                {{-- Keterangan Dinamis Jika Data Baru 1 Kunjungan --}}
                <div id="grafik-single-notice" class="hidden mt-3 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-emerald-500 text-sm shrink-0"></i>
                    <span>Tercatat 1 kali pemeriksaan pada tahun ini. Lakukan penimbangan rutin pada jadwal Posyandu berikutnya untuk memantau grafik kurva kenaikan/penurunan secara berkala.</span>
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN (SASARAN KELUARGA + TIPS + JADWAL POSYANDU) --}}
        <div class="lg:col-span-4 space-y-5 sm:space-y-6">
            
            {{-- 1. WIDGET DAFTAR KELUARGA --}}
            <div class="premium-card p-5 sm:p-6">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-2xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-house-chimney-medical"></i>
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-slate-800">Sasaran Keluarga</h2>
                            <p class="text-xs text-slate-400">Data anggota terhubung di KK</p>
                        </div>
                    </div>
                    <a href="{{ $profileRoute }}" class="text-xs font-semibold text-slate-400 hover:text-blue-600" title="Kelola Kartu Keluarga">
                        <i class="fa-solid fa-gear"></i>
                    </a>
                </div>

                <div class="space-y-3">
                    @if($healthItems->isEmpty())
                        <div class="flex flex-col items-center justify-center p-6 text-center bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-300 mb-2">
                                <i class="fa-solid fa-user-plus text-lg"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-600">Belum Ada Anggota Keluarga</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Lengkapi profil atau hubungi kader untuk verifikasi data NIK.</p>
                            <a href="{{ $profileRoute }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:underline">
                                Lengkapi NIK Sekarang <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    @else
                        @foreach($healthItems as $item)
                            <a href="{{ $item['href'] }}" class="flex items-center justify-between p-3.5 rounded-2xl border border-slate-100 bg-slate-50/40 hover:bg-white hover:border-slate-300 hover:shadow-sm transition-all group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 group-hover:bg-emerald-500 group-hover:text-white group-hover:border-emerald-500 transition-colors shrink-0">
                                        <i class="fas {{ $item['icon'] }} text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 truncate group-hover:text-emerald-700 transition-colors">{{ $item['name'] }}</h4>
                                        <p class="text-[11px] font-semibold text-slate-400">Usia: {{ $item['meta'] }}</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 text-[10px] font-black uppercase rounded-lg border {{ $item['badge_bg'] }} shrink-0">
                                    {{ $item['type'] }}
                                </span>
                            </a>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- 2. WIDGET INFORMASI / PANDUAN WARGA --}}
            <div class="premium-card p-5 bg-gradient-to-br from-slate-50 to-slate-100/60 border-slate-200">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 text-sm mt-0.5">
                        <i class="fa-solid fa-lightbulb"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-xs sm:text-sm font-bold text-slate-800">Tips Kunjungan Posyandu</h4>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Pastikan membawa Buku KIA saat jadwal pemeriksaan balita. Untuk lansia, lakukan pengecekan tensi darah dan gula darah secara rutin setiap bulan.
                        </p>
                    </div>
                </div>
            </div>

            {{-- 3. WIDGET JADWAL POSYANDU TERDEKAT --}}
            <div class="premium-card p-5 sm:p-6">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center text-sm">
                            <i class="fa-regular fa-calendar-days"></i>
                        </div>
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-slate-800">Jadwal Posyandu</h2>
                            <p class="text-xs text-slate-400">Agenda terdekat warga</p>
                        </div>
                    </div>
                    <a href="{{ $jadwalRoute }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                        Lihat Semua <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($jadwalList as $jadwal)
                        @php $isToday = !empty($jadwal->tanggal) && Carbon::parse($jadwal->tanggal)->isToday(); @endphp
                        <a href="{{ route('user.jadwal.show', $jadwal->id) }}" class="flex items-center gap-3.5 p-3 rounded-2xl border transition-all {{ $isToday ? 'bg-amber-50/70 border-amber-200/90 shadow-sm' : 'bg-slate-50/50 border-slate-200/70 hover:bg-white hover:border-emerald-200 hover:shadow-sm' }}">
                            <div class="flex flex-col items-center justify-center w-11 h-11 rounded-xl shrink-0 font-bold {{ $isToday ? 'bg-amber-500 text-white' : 'bg-white text-slate-700 border border-slate-200' }}">
                                <span class="text-sm font-black leading-none">{{ $formatDate($jadwal->tanggal, 'd') }}</span>
                                <span class="text-[8px] uppercase tracking-wider mt-0.5">{{ $formatDate($jadwal->tanggal, 'M') }}</span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <h4 class="text-xs font-bold text-slate-800 truncate">{{ $jadwal->judul }}</h4>
                                    @if($isToday)
                                        <span class="px-1.5 py-0.5 text-[8px] font-black uppercase rounded bg-amber-200 text-amber-900">Hari Ini</span>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-x-2.5 text-[11px] text-slate-500">
                                    <span><i class="fa-regular fa-clock text-slate-400"></i> {{ $formatTime($jadwal->waktu_mulai) }}</span>
                                    <span class="truncate max-w-[130px]"><i class="fa-solid fa-map-pin text-rose-400"></i> {{ $jadwal->lokasi }}</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="flex flex-col items-center justify-center p-6 text-center bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                            <div class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-300 mb-1.5">
                                <i class="fa-regular fa-calendar-xmark text-base"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-600">Belum ada agenda</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Live Time & Date Updater dengan DETIK Real-Time
    function updateLiveDateTime() {
        const now = new Date();
        const liveEl = document.getElementById('hero-live-datetime');
        const greetingEl = document.getElementById('dynamic-greeting');

        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        const dayName = days.at(now.getDay());
        const dateNum = now.getDate();
        const monthName = months.at(now.getMonth());
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        if (liveEl) {
            liveEl.innerHTML = dayName + ', ' + dateNum + ' ' + monthName + ' &bull; <span class="font-black text-white">' + hours + ':' + minutes + '</span><span class="text-emerald-200 font-black">:' + seconds + '</span> WIB';
        }

        const h = now.getHours();
        if (greetingEl) {
            if (h >= 4 && h < 11) greetingEl.innerText = 'Selamat Pagi';
            else if (h >= 11 && h < 15) greetingEl.innerText = 'Selamat Siang';
            else if (h >= 15 && h < 18) greetingEl.innerText = 'Selamat Sore';
            else greetingEl.innerText = 'Selamat Malam';
        }
    }
    updateLiveDateTime();
    setInterval(updateLiveDateTime, 1000);

    // 2. Format Data Antropometri Riil Pasien
    function preparePatientGrowthData(labels, rawBerat, rawTinggi) {
        const labelsData = Array.isArray(labels) ? labels : [];
        const beratData = Array.isArray(rawBerat) ? rawBerat : [];
        const tinggiData = Array.isArray(rawTinggi) ? rawTinggi : [];

        const validBerat = beratData.filter(function(v) { return v !== null && v !== undefined && !isNaN(v); });
        const validTinggi = tinggiData.filter(function(v) { return v !== null && v !== undefined && !isNaN(v); });

        // Hitung Smart Bounds Sumbu Y agar garis terlihat tajam naik/turunnya
        const minB = validBerat.length > 0 ? Math.max(0, Math.floor(Math.min(...validBerat) - 3)) : 5;
        const maxB = validBerat.length > 0 ? Math.ceil(Math.max(...validBerat) + 3) : 25;

        const minT = validTinggi.length > 0 ? Math.max(0, Math.floor(Math.min(...validTinggi) - 8)) : 50;
        const maxT = validTinggi.length > 0 ? Math.ceil(Math.max(...validTinggi) + 8) : 120;

        // Hubungkan titik jika ada 2 atau lebih data periksa riil
        let connectedBerat = beratData.slice();
        let connectedTinggi = tinggiData.slice();

        if (validBerat.length >= 2) {
            let firstIdx = -1;
            let lastIdx = -1;
            for (let i = 0; i < beratData.length; i++) {
                if (beratData.at(i) !== null) {
                    if (firstIdx === -1) firstIdx = i;
                    lastIdx = i;
                }
            }
            
            let prevValidIdx = firstIdx;
            for (let i = firstIdx + 1; i <= lastIdx; i++) {
                if (beratData.at(i) !== null) {
                    const stepCount = i - prevValidIdx;
                    const startB = beratData.at(prevValidIdx);
                    const endB = beratData.at(i);
                    const startT = tinggiData.at(prevValidIdx);
                    const endT = tinggiData.at(i);

                    for (let j = 1; j < stepCount; j++) {
                        const frac = j / stepCount;
                        connectedBerat[prevValidIdx + j] = Number((startB + (endB - startB) * frac).toFixed(1));
                        connectedTinggi[prevValidIdx + j] = Number((startT + (endT - startT) * frac).toFixed(1));
                    }
                    prevValidIdx = i;
                }
            }
        }

        return {
            labels: labelsData,
            berat: connectedBerat,
            tinggi: connectedTinggi,
            totalRecords: validBerat.length,
            minBerat: minB,
            maxBerat: maxB,
            minTinggi: minT,
            maxTinggi: maxT
        };
    }

    const grafikUrl = '{{ Route::has("user.dashboard.chart") ? route("user.dashboard.chart") : "" }}';
    const chartContainer = document.getElementById('apexGrowthChart');
    const emptyEl = document.getElementById('grafik-empty');
    const singleNoticeEl = document.getElementById('grafik-single-notice');
    
    let chartInstance = null;
    let currentPeriode = @json($grafikPeriodeAktif);
    let currentKategori = @json($sasaranAktif['kategori'] ?? null);
    let currentPasienId = @json($sasaranAktif['id'] ?? null);
    let currentTahun = @json($grafikTahunAktif) || new Date().getFullYear();

    const ringkasanBerat = document.getElementById('ringkasan-berat');
    const ringkasanTinggi = document.getElementById('ringkasan-tinggi');
    const ringkasanTanggal = document.getElementById('ringkasan-tanggal');

    function initOrUpdateChart(labels, berat, tinggi) {
        const prep = preparePatientGrowthData(labels, berat, tinggi);

        // Tampilkan info jika baru 1 data tercatat
        if (singleNoticeEl) {
            if (prep.totalRecords === 1) {
                singleNoticeEl.classList.remove('hidden');
            } else {
                singleNoticeEl.classList.add('hidden');
            }
        }

        const strokeWidths = Array.of(3.5, 3.5);
        const markerSizes = Array.of(6, 6);
        const gradientStops = Array.of(0, 95, 100);

        if (chartInstance) {
            chartInstance.updateOptions({
                xaxis: { categories: prep.labels },
                yaxis: [
                    {
                        title: { text: 'Berat (Kg)', style: { color: '#10b981', fontWeight: 700, fontSize: '11px' } },
                        labels: { style: { colors: '#10b981', fontWeight: 700, fontSize: '11px' } },
                        min: prep.minBerat,
                        max: prep.maxBerat,
                        tickAmount: 4
                    },
                    {
                        opposite: true,
                        title: { text: 'Tinggi (Cm)', style: { color: '#6366f1', fontWeight: 700, fontSize: '11px' } },
                        labels: { style: { colors: '#6366f1', fontWeight: 700, fontSize: '11px' } },
                        min: prep.minTinggi,
                        max: prep.maxTinggi,
                        tickAmount: 4
                    }
                ]
            }, false, true);

            chartInstance.updateSeries([
                { name: 'Berat Badan (Kg)', type: 'area', data: prep.berat },
                { name: 'Tinggi Badan (Cm)', type: 'line', data: prep.tinggi }
            ], true);
            return;
        }

        if (!chartContainer) return;

        const options = {
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false },
                fontFamily: 'inherit',
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 600
                }
            },
            colors: ['#10b981', '#6366f1'], // Emerald untuk Berat, Indigo untuk Tinggi
            series: [
                {
                    name: 'Berat Badan (Kg)',
                    type: 'area',
                    data: prep.berat
                },
                {
                    name: 'Tinggi Badan (Cm)',
                    type: 'line',
                    data: prep.tinggi
                }
            ],
            stroke: {
                curve: 'smooth',
                width: strokeWidths,
                lineCap: 'round'
            },
            fill: {
                type: ['gradient', 'solid'],
                gradient: {
                    shade: 'light',
                    type: 'vertical',
                    shadeIntensity: 0.2,
                    opacityFrom: 0.35,
                    opacityTo: 0.05,
                    stops: gradientStops
                }
            },
            markers: {
                size: markerSizes,
                strokeColors: '#ffffff',
                strokeWidth: 3,
                hover: { size: 9 }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: prep.labels,
                labels: {
                    rotate: 0,
                    hideOverlappingLabels: true,
                    style: { fontSize: '11px', fontWeight: 600, colors: '#64748b' }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: [
                {
                    title: { text: 'Berat (Kg)', style: { color: '#10b981', fontWeight: 700, fontSize: '11px' } },
                    labels: { style: { colors: '#10b981', fontWeight: 700, fontSize: '11px' } },
                    min: prep.minBerat,
                    max: prep.maxBerat,
                    tickAmount: 4
                },
                {
                    opposite: true,
                    title: { text: 'Tinggi (Cm)', style: { color: '#6366f1', fontWeight: 700, fontSize: '11px' } },
                    labels: { style: { colors: '#6366f1', fontWeight: 700, fontSize: '11px' } },
                    min: prep.minTinggi,
                    max: prep.maxTinggi,
                    tickAmount: 4
                }
            ],
            legend: { show: false },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                padding: { top: 12, right: 10, bottom: 0, left: 10 }
            },
            tooltip: {
                shared: true,
                intersect: false,
                theme: 'light',
                y: {
                    formatter: function (val, opts) {
                        return val ? (opts.seriesIndex === 0 ? val + ' Kg' : val + ' Cm') : '-';
                    }
                }
            }
        };

        chartInstance = new ApexCharts(chartContainer, options);
        chartInstance.render();
    }

    function toggleView(hasData) {
        if (hasData) {
            if (chartContainer) chartContainer.classList.remove('hidden');
            if (emptyEl) emptyEl.classList.add('hidden');
        } else {
            if (chartContainer) chartContainer.classList.add('hidden');
            if (emptyEl) emptyEl.classList.remove('hidden');
        }
    }

    async function fetchGrafik() {
        if (!grafikUrl || !currentKategori || !currentPasienId) return;

        const params = new URLSearchParams({
            periode: currentPeriode,
            kategori: currentKategori,
            pasien_id: currentPasienId
        });
        if (currentPeriode === 'bulanan' && currentTahun) {
            params.set('tahun', currentTahun);
        }

        try {
            const res = await fetch(grafikUrl + '?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();

            if (data.status === 'success' && data.labels && data.labels.length > 0) {
                toggleView(true);
                initOrUpdateChart(data.labels, data.berat, data.tinggi);

                if (data.ringkasan) {
                    if (ringkasanBerat) ringkasanBerat.innerText = data.ringkasan.berat_terakhir ?? '-';
                    if (ringkasanTinggi) ringkasanTinggi.innerText = data.ringkasan.tinggi_terakhir ?? '-';
                    if (ringkasanTanggal) ringkasanTanggal.innerText = data.ringkasan.tanggal_terakhir ?? '-';
                }
            } else {
                toggleView(false);
            }
        } catch (e) {
            console.error('Error fetching growth chart:', e);
        }
    }

    // Filter Buttons Switcher
    const filterButtons = document.querySelectorAll('.filter-pill-btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            filterButtons.forEach(b => {
                b.classList.remove('bg-white', 'text-slate-800', 'shadow-sm');
                b.classList.add('text-slate-500');
            });
            this.classList.add('bg-white', 'text-slate-800', 'shadow-sm');
            this.classList.remove('text-slate-500');

            currentPeriode = this.dataset.periode;
            const nav = document.getElementById('grafik-year-nav');
            if (nav) nav.classList.toggle('hidden', currentPeriode !== 'bulanan');
            fetchGrafik();
        });
    });

    // Dropdown Sasaran Pasien
    const selectSasaran = document.getElementById('grafik-sasaran-select');
    if (selectSasaran) {
        selectSasaran.addEventListener('change', function () {
            const splitParts = this.value.split('|');
            currentKategori = splitParts.at(0);
            currentPasienId = splitParts.at(1);
            fetchGrafik();
        });
    }

    // Navigasi Tahun
    const selectTahun = document.getElementById('grafik-year-select');
    const btnPrevTahun = document.getElementById('grafik-year-prev');
    const btnNextTahun = document.getElementById('grafik-year-next');

    if (selectTahun) {
        selectTahun.addEventListener('change', function () {
            currentTahun = this.value;
            fetchGrafik();
        });
    }
    if (btnPrevTahun && selectTahun) {
        btnPrevTahun.addEventListener('click', function () {
            if (selectTahun.selectedIndex > 0) {
                selectTahun.selectedIndex--;
                currentTahun = selectTahun.value;
                fetchGrafik();
            }
        });
    }
    if (btnNextTahun && selectTahun) {
        btnNextTahun.addEventListener('click', function () {
            if (selectTahun.selectedIndex < selectTahun.options.length - 1) {
                selectTahun.selectedIndex++;
                currentTahun = selectTahun.value;
                fetchGrafik();
            }
        });
    }

    // Render Data Awal dari Controller
    @if(!empty($grafikData['labels'] ?? []))
        initOrUpdateChart(
            {!! json_encode($grafikData['labels']) !!},
            {!! json_encode($grafikData['berat']) !!},
            {!! json_encode($grafikData['tinggi']) !!}
        );
        toggleView(true);
    @endif

    // Update Counter Dashboard AJAX
    const statsUrl = '{{ Route::has("user.dashboard.stats") ? route("user.dashboard.stats") : "" }}';
    function fetchDashboardStatsSafe() {
        if (!statsUrl) return;
        fetch(statsUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.ok ? res.json() : null)
        .then(data => {
            if (data && data.status === 'success') {
                const statKeluarga = document.getElementById('stat-keluarga');
                const statNotif = document.getElementById('stat-notif');
                const statJadwal = document.getElementById('stat-jadwal');

                if (statKeluarga && statKeluarga.innerText != data.total_sasaran) statKeluarga.innerText = data.total_sasaran;
                if (statNotif && statNotif.innerText != data.unread_count) statNotif.innerText = data.unread_count;
                if (statJadwal && statJadwal.innerText != data.total_jadwal) statJadwal.innerText = data.total_jadwal;
            }
        }).catch(() => {});
    }

    if (statsUrl) setInterval(fetchDashboardStatsSafe, 30000);
});
</script>
@endpush