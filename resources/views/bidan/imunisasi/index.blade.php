@extends('layouts.bidan')

@section('title', 'Vaksinasi & Imunisasi')
@section('page-name', 'Imunisasi')
@section('page-title', 'Vaksinasi & Imunisasi Balita')

@php
    use Carbon\Carbon;

    $imunisasis = $imunisasis ?? collect();
    $search = $search ?? request('search', '');

    $stats = $stats ?? [
        'total' => 0,
        'bulan_ini' => 0,
        'total_balita' => 0,
        'vaksin_tercatat' => 0,
    ];

    $getValue = function ($item, array $keys, mixed $default = '-') {
        foreach ($keys as $key) {
            $value = data_get($item, $key);
            if ($value !== null && $value !== '') return $value;
        }
        return $default;
    };

    $getBalita = function ($item) {
        return data_get($item, 'kunjungan.pasien') ?? data_get($item, 'balita') ?: null;
    };

    $getNamaBalita = function ($item) use ($getBalita, $getValue) {
        $balita = $getBalita($item);
        if ($balita) return $getValue($balita, ['nama_lengkap', 'nama', 'nama_balita'], 'Balita tidak terdata');
        return $getValue($item, ['nama_balita', 'nama_pasien'], 'Balita tidak terdata');
    };

    $getNikBalita = function ($item) use ($getBalita, $getValue) {
        $balita = $getBalita($item);
        if ($balita) return $getValue($balita, ['nik', 'nik_anak'], '-');
        return $getValue($item, ['nik', 'nik_balita', 'nik_anak'], '-');
    };

    $getWaliBalita = function ($item) use ($getBalita, $getValue) {
        $balita = $getBalita($item);
        if ($balita) return $getValue($balita, ['nama_ibu', 'nama_ayah', 'nama_wali'], '-');
        return $getValue($item, ['nama_ibu', 'nama_ayah', 'nama_wali'], '-');
    };

    $getPetugas = function ($item) {
        $petugas = data_get($item, 'kunjungan.petugas');
        if (!$petugas) return '-';
        return data_get($petugas, 'name') ?? data_get($petugas, 'nama') ?? '-';
    };

    $formatDate = fn($date) => $date ? Carbon::parse($date)->translatedFormat('d M Y') : '-';
    $formatBulanPendek = fn($date) => $date ? Carbon::parse($date)->translatedFormat('M') : '-';
    $formatTanggalAngka = fn($date) => $date ? Carbon::parse($date)->format('d') : '-';
    $isThisMonth = fn($date) => $date ? Carbon::parse($date)->isSameMonth(now()) : false;

    $visibleCount = method_exists($imunisasis, 'count') ? $imunisasis->count() : count($imunisasis);
    $totalData = method_exists($imunisasis, 'total') ? $imunisasis->total() : $visibleCount;

    $summaryCards = [
        ['label' => 'Total Catatan', 'value' => $stats['total'] ?? 0, 'icon' => 'fa-syringe', 'theme' => 'emerald'],
        ['label' => 'Bulan Ini', 'value' => $stats['bulan_ini'] ?? 0, 'icon' => 'fa-calendar-check', 'theme' => 'teal'],
        ['label' => 'Total Balita', 'value' => $stats['total_balita'] ?? 0, 'icon' => 'fa-baby', 'theme' => 'sky'],
        ['label' => 'Jenis Vaksin', 'value' => $stats['vaksin_tercatat'] ?? 0, 'icon' => 'fa-list-check', 'theme' => 'amber'],
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

    .slim-scroll { -webkit-overflow-scrolling: touch; overscroll-behavior-y: contain; }
    .slim-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: rgba(20, 184, 166, 0.2); border-radius: 9999px; }
    .slim-scroll::-webkit-scrollbar-thumb:hover { background: rgba(20, 184, 166, 0.5); }

    .animate-pop-in { animation: popIn .5s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popIn { from { opacity: 0; transform: translateY(16px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
</style>
@endpush

@section('content')
<div class="bg-mesh-fixed"></div>

<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6 animate-pop-in">

    {{-- HEADER BANNER --}}
    <section class="relative overflow-hidden rounded-[3rem] bg-gradient-to-r from-teal-500 via-teal-400 to-emerald-400 p-8 sm:p-10 shadow-2xl shadow-teal-500/20 flex flex-col lg:flex-row justify-between items-center gap-8 border-[6px] border-white/40" style="transform: translateZ(0);">
        <div class="absolute inset-0 bg-white/10 backdrop-blur-[2px]"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-full lg:w-2/3 flex flex-col gap-4 text-center lg:text-left">
            <div class="inline-flex justify-center lg:justify-start items-center gap-2 mb-2">
                <span class="btn-pill bg-white/20 border border-white/30 text-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest backdrop-blur-md shadow-inner flex items-center gap-2">
                    <i class="fa-solid fa-syringe"></i> Layanan Balita
                </span>
            </div>
            
            <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight leading-tight">
                Vaksinasi & Imunisasi
            </h1>

            <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-xl mx-auto lg:mx-0">
                Halaman ini digunakan Bidan untuk mencatat, mengelola, dan melihat arsip riwayat imunisasi pada sasaran Balita.
            </p>

            <div class="flex justify-center lg:justify-start mt-2">
                <a href="{{ route('bidan.imunisasi.create') }}" class="btn-pill bg-white text-teal-600 hover:bg-teal-50 px-6 py-3.5 text-[11px] font-black uppercase tracking-widest shadow-md flex items-center gap-2 transition-all hover:-translate-y-0.5">
                    <i class="fa-solid fa-plus text-sm"></i> Catat Imunisasi Baru
                </a>
            </div>
        </div>

        <div class="relative z-10 w-full lg:w-1/3 flex justify-center lg:justify-end">
            <div class="widget-card !rounded-[2rem] !shadow-none bg-white/20 border border-white/30 backdrop-blur-md px-6 py-4 flex items-center gap-5">
                <div>
                    <span class="block text-[10px] font-black uppercase tracking-widest text-teal-50">Total Tampil</span>
                    <span class="block text-3xl font-black text-white mt-0.5" id="imunisasiVisibleCount">{{ $visibleCount }}</span>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-teal-500 shadow-lg">
                    <i class="fa-solid fa-database text-2xl"></i>
                </div>
            </div>
        </div>
    </section>

    {{-- SUMMARY CARDS --}}
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

    {{-- MAIN TABLE AREA (FIXED HEIGHT & SCROLLABLE) --}}
    <section class="widget-card overflow-hidden flex flex-col relative">
        
        {{-- Header & Pencarian --}}
        <div class="p-6 border-b border-slate-100 bg-white flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between shrink-0 z-20">
            <div class="min-w-0">
                <h2 class="text-base font-black tracking-tight text-slate-800 uppercase flex items-center gap-2"><i class="fa-solid fa-folder-open text-teal-500"></i> Arsip Imunisasi</h2>
                <p class="mt-1 text-[11px] font-bold text-slate-400 tracking-widest uppercase">Catatan Layanan Balita</p>
            </div>

            <form method="GET" action="{{ route('bidan.imunisasi.index') }}" class="flex w-full gap-2 xl:max-w-md relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="imunisasiLiveSearch" name="search" value="{{ $search }}" autocomplete="off" placeholder="Cari nama, NIK, atau jenis vaksin..."
                       class="w-full rounded-[1.2rem] border border-slate-200 bg-slate-50 py-3 pl-10 pr-10 text-xs font-bold text-slate-700 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100 shadow-inner">
                
                <button type="button" id="imunisasiClearSearch" class="absolute right-3 top-1/2 hidden h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-slate-400 hover:bg-slate-200 hover:text-slate-700 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </form>
        </div>

        {{-- Tabel Scrollable Area (Kunci Presisi h-[550px]) --}}
        <div class="min-h-[200px] max-h-[600px] overflow-y-auto overflow-x-auto slim-scroll relative bg-slate-50/30">
            <div class="hidden lg:block w-full min-w-[1000px]">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead class="sticky top-0 z-10 bg-white/95 backdrop-blur-sm border-b border-slate-200 shadow-sm">
                        <tr>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Tanggal</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Balita & Wali</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Imunisasi & Vaksin</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Dosis / Batch</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Petugas</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="imunisasiTableBody" class="divide-y divide-slate-100 bg-white">
                        @forelse($imunisasis as $imunisasi)
                            @php
                                $namaBalita = $getNamaBalita($imunisasi);
                                $nikBalita = $getNikBalita($imunisasi);
                                $waliBalita = $getWaliBalita($imunisasi);
                                $jenisImunisasi = $getValue($imunisasi, ['jenis_imunisasi', 'nama_imunisasi', 'jenis'], 'Imunisasi');
                                $vaksin = $getValue($imunisasi, ['vaksin', 'nama_vaksin'], '-');
                                $dosis = $getValue($imunisasi, ['dosis', 'dosis_ke'], '-');
                                $batch = $getValue($imunisasi, ['batch_number', 'no_batch', 'nomor_batch'], '-');
                                $tanggal = $getValue($imunisasi, ['tanggal_imunisasi', 'tanggal', 'created_at'], null);
                                $petugas = $getPetugas($imunisasi);
                                $bulanIni = $isThisMonth($tanggal);

                                $searchName = mb_strtolower(trim((string) $namaBalita), 'UTF-8');
                                $searchNik = mb_strtolower(trim((string) $nikBalita), 'UTF-8');
                                $searchJenis = mb_strtolower(trim((string) $jenisImunisasi), 'UTF-8');
                                $searchVaksin = mb_strtolower(trim((string) $vaksin), 'UTF-8');
                            @endphp

                            <tr class="js-imunisasi-row hover:bg-slate-50/80 transition-colors group" data-name="{{ $searchName }}" data-nik="{{ $searchNik }}" data-jenis="{{ $searchJenis }}" data-vaksin="{{ $searchVaksin }}">
                                <td class="px-6 py-5 align-middle">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl bg-white border border-slate-200 shadow-sm group-hover:border-teal-300 transition-colors">
                                            <span class="text-[9px] font-black uppercase text-teal-600">{{ $formatBulanPendek($tanggal) }}</span>
                                            <span class="text-lg font-black leading-none text-slate-800">{{ $formatTanggalAngka($tanggal) }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[11px] font-bold text-slate-800">{{ $formatDate($tanggal) }}</p>
                                            @if($bulanIni)
                                                <span class="mt-1 inline-flex rounded-md bg-emerald-50 px-2 py-0.5 text-[8px] font-black uppercase tracking-widest text-emerald-600 border border-emerald-100">Bulan Ini</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <p class="text-sm font-bold text-slate-800 group-hover:text-teal-600 transition-colors">{{ $namaBalita }}</p>
                                    <p class="text-[10px] font-semibold text-slate-400 font-mono mt-0.5">{{ $nikBalita }} <span class="mx-1">•</span> Wali: {{ $waliBalita }}</p>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <p class="text-[11px] font-black uppercase tracking-widest text-slate-700 bg-slate-50 border border-slate-200 px-2 py-1 rounded-md inline-block mb-1">{{ $jenisImunisasi }}</p>
                                    <p class="text-xs font-bold text-slate-500 block">{{ $vaksin }}</p>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <p class="text-xs font-bold text-slate-800">Dosis: {{ $dosis }}</p>
                                    <p class="text-[10px] font-semibold text-slate-400 mt-0.5">Batch: {{ $batch }}</p>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <p class="text-xs font-bold text-slate-600 flex items-center gap-1.5"><i class="fa-solid fa-user-nurse text-slate-400"></i> {{ $petugas }}</p>
                                </td>
                                <td class="px-6 py-5 align-middle text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('bidan.imunisasi.show', $imunisasi->id) }}" class="btn-pill inline-flex h-9 w-9 items-center justify-center border border-slate-200 bg-white text-slate-500 hover:border-teal-300 hover:text-teal-600 hover:bg-teal-50 transition shadow-sm" title="Detail">
                                            <i class="fa-solid fa-eye text-sm"></i>
                                        </a>
                                        <a href="{{ route('bidan.imunisasi.edit', $imunisasi->id) }}" class="btn-pill inline-flex h-9 w-9 items-center justify-center border border-slate-200 bg-white text-slate-500 hover:border-amber-300 hover:text-amber-600 hover:bg-amber-50 transition shadow-sm" title="Edit">
                                            <i class="fa-solid fa-pen text-sm"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-14 text-center bg-white">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-slate-50 border border-slate-100 text-slate-300 mb-4 shadow-inner">
                                        <i class="fa-solid fa-folder-open text-3xl"></i>
                                    </div>
                                    <h3 class="text-sm font-black text-slate-700">Belum Ada Catatan Imunisasi</h3>
                                    <p class="text-xs font-medium text-slate-400 mt-1 mb-4">Data akan tampil setelah Anda mencatat layanan imunisasi baru.</p>
                                    <a href="{{ route('bidan.imunisasi.create') }}" class="btn-pill bg-teal-500 text-white px-5 py-2.5 text-[10px] font-black uppercase tracking-widest shadow-md">Catat Sekarang</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE VIEW --}}
            <div id="imunisasiCardContainer" class="space-y-4 lg:hidden p-4">
                @foreach($imunisasis as $imunisasi)
                    @php
                        $namaBalita = $getNamaBalita($imunisasi);
                        $nikBalita = $getNikBalita($imunisasi);
                        $jenisImunisasi = $getValue($imunisasi, ['jenis_imunisasi', 'nama_imunisasi', 'jenis'], 'Imunisasi');
                        $vaksin = $getValue($imunisasi, ['vaksin', 'nama_vaksin'], '-');
                        $tanggal = $getValue($imunisasi, ['tanggal_imunisasi', 'tanggal', 'created_at'], null);
                        
                        $searchName = mb_strtolower(trim((string) $namaBalita), 'UTF-8');
                        $searchNik = mb_strtolower(trim((string) $nikBalita), 'UTF-8');
                        $searchJenis = mb_strtolower(trim((string) $jenisImunisasi), 'UTF-8');
                        $searchVaksin = mb_strtolower(trim((string) $vaksin), 'UTF-8');
                    @endphp
                    <article class="js-imunisasi-card rounded-[1.5rem] border border-slate-100 bg-white p-5 shadow-sm" data-name="{{ $searchName }}" data-nik="{{ $searchNik }}" data-jenis="{{ $searchJenis }}" data-vaksin="{{ $searchVaksin }}">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $namaBalita }}</h3>
                                <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $nikBalita }}</p>
                            </div>
                            <span class="inline-flex rounded-md border border-teal-100 bg-teal-50 px-2 py-0.5 text-[9px] font-black uppercase tracking-widest text-teal-600">{{ $formatDate($tanggal) }}</span>
                        </div>
                        <div class="flex items-center gap-2 mb-4 bg-slate-50 p-2 rounded-lg border border-slate-100">
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-700">{{ $jenisImunisasi }}</span>
                            <span class="text-slate-300">•</span>
                            <span class="text-[10px] font-bold text-slate-500">{{ $vaksin }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-slate-50">
                            <a href="{{ route('bidan.imunisasi.show', $imunisasi->id) }}" class="btn-pill inline-flex justify-center items-center border border-slate-200 bg-slate-50 px-3 py-2.5 text-[10px] font-black uppercase tracking-widest text-slate-500">Detail</a>
                            <a href="{{ route('bidan.imunisasi.edit', $imunisasi->id) }}" class="btn-pill inline-flex justify-center items-center border border-slate-200 bg-white px-3 py-2.5 text-[10px] font-black uppercase tracking-widest text-slate-600 shadow-sm">Edit</a>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- LIVE EMPTY STATE --}}
            <div id="imunisasiLiveEmpty" class="hidden rounded-[2rem] border border-dashed border-slate-300 bg-white/50 p-10 text-center m-6">
                <i class="fa-solid fa-magnifying-glass text-4xl text-slate-300 mb-3"></i>
                <h3 class="text-sm font-black text-slate-700">Data Tidak Ditemukan</h3>
                <p class="text-xs font-medium text-slate-500 mt-1">Pencarian untuk kriteria tersebut tidak cocok dengan data manapun.</p>
            </div>
        </div>

        {{-- PAGINATION CUSTOM UI (Rounded Teal) --}}
        @if(method_exists($imunisasis, 'hasPages') && $imunisasis->hasPages())
            <div id="imunisasiPagination" class="bg-white p-5 border-t border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shrink-0 rounded-b-[2rem]">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                    Menampilkan <span class="text-slate-700">{{ $imunisasis->firstItem() }}</span> - <span class="text-slate-700">{{ $imunisasis->lastItem() }}</span> dari <span class="text-slate-800">{{ $imunisasis->total() }}</span> data
                </p>
                
                <div class="flex items-center gap-1.5">
                    {{-- Previous --}}
                    @if ($imunisasis->onFirstPage())
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-400 cursor-not-allowed"><i class="fa-solid fa-chevron-left text-xs"></i></span>
                    @else
                        <a href="{{ $imunisasis->previousPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-teal-50 hover:text-teal-600 transition-all shadow-sm"><i class="fa-solid fa-chevron-left text-xs"></i></a>
                    @endif

                    {{-- Page Numbers --}}
                    @php
                        $start = max(1, $imunisasis->currentPage() - 1);
                        $end = min($imunisasis->lastPage(), $imunisasis->currentPage() + 1);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $imunisasis->url(1) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-teal-50 hover:text-teal-600 transition-all shadow-sm text-xs font-bold">1</a>
                        @if($start > 2)<span class="inline-flex h-8 w-8 items-center justify-center text-slate-400 text-xs font-bold">...</span>@endif
                    @endif

                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $imunisasis->currentPage())
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-teal-500 text-white shadow-md shadow-teal-500/30 text-xs font-black">{{ $page }}</span>
                        @else
                            <a href="{{ $imunisasis->url($page) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-teal-50 hover:text-teal-600 transition-all shadow-sm text-xs font-bold">{{ $page }}</a>
                        @endif
                    @endfor

                    @if($end < $imunisasis->lastPage())
                        @if($end < $imunisasis->lastPage() - 1)<span class="inline-flex h-8 w-8 items-center justify-center text-slate-400 text-xs font-bold">...</span>@endif
                        <a href="{{ $imunisasis->url($imunisasis->lastPage()) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-teal-50 hover:text-teal-600 transition-all shadow-sm text-xs font-bold">{{ $imunisasis->lastPage() }}</a>
                    @endif

                    {{-- Next --}}
                    @if ($imunisasis->hasMorePages())
                        <a href="{{ $imunisasis->nextPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-teal-50 hover:text-teal-600 transition-all shadow-sm"><i class="fa-solid fa-chevron-right text-xs"></i></a>
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
        const searchInput = document.getElementById('imunisasiLiveSearch');
        const clearButton = document.getElementById('imunisasiClearSearch');
        const visibleCountText = document.getElementById('imunisasiVisibleCount');
        const liveEmpty = document.getElementById('imunisasiLiveEmpty');
        const pagination = document.getElementById('imunisasiPagination');
        const tableBody = document.getElementById('imunisasiTableBody');
        const cardContainer = document.getElementById('imunisasiCardContainer');
        const rows = Array.from(document.querySelectorAll('.js-imunisasi-row'));
        const cards = Array.from(document.querySelectorAll('.js-imunisasi-card'));

        const normalize = (value) => String(value || '').toLowerCase().trim();
        
        const itemMatches = (item, keyword) => {
            if (keyword === '') return true;
            return normalize(item.dataset.name).includes(keyword) || 
                   normalize(item.dataset.nik).includes(keyword) || 
                   normalize(item.dataset.jenis).includes(keyword) || 
                   normalize(item.dataset.vaksin).includes(keyword);
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