@extends('layouts.user')

@section('title', 'Beranda Saya')
@section('page_title', 'Beranda Saya')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    $user = Auth::user();
    $userName = $user->name ?? 'Warga';
    $firstName = Str::of($userName)->explode(' ')->first() ?: 'Warga';

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
    $jadwalUtama = $jadwalList->first();

    $notifList = collect($notifikasiTerbaru ?? [])->take(5);
    $jumlahNotif = $totalNotifikasiBelumDibaca ?? $notifList->where('is_read', false)->count();

    $totalSasaran = $anakList->count() + $remajaList->count() + $lansiaList->count();

    // Routes
    $monitoringRoute = $routeTo('user.monitoring.index');
    $jadwalRoute = $routeTo('user.jadwal.index');
    $notifikasiRoute = $routeTo('user.notifikasi.index');
    $profileRoute = $routeTo('user.profile.edit');

    $balitaShowRoute = fn ($id) => $routeTo(['user.balita.show', 'user.monitoring.balita.show'], [$id]);
    $remajaShowRoute = fn ($id) => $routeTo(['user.remaja.show', 'user.monitoring.remaja.show'], [$id]);
    $lansiaShowRoute = fn ($id) => $routeTo(['user.lansia.show', 'user.monitoring.lansia.show'], [$id]);

    $formatDate = fn ($value, $format = 'd F Y') => filled($value) && $value !== '-' ? Carbon::parse($value)->translatedFormat($format) : '-';
    $formatTime = fn ($value) => filled($value) && $value !== '-' ? Carbon::parse($value)->format('H:i') : '-';

    // EKSTRAKTOR DATA
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
        $healthItems->push(['type' => 'Balita', 'name' => $getName($anak), 'meta' => $ageText($getDob($anak)), 'href' => $balitaShowRoute(data_get($anak, 'id')), 'icon' => 'fa-baby', 'tone' => 'blue']);
    }
    foreach ($remajaList as $remaja) {
        $healthItems->push(['type' => 'Remaja', 'name' => $getName($remaja), 'meta' => $ageText($getDob($remaja)), 'href' => $remajaShowRoute(data_get($remaja, 'id')), 'icon' => 'fa-child', 'tone' => 'emerald']);
    }
    foreach ($lansiaList as $lansia) {
        $healthItems->push(['type' => 'Lansia', 'name' => $getName($lansia), 'meta' => $ageText($getDob($lansia)), 'href' => $lansiaShowRoute(data_get($lansia, 'id')), 'icon' => 'fa-person-cane', 'tone' => 'amber']);
    }

    $statCards = [
        ['id' => 'stat-keluarga', 'label' => 'Total Sasaran', 'value' => $totalSasaran, 'icon' => 'fa-users', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50', 'label_color' => 'text-blue-600'],
        ['id' => 'stat-riwayat', 'label' => 'Rekam Medis', 'value' => $healthItems->count(), 'icon' => 'fa-notes-medical', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50', 'label_color' => 'text-emerald-600'],
        ['id' => 'stat-jadwal', 'label' => 'Agenda', 'value' => $jadwalList->count(), 'icon' => 'fa-calendar-check', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50', 'label_color' => 'text-amber-600'],
        ['id' => 'stat-notif', 'label' => 'Pesan Baru', 'value' => $jumlahNotif, 'icon' => 'fa-envelope', 'color' => 'text-rose-500', 'bg' => 'bg-rose-50', 'label_color' => 'text-rose-600'],
    ];

    $toneMap = [
        'blue'    => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
        'amber'   => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
        'rose'    => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600'],
    ];

    // ============================================================
    // GRAFIK PERTUMBUHAN: daftar sasaran, ringkasan, & indikator tren
    // ============================================================
    $sasaranList = collect($sasaranList ?? []);
    $sasaranAktif = $sasaranAktif ?? $sasaranList->first();
    $grafikPeriodeAktif = $grafikPeriode ?? 'bulanan';
    $grafikRingkasan = $grafikData['ringkasan'] ?? null;
    $grafikTahunAktif = $grafikData['tahun'] ?? null;
    $grafikTahunTersedia = collect($grafikData['available_years'] ?? []);
    $grafikKosongTahunIni = (bool) ($grafikData['kosong_tahun_ini'] ?? false);

    $grafikTrenInfo = function (?string $tren) {
        return match ($tren) {
            'naik'   => ['icon' => 'fa-arrow-trend-up', 'color' => 'text-emerald-600', 'label' => 'Naik'],
            'turun'  => ['icon' => 'fa-arrow-trend-down', 'color' => 'text-rose-500', 'label' => 'Turun'],
            'stabil' => ['icon' => 'fa-minus', 'color' => 'text-slate-500', 'label' => 'Stabil'],
            default  => null,
        };
    };
@endphp

@push('styles')
<style>
    body {
        background-color: #f8fafc; 
    }

    .animate-pop-up { animation: popUp .5s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .hero-banner {
        background: linear-gradient(to right, #10b981 0%, #0d9488 100%);
        position: relative;
        overflow: hidden;
        border-radius: 3rem; 
        box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.3);
    }
    
    .banner-circle {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 50%;
    }

    .glass-badge {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .premium-card {
        background: #ffffff;
        border-radius: 24px; 
        box-shadow: 0 4px 20px -4px rgba(0, 0, 0, 0.04);
        transition: box-shadow 0.3s ease, transform 0.3s ease;
    }
    .premium-card:hover { 
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08); 
    }

    .value-update { animation: highlightUpdate 1s ease; }
    @keyframes highlightUpdate {
        0% { color: #10b981; transform: scale(1.1); }
        100% { color: inherit; transform: scale(1); }
    }

    .hide-scroll::-webkit-scrollbar { width: 4px; }
    .hide-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .hide-scroll::-webkit-scrollbar-track { background: transparent; }

    /* --- Filter periode grafik (Bulanan / Tahunan) --- */
    .filter-pill {
        color: #64748b;
        background: transparent;
    }
    .filter-pill.active {
        background: #10b981;
        color: #ffffff;
        box-shadow: 0 4px 10px -2px rgba(16, 185, 129, 0.4);
    }
    .filter-pill:hover:not(.active) { color: #0f172a; }

    /* --- Loader ringan --- */
    .live-dot {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.6);
        animation: livePulse 1.6s ease-out infinite;
    }
    @keyframes livePulse {
        0%   { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.55); }
        70%  { box-shadow: 0 0 0 7px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .fade-swap { animation: popUp .35s ease forwards; }

    /* --- Navigasi Tahun (grafik bulanan) --- */
    .year-nav {
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 0.75rem;
    }
    .year-nav-btn {
        color: #64748b;
        width: 28px; height: 28px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 0.5rem;
        transition: background .15s ease, color .15s ease;
    }
    .year-nav-btn:hover:not(:disabled) { background: #e2e8f0; color: #0f172a; }
    .year-nav-btn:disabled { opacity: .35; cursor: not-allowed; }
    .year-nav select {
        background: transparent;
        font-weight: 800;
        font-size: 12px;
        color: #0f172a;
        text-align: center;
        cursor: pointer;
    }
    .year-nav select:focus { outline: none; }

    .grafik-empty-year-hint {
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
    }
</style>
@endpush

@section('content')
<div class="max-w-[1280px] mx-auto animate-pop-up pb-24 px-4 sm:px-6 lg:px-8 mt-6">

    {{-- 1. HERO BANNER --}}
    <div class="hero-banner pt-10 pb-12 px-8 sm:px-14 mb-8 flex flex-col md:flex-row justify-between items-center gap-6">
        <div class="relative z-10 flex flex-col justify-center max-w-2xl w-full">
            <div class="flex flex-wrap items-center gap-3 mb-5">
                <span class="glass-badge px-4 py-1.5 rounded-full flex items-center gap-2 text-[10px] font-bold text-white uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-yellow-300"></span> Mode Warga
                </span>
                <span class="glass-badge px-4 py-1.5 rounded-full flex items-center gap-2 text-[10px] font-bold text-white tracking-wider" id="realtime-date">
                    <i class="far fa-clock text-white/80"></i> Memuat waktu...
                </span>
            </div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white tracking-tight mb-3">
                <span id="dynamic-greeting" class="font-normal opacity-90">Selamat Siang,</span> {{ ucwords($firstName) }}!
            </h1>
            <p class="text-emerald-50 opacity-90 text-sm sm:text-base max-w-xl leading-relaxed">
                Pusat informasi dan kendali Posyandu keluarga Anda. Pantau jadwal, riwayat imunisasi, dan pertumbuhan fisik secara real-time.
            </p>
        </div>
        <div class="hidden md:flex banner-circle w-32 h-32 flex-col items-center justify-center shrink-0 shadow-sm">
            <span class="text-[9px] font-bold text-white uppercase tracking-widest text-center opacity-90 mb-1">Total Sasaran</span>
            <span class="text-4xl font-extrabold text-white leading-none">{{ $totalSasaran }}</span>
        </div>
    </div>

    {{-- 2. STATS CARDS --}}
    <div class="flex items-center justify-between mb-4 px-1">
        <h2 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Ringkasan Cepat</h2>
        <span class="flex items-center gap-1.5 text-[10px] font-bold text-emerald-600 uppercase tracking-widest">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 live-dot"></span> Sinkron Otomatis
        </span>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6 mb-8">
        @foreach($statCards as $card)
            <div class="premium-card p-6 flex flex-col justify-between group cursor-default">
                <div class="flex h-14 w-14 rounded-[16px] {{ $card['bg'] }} {{ $card['color'] }} items-center justify-center text-2xl mb-5 transition-transform group-hover:scale-110 duration-300">
                    <i class="fas {{ $card['icon'] }}"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold {{ $card['label_color'] }} uppercase tracking-widest mb-1 truncate">{{ $card['label'] }}</p>
                    <h3 class="text-4xl font-extrabold text-slate-800 leading-none tracking-tight" id="{{ $card['id'] }}">{{ $card['value'] }}</h3>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ALERT ERROR --}}
    @if(isset($pesanError) && $pesanError)
        <div class="mb-8 rounded-[24px] bg-rose-50/80 border border-rose-100 p-5 flex items-center gap-4 shadow-sm">
            <div class="h-12 w-12 rounded-full bg-white flex items-center justify-center text-rose-500 shrink-0 shadow-sm"><i class="fas fa-exclamation-triangle text-xl"></i></div>
            <div class="flex-1"><h3 class="text-sm font-bold text-rose-900">Pemberitahuan Penting</h3><p class="text-xs font-medium text-rose-700 mt-1">{{ $pesanError }}</p></div>
        </div>
    @endif

    {{-- 3. MAIN CONTENT GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8">
        
        {{-- KOLOM KIRI (Agenda, Grafik, & Keluarga) --}}
        <div class="lg:col-span-8 flex flex-col gap-6 sm:gap-8">
            
            {{-- WIDGET AGENDA TERDEKAT --}}
            <div class="premium-card p-6 sm:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-base font-bold text-slate-800 tracking-wide flex items-center gap-3">
                        <span class="flex h-8 w-8 rounded-lg bg-emerald-50 items-center justify-center text-emerald-500"><i class="fas fa-calendar-alt"></i></span>
                        Agenda Terdekat
                    </h2>
                    <a href="{{ $jadwalRoute }}" class="text-[11px] font-bold uppercase bg-slate-50 hover:bg-emerald-50 text-slate-500 hover:text-emerald-600 px-4 py-2 rounded-xl transition-colors tracking-widest">Lihat Semua</a>
                </div>
                
                <div>
                    @if($jadwalUtama)
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 bg-white border border-slate-100 rounded-[20px] p-5 hover:border-emerald-200 transition-all shadow-[0_2px_12px_rgba(0,0,0,0.02)] group">
                            <div class="flex flex-col items-center justify-center w-[84px] h-[84px] rounded-[18px] bg-emerald-50 text-emerald-600 shrink-0 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                                <span class="text-[10px] font-bold uppercase tracking-widest">{{ $formatDate($jadwalUtama->tanggal, 'M Y') }}</span>
                                <span class="text-3xl font-extrabold leading-none mt-1">{{ $formatDate($jadwalUtama->tanggal, 'd') }}</span>
                            </div>
                            <div class="flex-1 min-w-0 w-full">
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <span class="px-3 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-emerald-50 text-emerald-600">
                                        {{ ucwords(str_replace('_', ' ', $jadwalUtama->target_peserta ?? 'Semua Sasaran')) }}
                                    </span>
                                    <span class="px-3 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-slate-100 text-slate-600">
                                        <i class="far fa-clock mr-1"></i> {{ $formatTime($jadwalUtama->waktu_mulai) }} WIB
                                    </span>
                                </div>
                                <h3 class="text-lg font-bold text-slate-800 truncate" title="{{ $jadwalUtama->judul }}">{{ $jadwalUtama->judul }}</h3>
                                <p class="text-sm font-medium text-slate-500 mt-1.5 truncate">
                                    <i class="fas fa-map-pin text-slate-300 mr-1.5"></i> {{ $jadwalUtama->lokasi ?? 'Lokasi belum ditentukan' }}
                                </p>
                            </div>
                            <div class="shrink-0 w-full sm:w-auto mt-3 sm:mt-0">
                                <a href="{{ route('user.jadwal.show', $jadwalUtama->id) }}" class="flex items-center justify-center w-full sm:w-auto px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold tracking-wide rounded-xl shadow-sm transition-colors">
                                    Detail <i class="fas fa-arrow-right ml-2 text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="py-10 text-center bg-slate-50/50 rounded-[20px] border border-dashed border-slate-200">
                            <i class="fa-regular fa-calendar-xmark text-4xl text-slate-300 mb-3"></i>
                            <h3 class="text-sm font-bold text-slate-700">Tidak ada jadwal</h3>
                            <p class="text-xs text-slate-500 mt-1">Belum ada agenda posyandu dalam waktu dekat.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- WIDGET GRAFIK PERTUMBUHAN --}}
            <div class="premium-card p-6 sm:p-8" id="widget-grafik">
                <div class="flex flex-col gap-5 mb-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-base font-bold text-slate-800 tracking-wide flex items-center gap-3">
                            <span class="flex h-8 w-8 rounded-lg bg-indigo-50 items-center justify-center text-indigo-500"><i class="fas fa-chart-bar"></i></span>
                            Grafik Pertumbuhan Fisik
                        </h2>
                        <span class="text-[10px] font-bold uppercase bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-xl tracking-widest border border-indigo-100">
                            <i class="fas fa-sync-alt mr-1"></i> Real-time
                        </span>
                    </div>

                    @if($sasaranAktif)
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            {{-- Pemilih anggota keluarga (hanya tampil jika sasaran lebih dari satu) --}}
                            @if($sasaranList->count() > 1)
                                <div class="flex items-center gap-2 bg-slate-50 border border-slate-100 rounded-xl px-3 py-2">
                                    <i class="fas fa-user text-slate-400 text-xs"></i>
                                    <select id="grafik-sasaran-select" class="bg-transparent text-xs font-bold text-slate-700 focus:outline-none cursor-pointer max-w-[180px] sm:max-w-none">
                                        @foreach($sasaranList as $s)
                                            <option value="{{ $s['kategori'] }}|{{ $s['id'] }}" @selected($sasaranAktif['id'] == $s['id'] && $sasaranAktif['kategori'] == $s['kategori'])>
                                                {{ $s['nama'] }} &middot; {{ $s['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <p class="text-xs font-semibold text-slate-500">
                                    Menampilkan data <span class="text-slate-700">{{ $sasaranAktif['nama'] }}</span>
                                </p>
                            @endif

                            <div class="flex flex-wrap items-center gap-2">
                                {{-- Navigasi tahun: hanya relevan & tampil pada mode Bulanan --}}
                                <div id="grafik-year-nav" class="year-nav flex items-center gap-0.5 px-1 py-1 {{ $grafikPeriodeAktif === 'tahunan' ? 'hidden' : '' }}">
                                    <button type="button" id="grafik-year-prev" class="year-nav-btn" title="Tahun sebelumnya" aria-label="Tahun sebelumnya">
                                        <i class="fas fa-chevron-left text-[10px]"></i>
                                    </button>
                                    <select id="grafik-year-select" class="px-1.5 py-1 cursor-pointer">
                                        @forelse($grafikTahunTersedia as $th)
                                            <option value="{{ $th }}" @selected($th == $grafikTahunAktif)>{{ $th }}</option>
                                        @empty
                                            <option value="{{ $grafikTahunAktif ?? now()->year }}">{{ $grafikTahunAktif ?? now()->year }}</option>
                                        @endforelse
                                    </select>
                                    <button type="button" id="grafik-year-next" class="year-nav-btn" title="Tahun berikutnya" aria-label="Tahun berikutnya">
                                        <i class="fas fa-chevron-right text-[10px]"></i>
                                    </button>
                                </div>

                                {{-- Filter periode --}}
                                <div class="flex items-center bg-slate-50 border border-slate-100 rounded-xl p-1" role="tablist" aria-label="Filter periode grafik pertumbuhan">
                                    <button type="button" class="filter-pill {{ $grafikPeriodeAktif === 'bulanan' ? 'active' : '' }} px-4 py-1.5 rounded-lg text-[11px] font-bold uppercase tracking-wide transition-all" data-periode="bulanan">Bulanan</button>
                                    <button type="button" class="filter-pill {{ $grafikPeriodeAktif === 'tahunan' ? 'active' : '' }} px-4 py-1.5 rounded-lg text-[11px] font-bold uppercase tracking-wide transition-all" data-periode="tahunan">Tahunan</button>
                                </div>
                            </div>
                        </div>

                        {{-- Info jika tahun yang dipilih belum memiliki riwayat pemeriksaan --}}
                        <div id="grafik-empty-year-hint" class="grafik-empty-year-hint {{ ($grafikPeriodeAktif === 'bulanan' && $grafikKosongTahunIni) ? '' : 'hidden' }} rounded-xl px-4 py-2.5 text-[11px] font-semibold flex items-center gap-2">
                            <i class="fas fa-circle-info"></i>
                            <span>Belum ada pemeriksaan tercatat pada tahun <span id="grafik-empty-year-label">{{ $grafikTahunAktif }}</span>. Coba pilih tahun lain.</span>
                        </div>

                        {{-- Ringkasan angka agar mudah dipahami sekilas pandang --}}
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-emerald-50/60 border border-emerald-100 rounded-2xl p-3.5">
                                <p class="text-[10px] font-bold text-emerald-700 uppercase tracking-widest mb-1 truncate">Berat Terakhir</p>
                                <div class="flex items-end gap-1.5">
                                    <span class="text-xl font-extrabold text-slate-800" id="ringkasan-berat">{{ data_get($grafikRingkasan, 'berat_terakhir', '-') }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 mb-1">Kg</span>
                                </div>
                                <span id="ringkasan-tren-berat">
                                    @php $trenBerat = $grafikTrenInfo(data_get($grafikRingkasan, 'tren_berat')); @endphp
                                    @if($trenBerat)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold mt-1 {{ $trenBerat['color'] }}"><i class="fas {{ $trenBerat['icon'] }}"></i> {{ $trenBerat['label'] }}</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold mt-1 text-slate-400">-</span>
                                    @endif
                                </span>
                            </div>
                            <div class="bg-indigo-50/60 border border-indigo-100 rounded-2xl p-3.5">
                                <p class="text-[10px] font-bold text-indigo-700 uppercase tracking-widest mb-1 truncate">Tinggi Terakhir</p>
                                <div class="flex items-end gap-1.5">
                                    <span class="text-xl font-extrabold text-slate-800" id="ringkasan-tinggi">{{ data_get($grafikRingkasan, 'tinggi_terakhir', '-') }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 mb-1">Cm</span>
                                </div>
                                <span id="ringkasan-tren-tinggi">
                                    @php $trenTinggi = $grafikTrenInfo(data_get($grafikRingkasan, 'tren_tinggi')); @endphp
                                    @if($trenTinggi)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold mt-1 {{ $trenTinggi['color'] }}"><i class="fas {{ $trenTinggi['icon'] }}"></i> {{ $trenTinggi['label'] }}</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold mt-1 text-slate-400">-</span>
                                    @endif
                                </span>
                            </div>
                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-3.5">
                                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 truncate">Pemeriksaan Terakhir</p>
                                <span class="text-sm font-extrabold text-slate-800 block mt-1.5" id="ringkasan-tanggal">{{ data_get($grafikRingkasan, 'tanggal_terakhir', '-') }}</span>
                                <span class="text-[10px] font-semibold text-slate-400" id="grafik-updated-at">&nbsp;</span>
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="relative w-full h-[260px] sm:h-[320px]">
                    <div id="grafik-loading" class="hidden absolute inset-0 z-10 bg-white/70 backdrop-blur-[2px] rounded-[20px] flex items-center justify-center">
                        <div class="flex flex-col items-center gap-2">
                            <i class="fas fa-circle-notch fa-spin text-2xl text-emerald-500"></i>
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Memuat data...</span>
                        </div>
                    </div>

                    <canvas id="growthChart" class="{{ empty($grafikData['labels'] ?? []) ? 'hidden' : '' }}"></canvas>

                    <div id="grafik-empty" class="{{ empty($grafikData['labels'] ?? []) ? '' : 'hidden' }} flex flex-col items-center justify-center h-full bg-slate-50/50 rounded-[20px] border border-dashed border-slate-200">
                        <div class="flex h-16 w-16 rounded-full bg-white shadow-sm items-center justify-center mb-4">
                            <i class="fas fa-chart-area text-3xl text-slate-300"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-700">Grafik Belum Tersedia</h3>
                        <p class="text-xs text-slate-500 mt-1 text-center max-w-xs">Data pemeriksaan antropometri Anda saat ini sedang menunggu validasi klinis dari Bidan.</p>
                    </div>
                </div>

                <p class="text-[11px] text-slate-400 font-medium mt-4 flex items-start gap-1.5">
                    <i class="fas fa-circle-info mt-0.5"></i>
                    <span>Batang <span class="text-emerald-600 font-bold">hijau</span> menunjukkan Berat Badan, batang <span class="text-indigo-600 font-bold">ungu</span> menunjukkan Tinggi Badan. Arahkan kursor atau sentuh grafik untuk melihat detail angka pada setiap titik.</span>
                </p>
            </div>

            {{-- WIDGET KELUARGA --}}
            <div class="premium-card p-6 sm:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-base font-bold text-slate-800 tracking-wide flex items-center gap-3">
                        <span class="flex h-8 w-8 rounded-lg bg-blue-50 items-center justify-center text-blue-500"><i class="fas fa-users"></i></span>
                        Profil Medis Keluarga
                    </h2>
                    <a href="{{ $monitoringRoute }}" class="text-[11px] font-bold uppercase bg-slate-50 hover:bg-blue-50 text-slate-500 hover:text-blue-600 px-4 py-2 rounded-xl transition-colors tracking-widest">Buku Rekam</a>
                </div>
                
                <div>
                    @if($healthItems->isEmpty())
                        <div class="py-10 text-center bg-slate-50/50 rounded-[20px] border border-dashed border-slate-200">
                            <i class="fas fa-user-plus text-4xl text-slate-300 mb-3"></i>
                            <h3 class="text-sm font-bold text-slate-700">Belum Ada Data</h3>
                            <a href="{{ $profileRoute }}" class="text-xs font-bold text-blue-600 mt-2 inline-block hover:underline">Sinkronisasi NIK Sekarang</a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($healthItems->take(4) as $item)
                                @php $tone = $toneMap[$item['tone']]; @endphp
                                <a href="{{ $item['href'] }}" class="flex items-center gap-4 p-4 rounded-[20px] border border-slate-100 hover:border-slate-300 hover:shadow-md transition-all group bg-white">
                                    <div class="h-14 w-14 rounded-2xl {{ $tone['bg'] }} {{ $tone['text'] }} flex items-center justify-center text-2xl shrink-0">
                                        <i class="fas {{ $item['icon'] }}"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-sm font-bold text-slate-800 truncate">{{ $item['name'] }}</h4>
                                        <p class="text-xs font-medium text-slate-500 mt-1 truncate">{{ $item['meta'] }}</p>
                                    </div>
                                    <span class="text-[10px] font-bold uppercase tracking-widest {{ $tone['text'] }} {{ $tone['bg'] }} px-2.5 py-1.5 rounded-lg">{{ $item['type'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN (Pesan Bidan) --}}
        <div class="lg:col-span-4 h-full relative">
            <div class="premium-card p-6 flex flex-col h-full lg:absolute lg:inset-0">
                <div class="flex items-center justify-between mb-6 shrink-0">
                    <h2 class="text-base font-bold text-slate-800 tracking-wide flex items-center gap-3">
                        <span class="flex h-8 w-8 rounded-lg bg-rose-50 items-center justify-center text-rose-500"><i class="fas fa-envelope-open-text"></i></span>
                        Pesan Bidan
                        @if($jumlahNotif > 0)
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-white text-[10px] font-bold" id="notif-badge">{{ $jumlahNotif }}</span>
                        @endif
                    </h2>
                    <a href="{{ $notifikasiRoute }}" class="text-[11px] font-bold uppercase text-rose-500 hover:text-rose-700 transition-colors tracking-widest">Semua</a>
                </div>

                <div class="flex-1 overflow-y-auto hide-scroll space-y-3 pr-2">
                    @forelse($notifList as $notif)
                        @php $isNew = !($notif['is_read'] ?? true); @endphp
                        <a href="{{ $notifikasiRoute }}" class="block p-5 rounded-[20px] transition-all {{ $isNew ? 'bg-rose-50/50 border border-rose-100' : 'bg-slate-50/50 border border-transparent hover:bg-slate-100' }}">
                            <div class="flex gap-4">
                                <div class="mt-1 shrink-0">
                                    <div class="h-2.5 w-2.5 rounded-full {{ $isNew ? 'bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.5)]' : 'bg-slate-300' }}"></div>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-slate-800 truncate">{{ $notif['judul'] ?? 'Pemberitahuan' }}</h4>
                                    <p class="text-xs font-medium text-slate-500 line-clamp-2 mt-1.5 leading-relaxed">{{ $notif['pesan'] ?? '-' }}</p>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-3 block flex items-center gap-1.5">
                                        <i class="far fa-clock"></i> {{ $notif['waktu'] ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="py-12 text-center flex flex-col items-center justify-center h-full">
                            <i class="fas fa-check-circle text-5xl text-emerald-200 mb-4"></i>
                            <h4 class="text-sm font-bold text-slate-600">Semua Terbaca</h4>
                            <p class="text-xs text-slate-400 mt-1">Belum ada pesan baru untuk Anda.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
{{-- Pustaka Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. DATE & GREETING
    const dateEl = document.getElementById('realtime-date');
    const greetingEl = document.getElementById('dynamic-greeting');

    function updateTimeInfo() {
        const now = new Date();
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        if (dateEl) {
            dateEl.innerHTML = `<i class="far fa-clock text-white/80"></i> ${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()} • ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')} WIB`;
        }

        const h = now.getHours();
        let greeting = 'Selamat Malam,';
        if (h >= 4 && h < 11) greeting = 'Selamat Pagi,';
        else if (h >= 11 && h < 15) greeting = 'Selamat Siang,';
        else if (h >= 15 && h < 18) greeting = 'Selamat Sore,';

        if (greetingEl && greetingEl.textContent !== greeting) greetingEl.textContent = greeting;
    }
    
    updateTimeInfo();
    setInterval(updateTimeInfo, 60000); 

    // 2. GRAFIK PERTUMBUHAN BATANG (Chart.js)
    (function () {
        const canvasEl = document.getElementById('growthChart');
        if (!canvasEl) return;

        const grafikUrl = '{{ Route::has("user.dashboard.chart") ? route("user.dashboard.chart") : "" }}';
        const emptyEl = document.getElementById('grafik-empty');
        const loadingEl = document.getElementById('grafik-loading');
        const updatedAtEl = document.getElementById('grafik-updated-at');
        const selectEl = document.getElementById('grafik-sasaran-select');
        const filterButtons = document.querySelectorAll('.filter-pill');
        const ringkasanBerat = document.getElementById('ringkasan-berat');
        const ringkasanTinggi = document.getElementById('ringkasan-tinggi');
        const ringkasanTanggal = document.getElementById('ringkasan-tanggal');
        const trenBeratEl = document.getElementById('ringkasan-tren-berat');
        const trenTinggiEl = document.getElementById('ringkasan-tren-tinggi');

        // Navigasi tahun (khusus mode Bulanan)
        const yearNavEl = document.getElementById('grafik-year-nav');
        const yearSelectEl = document.getElementById('grafik-year-select');
        const yearPrevBtn = document.getElementById('grafik-year-prev');
        const yearNextBtn = document.getElementById('grafik-year-next');
        const emptyYearHintEl = document.getElementById('grafik-empty-year-hint');
        const emptyYearLabelEl = document.getElementById('grafik-empty-year-label');

        let chartInstance = null;
        let currentPeriode = @json($grafikPeriodeAktif);
        let currentKategori = @json($sasaranAktif['kategori'] ?? null);
        let currentPasienId = @json($sasaranAktif['id'] ?? null);
        let currentTahun = @json($grafikTahunAktif) || new Date().getFullYear();
        let availableYears = @json($grafikTahunTersedia->values());

        const trenMap = {
            naik:   { icon: 'fa-arrow-trend-up', cls: 'text-emerald-600', label: 'Naik' },
            turun:  { icon: 'fa-arrow-trend-down', cls: 'text-rose-500', label: 'Turun' },
            stabil: { icon: 'fa-minus', cls: 'text-slate-500', label: 'Stabil' },
        };

        function renderTren(el, tren) {
            if (!el) return;
            const info = trenMap[tren];
            el.innerHTML = info
                ? `<span class="inline-flex items-center gap-1 text-[10px] font-bold mt-1 ${info.cls}"><i class="fas ${info.icon}"></i> ${info.label}</span>`
                : `<span class="inline-flex items-center gap-1 text-[10px] font-bold mt-1 text-slate-400">-</span>`;
        }

        function xAxisTitle(periode, tahun) {
            return periode === 'tahunan' ? 'Tahun' : ('Bulan' + (tahun ? ' (' + tahun + ')' : ''));
        }

        function buildOrUpdateChart(labels, berat, tinggi, periode, tahun) {
            if (chartInstance) {
                chartInstance.data.labels = labels;
                chartInstance.data.datasets[0].data = berat;
                chartInstance.data.datasets[1].data = tinggi;
                chartInstance.options.scales.x.title.text = xAxisTitle(periode, tahun);
                chartInstance.update();
                return;
            }

            const ctx = canvasEl.getContext('2d');

            chartInstance = new Chart(ctx, {
                type: 'line', 
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Berat Badan (Kg)',
                            data: berat,
                            backgroundColor: 'rgba(16, 185, 129, 0.15)',
                            borderColor: '#10b981',
                            borderWidth: 3,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#10b981',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.4, 
                            spanGaps: true, 
                            yAxisID: 'y'
                        },
                        {
                            label: 'Tinggi Badan (Cm)',
                            data: tinggi,
                            backgroundColor: 'transparent', 
                            borderColor: '#6366f1',
                            borderWidth: 3,
                            borderDash: [5, 5], 
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#6366f1',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            tension: 0.4,
                            spanGaps: true, 
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: { family: "'Nunito', 'Segoe UI', 'Arial', sans-serif", size: 12, weight: 'bold' },
                                color: '#475569'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleFont: { size: 13, family: "'Nunito', sans-serif" },
                            bodyFont: { size: 12, family: "'Nunito', sans-serif" },
                            padding: 12,
                            cornerRadius: 12,
                            displayColors: true,
                            usePointStyle: true
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: { font: { size: 11 }, color: '#64748b' },
                            title: {
                                display: true,
                                text: xAxisTitle(periode, tahun),
                                color: '#94a3b8',
                                font: { size: 10, weight: 'bold' }
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            grid: { color: '#f1f5f9', drawBorder: false },
                            title: { display: true, text: 'Berat (Kg)', color: '#10b981', font: { size: 11, weight: 'bold' } },
                            ticks: { color: '#64748b' }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            title: { display: true, text: 'Tinggi (Cm)', color: '#6366f1', font: { size: 11, weight: 'bold' } },
                            ticks: { color: '#64748b' }
                        }
                    }
                }
            });
        }

        function showEmpty(message) {
            canvasEl.classList.add('hidden');
            if (emptyEl) {
                emptyEl.classList.remove('hidden');
                const msgEl = emptyEl.querySelector('p');
                if (msgEl && message) msgEl.textContent = message;
            }
        }

        function showChart() {
            if (emptyEl) emptyEl.classList.add('hidden');
            canvasEl.classList.remove('hidden');
        }

        // PERBAIKAN: Fungsi ini kini hanya menampilkan tahun yang BENAR-BENAR ADA di database
        function renderYearSelect(selectedYear) {
            if (!yearSelectEl) return;
            
            // Ambil array tahun dari backend, jika kosong gunakan tahun ini
            let years = (Array.isArray(availableYears) && availableYears.length > 0) 
                ? [...availableYears] 
                : [new Date().getFullYear()];

            // Pastikan tahun yg aktif terpilih selalu ada di dalam daftar
            if (!years.includes(Number(selectedYear))) {
                years.push(Number(selectedYear));
            }

            // Urutkan dari terbaru ke terlama secara mendatar
            years.sort((a, b) => b - a);

            const options = [];
            years.forEach(function (y) {
                options.push(`<option value="${y}" ${y === Number(selectedYear) ? 'selected' : ''}>${y}</option>`);
            });
            
            yearSelectEl.innerHTML = options.join('');
            updateYearNavButtons();
        }

        function updateYearNavButtons() {
            if (!yearSelectEl) return;
            const selectedIndex = yearSelectEl.selectedIndex;
            // prevBtn = tahun sebelumnya (lebih lama) -> letaknya di index lebih besar
            if (yearPrevBtn) yearPrevBtn.disabled = selectedIndex >= (yearSelectEl.options.length - 1);
            // nextBtn = tahun berikutnya (lebih baru) -> letaknya di index lebih kecil
            if (yearNextBtn) yearNextBtn.disabled = selectedIndex <= 0;
        }

        function toggleYearNav(show) {
            if (yearNavEl) yearNavEl.classList.toggle('hidden', !show);
        }

        function toggleEmptyYearHint(show, tahun) {
            if (!emptyYearHintEl) return;
            emptyYearHintEl.classList.toggle('hidden', !show);
            if (show && emptyYearLabelEl) emptyYearLabelEl.textContent = tahun ?? '-';
        }

        function applyResponse(data) {
            if (Array.isArray(data.available_years)) availableYears = data.available_years;
            if (data.periode) currentPeriode = data.periode;
            if (data.tahun) currentTahun = data.tahun;

            const isBulanan = currentPeriode === 'bulanan';
            toggleYearNav(isBulanan);
            if (isBulanan) renderYearSelect(currentTahun);

            if (data.status === 'success') {
                showChart();
                buildOrUpdateChart(data.labels, data.berat, data.tinggi, data.periode, data.tahun);

                const r = data.ringkasan || {};
                if (ringkasanBerat) ringkasanBerat.textContent = (r.berat_terakhir ?? '-');
                if (ringkasanTinggi) ringkasanTinggi.textContent = (r.tinggi_terakhir ?? '-');
                if (ringkasanTanggal) ringkasanTanggal.textContent = (r.tanggal_terakhir ?? '-');
                renderTren(trenBeratEl, r.tren_berat);
                renderTren(trenTinggiEl, r.tren_tinggi);

                toggleEmptyYearHint(isBulanan && !!data.kosong_tahun_ini, currentTahun);
            } else if (data.status === 'empty') {
                showEmpty(data.message);
                toggleEmptyYearHint(false);
            }

            if (updatedAtEl && data.updated_at) {
                updatedAtEl.textContent = 'Diperbarui ' + data.updated_at + ' WIB';
            }
        }

        function fetchGrafik(showLoader) {
            if (!grafikUrl || !currentKategori || !currentPasienId) return;

            if (showLoader && loadingEl) loadingEl.classList.remove('hidden');

            const params = new URLSearchParams({
                periode: currentPeriode,
                kategori: currentKategori,
                pasien_id: currentPasienId,
            });
            if (currentPeriode === 'bulanan' && currentTahun) {
                params.set('tahun', currentTahun);
            }

            fetch(grafikUrl + '?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.ok ? res.json() : Promise.reject('Network err'))
            .then(applyResponse)
            .catch(() => {})
            .finally(() => {
                if (loadingEl) loadingEl.classList.add('hidden');
            });
        }

        // Inisialisasi Awal
        toggleYearNav(currentPeriode === 'bulanan');
        if (currentPeriode === 'bulanan') renderYearSelect(currentTahun);

        @if(!empty($grafikData['labels'] ?? []))
            buildOrUpdateChart(
                {!! json_encode($grafikData['labels']) !!},
                {!! json_encode($grafikData['berat']) !!},
                {!! json_encode($grafikData['tinggi']) !!},
                currentPeriode,
                currentTahun
            );
            toggleEmptyYearHint(currentPeriode === 'bulanan' && @json($grafikKosongTahunIni), currentTahun);
        @endif

        filterButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (this.classList.contains('active')) return;
                filterButtons.forEach(function (b) { b.classList.remove('active'); });
                this.classList.add('active');
                currentPeriode = this.dataset.periode;
                toggleYearNav(currentPeriode === 'bulanan');
                fetchGrafik(true);
            });
        });

        if (selectEl) {
            selectEl.addEventListener('change', function () {
                const parts = this.value.split('|');
                currentKategori = parts[0];
                currentPasienId = parts[1];
                currentTahun = null;
                fetchGrafik(true);
            });
        }

        // PERBAIKAN: Menavigasi ke tahun yang LAMA berdasar urutan Index
        if (yearPrevBtn) {
            yearPrevBtn.addEventListener('click', function () {
                if (this.disabled || !yearSelectEl) return;
                yearSelectEl.selectedIndex += 1; 
                currentTahun = Number(yearSelectEl.value);
                updateYearNavButtons();
                fetchGrafik(true);
            });
        }
        
        // PERBAIKAN: Menavigasi ke tahun yang BARU berdasar urutan Index
        if (yearNextBtn) {
            yearNextBtn.addEventListener('click', function () {
                if (this.disabled || !yearSelectEl) return;
                yearSelectEl.selectedIndex -= 1; 
                currentTahun = Number(yearSelectEl.value);
                updateYearNavButtons();
                fetchGrafik(true);
            });
        }

        if (yearSelectEl) {
            yearSelectEl.addEventListener('change', function () {
                currentTahun = Number(this.value);
                updateYearNavButtons();
                fetchGrafik(true);
            });
        }

        if (currentKategori && currentPasienId) {
            setInterval(function () { fetchGrafik(false); }, 30000);
        }
    })();

    // 3. SAFE AJAX POLLING (Statistik Angka Real-Time)
    const statsUrl = '{{ Route::has("user.dashboard.stats") ? route("user.dashboard.stats") : "" }}'; 
    const statKeluarga = document.getElementById('stat-keluarga');
    const statNotif = document.getElementById('stat-notif');
    const statJadwal = document.getElementById('stat-jadwal');
    
    function fetchDashboardStatsSafe() {
        if (!statsUrl || statsUrl.trim() === '') return;

        fetch(statsUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.ok ? res.json() : Promise.reject('Network err'))
        .then(data => {
            if (data.status === 'success') {
                updateValueWithAnimation(statKeluarga, data.total_sasaran);
                updateValueWithAnimation(statNotif, data.unread_count);
                updateValueWithAnimation(statJadwal, data.total_jadwal);
                
                const badge = document.getElementById('notif-badge');
                if(badge && data.unread_count !== undefined) {
                    badge.textContent = data.unread_count;
                    badge.style.display = data.unread_count > 0 ? 'flex' : 'none';
                }
            }
        })
        .catch(() => {});
    }

    function updateValueWithAnimation(element, newValue) {
        if (element && element.innerText != newValue && newValue !== undefined) {
            element.innerText = newValue;
            element.classList.remove('value-update');
            void element.offsetWidth; 
            element.classList.add('value-update');
        }
    }

    if(statsUrl) {
        setInterval(fetchDashboardStatsSafe, 15000);
    }
});
</script>
@endpush