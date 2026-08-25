@extends('layouts.bidan')

@section('title', 'Rekam Medis')
@section('page-name', 'Rekam Medis')
@section('page-title', 'Rekam Medis')

@php
    $data = $data ?? collect();
    $type = $type ?? request('type', 'balita');
    $search = $search ?? request('search', '');

    // KONSISTENSI WARNA: Semua menggunakan identitas Emerald Posyandu[cite: 19]
    $typeOptions = $typeOptions ?? [
        'balita' => ['label' => 'Balita', 'desc' => 'Riwayat pertumbuhan, pemeriksaan dasar, dan imunisasi.'],
        'remaja' => ['label' => 'Remaja', 'desc' => 'Riwayat pemeriksaan kesehatan dan pemantauan gizi remaja.'],
        'lansia' => ['label' => 'Lansia', 'desc' => 'Riwayat pemeriksaan dasar dan pemantauan kesehatan lansia.'],
    ];

    $getUiTheme = function ($key) {
        return match ($key) {
            'remaja' => [
                'icon' => 'fa-user-graduate',
                'theme' => 'emerald',
                'gradient' => 'from-teal-500 via-emerald-500 to-green-500',
                'shadow' => 'shadow-emerald-500/20'
            ],
            'lansia' => [
                'icon' => 'fa-person-cane',
                'theme' => 'emerald',
                'gradient' => 'from-emerald-600 via-teal-500 to-emerald-500',
                'shadow' => 'shadow-emerald-500/20'
            ],
            default => [
                'icon' => 'fa-baby',
                'theme' => 'emerald',
                'gradient' => 'from-emerald-400 via-emerald-500 to-teal-500',
                'shadow' => 'shadow-emerald-500/20'
            ],
        };
    };

    $stats = $stats ?? [
        'total' => ['balita' => 0, 'remaja' => 0, 'lansia' => 0],
        'verified' => ['balita' => 0, 'remaja' => 0, 'lansia' => 0],
        'total_semua' => 0,
        'verified_semua' => 0,
    ];

    $getValue = function ($item, array $keys, mixed $default = '-') {
        foreach ($keys as $key) {
            $value = data_get($item, $key);
            if ($value !== null && $value !== '') return $value;
        }
        return $default;
    };

    $getNama = fn($item) => $getValue($item, ['nama_lengkap', 'nama', 'nama_balita', 'nama_remaja', 'nama_lansia'], 'Nama tidak tersedia');
    $getNik = fn($item) => $getValue($item, ['nik', 'nik_anak', 'nik_remaja', 'nik_lansia'], '-');
    $getAlamat = fn($item) => $getValue($item, ['alamat', 'alamat_lengkap', 'dusun'], '-');
    $getKontak = fn($item) => $getValue($item, ['no_hp', 'nomor_hp', 'telepon', 'no_telepon'], '-');
    
    $getWali = function ($item) use ($getValue, $type) {
        if ($type === 'balita') return $getValue($item, ['nama_ibu', 'nama_ayah', 'nama_wali'], '-');
        if ($type === 'remaja') return $getValue($item, ['nama_orang_tua', 'nama_wali', 'sekolah'], '-');
        return $getValue($item, ['kontak_keluarga', 'nama_keluarga', 'nama_wali'], '-');
    };

    $getGender = function ($item) use ($getValue) {
        $gender = strtolower((string) $getValue($item, ['jenis_kelamin', 'jk', 'gender'], '-'));
        return match ($gender) {
            'l', 'laki-laki', 'male' => 'Laki-laki',
            'p', 'perempuan', 'female' => 'Perempuan',
            default => $gender === '-' ? '-' : ucfirst($gender),
        };
    };

    $formatDate = function ($date) {
        if (!$date) return '-';
        try { return \Carbon\Carbon::parse($date)->translatedFormat('d M Y'); } catch (\Throwable $e) { return '-'; }
    };

    $getTanggalLahir = fn($item) => $formatDate($getValue($item, ['tanggal_lahir', 'tgl_lahir', 'lahir'], null));

    $currentTypeMeta = $typeOptions[$type] ?? $typeOptions['balita'];
    $currentUi = $getUiTheme($type);
    
    $totalCurrent = (int) data_get($stats, "total.$type", 0);
    $verifiedCurrent = (int) data_get($stats, "verified.$type", 0);
    $visibleCount = method_exists($data, 'count') ? $data->count() : count($data);

    $summaryCards = [
        ['label' => 'Total Sasaran', 'value' => $totalCurrent, 'icon' => $currentUi['icon'], 'theme' => $currentUi['theme']],
        ['label' => 'Rekam Medis Tervalidasi', 'value' => $verifiedCurrent, 'icon' => 'fa-file-medical', 'theme' => 'emerald'],
        ['label' => 'Semua Sasaran', 'value' => data_get($stats, 'total_semua', 0), 'icon' => 'fa-users', 'theme' => 'slate'],
        ['label' => 'Total Tervalidasi', 'value' => data_get($stats, 'verified_semua', 0), 'icon' => 'fa-check-circle', 'theme' => 'teal'],
    ];
@endphp

@push('styles')
<style>
    html { scroll-behavior: smooth; }
    body { background-color: #f4f7f6; } 

    .bg-mesh-fixed {
        position: fixed; inset: 0; z-index: -10;
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(150, 100%, 94%, 1) 0px, transparent 50%);
        pointer-events: none;
    }

    .widget-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        transform: translateZ(0); 
        will-change: transform, box-shadow;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .widget-card:hover {
        transform: translateY(-2px) translateZ(0);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
    }

    .btn-pill { border-radius: 9999px; transition: all 0.2s ease; cursor: pointer; }
    .btn-pill:active { transform: scale(0.97); }

    .slim-scroll { -webkit-overflow-scrolling: touch; overscroll-behavior-y: contain; }
    .slim-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: rgba(16, 185, 129, 0.3); border-radius: 9999px; }
    .slim-scroll::-webkit-scrollbar-thumb:hover { background: rgba(16, 185, 129, 0.5); }

    .animate-pop-in { animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popIn { from { opacity: 0; transform: scale(.98) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
</style>
@endpush

@section('content')
<div class="bg-mesh-fixed"></div>

<div class="px-3 py-6 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6 sm:space-y-8 animate-pop-in">

    {{-- 1. HEADER BANNER DINAMIS --}}
    <section class="relative overflow-hidden rounded-[2.5rem] sm:rounded-[3rem] bg-gradient-to-r {{ $currentUi['gradient'] }} p-6 sm:p-10 shadow-xl sm:shadow-2xl {{ $currentUi['shadow'] }} flex flex-col lg:flex-row justify-between items-center gap-6 sm:gap-8 border-[4px] sm:border-[6px] border-white/40" style="transform: translateZ(0);">
        <div class="absolute inset-0 bg-white/10 backdrop-blur-[2px]"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-full lg:w-2/3 flex flex-col gap-3 sm:gap-4 text-center lg:text-left">
            <div class="inline-flex justify-center lg:justify-start items-center gap-2">
                <span class="btn-pill bg-white/20 border border-white/30 text-white px-3 sm:px-4 py-1.5 text-[10px] font-black uppercase tracking-widest backdrop-blur-md shadow-inner flex items-center gap-2">
                    <i class="fa-solid fa-folder-tree"></i> Arsip Kesehatan
                </span>
            </div>
            
            <h1 class="text-2xl sm:text-4xl md:text-5xl font-black text-white tracking-tight leading-tight">
                Rekam Medis {{ $currentTypeMeta['label'] ?? ucfirst($type) }}
            </h1>

            <p class="text-white/90 text-xs sm:text-sm font-medium leading-relaxed max-w-xl mx-auto lg:mx-0">
                Akses ringkas riwayat pemeriksaan klinis yang telah divalidasi oleh Bidan untuk sasaran {{ $currentTypeMeta['label'] ?? ucfirst($type) }}.
            </p>
        </div>

        <div class="relative z-10 w-full lg:w-1/3 flex justify-center lg:justify-end">
            <div class="widget-card !rounded-[1.75rem] sm:!rounded-[2rem] !shadow-none bg-white/90 border border-white/50 backdrop-blur-md px-5 sm:px-6 py-3.5 sm:py-4 flex items-center gap-4 sm:gap-5 shadow-lg">
                <div>
                    <span class="block text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-teal-800">Total Tampil</span>
                    <span class="block text-2xl sm:text-3xl font-black text-slate-900 mt-0.5" id="rekamMedisVisibleCount">{{ $visibleCount }}</span>
                </div>
                <div class="flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-2xl bg-teal-500 text-white shadow-md">
                    <i class="fa-solid fa-database text-xl sm:text-2xl"></i>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. SUMMARY CARDS --}}
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach($summaryCards as $card)
            <div class="widget-card p-4 sm:p-5 group flex items-center justify-between">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1 group-hover:text-{{ $card['theme'] }}-500 transition-colors">{{ $card['label'] }}</p>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-800 leading-none">{{ $card['value'] }}</h2>
                </div>
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-{{ $card['theme'] }}-50 text-{{ $card['theme'] }}-500 border border-{{ $card['theme'] }}-100 flex items-center justify-center text-xl sm:text-2xl shrink-0 group-hover:scale-110 transition-transform">
                    <i class="fa-solid {{ $card['icon'] }}"></i>
                </div>
            </div>
        @endforeach
    </section>

    {{-- 3. TYPE SELECTOR (TABS) --}}
    <section class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
        @foreach($typeOptions as $key => $option)
            @php 
                $active = $type === $key; 
                $ui = $getUiTheme($key);
            @endphp
            <a href="{{ route('bidan.rekam-medis.index', ['type' => $key]) }}"
               class="widget-card p-4 sm:p-5 border-2 transition-all {{ $active ? 'border-'.$ui['theme'].'-400 shadow-md shadow-'.$ui['theme'].'-500/10 bg-'.$ui['theme'].'-50/30' : 'border-transparent hover:border-emerald-200' }}">
                <div class="flex gap-3 sm:gap-4 items-center">
                    <div class="flex h-12 w-12 sm:h-14 sm:w-14 shrink-0 items-center justify-center rounded-2xl border bg-white shadow-sm {{ $active ? 'text-'.$ui['theme'].'-500 border-'.$ui['theme'].'-200' : 'text-slate-400 border-slate-100' }}">
                        <i class="fa-solid {{ $ui['icon'] }} text-xl sm:text-2xl"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-sm sm:text-base font-black text-slate-800">{{ $option['label'] }}</h3>
                            <span class="rounded-md bg-white px-2 py-0.5 text-[9px] sm:text-[10px] font-black border shadow-sm {{ $active ? 'text-'.$ui['theme'].'-600 border-'.$ui['theme'].'-200' : 'text-slate-400 border-slate-200' }}">
                                {{ data_get($stats, "total.$key", 0) }} Data
                            </span>
                        </div>
                        <p class="truncate text-[10px] sm:text-[11px] font-semibold text-slate-500">{{ $option['desc'] }}</p>
                    </div>
                </div>
            </a>
        @endforeach
    </section>

    {{-- 4. MAIN TABLE AREA (FIXED HEIGHT & SCROLLABLE) --}}
    <section class="widget-card overflow-hidden flex flex-col relative">
        
        {{-- Header & Pencarian --}}
        <div class="p-4 sm:p-6 border-b border-slate-100 bg-white flex flex-col gap-3 sm:gap-4 xl:flex-row xl:items-center xl:justify-between shrink-0 z-20">
            <div class="min-w-0 flex items-center gap-3">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-{{ $currentUi['theme'] }}-50 text-{{ $currentUi['theme'] }}-500 flex items-center justify-center text-base sm:text-lg shadow-sm border border-{{ $currentUi['theme'] }}-100"><i class="fa-solid fa-address-book"></i></div>
                <div>
                    <h2 class="text-sm sm:text-base font-black tracking-tight text-slate-800 uppercase">Direktori Pasien</h2>
                    <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 tracking-widest uppercase mt-0.5">Database {{ $currentTypeMeta['label'] ?? ucfirst($type) }}</p>
                </div>
            </div>

            <form method="GET" action="{{ route('bidan.rekam-medis.index') }}" class="flex w-full gap-2 xl:max-w-md relative">
                <input type="hidden" name="type" value="{{ $type }}">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="rekamMedisLiveSearch" name="search" value="{{ $search }}" autocomplete="off" placeholder="Cari nama atau NIK sasaran..."
                       class="w-full rounded-[1.2rem] border border-slate-200 bg-slate-50 py-2.5 sm:py-3 pl-10 pr-10 text-xs font-bold text-slate-700 outline-none transition focus:border-{{ $currentUi['theme'] }}-400 focus:bg-white focus:ring-4 focus:ring-{{ $currentUi['theme'] }}-100 shadow-inner">
                
                <button type="button" id="rekamMedisClearSearch" class="absolute right-3 top-1/2 hidden h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-slate-400 hover:bg-slate-200 hover:text-slate-700 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </form>
        </div>

        {{-- Tabel Scrollable Area --}}
        <div class="min-h-[200px] max-h-[600px] overflow-y-auto overflow-x-auto slim-scroll relative bg-slate-50/30">
            <div class="hidden lg:block w-full min-w-[1000px]">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead class="sticky top-0 z-10 bg-white/95 backdrop-blur-sm border-b border-slate-200 shadow-sm">
                        <tr>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Pasien</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">NIK</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Gender / Usia</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Wali / Kontak</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Alamat Domisili</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="rekamMedisTableBody" class="divide-y divide-slate-100 bg-white">
                        @forelse($data as $pasien)
                            @php
                                $nama = $getNama($pasien);
                                $nik = $getNik($pasien);
                                $alamat = $getAlamat($pasien);
                                $kontak = $getKontak($pasien);
                                $wali = $getWali($pasien);
                                $gender = $getGender($pasien);
                                $tanggalLahir = $getTanggalLahir($pasien);

                                $searchName = mb_strtolower(trim((string) $nama), 'UTF-8');
                                $searchNik = mb_strtolower(trim((string) $nik), 'UTF-8');
                            @endphp

                            <tr class="js-rekam-row hover:bg-slate-50/80 transition-colors group" data-name="{{ $searchName }}" data-nik="{{ $searchNik }}">
                                <td class="px-6 py-5 align-middle">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-50 text-slate-400 border border-slate-200 group-hover:border-{{ $currentUi['theme'] }}-300 group-hover:text-{{ $currentUi['theme'] }}-500 transition-colors">
                                            <i class="fa-solid fa-user text-sm"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-slate-800 group-hover:text-{{ $currentUi['theme'] }}-600 transition-colors">{{ $nama }}</p>
                                            <span class="mt-1 inline-flex rounded-md bg-{{ $currentUi['theme'] }}-50 px-2 py-0.5 text-[8px] font-black uppercase tracking-widest text-{{ $currentUi['theme'] }}-600 border border-{{ $currentUi['theme'] }}-100">{{ $currentTypeMeta['label'] ?? ucfirst($type) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <p class="text-xs font-bold text-slate-600 font-mono">{{ $nik }}</p>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <p class="text-xs font-black text-slate-700">{{ $gender }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 mt-0.5"><i class="fa-solid fa-cake-candles mr-1"></i> {{ $tanggalLahir }}</p>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <p class="text-xs font-bold text-slate-700">{{ $wali }}</p>
                                    <p class="text-[10px] font-semibold text-slate-400 mt-0.5 flex items-center gap-1"><i class="fa-solid fa-phone"></i> {{ $kontak }}</p>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <p class="text-[11px] font-medium text-slate-600 max-w-[200px] truncate" title="{{ $alamat }}"><i class="fa-solid fa-map-location-dot text-slate-400 mr-1"></i> {{ $alamat }}</p>
                                </td>
                                <td class="px-6 py-5 align-middle text-right">
                                    <a href="{{ route('bidan.rekam-medis.show', [$type, $pasien->id]) }}" class="btn-pill inline-flex items-center gap-2 bg-slate-900 text-white px-4 py-2 text-[10px] font-black uppercase tracking-widest shadow-md hover:bg-{{ $currentUi['theme'] }}-600 transition-colors">
                                        <i class="fa-solid fa-folder-open"></i> Buka Berkas
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-14 text-center bg-white">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-slate-50 border border-slate-100 text-slate-300 mb-4 shadow-inner">
                                        <i class="fa-solid fa-folder-open text-3xl"></i>
                                    </div>
                                    <h3 class="text-sm font-black text-slate-700">Data Sasaran Kosong</h3>
                                    <p class="text-xs font-medium text-slate-400 mt-1">Belum ada pasien {{ $currentTypeMeta['label'] ?? ucfirst($type) }} yang terdaftar.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE VIEW --}}
            <div id="rekamMedisCardContainer" class="space-y-3 lg:hidden p-3 sm:p-4">
                @foreach($data as $pasien)
                    @php
                        $nama = $getNama($pasien);
                        $nik = $getNik($pasien);
                        $alamat = $getAlamat($pasien);
                        $kontak = $getKontak($pasien);
                        $wali = $getWali($pasien);
                        $gender = $getGender($pasien);
                        $tanggalLahir = $getTanggalLahir($pasien);

                        $searchName = mb_strtolower(trim((string) $nama), 'UTF-8');
                        $searchNik = mb_strtolower(trim((string) $nik), 'UTF-8');
                    @endphp
                    <article class="js-rekam-card rounded-[1.5rem] border border-slate-100 bg-white p-4 sm:p-5 shadow-sm" data-name="{{ $searchName }}" data-nik="{{ $searchNik }}">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-50 text-slate-400 border border-slate-200 flex items-center justify-center"><i class="fa-solid fa-user"></i></div>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-bold text-slate-900 truncate">{{ $nama }}</h3>
                                    <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $nik }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-slate-50 mb-3">
                            <div class="bg-slate-50 rounded-lg p-2"><p class="text-[9px] font-black uppercase text-slate-400 mb-0.5">Gender</p><p class="text-xs font-bold text-slate-700">{{ $gender }}</p></div>
                            <div class="bg-slate-50 rounded-lg p-2"><p class="text-[9px] font-black uppercase text-slate-400 mb-0.5">Lahir</p><p class="text-xs font-bold text-slate-700">{{ $tanggalLahir }}</p></div>
                        </div>
                        <a href="{{ route('bidan.rekam-medis.show', [$type, $pasien->id]) }}" class="btn-pill w-full inline-flex justify-center items-center bg-slate-900 text-white px-3 py-2.5 text-[10px] font-black uppercase tracking-widest shadow-sm">Buka Berkas Rekam Medis</a>
                    </article>
                @endforeach
            </div>

            {{-- LIVE EMPTY STATE --}}
            <div id="rekamMedisLiveEmpty" class="hidden rounded-[2rem] border border-dashed border-slate-300 bg-white/50 p-10 text-center m-6">
                <i class="fa-solid fa-magnifying-glass text-4xl text-slate-300 mb-3"></i>
                <h3 class="text-sm font-black text-slate-700">Data Tidak Ditemukan</h3>
                <p class="text-xs font-medium text-slate-500 mt-1">Pencarian untuk NIK/Nama tersebut tidak cocok.</p>
            </div>
        </div>

        {{-- PAGINATION CUSTOM UI --}}
        @if(method_exists($data, 'hasPages') && $data->hasPages())
            <div id="rekamMedisPagination" class="bg-white p-4 sm:p-5 border-t border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shrink-0 rounded-b-[2rem]">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                    Menampilkan <span class="text-slate-700">{{ $data->firstItem() }}</span> - <span class="text-slate-700">{{ $data->lastItem() }}</span> dari <span class="text-slate-800">{{ $data->total() }}</span> data
                </p>
                
                <div class="flex items-center gap-1.5">
                    @if ($data->onFirstPage())
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-400 cursor-not-allowed"><i class="fa-solid fa-chevron-left text-xs"></i></span>
                    @else
                        <a href="{{ $data->previousPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-{{ $currentUi['theme'] }}-50 hover:text-{{ $currentUi['theme'] }}-600 hover:border-{{ $currentUi['theme'] }}-200 transition-all shadow-sm"><i class="fa-solid fa-chevron-left text-xs"></i></a>
                    @endif

                    @php
                        $start = max(1, $data->currentPage() - 1);
                        $end = min($data->lastPage(), $data->currentPage() + 1);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $data->url(1) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-{{ $currentUi['theme'] }}-50 hover:text-{{ $currentUi['theme'] }}-600 hover:border-{{ $currentUi['theme'] }}-200 transition-all shadow-sm text-xs font-bold">1</a>
                        @if($start > 2)<span class="inline-flex h-8 w-8 items-center justify-center text-slate-400 text-xs font-bold">...</span>@endif
                    @endif

                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $data->currentPage())
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-{{ $currentUi['theme'] }}-500 text-white shadow-md shadow-{{ $currentUi['theme'] }}-500/30 text-xs font-black">{{ $page }}</span>
                        @else
                            <a href="{{ $data->url($page) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-{{ $currentUi['theme'] }}-50 hover:text-{{ $currentUi['theme'] }}-600 hover:border-{{ $currentUi['theme'] }}-200 transition-all shadow-sm text-xs font-bold">{{ $page }}</a>
                        @endif
                    @endfor

                    @if($end < $data->lastPage())
                        @if($end < $data->lastPage() - 1)<span class="inline-flex h-8 w-8 items-center justify-center text-slate-400 text-xs font-bold">...</span>@endif
                        <a href="{{ $data->url($data->lastPage()) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-{{ $currentUi['theme'] }}-50 hover:text-{{ $currentUi['theme'] }}-600 hover:border-{{ $currentUi['theme'] }}-200 transition-all shadow-sm text-xs font-bold">{{ $data->lastPage() }}</a>
                    @endif

                    @if ($data->hasMorePages())
                        <a href="{{ $data->nextPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-{{ $currentUi['theme'] }}-50 hover:text-{{ $currentUi['theme'] }}-600 hover:border-{{ $currentUi['theme'] }}-200 transition-all shadow-sm"><i class="fa-solid fa-chevron-right text-xs"></i></a>
                    @else
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-400 cursor-not-allowed"><i class="fa-solid fa-chevron-right text-xs"></i></span>
                    @endif
                </div>
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const searchInput = document.getElementById('rekamMedisLiveSearch');
        const clearButton = document.getElementById('rekamMedisClearSearch');
        const visibleCountText = document.getElementById('rekamMedisVisibleCount');
        const liveEmpty = document.getElementById('rekamMedisLiveEmpty');
        const pagination = document.getElementById('rekamMedisPagination');
        const rows = Array.from(document.querySelectorAll('.js-rekam-row'));
        const cards = Array.from(document.querySelectorAll('.js-rekam-card'));

        const normalize = (value) => String(value || '').toLowerCase().trim();
        const isNumericKeyword = (keyword) => /^[0-9]+$/.test(keyword);

        const itemMatches = (item, keyword) => {
            if (keyword === '') return true;
            return normalize(item.dataset.name).includes(keyword) || 
                   (isNumericKeyword(keyword) && normalize(item.dataset.nik).includes(keyword));
        };

        let frameId = null;
        const applyFilter = () => {
            if (frameId) cancelAnimationFrame(frameId);
            frameId = requestAnimationFrame(() => {
                const keyword = normalize(searchInput?.value);
                let visibleCount = 0;

                rows.forEach((row) => {
                    const visible = itemMatches(row, keyword);
                    row.classList.toggle('hidden', !visible);
                    if (visible) visibleCount++;
                });

                cards.forEach((card) => {
                    card.classList.toggle('hidden', !itemMatches(card, keyword));
                });

                if (visibleCountText) visibleCountText.textContent = String(visibleCount);
                if (liveEmpty) {
                    const hasData = rows.length > 0 || cards.length > 0;
                    liveEmpty.classList.toggle('hidden', !hasData || visibleCount > 0);
                }
                if (clearButton) {
                    const hasKeyword = keyword !== '';
                    clearButton.classList.toggle('hidden', !hasKeyword);
                }
                if (pagination) pagination.classList.toggle('hidden', keyword !== '');
            });
        };

        searchInput?.addEventListener('input', applyFilter, { passive: true });
        clearButton?.addEventListener('click', () => { if (searchInput) { searchInput.value = ''; searchInput.focus(); applyFilter(); } });
    })();
</script>
@endpush