@extends('layouts.user')

@section('title', 'Detail Kesehatan Balita')
@section('page_title', 'Detail Balita')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Pagination\LengthAwarePaginator;

    Carbon::setLocale('id');

    $dataBalita = $balita ?? $dataBalita ?? null;

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
    $growthMetrics = collect($growthMetrics ?? []);
    $trend = collect($trend ?? []);
    $riwayatCards = collect($riwayatCards ?? []);
    $imunisasiCards = collect($imunisasiCards ?? []);

    $growthAnalysis = $growthAnalysis ?? [
        'label' => 'Belum Ada Data',
        'message' => 'Data pengukuran balita belum tersedia.',
        'suggestion' => 'Lakukan pengukuran rutin di Posyandu.',
        'tone' => 'slate',
        'icon' => 'fa-circle-info',
    ];

    $genderLabel = match ($dataBalita->jenis_kelamin ?? null) {
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        default => 'Belum diisi',
    };

    $initial = strtoupper(substr(trim((string) ($dataBalita->nama_lengkap ?? 'B')), 0, 1)) ?: 'B';

    $formatDate = fn ($value, $format = 'd F Y') => $value
        ? Carbon::parse($value)->translatedFormat($format)
        : '-';

    $analysisTone = match ($growthAnalysis['tone'] ?? 'slate') {
        'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'amber'   => 'border-amber-200 bg-amber-50 text-amber-800',
        'rose'    => 'border-rose-200 bg-rose-50 text-rose-800',
        'sky'     => 'border-sky-200 bg-sky-50 text-sky-800',
        default   => 'border-slate-200 bg-slate-50 text-slate-800',
    };

    // Ekstraksi class tone agar aman seperti di Remaja
    $analysisToneParts = explode(' ', $analysisTone);
    $analysisBorderClass = $analysisToneParts[0] ?? 'border-slate-200';
    $analysisTextClass = $analysisToneParts[2] ?? 'text-slate-800';

    $metricTone = function ($tone) {
        return match ($tone) {
            'emerald' => 'bg-emerald-50 text-emerald-600',
            'sky'     => 'bg-sky-50 text-sky-600',
            'amber'   => 'bg-amber-50 text-amber-600',
            'rose'    => 'bg-rose-50 text-rose-600',
            default   => 'bg-slate-50 text-slate-600',
        };
    };

    // LOGIKA PAGINATION MANUAL DI VIEW
    $perPage = 4; 

    // Pagination Riwayat Pengukuran
    $currentPageRiwayat = LengthAwarePaginator::resolveCurrentPage('pemeriksaan');
    $currentItemsRiwayat = $riwayatCards->slice(($currentPageRiwayat - 1) * $perPage, $perPage)->all();
    $paginatedRiwayat = new LengthAwarePaginator(
        $currentItemsRiwayat, $riwayatCards->count(), $perPage, $currentPageRiwayat, 
        ['path' => request()->url(), 'query' => array_merge(request()->query(), ['pemeriksaan' => $currentPageRiwayat]), 'pageName' => 'pemeriksaan']
    );

    // Pagination Riwayat Imunisasi
    $currentPageImun = LengthAwarePaginator::resolveCurrentPage('imunisasi');
    $currentItemsImun = $imunisasiCards->slice(($currentPageImun - 1) * $perPage, $perPage)->all();
    $paginatedImun = new LengthAwarePaginator(
        $currentItemsImun, $imunisasiCards->count(), $perPage, $currentPageImun, 
        ['path' => request()->url(), 'query' => array_merge(request()->query(), ['imunisasi' => $currentPageImun]), 'pageName' => 'imunisasi']
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
</style>
@endpush

@section('content')
<div class="max-w-[1280px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6 space-y-5">

    {{-- 1. HERO SECTION (PROFIL COMPACT) --}}
    <section class="bg-gradient-to-br from-rose-500 via-rose-400 to-pink-500 rounded-[2rem] p-6 md:p-8 relative overflow-hidden shadow-[0_15px_30px_-10px_rgba(244,63,94,.3)] border border-white/20 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-20 pointer-events-none"></div>
        
        <div class="relative z-10 flex items-center gap-5">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[1rem] bg-white text-2xl font-black text-rose-500 shadow-md border-2 border-white/40">
                {{ $initial }}
            </div>
            <div>
                <div class="flex flex-wrap gap-2 mb-1.5">
                    <span class="btn-pill bg-white/20 border border-white/30 text-white px-2.5 py-0.5 text-[9px] font-black uppercase tracking-widest backdrop-blur-md shadow-sm">
                        Data Balita
                    </span>
                    <span class="btn-pill bg-white/20 border border-white/30 text-white px-2.5 py-0.5 text-[9px] font-black uppercase tracking-widest backdrop-blur-md shadow-sm">
                        {{ $genderLabel }}
                    </span>
                    <span class="btn-pill bg-white/20 border border-white/30 text-white px-2.5 py-0.5 text-[9px] font-black uppercase tracking-widest backdrop-blur-md shadow-sm">
                        {{ $usia['kategori'] ?? 'Balita' }}
                    </span>
                </div>
                <h1 class="text-xl md:text-2xl font-black text-white tracking-tight leading-tight line-clamp-1" title="{{ $dataBalita->nama_lengkap ?? '-' }}">
                    {{ $dataBalita->nama_lengkap ?? '-' }}
                </h1>
                <p class="text-rose-50 text-[11px] font-semibold mt-1 opacity-90">
                    NIK: {{ $dataBalita->nik ?? '-' }} • {{ $formatDate($dataBalita->tanggal_lahir ?? null) }}
                </p>
            </div>
        </div>

        <div class="relative z-10 flex flex-wrap gap-2 shrink-0 w-full md:w-auto">
            <a href="{{ $backRoute }}" data-no-delay="true" class="btn-pill flex-1 md:flex-none text-center bg-white/10 hover:bg-white/20 text-white border border-white/30 px-5 py-2.5 text-[10px] font-black uppercase tracking-widest backdrop-blur-md transition-all shadow-sm">
                Kembali
            </a>
            <a href="{{ $dashboardRoute }}" data-no-delay="true" class="btn-pill flex-1 md:flex-none text-center bg-white hover:bg-rose-50 text-rose-600 px-5 py-2.5 text-[10px] font-black uppercase tracking-widest shadow-md transition-all hover:-translate-y-0.5">
                Dashboard
            </a>
        </div>
    </section>

    {{-- 2. METRIK KESEHATAN UTAMA --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($metrics as $metric)
            <div class="widget-card p-4 flex flex-col justify-center text-center hover:-translate-y-1 transition-all duration-300">
                <div class="w-10 h-10 mx-auto rounded-full {{ $metricTone($metric['tone'] ?? 'slate') }} flex items-center justify-center text-lg mb-2 shadow-inner">
                    <i class="fas @if($metric['label']=='Usia') fa-baby @elseif($metric['label']=='Berat') fa-scale-unbalanced @elseif($metric['label']=='Tinggi') fa-ruler-vertical @else fa-clipboard-check @endif"></i>
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

    {{-- 3. AREA UTAMA: MENGGUNAKAN GRID 5:7 SEPERTI REMAJA --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-12 xl:items-stretch relative">

        {{-- KOLOM KIRI (STICKY): Profil & Kesimpulan Pertumbuhan --}}
        <div class="xl:col-span-5 flex flex-col gap-5 xl:sticky xl:top-6 z-10">
            <div class="widget-card flex flex-col overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-4 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-white border border-slate-200 text-rose-500 flex items-center justify-center shadow-sm shrink-0">
                        <i class="fas fa-id-card-clip text-xs"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-800">Buku KIA / Rekam Medis</h2>
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-0.5">Profil & Status Gizi Balita</p>
                    </div>
                </div>

                <div class="p-5 flex flex-col gap-5">
                    
                    {{-- Alert Kesimpulan (Sama presisi dengan Remaja) --}}
                    <div class="rounded-[1rem] border p-4 flex flex-col gap-3 {{ $analysisTone }}">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-lg shadow-sm border {{ $analysisBorderClass }} {{ $analysisTextClass }}">
                                <i class="fas {{ $growthAnalysis['icon'] ?? 'fa-circle-info' }}"></i>
                            </div>
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest opacity-70">Status Gizi & Tumbuh Kembang</p>
                                <h2 class="text-lg font-black leading-none mt-0.5 {{ $analysisTextClass }}">
                                    {{ $growthAnalysis['label'] ?? 'Belum Ada Data' }}
                                </h2>
                            </div>
                        </div>
                        <div class="pt-2 border-t {{ $analysisBorderClass }} opacity-80 border-dashed">
                            <p class="text-xs font-bold leading-relaxed">
                                {{ $growthAnalysis['message'] ?? '-' }}
                            </p>
                            <p class="mt-1.5 text-[10px] font-semibold leading-relaxed">
                                <i class="fa-solid fa-lightbulb mr-1"></i> {{ $growthAnalysis['suggestion'] ?? 'Lakukan pemantauan rutin di Posyandu.' }}
                            </p>
                        </div>
                    </div>

                    {{-- Data Identitas (Format Vertikal Bersih seperti Remaja) --}}
                    <div class="space-y-4 bg-slate-50/50 p-4 rounded-[1rem] border border-slate-100">
                        <div class="flex items-start gap-3 border-b border-slate-100/50 pb-3">
                            <div class="w-6 h-6 rounded-md bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-user text-[10px]"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Nama Lengkap</p>
                                <p class="text-xs font-black text-slate-800 mt-0.5 break-words">{{ $dataBalita->nama_lengkap ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 border-b border-slate-100/50 pb-3">
                            <div class="w-6 h-6 rounded-md bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-people-roof text-[10px]"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Nama Orang Tua / Ibu</p>
                                <p class="text-xs font-black text-slate-800 mt-0.5 break-words">{{ $dataBalita->nama_orangtua ?? $dataBalita->nama_ibu ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 border-b border-slate-100/50 pb-3">
                            <div class="w-6 h-6 rounded-md bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-id-card text-[10px]"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Nomor Induk (NIK)</p>
                                <p class="text-xs font-black text-slate-800 mt-0.5 tracking-wider">{{ $dataBalita->nik ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 border-b border-slate-100/50 pb-3">
                            <div class="w-6 h-6 rounded-md bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-calendar-day text-[10px]"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Tempat, Tgl Lahir</p>
                                <p class="text-xs font-black text-slate-800 mt-0.5">{{ $dataBalita->tempat_lahir ?? '-' }}, {{ $formatDate($dataBalita->tanggal_lahir ?? null) }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-md bg-rose-50 text-rose-500 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-map-location-dot text-[10px]"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Alamat</p>
                                <p class="text-xs font-black text-slate-800 mt-0.5 leading-relaxed break-words">{{ $dataBalita->alamat ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Metrik Kesehatan Lanjutan (Grid Kecil di Bawah Profil) --}}
                    @if($growthMetrics->isNotEmpty())
                        <div class="grid grid-cols-3 gap-2 mt-auto pt-2">
                            @foreach($growthMetrics->take(6) as $metric)
                                <div class="rounded-xl border border-slate-100 bg-white p-2.5 text-center shadow-sm flex flex-col justify-between min-w-0">
                                    <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-1 truncate">{{ $metric['label'] }}</p>
                                    <p class="text-[11px] font-black text-slate-800 leading-tight whitespace-normal break-words">{{ $metric['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: Daftar Riwayat (Pengukuran & Imunisasi Ditumpuk Rapi) --}}
        <div class="xl:col-span-7 flex flex-col gap-5">
            
            {{-- 1. KARTU RIWAYAT PENGUKURAN --}}
            <div class="widget-card flex flex-col overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white border border-slate-200 text-rose-500 flex items-center justify-center shadow-sm shrink-0">
                            <i class="fas fa-weight-scale text-xs"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-slate-800">Riwayat Pengukuran</h2>
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-0.5">Log Antropometri Terakhir</p>
                        </div>
                    </div>
                    <span class="btn-pill bg-white border border-slate-200 text-slate-500 px-3 py-1 text-[9px] font-black uppercase tracking-widest shadow-sm">
                        {{ $riwayatCards->count() }} Catatan
                    </span>
                </div>

                <div class="p-5 bg-slate-50/30 flex-1">
                    {{-- Dibuat Grid 2 Kolom seperti standar UI Remaja --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-stretch h-full">
                        @forelse($paginatedRiwayat as $item)
                            <article class="rounded-[1.25rem] border border-slate-200/80 bg-white p-4 shadow-sm hover:border-rose-300 transition-all flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start justify-between gap-3 mb-3 border-b border-slate-100/50 pb-3">
                                        <div>
                                            <p class="text-xs font-black text-slate-800">{{ $item['tanggal'] ?? '-' }}</p>
                                            <p class="mt-0.5 text-[8px] font-bold text-slate-400 uppercase tracking-widest">Usia: {{ $item['usia'] ?? '-' }}</p>
                                        </div>
                                        <span class="rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[8px] font-black uppercase tracking-widest text-emerald-700 shrink-0">
                                            <i class="fa-solid fa-check mr-0.5"></i> {{ $item['status'] ?? 'Tervalidasi' }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-3 gap-2 mb-3">
                                        <div class="rounded-lg border border-slate-100 bg-slate-50 p-2 text-center min-w-0">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">BB</p>
                                            <p class="text-[11px] font-black text-slate-800 tracking-tight leading-tight">{{ $item['berat'] ?? '-' }}</p>
                                        </div>
                                        <div class="rounded-lg border border-slate-100 bg-slate-50 p-2 text-center min-w-0">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">TB</p>
                                            <p class="text-[11px] font-black text-slate-800 tracking-tight leading-tight">{{ $item['tinggi'] ?? '-' }}</p>
                                        </div>
                                        <div class="rounded-lg border border-slate-100 bg-slate-50 p-2 text-center min-w-0">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">LK/LILA</p>
                                            <p class="text-[11px] font-black text-slate-800 tracking-tight leading-tight">{{ $item['lingkar_kepala'] !== '-' ? $item['lingkar_kepala'] : $item['lila'] }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-auto bg-rose-50/50 p-2.5 rounded-lg border border-rose-100/50">
                                    <p class="text-[8px] font-black uppercase tracking-widest text-rose-600 mb-0.5">Status Gizi (BB/TB)</p>
                                    <p class="text-[10px] font-black text-slate-700">{{ $item['status_gizi'] ?? 'Belum Dinilai' }}</p>
                                </div>
                            </article>
                        @empty
                            <div class="md:col-span-2 flex flex-col items-center justify-center text-center opacity-70 p-8 border border-dashed border-slate-200 rounded-[1.5rem]">
                                <div class="w-14 h-14 rounded-xl bg-white flex items-center justify-center mb-3 text-2xl text-slate-300 border border-slate-100 shadow-sm">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <h3 class="text-xs font-black text-slate-700">Belum Ada Pengukuran</h3>
                                <p class="text-[10px] font-semibold text-slate-500 mt-1 max-w-xs leading-relaxed">Catatan akan muncul setelah validasi Bidan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Pagination Riwayat Pengukuran --}}
                @if($paginatedRiwayat->hasPages())
                    <div class="border-t border-slate-100 bg-slate-50/80 px-5 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-500">
                            Hal <span class="text-slate-900">{{ $paginatedRiwayat->currentPage() }}</span> dari <span class="text-slate-900">{{ $paginatedRiwayat->lastPage() }}</span>
                        </p>
                        <div class="flex items-center gap-1.5">
                            @if ($paginatedRiwayat->onFirstPage())
                                <button type="button" disabled class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50"><i class="fas fa-chevron-left text-[10px]"></i></button>
                            @else
                                <a href="{{ $paginatedRiwayat->previousPageUrl() }}" class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-rose-50 hover:text-rose-600"><i class="fas fa-chevron-left text-[10px]"></i></a>
                            @endif

                            @php $start = max(1, $paginatedRiwayat->currentPage() - 1); $end = min($paginatedRiwayat->lastPage(), $paginatedRiwayat->currentPage() + 1); @endphp
                            @for ($page = $start; $page <= $end; $page++)
                                @if ($page == $paginatedRiwayat->currentPage())
                                    <span class="btn-pill w-8 h-8 flex items-center justify-center bg-rose-500 text-white font-black text-[10px] shadow-sm">{{ $page }}</span>
                                @else
                                    <a href="{{ $paginatedRiwayat->url($page) }}" class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-200 bg-white text-slate-600 font-bold text-[10px] shadow-sm hover:bg-rose-50 hover:text-rose-600">{{ $page }}</a>
                                @endif
                            @endfor

                            @if ($paginatedRiwayat->hasMorePages())
                                <a href="{{ $paginatedRiwayat->nextPageUrl() }}" class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-rose-50 hover:text-rose-600"><i class="fas fa-chevron-right text-[10px]"></i></a>
                            @else
                                <button type="button" disabled class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50"><i class="fas fa-chevron-right text-[10px]"></i></button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- 2. KARTU RIWAYAT IMUNISASI --}}
            <div class="widget-card flex flex-col overflow-hidden mt-2">
                <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white border border-slate-200 text-sky-500 flex items-center justify-center shadow-sm shrink-0">
                            <i class="fas fa-syringe text-xs"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-slate-800">Riwayat Imunisasi</h2>
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-0.5">Log Vaksin & Tindakan</p>
                        </div>
                    </div>
                    <span class="btn-pill bg-white border border-slate-200 text-slate-500 px-3 py-1 text-[9px] font-black uppercase tracking-widest shadow-sm">
                        {{ $imunisasiCards->count() }} Catatan
                    </span>
                </div>

                <div class="p-5 bg-slate-50/30 flex-1">
                    {{-- Grid 2 Kolom juga --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-stretch h-full">
                        @forelse($paginatedImun as $imun)
                            <article class="rounded-[1.25rem] border border-slate-200/80 bg-white p-4 shadow-sm hover:border-sky-300 transition-all flex flex-col justify-between">
                                <div>
                                    <div class="flex items-start gap-3 mb-3 border-b border-slate-100/50 pb-3">
                                        <div class="w-8 h-8 rounded-xl bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-500 shrink-0">
                                            <i class="fas fa-shield-virus text-[10px]"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-slate-800 leading-tight">{{ $imun['nama'] ?? 'Imunisasi' }}</p>
                                            <p class="mt-0.5 text-[8px] font-bold text-slate-400 uppercase tracking-widest">{{ $imun['tanggal'] ?? '-' }}</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 mb-3">
                                        <div class="rounded-lg border border-slate-100 bg-slate-50 p-2 text-center min-w-0">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Batch / Vaksin</p>
                                            <p class="text-[11px] font-black text-slate-800 tracking-tight truncate">{{ $imun['batch'] ?? '-' }}</p>
                                        </div>
                                        <div class="rounded-lg border border-slate-100 bg-slate-50 p-2 text-center min-w-0">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Petugas</p>
                                            <p class="text-[11px] font-black text-slate-800 tracking-tight truncate">{{ $imun['petugas'] ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-auto bg-slate-50/50 p-2.5 rounded-lg border border-slate-100/50">
                                    <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5 flex items-center gap-1"><i class="fa-regular fa-comment-dots"></i> Catatan Khusus</p>
                                    <p class="text-[10px] font-semibold leading-relaxed text-slate-600 line-clamp-2">{{ $imun['catatan'] ?? 'Tidak ada catatan.' }}</p>
                                </div>
                            </article>
                        @empty
                            <div class="md:col-span-2 flex flex-col items-center justify-center text-center opacity-70 p-8 border border-dashed border-slate-200 rounded-[1.5rem]">
                                <div class="w-14 h-14 rounded-xl bg-white flex items-center justify-center mb-3 text-2xl text-slate-300 border border-slate-100 shadow-sm">
                                    <i class="fas fa-syringe"></i>
                                </div>
                                <h3 class="text-xs font-black text-slate-700">Belum Ada Imunisasi</h3>
                                <p class="text-[10px] font-semibold text-slate-500 mt-1 max-w-xs leading-relaxed">Log vaksin akan muncul setelah ditambahkan Bidan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Pagination Riwayat Imunisasi --}}
                @if($paginatedImun->hasPages())
                    <div class="border-t border-slate-100 bg-slate-50/80 px-5 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-500">
                            Hal <span class="text-slate-900">{{ $paginatedImun->currentPage() }}</span> dari <span class="text-slate-900">{{ $paginatedImun->lastPage() }}</span>
                        </p>
                        <div class="flex items-center gap-1.5">
                            @if ($paginatedImun->onFirstPage())
                                <button type="button" disabled class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50"><i class="fas fa-chevron-left text-[10px]"></i></button>
                            @else
                                <a href="{{ $paginatedImun->previousPageUrl() }}" class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-sky-50 hover:text-sky-600"><i class="fas fa-chevron-left text-[10px]"></i></a>
                            @endif

                            @php $startImun = max(1, $paginatedImun->currentPage() - 1); $endImun = min($paginatedImun->lastPage(), $paginatedImun->currentPage() + 1); @endphp
                            @for ($page = $startImun; $page <= $endImun; $page++)
                                @if ($page == $paginatedImun->currentPage())
                                    <span class="btn-pill w-8 h-8 flex items-center justify-center bg-sky-500 text-white font-black text-[10px] shadow-sm">{{ $page }}</span>
                                @else
                                    <a href="{{ $paginatedImun->url($page) }}" class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-200 bg-white text-slate-600 font-bold text-[10px] shadow-sm hover:bg-sky-50 hover:text-sky-600">{{ $page }}</a>
                                @endif
                            @endfor

                            @if ($paginatedImun->hasMorePages())
                                <a href="{{ $paginatedImun->nextPageUrl() }}" class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-sky-50 hover:text-sky-600"><i class="fas fa-chevron-right text-[10px]"></i></a>
                            @else
                                <button type="button" disabled class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50"><i class="fas fa-chevron-right text-[10px]"></i></button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

        </div>

    </div>
</div>
@endsection