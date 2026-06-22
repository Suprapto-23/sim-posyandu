@extends('layouts.bidan')

@section('title', 'Pemeriksaan Klinis')
@section('page-name', 'Pemeriksaan Klinis')
@section('page-title', 'Pemeriksaan Klinis')

@php
    use Carbon\Carbon;

    $pemeriksaans = $pemeriksaans ?? collect();
    $tab = $tab ?? request('tab', 'pending');
    $kategori = $kategori ?? request('kategori', 'semua');
    $search = $search ?? request('search', '');

    // ICON FULL FONTAWESOME
    $kategoriOptions = $kategoriOptions ?? [
        'balita' => ['label' => 'Balita', 'icon' => 'fa-baby', 'desc' => 'Pemeriksaan pertumbuhan Balita.'],
        'remaja' => ['label' => 'Remaja', 'icon' => 'fa-user-graduate', 'desc' => 'Pemeriksaan kesehatan Remaja.'],
        'lansia' => ['label' => 'Lansia', 'icon' => 'fa-person-cane', 'desc' => 'Pemeriksaan kesehatan Lansia.'],
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

    $statusTheme = function ($item) use ($isVerified, $getValue) {
        $status = strtolower((string) $getValue($item, ['status_verifikasi'], ''));
        if ($isVerified($item)) return 'bg-teal-50 text-teal-600 border-teal-200';
        if (in_array($status, ['rejected', 'ditolak'], true)) return 'bg-rose-50 text-rose-600 border-rose-200';
        return 'bg-amber-50 text-amber-600 border-amber-200';
    };

    $kategoriTheme = function ($key) {
        return match ($key) {
            'remaja' => ['badge' => 'bg-sky-50 text-sky-600 border-sky-200', 'active' => 'bg-sky-500 text-white shadow-md'],
            'lansia' => ['badge' => 'bg-amber-50 text-amber-600 border-amber-200', 'active' => 'bg-amber-500 text-white shadow-md'],
            default => ['badge' => 'bg-emerald-50 text-emerald-600 border-emerald-200', 'active' => 'bg-emerald-500 text-white shadow-md'],
        };
    };

    $getParameterUtama = function ($item, string $kategori) use ($displayValue) {
        if ($kategori === 'lansia') return ['Tensi' => $displayValue(data_get($item, 'tekanan_darah')), 'Gula' => $displayValue(data_get($item, 'gula_darah'), 'mg/dL')];
        if ($kategori === 'remaja') return ['BB' => $displayValue(data_get($item, 'berat_badan'), 'kg'), 'TB' => $displayValue(data_get($item, 'tinggi_badan'), 'cm')];
        return ['BB' => $displayValue(data_get($item, 'berat_badan'), 'kg'), 'TB' => $displayValue(data_get($item, 'tinggi_badan'), 'cm')];
    };

    $buildUrl = function (array $overrides = []) use ($tab, $kategori, $search) {
        $query = array_merge(['tab' => $tab, 'kategori' => $kategori, 'search' => $search], $overrides);
        $query = collect($query)->filter(fn ($value) => $value !== null && $value !== '' && $value !== 'semua')->all();
        return route('bidan.pemeriksaan.index', $query);
    };

    $visibleCount = method_exists($pemeriksaans, 'count') ? $pemeriksaans->count() : count($pemeriksaans);
    $pageTitle = $tab === 'verified' ? 'Data Tervalidasi' : 'Antrean Validasi';
    $pageSubtitle = $tab === 'verified' ? 'Arsip rekam medis yang telah disahkan.' : 'Tinjau dan validasi segera.';

    $summaryCards = [
        ['label' => 'Total Data', 'value' => $stats['total'] ?? 0, 'icon' => 'fa-stethoscope', 'theme' => 'emerald'],
        ['label' => 'Menunggu', 'value' => $stats['pending'] ?? 0, 'icon' => 'fa-clock-rotate-left', 'theme' => 'amber'],
        ['label' => 'Tervalidasi', 'value' => $stats['verified'] ?? 0, 'icon' => 'fa-circle-check', 'theme' => 'teal'],
        ['label' => 'Bulan Ini', 'value' => $stats['bulan_ini'] ?? 0, 'icon' => 'fa-calendar-check', 'theme' => 'sky'],
    ];
@endphp

@push('styles')
<style>
    html { scroll-behavior: smooth; }
    body { background-color: #f1f5f9; }
    .bg-mesh-fixed {
        position: fixed; inset: 0; z-index: -10;
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(150, 100%, 94%, 1) 0px, transparent 50%);
        pointer-events: none;
    }
    
    .widget-card {
        background: #ffffff; 
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 2rem; 
        box-shadow: 0 4px 20px -10px rgba(0, 0, 0, 0.05);
        transform: translateZ(0); 
        will-change: transform, box-shadow;
        transition: transform 0.25s ease-out, box-shadow 0.25s ease-out;
    }
    .widget-card:hover {
        transform: translateY(-4px) translateZ(0);
        box-shadow: 0 20px 40px -10px rgba(20, 184, 166, 0.12);
        border-color: rgba(20, 184, 166, 0.2);
    }

    .btn-pill { border-radius: 9999px; transition: all 0.2s ease; cursor: pointer; }
    .btn-pill:active { transform: scale(0.97); }

    /* Custom Scrollbar */
    .slim-scroll { -webkit-overflow-scrolling: touch; overscroll-behavior-y: contain; }
    .slim-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: rgba(20, 184, 166, 0.2); border-radius: 9999px; }
    .slim-scroll::-webkit-scrollbar-thumb:hover { background: rgba(20, 184, 166, 0.5); }

    .animate-pop-in { animation: popIn .5s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popIn { from { opacity: 0; transform: translateY(16px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
    
    .live-pulse { position: relative; display: flex; width: 8px; height: 8px; }
    .live-pulse span { position: absolute; display: inline-flex; height: 100%; width: 100%; border-radius: 999px; background-color: #f59e0b; opacity: 0.75; animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite; }
    .live-pulse .dot { position: relative; display: inline-flex; border-radius: 999px; height: 8px; width: 8px; background-color: #d97706; }
    @keyframes ping { 75%, 100% { transform: scale(2.5); opacity: 0; } }
</style>
@endpush

@section('content')
<div class="bg-mesh-fixed"></div>

<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6 animate-pop-in">

    {{-- 1. HEADER BANNER HIJAU MEWAH --}}
    <section class="relative overflow-hidden rounded-[3rem] bg-gradient-to-r from-teal-500 via-teal-400 to-emerald-400 p-8 sm:p-10 shadow-2xl shadow-teal-500/20 flex flex-col lg:flex-row justify-between items-center gap-8 border-[6px] border-white/40" style="transform: translateZ(0);">
        <div class="absolute inset-0 bg-white/10 backdrop-blur-[2px]"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-full lg:w-2/3 flex flex-col gap-4 text-center lg:text-left">
            <div class="inline-flex justify-center lg:justify-start items-center gap-2 mb-2">
                <span class="btn-pill bg-white/20 border border-white/30 text-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest backdrop-blur-md shadow-inner flex items-center gap-2">
                    <i class="fa-solid fa-stethoscope"></i> Manajemen Layanan Medis
                </span>
            </div>
            
            <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight leading-tight">
                Pemeriksaan Klinis
            </h1>

            <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-xl mx-auto lg:mx-0">
                Tinjau data pemeriksaan dari Kader, berikan catatan medis, dan validasi hasil pemeriksaan untuk dimasukkan ke dalam sistem Rekam Medis terpadu.
            </p>
        </div>

        <div class="relative z-10 w-full lg:w-1/3 flex justify-center lg:justify-end">
            <div class="widget-card !rounded-[2rem] !shadow-none bg-white/20 border border-white/30 backdrop-blur-md px-6 py-4 flex items-center gap-5">
                <div>
                    <span class="block text-[10px] font-black uppercase tracking-widest text-teal-50">Total Tampil</span>
                    <span class="block text-3xl font-black text-white mt-0.5" id="pemeriksaanVisibleCount">{{ $visibleCount }}</span>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-teal-500 shadow-lg">
                    <i class="fa-solid fa-database text-2xl"></i>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. SUMMARY CARDS --}}
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach($summaryCards as $card)
            <div class="widget-card p-5 group flex items-center justify-between">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1 group-hover:text-{{ $card['theme'] }}-500 transition-colors">{{ $card['label'] }}</p>
                    <h2 class="text-3xl font-black text-slate-800 leading-none">{{ $card['value'] }}</h2>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-{{ $card['theme'] }}-50 text-{{ $card['theme'] }}-500 border border-{{ $card['theme'] }}-100 flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition-transform">
                    <i class="fa-solid {{ $card['icon'] }}"></i>
                </div>
            </div>
        @endforeach
    </section>

    {{-- 3. FILTER BAR --}}
    <section class="widget-card p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex p-1.5 bg-slate-50 border border-slate-100 rounded-[1.2rem] shadow-inner shrink-0 overflow-x-auto slim-scroll">
                <a href="{{ $buildUrl(['tab' => 'pending']) }}" class="btn-pill inline-flex items-center justify-center gap-2 px-6 py-2.5 text-xs font-black uppercase tracking-widest transition-all {{ $tab === 'pending' ? 'bg-white text-amber-600 shadow-sm border border-slate-200' : 'text-slate-500 hover:text-slate-800' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i> Menunggu
                    <span class="rounded-full bg-amber-100/80 px-2 py-0.5 text-[10px] text-amber-800 ml-1">{{ $stats['pending'] ?? 0 }}</span>
                </a>
                <a href="{{ $buildUrl(['tab' => 'verified']) }}" class="btn-pill inline-flex items-center justify-center gap-2 px-6 py-2.5 text-xs font-black uppercase tracking-widest transition-all {{ $tab === 'verified' ? 'bg-white text-teal-600 shadow-sm border border-slate-200' : 'text-slate-500 hover:text-slate-800' }}">
                    <i class="fa-solid fa-circle-check"></i> Tervalidasi
                    <span class="rounded-full bg-teal-100/80 px-2 py-0.5 text-[10px] text-teal-800 ml-1">{{ $stats['verified'] ?? 0 }}</span>
                </a>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ $buildUrl(['kategori' => 'semua']) }}" class="btn-pill inline-flex items-center gap-2 border px-4 py-2 text-[10px] font-black uppercase tracking-widest transition-all {{ $kategori === 'semua' ? 'border-slate-800 bg-slate-800 text-white shadow-md' : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50' }}">
                    <i class="fa-solid fa-users"></i> Semua
                </a>
                @foreach($kategoriOptions as $key => $option)
                    @php $theme = $kategoriTheme($key); $active = $kategori === $key; @endphp
                    <a href="{{ $buildUrl(['kategori' => $key]) }}" class="btn-pill inline-flex items-center gap-2 border px-4 py-2 text-[10px] font-black uppercase tracking-widest transition-all {{ $active ? $theme['active'] : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50' }}">
                        <i class="fa-solid {{ $option['icon'] }}"></i> {{ $option['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 4. MAIN TABLE AREA (ELASTIS & RESPONSIVE) --}}
    <section class="widget-card overflow-hidden flex flex-col relative">
        
        {{-- Header & Pencarian --}}
        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between shrink-0 z-20">
            <div class="min-w-0">
                <h2 class="text-base font-black tracking-tight text-slate-800 uppercase flex items-center gap-2"><i class="fa-solid fa-list-check text-teal-500"></i> {{ $pageTitle }}</h2>
                <p class="mt-1 text-[11px] font-bold text-slate-400 tracking-widest uppercase">{{ $pageSubtitle }}</p>
            </div>

            <form method="GET" action="{{ route('bidan.pemeriksaan.index') }}" class="flex w-full gap-2 xl:max-w-md relative">
                <input type="hidden" name="tab" value="{{ $tab }}">
                @if($kategori !== 'semua') <input type="hidden" name="kategori" value="{{ $kategori }}"> @endif

                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="pemeriksaanLiveSearch" name="search" value="{{ $search }}" autocomplete="off" placeholder="Cari nama atau NIK..."
                       class="w-full rounded-[1.2rem] border border-slate-200 bg-white py-3 pl-10 pr-10 text-xs font-bold text-slate-700 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100 shadow-inner">
                
                <button type="button" id="pemeriksaanClearSearch" class="absolute right-3 top-1/2 hidden h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-slate-400 hover:bg-slate-200 hover:text-slate-700 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </form>
        </div>

        {{-- PERBAIKAN: Menggunakan min-h dan max-h agar kotak memendek saat data sedikit dan men-scroll saat data banyak --}}
        <div class="min-h-[200px] max-h-[600px] overflow-y-auto overflow-x-auto slim-scroll relative bg-slate-50/30">
            <div class="hidden lg:block w-full min-w-[1000px]">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead class="sticky top-0 z-10 bg-white/95 backdrop-blur-sm border-b border-slate-200 shadow-sm">
                        <tr>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Pasien & NIK</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Kategori</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Parameter Kunci</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Waktu & Petugas</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pemeriksaanTableBody" class="divide-y divide-slate-100 bg-white">
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

                            <tr class="js-pemeriksaan-row hover:bg-slate-50/80 transition-colors group" data-name="{{ $searchName }}" data-nik="{{ $searchNik }}" data-order="{{ $loop->index }}">
                                <td class="px-6 py-5 align-middle">
                                    <p class="text-sm font-bold text-slate-800 group-hover:text-teal-600 transition-colors">{{ $namaPasien }}</p>
                                    <p class="text-[10px] font-semibold text-slate-400 font-mono mt-0.5">{{ $nikPasien }}</p>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <span class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-[9px] font-black uppercase tracking-widest bg-white {{ $itemTheme['badge'] }}">
                                        {{ $itemMeta['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <div class="inline-flex items-center gap-2 rounded-md border bg-white px-3 py-1 text-[10px] font-black uppercase tracking-widest shadow-sm {{ $statusTheme($pemeriksaan) }}">
                                        @if(!$verified)
                                            <div class="live-pulse"><span class=""></span><div class="dot"></div></div>
                                        @else
                                            <i class="fa-solid fa-check"></i>
                                        @endif
                                        {{ $statusLabel($pemeriksaan) }}
                                    </div>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <div class="flex flex-wrap gap-1.5 max-w-[280px]">
                                        @foreach($parameterUtama as $label => $value)
                                            <span class="bg-slate-50 border border-slate-100 px-2.5 py-1 rounded-md text-[10px] font-semibold text-slate-500 shadow-sm">{{ $label }}: <strong class="text-slate-800 font-bold ml-0.5">{{ $value }}</strong></span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <p class="text-[10px] font-bold text-slate-600"><i class="fa-regular fa-calendar text-slate-400 mr-1"></i> {{ $formatDate($tanggal) }}</p>
                                    <p class="text-[9px] font-black text-slate-400 mt-1 uppercase tracking-widest"><i class="fa-solid fa-user mr-0.5"></i> {{ $petugas }}</p>
                                </td>
                                <td class="px-6 py-5 align-middle text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('bidan.pemeriksaan.show', $pemeriksaan->id) }}" class="btn-pill inline-flex h-9 w-9 items-center justify-center border border-slate-200 bg-white text-slate-500 hover:border-teal-300 hover:text-teal-600 hover:bg-teal-50 transition shadow-sm" title="Detail">
                                            <i class="fa-solid fa-eye text-sm"></i>
                                        </a>
                                        @unless($verified)
                                            <a href="{{ route('bidan.pemeriksaan.validasi', $pemeriksaan->id) }}" class="btn-pill inline-flex items-center gap-1.5 bg-teal-500 text-white hover:bg-teal-600 transition px-4 py-1.5 text-[10px] font-black uppercase tracking-widest shadow-md">
                                                Validasi
                                            </a>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-14 text-center bg-white">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-slate-50 border border-slate-100 text-slate-300 mb-4 shadow-inner">
                                        <i class="fa-solid fa-folder-open text-3xl"></i>
                                    </div>
                                    <h3 class="text-sm font-black text-slate-700">Antrean Bersih</h3>
                                    <p class="text-xs font-medium text-slate-400 mt-1">Tidak ada data pemeriksaan yang perlu ditinjau.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE VIEW --}}
            <div id="pemeriksaanCardContainer" class="space-y-4 lg:hidden p-4">
                @foreach($pemeriksaans as $pemeriksaan)
                    @php
                        $itemKategori = $getKategori($pemeriksaan);
                        $itemMeta = $kategoriOptions[$itemKategori] ?? $kategoriOptions['balita'];
                        $itemTheme = $kategoriTheme($itemKategori);
                        $namaPasien = $getNamaPasien($pemeriksaan);
                        $nikPasien = $getNikPasien($pemeriksaan);
                        $verified = $isVerified($pemeriksaan);
                    @endphp
                    <article class="js-pemeriksaan-card rounded-[1.5rem] border border-slate-100 bg-white p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $namaPasien }}</h3>
                                <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $nikPasien }}</p>
                            </div>
                            <span class="inline-flex rounded-md border px-2 py-0.5 text-[9px] font-black uppercase tracking-widest {{ $itemTheme['badge'] }}">{{ $itemMeta['label'] }}</span>
                        </div>
                        <div class="flex items-center gap-2 mb-4">
                             <div class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-[9px] font-black uppercase tracking-widest shadow-sm {{ $statusTheme($pemeriksaan) }}">
                                {{ $statusLabel($pemeriksaan) }}
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-slate-50">
                            <a href="{{ route('bidan.pemeriksaan.show', $pemeriksaan->id) }}" class="btn-pill inline-flex justify-center items-center border border-slate-200 bg-slate-50 px-3 py-2.5 text-[10px] font-black uppercase tracking-widest text-slate-500">Detail</a>
                            @unless($verified)
                                <a href="{{ route('bidan.pemeriksaan.validasi', $pemeriksaan->id) }}" class="btn-pill inline-flex justify-center items-center bg-teal-500 px-3 py-2.5 text-[10px] font-black uppercase tracking-widest text-white shadow-sm">Validasi</a>
                            @endunless
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- LIVE EMPTY STATE --}}
            <div id="pemeriksaanLiveEmpty" class="hidden rounded-[2rem] border border-dashed border-slate-300 bg-white/50 p-10 text-center m-6">
                <i class="fa-solid fa-magnifying-glass text-4xl text-slate-300 mb-3"></i>
                <h3 class="text-sm font-black text-slate-700">Data Tidak Ditemukan</h3>
                <p class="text-xs font-medium text-slate-500 mt-1">Pencarian untuk NIK/Nama tersebut tidak cocok.</p>
            </div>
        </div>

        {{-- PAGINATION CUSTOM UI --}}
        @if(method_exists($pemeriksaans, 'hasPages') && $pemeriksaans->hasPages())
            <div id="pemeriksaanPagination" class="bg-white p-5 border-t border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shrink-0 rounded-b-[2rem]">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                    Menampilkan <span class="text-slate-700">{{ $pemeriksaans->firstItem() }}</span> - <span class="text-slate-700">{{ $pemeriksaans->lastItem() }}</span> dari <span class="text-slate-800">{{ $pemeriksaans->total() }}</span> data
                </p>
                
                <div class="flex items-center gap-1.5">
                    {{-- Previous Page --}}
                    @if ($pemeriksaans->onFirstPage())
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-400 cursor-not-allowed">
                            <i class="fa-solid fa-chevron-left text-xs"></i>
                        </span>
                    @else
                        <a href="{{ $pemeriksaans->previousPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 transition-all shadow-sm">
                            <i class="fa-solid fa-chevron-left text-xs"></i>
                        </a>
                    @endif

                    {{-- Pagination Numbers --}}
                    @php
                        $start = max(1, $pemeriksaans->currentPage() - 1);
                        $end = min($pemeriksaans->lastPage(), $pemeriksaans->currentPage() + 1);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $pemeriksaans->url(1) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 transition-all shadow-sm text-xs font-bold">1</a>
                        @if($start > 2)
                            <span class="inline-flex h-8 w-8 items-center justify-center text-slate-400 text-xs font-bold">...</span>
                        @endif
                    @endif

                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $pemeriksaans->currentPage())
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-teal-500 text-white shadow-md shadow-teal-500/30 text-xs font-black">{{ $page }}</span>
                        @else
                            <a href="{{ $pemeriksaans->url($page) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 transition-all shadow-sm text-xs font-bold">{{ $page }}</a>
                        @endif
                    @endfor

                    @if($end < $pemeriksaans->lastPage())
                        @if($end < $pemeriksaans->lastPage() - 1)
                            <span class="inline-flex h-8 w-8 items-center justify-center text-slate-400 text-xs font-bold">...</span>
                        @endif
                        <a href="{{ $pemeriksaans->url($pemeriksaans->lastPage()) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 transition-all shadow-sm text-xs font-bold">{{ $pemeriksaans->lastPage() }}</a>
                    @endif

                    {{-- Next Page --}}
                    @if ($pemeriksaans->hasMorePages())
                        <a href="{{ $pemeriksaans->nextPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 transition-all shadow-sm">
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </a>
                    @else
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-400 cursor-not-allowed">
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </span>
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
                    entry.item.classList.toggle('hidden', !matched); 
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