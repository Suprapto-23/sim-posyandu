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

    $metricTone = function ($tone) {
        return match ($tone) {
            'emerald' => 'bg-emerald-50 text-emerald-600',
            'sky'     => 'bg-sky-50 text-sky-600',
            'amber'   => 'bg-amber-50 text-amber-600',
            'rose'    => 'bg-rose-50 text-rose-600',
            default   => 'bg-slate-50 text-slate-600',
        };
    };

    // LOGIKA PAGINATION MANUAL DI VIEW (Pemeriksaan & Imunisasi)
    $perPage = 4; 

    // Pagination Riwayat Pemeriksaan
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

    .slim-scroll::-webkit-scrollbar { width: 6px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: rgba(244, 63, 94, 0.2); border-radius: 10px; }
    .slim-scroll::-webkit-scrollbar-thumb:hover { background: rgba(244, 63, 94, 0.5); }
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

        <div class="relative z-10 flex gap-2 shrink-0">
            <a href="{{ $backRoute }}" data-no-delay="true" class="btn-pill bg-white/10 hover:bg-white/20 text-white border border-white/30 px-5 py-2.5 text-[10px] font-black uppercase tracking-widest backdrop-blur-md transition-all shadow-sm">
                Kembali
            </a>
            <a href="{{ $dashboardRoute }}" data-no-delay="true" class="btn-pill bg-white hover:bg-rose-50 text-rose-600 px-5 py-2.5 text-[10px] font-black uppercase tracking-widest shadow-md transition-all hover:-translate-y-0.5">
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

    {{-- 3. AREA PROFIL & KESIMPULAN (MEMBENTANG PENUH / FULL WIDTH) --}}
    <div class="widget-card flex flex-col overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-white border border-slate-200 text-rose-500 flex items-center justify-center shadow-sm shrink-0">
                <i class="fas fa-id-card-clip text-xs"></i>
            </div>
            <div>
                <h2 class="text-sm font-black text-slate-800">Buku KIA / Rekam Medis</h2>
                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-0.5">Informasi Profil & Status Pertumbuhan</p>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            
            {{-- Bagian Kiri: Identitas & Metrik Tambahan --}}
            <div class="lg:col-span-7 flex flex-col gap-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3.5 shadow-sm">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1 flex items-center gap-1.5"><i class="fa-solid fa-user text-rose-500"></i> Nama Lengkap</p>
                        <p class="text-sm font-black text-slate-800">{{ $dataBalita->nama_lengkap ?? '-' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3.5 shadow-sm">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1 flex items-center gap-1.5"><i class="fa-solid fa-people-roof text-rose-500"></i> Nama Orang Tua</p>
                        <p class="text-sm font-black text-slate-800">{{ $dataBalita->nama_orangtua ?? $dataBalita->nama_ibu ?? '-' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3.5 shadow-sm">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1 flex items-center gap-1.5"><i class="fa-solid fa-calendar-day text-rose-500"></i> Tempat, Tgl Lahir</p>
                        <p class="text-sm font-black text-slate-800">{{ $dataBalita->tempat_lahir ?? '-' }}, {{ $formatDate($dataBalita->tanggal_lahir ?? null) }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3.5 shadow-sm">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1 flex items-center gap-1.5"><i class="fa-solid fa-id-card text-rose-500"></i> NIK Balita</p>
                        <p class="text-sm font-black text-slate-800">{{ $dataBalita->nik ?? '-' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3.5 shadow-sm sm:col-span-2">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1 flex items-center gap-1.5"><i class="fa-solid fa-map-location-dot text-rose-500"></i> Alamat</p>
                        <p class="text-sm font-black text-slate-800 leading-relaxed">{{ $dataBalita->alamat ?? '-' }}</p>
                    </div>
                </div>

                @if($growthMetrics->isNotEmpty())
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                        @foreach($growthMetrics->take(6) as $metric)
                            <div class="rounded-lg border border-slate-100 bg-white p-2 text-center shadow-sm">
                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5 truncate">{{ $metric['label'] }}</p>
                                <p class="text-xs font-black text-slate-800 truncate">{{ $metric['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Bagian Kanan: Kesimpulan Status --}}
            <div class="lg:col-span-5 h-full">
                <div class="h-full rounded-[1.5rem] border p-6 flex flex-col justify-center gap-4 {{ $analysisTone }}">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white text-2xl shadow-sm border {{ explode(' ', $analysisTone)[0] }} {{ explode(' ', $analysisTone)[2] }}">
                            <i class="fas {{ $growthAnalysis['icon'] ?? 'fa-circle-info' }}"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest opacity-70 mb-1">Status Gizi & Pertumbuhan</p>
                            <h2 class="text-xl font-black leading-none {{ explode(' ', $analysisTone)[2] }}">
                                {{ $growthAnalysis['label'] ?? 'Belum Ada Data' }}
                            </h2>
                        </div>
                    </div>
                    <div class="pt-3 border-t {{ explode(' ', $analysisTone)[0] }} opacity-80 border-dashed">
                        <p class="text-sm font-bold leading-relaxed mb-1">
                            {{ $growthAnalysis['message'] ?? '-' }}
                        </p>
                        <p class="text-xs font-semibold leading-relaxed">
                            <i class="fa-solid fa-lightbulb mr-1"></i> {{ $growthAnalysis['suggestion'] ?? 'Lakukan pemantauan di Posyandu secara berkala.' }}
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- 4. AREA BAWAH: RIWAYAT KLINIS (MEMBENTANG & BER-GRID) --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 items-start">
        
        {{-- BAGIAN KIRI: RIWAYAT PENGUKURAN (7 KOLOM) --}}
        <div class="lg:col-span-7 widget-card flex flex-col h-full overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-400 flex items-center justify-center shadow-sm shrink-0">
                        <i class="fas fa-weight-scale text-xs"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-800">Riwayat Pengukuran</h2>
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-0.5">Log Antropometri Terakhir</p>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto slim-scroll p-5 bg-slate-50/30 space-y-4">
                @forelse($paginatedRiwayat as $item)
                    <article class="rounded-[1.25rem] border border-slate-200/80 bg-white p-4 shadow-sm hover:border-rose-300 transition-all">
                        <div class="flex items-start justify-between gap-3 mb-3 border-b border-slate-50 pb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex flex-col items-center justify-center text-slate-600 shrink-0">
                                    <span class="text-sm font-black leading-none">{{ Carbon::parse($item['tanggal'] ?? '')->format('d') }}</span>
                                    <span class="text-[8px] font-black uppercase">{{ Carbon::parse($item['tanggal'] ?? '')->translatedFormat('M') }}</span>
                                </div>
                                <div>
                                    <p class="text-xs font-black text-slate-800">{{ $item['tanggal'] ?? '-' }}</p>
                                    <p class="mt-0.5 text-[8px] font-bold text-slate-400 uppercase tracking-widest">Usia: {{ $item['usia'] ?? '-' }}</p>
                                </div>
                            </div>
                            <span class="rounded-md border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[8px] font-black uppercase tracking-widest text-emerald-700 shrink-0">
                                <i class="fa-solid fa-check mr-0.5"></i> {{ $item['status'] ?? 'Tervalidasi' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-3 gap-2 mb-3">
                            <div class="rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-center">
                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">BB</p>
                                <p class="truncate text-xs font-black text-slate-800">{{ $item['berat'] ?? '-' }}</p>
                            </div>
                            <div class="rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-center">
                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">TB</p>
                                <p class="truncate text-xs font-black text-slate-800">{{ $item['tinggi'] ?? '-' }}</p>
                            </div>
                            <div class="rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-center">
                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">LK / LILA</p>
                                <p class="truncate text-xs font-black text-slate-800">{{ $item['lingkar_kepala'] !== '-' ? $item['lingkar_kepala'] : $item['lila'] }}</p>
                            </div>
                        </div>

                        <div class="mt-auto bg-rose-50 p-2.5 rounded-lg border border-rose-100">
                            <p class="text-[8px] font-black uppercase tracking-widest text-rose-600 mb-0.5">Status Gizi (BB/TB)</p>
                            <p class="text-xs font-black text-slate-700">{{ $item['status_gizi'] ?? 'Belum Dinilai' }}</p>
                        </div>
                    </article>
                @empty
                    <div class="flex flex-col items-center justify-center text-center opacity-70 p-8 border border-dashed border-slate-200 rounded-[1.5rem] min-h-[250px]">
                        <div class="w-14 h-14 rounded-xl bg-white flex items-center justify-center mb-3 text-2xl text-slate-300 border border-slate-100 shadow-sm">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h3 class="text-xs font-black text-slate-700">Belum Ada Pengukuran</h3>
                        <p class="text-[10px] font-semibold text-slate-500 mt-1 max-w-xs leading-relaxed">Catatan akan muncul setelah validasi Bidan.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination Pengukuran --}}
            @if($paginatedRiwayat->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/80 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-500">
                        Hal. <span class="text-slate-900">{{ $paginatedRiwayat->currentPage() }}</span> dari <span class="text-slate-900">{{ $paginatedRiwayat->lastPage() }}</span>
                    </p>
                    <div class="flex items-center gap-1.5">
                        @if ($paginatedRiwayat->onFirstPage())
                            <button disabled class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50"><i class="fas fa-chevron-left text-[10px]"></i></button>
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
                            <button disabled class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50"><i class="fas fa-chevron-right text-[10px]"></i></button>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- BAGIAN KANAN: RIWAYAT IMUNISASI (5 KOLOM) --}}
        <div class="lg:col-span-5 widget-card flex flex-col h-full overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-white border border-slate-200 text-sky-500 flex items-center justify-center shadow-sm shrink-0">
                        <i class="fas fa-syringe text-xs"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-800">Riwayat Imunisasi</h2>
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-0.5">Log Vaksin Balita</p>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto slim-scroll p-5 bg-slate-50/30 space-y-4">
                @forelse($paginatedImun as $imun)
                    <article class="rounded-[1.25rem] border border-slate-200/80 bg-white p-4 shadow-sm hover:border-sky-300 transition-all">
                        <div class="flex items-start gap-3 mb-3 border-b border-slate-50 pb-3">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-500 shrink-0">
                                <i class="fas fa-shield-virus"></i>
                            </div>
                            <div>
                                <p class="text-xs font-black text-slate-800">{{ $imun['nama'] ?? 'Imunisasi' }}</p>
                                <p class="mt-0.5 text-[8px] font-bold text-slate-400 uppercase tracking-widest">{{ $imun['tanggal'] ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div class="rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-center">
                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Vaksin</p>
                                <p class="truncate text-xs font-black text-slate-800">{{ $imun['batch'] ?? '-' }}</p>
                            </div>
                            <div class="rounded-lg border border-slate-100 bg-slate-50 px-2 py-1.5 text-center">
                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Petugas</p>
                                <p class="truncate text-xs font-black text-slate-800">{{ $imun['petugas'] ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="mt-auto bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                            <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5 flex items-center gap-1"><i class="fa-regular fa-comment-dots"></i> Keterangan</p>
                            <p class="text-[10px] font-semibold leading-relaxed text-slate-600">{{ $imun['catatan'] ?? 'Tidak ada catatan khusus.' }}</p>
                        </div>
                    </article>
                @empty
                    <div class="flex flex-col items-center justify-center text-center opacity-70 p-8 border border-dashed border-slate-200 rounded-[1.5rem] min-h-[250px]">
                        <div class="w-14 h-14 rounded-xl bg-white flex items-center justify-center mb-3 text-2xl text-slate-300 border border-slate-100 shadow-sm">
                            <i class="fas fa-syringe"></i>
                        </div>
                        <h3 class="text-xs font-black text-slate-700">Belum Ada Imunisasi</h3>
                        <p class="text-[10px] font-semibold text-slate-500 mt-1 max-w-xs leading-relaxed">Log vaksin akan muncul setelah data dimasukkan oleh Bidan.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination Imunisasi --}}
            @if($paginatedImun->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/80 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-500">
                        Hal. <span class="text-slate-900">{{ $paginatedImun->currentPage() }}</span> dari <span class="text-slate-900">{{ $paginatedImun->lastPage() }}</span>
                    </p>
                    <div class="flex items-center gap-1.5">
                        @if ($paginatedImun->onFirstPage())
                            <button disabled class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50"><i class="fas fa-chevron-left text-[10px]"></i></button>
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
                            <button disabled class="btn-pill w-8 h-8 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50"><i class="fas fa-chevron-right text-[10px]"></i></button>
                        @endif
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection