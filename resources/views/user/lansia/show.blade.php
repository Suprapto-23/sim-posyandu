@extends('layouts.user')

@section('title', 'Detail Kesehatan Lansia')
@section('page_title', 'Detail Lansia')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Pagination\LengthAwarePaginator;

    Carbon::setLocale('id');

    $dataLansia = $lansia ?? $dataLansia ?? $data ?? null;

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

    $metrics = collect($metrics ?? []);
    $healthMetrics = collect($healthMetrics ?? []);
    $riwayatCards = collect($riwayatCards ?? []);

    $ptmAnalysis = $ptmAnalysis ?? [
        'label' => 'Menunggu Data',
        'message' => 'Data pemeriksaan dasar belum tersedia.',
        'suggestion' => 'Lakukan pemeriksaan kesehatan dasar di Posyandu.',
        'tone' => 'slate',
        'icon' => 'fa-circle-info',
    ];

    $genderLabel = match ($dataLansia->jenis_kelamin ?? null) {
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        default => 'Belum diisi',
    };

    $initial = strtoupper(substr(trim((string) ($dataLansia->nama_lengkap ?? 'L')), 0, 1)) ?: 'L';

    $formatDate = fn ($value, $format = 'd F Y') => $value
        ? Carbon::parse($value)->translatedFormat($format)
        : '-';

    $kemandirianLabel = match ($dataLansia->tingkat_kemandirian ?? null) {
        'mandiri' => 'Mandiri',
        'bantuan_sebagian' => 'Bantuan Sebagian',
        'ketergantungan_penuh' => 'Ketergantungan Penuh',
        default => 'Belum Diisi',
    };

    // Styling untuk Alert Kesimpulan Kesehatan
    $analysisTone = match ($ptmAnalysis['tone'] ?? 'slate') {
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'amber'   => 'border-amber-200 bg-amber-50 text-amber-800',
        'rose'    => 'border-rose-200 bg-rose-50 text-rose-800',
        default   => 'border-slate-200 bg-slate-50 text-slate-800',
    };

    // Styling untuk Metrik
    $metricTone = function ($tone) {
        return match ($tone) {
            'emerald' => 'bg-emerald-50 text-emerald-600',
            'sky'     => 'bg-sky-50 text-sky-600',
            'amber'   => 'bg-amber-50 text-amber-600',
            'rose'    => 'bg-rose-50 text-rose-600',
            default   => 'bg-slate-50 text-slate-600',
        };
    };

    // LOGIKA PAGINATION MANUAL DI VIEW UNTUK RIWAYAT
    $perPage = 4;
    $currentPageRiwayat = LengthAwarePaginator::resolveCurrentPage('pemeriksaan');
    $currentItemsRiwayat = $riwayatCards->slice(($currentPageRiwayat - 1) * $perPage, $perPage)->all();
    $paginatedRiwayat = new LengthAwarePaginator(
        $currentItemsRiwayat, $riwayatCards->count(), $perPage, $currentPageRiwayat, 
        ['path' => request()->url(), 'query' => array_merge(request()->query(), ['pemeriksaan' => $currentPageRiwayat]), 'pageName' => 'pemeriksaan']
    );
@endphp

@push('styles')
<style>
    body {
        background-color: #f8fafc;
        background-image: radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
                          radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }

    .animate-pop-in {
        animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }

    @keyframes popIn {
        from { opacity: 0; transform: scale(.96) translateY(12px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .widget-card {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 1.5rem; 
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
        transition: all 0.3s ease;
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.96); }

    .slim-scroll::-webkit-scrollbar { width: 6px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: rgba(245, 158, 11, 0.2); border-radius: 10px; }
    .slim-scroll::-webkit-scrollbar-thumb:hover { background: rgba(245, 158, 11, 0.5); }
</style>
@endpush

@section('content')
<div class="max-w-[1280px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6 space-y-5">

    {{-- 1. HERO SECTION (PROFIL COMPACT AMBER THEME) --}}
    <section class="bg-gradient-to-br from-amber-500 via-amber-400 to-yellow-500 rounded-[2rem] p-6 md:p-8 relative overflow-hidden shadow-[0_15px_30px_-10px_rgba(245,158,11,.3)] border border-white/20 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-20 pointer-events-none"></div>
        
        <div class="relative z-10 flex items-center gap-5">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[1rem] bg-white text-2xl font-black text-amber-500 shadow-md border-2 border-white/40">
                {{ $initial }}
            </div>
            <div>
                <div class="flex flex-wrap gap-2 mb-1.5">
                    <span class="btn-pill bg-white/20 border border-white/30 text-white px-2.5 py-0.5 text-[9px] font-black uppercase tracking-widest backdrop-blur-md shadow-sm">
                        Data Lansia
                    </span>
                    <span class="btn-pill bg-white/20 border border-white/30 text-white px-2.5 py-0.5 text-[9px] font-black uppercase tracking-widest backdrop-blur-md shadow-sm">
                        {{ $genderLabel }}
                    </span>
                    <span class="btn-pill bg-white/20 border border-white/30 text-white px-2.5 py-0.5 text-[9px] font-black uppercase tracking-widest backdrop-blur-md shadow-sm">
                        {{ $kemandirianLabel }}
                    </span>
                </div>
                <h1 class="text-xl md:text-2xl font-black text-white tracking-tight leading-tight line-clamp-1" title="{{ $dataLansia->nama_lengkap ?? '-' }}">
                    {{ $dataLansia->nama_lengkap ?? '-' }}
                </h1>
                <p class="text-amber-50 text-[11px] font-semibold mt-1 opacity-90">
                    NIK: {{ $dataLansia->nik ?? '-' }} • {{ $formatDate($dataLansia->tanggal_lahir ?? null) }}
                </p>
            </div>
        </div>

        <div class="relative z-10 flex gap-2 shrink-0">
            <a href="{{ $backRoute }}" data-no-delay="true" class="btn-pill bg-white/10 hover:bg-white/20 text-white border border-white/30 px-5 py-2.5 text-[10px] font-black uppercase tracking-widest backdrop-blur-md transition-all">
                Kembali
            </a>
            <a href="{{ $dashboardRoute }}" data-no-delay="true" class="btn-pill bg-white hover:bg-amber-50 text-amber-600 px-5 py-2.5 text-[10px] font-black uppercase tracking-widest shadow-md transition-all hover:-translate-y-0.5">
                Dashboard
            </a>
        </div>
    </section>

    {{-- 2. METRIK KESEHATAN UTAMA --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($metrics as $metric)
            <div class="widget-card p-4 flex flex-col justify-center text-center hover:-translate-y-1 transition-all duration-300">
                <div class="w-10 h-10 mx-auto rounded-full {{ $metricTone($metric['tone'] ?? 'slate') }} flex items-center justify-center text-lg mb-2 shadow-inner">
                    <i class="fas @if($metric['label']=='Usia') fa-cake-candles @elseif($metric['label']=='Tensi') fa-heart-pulse @elseif($metric['label']=='Gula Darah') fa-droplet @else fa-person-cane @endif"></i>
                </div>
                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">
                    {{ $metric['label'] }}
                </p>
                <p class="text-xl font-black text-slate-800 leading-none">
                    {{ $metric['value'] }}
                </p>
                <p class="text-[9px] font-bold text-slate-500 mt-1.5">
                    {{ $metric['caption'] }}
                </p>
            </div>
        @endforeach
    </div>

    {{-- 3. AREA UTAMA: PROFIL, KESIMPULAN & METRIK TAMBAHAN --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-12 items-start relative">

        {{-- KOLOM KIRI (STICKY): Profil & Kesimpulan --}}
        <div class="xl:col-span-5 flex flex-col gap-5 sticky top-6 z-10">
            <div class="widget-card flex flex-col overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-4 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-white border border-slate-200 text-amber-500 flex items-center justify-center shadow-sm shrink-0">
                        <i class="fas fa-id-card-clip text-xs"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-800">Buku Rekam Medis</h2>
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-0.5">Informasi Profil & Kesimpulan PTM</p>
                    </div>
                </div>

                <div class="p-5 flex flex-col gap-5">
                    
                    {{-- Alert Kesimpulan --}}
                    <div class="rounded-[1rem] border p-4 flex flex-col gap-3 {{ $analysisTone }}">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-lg shadow-sm border {{ explode(' ', $analysisTone)[0] }} {{ explode(' ', $analysisTone)[2] }}">
                                <i class="fas {{ $ptmAnalysis['icon'] ?? 'fa-circle-info' }}"></i>
                            </div>
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest opacity-70">Analisis Penyakit Tidak Menular</p>
                                <h2 class="text-lg font-black leading-none mt-0.5 {{ explode(' ', $analysisTone)[2] }}">
                                    {{ $ptmAnalysis['label'] ?? 'Menunggu Data' }}
                                </h2>
                            </div>
                        </div>
                        <div class="pt-2 border-t {{ explode(' ', $analysisTone)[0] }} opacity-80 border-dashed">
                            <p class="text-xs font-bold leading-relaxed">
                                {{ $ptmAnalysis['message'] ?? '-' }}
                            </p>
                            <p class="mt-1.5 text-[10px] font-semibold leading-relaxed">
                                <i class="fa-solid fa-lightbulb mr-1"></i> {{ $ptmAnalysis['suggestion'] ?? 'Lakukan pemeriksaan di Posyandu.' }}
                            </p>
                        </div>
                    </div>

                    {{-- Data Identitas --}}
                    <div class="space-y-4 bg-slate-50/50 p-4 rounded-[1rem] border border-slate-100">
                        <div class="flex items-start gap-3 border-b border-slate-100/50 pb-3">
                            <div class="w-6 h-6 rounded-md bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-user text-[10px]"></i>
                            </div>
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Nama Lengkap</p>
                                <p class="text-xs font-black text-slate-800 mt-0.5">{{ $dataLansia->nama_lengkap ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 border-b border-slate-100/50 pb-3">
                            <div class="w-6 h-6 rounded-md bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-id-card text-[10px]"></i>
                            </div>
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Nomor Induk (NIK)</p>
                                <p class="text-xs font-black text-slate-800 mt-0.5">{{ $dataLansia->nik ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 border-b border-slate-100/50 pb-3">
                            <div class="w-6 h-6 rounded-md bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-calendar-day text-[10px]"></i>
                            </div>
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Tgl Lahir</p>
                                <p class="text-xs font-black text-slate-800 mt-0.5">{{ $formatDate($dataLansia->tanggal_lahir ?? null) }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 border-b border-slate-100/50 pb-3">
                            <div class="w-6 h-6 rounded-md bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-notes-medical text-[10px]"></i>
                            </div>
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Penyakit Bawaan</p>
                                <p class="text-xs font-black text-slate-800 mt-0.5 leading-relaxed">{{ $dataLansia->penyakit_bawaan ?: 'Tidak ada catatan.' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-md bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-map-location-dot text-[10px]"></i>
                            </div>
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Alamat</p>
                                <p class="text-xs font-black text-slate-800 mt-0.5 leading-relaxed">{{ $dataLansia->alamat ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Metrik Kesehatan Lanjutan (Grid Kecil) --}}
                    @if(isset($healthMetrics) && count($healthMetrics) > 0)
                        <div class="grid grid-cols-3 gap-2 mt-auto">
                            @foreach($healthMetrics as $metric)
                                <div class="rounded-xl border border-slate-100 bg-white p-2.5 text-center shadow-sm">
                                    <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5 truncate">{{ $metric['label'] }}</p>
                                    <p class="text-sm font-black text-slate-800 truncate">{{ $metric['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: Daftar Riwayat PTM --}}
        <div class="xl:col-span-7 flex flex-col gap-5">
            <div class="widget-card flex flex-col overflow-hidden h-full">
                <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white border border-slate-200 text-amber-500 flex items-center justify-center shadow-sm shrink-0">
                            <i class="fas fa-stethoscope text-xs"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-slate-800">Riwayat Pemeriksaan PTM</h2>
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-0.5">Log Pemeriksaan Fisik Lansia</p>
                        </div>
                    </div>
                    <span class="btn-pill bg-white border border-slate-200 text-slate-500 px-3 py-1 text-[9px] font-black uppercase tracking-widest shadow-sm">
                        {{ $riwayatCards->count() }} Catatan
                    </span>
                </div>

                <div class="flex-1 overflow-y-auto slim-scroll p-5 bg-slate-50/30 space-y-4">
                    @forelse($paginatedRiwayat as $item)
                        <article class="rounded-[1.25rem] border border-slate-200/80 bg-white p-4 shadow-sm hover:border-amber-300 hover:shadow-md transition-all">
                            <div class="flex items-start justify-between gap-3 mb-3 border-b border-slate-50 pb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex flex-col items-center justify-center text-slate-600 shrink-0">
                                        <span class="text-sm font-black leading-none">{{ Carbon::parse($item['tanggal'] ?? '')->format('d') }}</span>
                                        <span class="text-[8px] font-black uppercase">{{ Carbon::parse($item['tanggal'] ?? '')->translatedFormat('M') }}</span>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-slate-800">{{ $item['tanggal'] ?? '-' }}</p>
                                        <p class="mt-0.5 text-[8px] font-bold text-slate-400 uppercase tracking-widest">Pemeriksaan Rutin</p>
                                    </div>
                                </div>
                                <span class="rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[8px] font-black uppercase tracking-widest text-emerald-700 shrink-0">
                                    <i class="fa-solid fa-check mr-0.5"></i> {{ $item['status'] ?? 'Tervalidasi' }}
                                </span>
                            </div>

                            {{-- Baris 1: Tensi, Gula, IMT --}}
                            <div class="grid grid-cols-3 gap-2 mb-2">
                                <div class="rounded-lg border border-rose-100 bg-rose-50/50 px-2 py-1.5 text-center">
                                    <p class="text-[8px] font-black uppercase tracking-widest text-rose-600 mb-0.5">Tensi</p>
                                    <p class="truncate text-xs font-black text-rose-700">{{ $item['tensi'] ?? '-' }}</p>
                                </div>
                                <div class="rounded-lg border border-sky-100 bg-sky-50/50 px-2 py-1.5 text-center">
                                    <p class="text-[8px] font-black uppercase tracking-widest text-sky-600 mb-0.5">Gula Darah</p>
                                    <p class="truncate text-xs font-black text-sky-700">{{ $item['gula'] ?? '-' }}</p>
                                </div>
                                <div class="rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-center">
                                    <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">IMT</p>
                                    <p class="truncate text-xs font-black text-slate-800">{{ $item['imt'] ?? '-' }}</p>
                                </div>
                            </div>

                            {{-- Baris 2: Kolesterol, Asam Urat, L. Perut --}}
                            <div class="grid grid-cols-3 gap-2 mb-3">
                                <div class="rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-center">
                                    <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Kolesterol</p>
                                    <p class="truncate text-xs font-black text-slate-800">{{ $item['kolesterol'] ?? '-' }}</p>
                                </div>
                                <div class="rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-center">
                                    <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Asam Urat</p>
                                    <p class="truncate text-xs font-black text-slate-800">{{ $item['asam_urat'] ?? '-' }}</p>
                                </div>
                                <div class="rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-center">
                                    <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">L. Perut</p>
                                    <p class="truncate text-xs font-black text-slate-800">{{ $item['lingkar_perut'] ?? '-' }}</p>
                                </div>
                            </div>

                            <div class="mt-auto flex flex-col gap-2">
                                @if(!empty($item['keluhan']))
                                    <div class="bg-amber-50/50 p-2.5 rounded-lg border border-amber-100/50">
                                        <p class="text-[8px] font-black uppercase tracking-widest text-amber-600 mb-0.5">Keluhan</p>
                                        <p class="text-[10px] font-semibold leading-relaxed text-slate-600 line-clamp-2">{{ $item['keluhan'] }}</p>
                                    </div>
                                @endif
                                
                                @if(!empty($item['edukasi']))
                                    <div class="bg-emerald-50/50 p-2.5 rounded-lg border border-emerald-100/50">
                                        <p class="text-[8px] font-black uppercase tracking-widest text-emerald-600 mb-0.5">Edukasi Bidan</p>
                                        <p class="text-[10px] font-semibold leading-relaxed text-slate-600 line-clamp-2">{{ $item['edukasi'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="flex flex-col items-center justify-center text-center opacity-70 p-8 border border-dashed border-slate-200 rounded-[1.5rem] min-h-[250px]">
                            <div class="w-14 h-14 rounded-xl bg-white flex items-center justify-center mb-3 text-2xl text-slate-300 border border-slate-100 shadow-sm">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <h3 class="text-xs font-black text-slate-700">Belum Ada Pemeriksaan</h3>
                            <p class="text-[10px] font-semibold text-slate-500 mt-1 max-w-xs leading-relaxed">Catatan akan muncul setelah validasi Bidan.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination Riwayat Pemeriksaan --}}
                @if($paginatedRiwayat->hasPages())
                    <div class="border-t border-slate-100 bg-slate-50/80 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-500">
                            Hal. <span class="text-slate-900">{{ $paginatedRiwayat->currentPage() }}</span> dari <span class="text-slate-900">{{ $paginatedRiwayat->lastPage() }}</span>
                        </p>
                        <div class="flex items-center gap-1.5">
                            @if ($paginatedRiwayat->onFirstPage())
                                <button disabled class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50"><i class="fas fa-chevron-left text-[10px]"></i></button>
                            @else
                                <a href="{{ $paginatedRiwayat->previousPageUrl() }}" class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-amber-50 hover:text-amber-600"><i class="fas fa-chevron-left text-[10px]"></i></a>
                            @endif

                            @php $start = max(1, $paginatedRiwayat->currentPage() - 1); $end = min($paginatedRiwayat->lastPage(), $paginatedRiwayat->currentPage() + 1); @endphp
                            @for ($page = $start; $page <= $end; $page++)
                                @if ($page == $paginatedRiwayat->currentPage())
                                    <span class="btn-pill w-8 h-8 flex items-center justify-center bg-amber-500 text-white font-black text-[10px] shadow-sm">{{ $page }}</span>
                                @else
                                    <a href="{{ $paginatedRiwayat->url($page) }}" class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-200 bg-white text-slate-600 font-bold text-[10px] shadow-sm hover:bg-amber-50 hover:text-amber-600">{{ $page }}</a>
                                @endif
                            @endfor

                            @if ($paginatedRiwayat->hasMorePages())
                                <a href="{{ $paginatedRiwayat->nextPageUrl() }}" class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-amber-50 hover:text-amber-600"><i class="fas fa-chevron-right text-[10px]"></i></a>
                            @else
                                <button disabled class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50"><i class="fas fa-chevron-right text-[10px]"></i></button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection