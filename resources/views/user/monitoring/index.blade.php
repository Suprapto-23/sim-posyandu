@extends('layouts.user')

@section('title', 'Pantau Kesehatan')
@section('page_title', 'Pantau Kesehatan')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    $routeTo = function ($names, $params = []) {
        foreach ((array) $names as $name) {
            if (Route::has($name)) {
                return route($name, $params);
            }
        }
        return '#';
    };

    $counts = $counts ?? [
        'total' => 0,
        'balita' => isset($balitas) ? $balitas->count() : 0,
        'remaja' => isset($remajas) ? $remajas->count() : 0,
        'lansia' => isset($lansias) ? $lansias->count() : 0,
    ];

    // TEMA DINAMIS UNTUK HERO BANNER
    $heroTheme = 'emerald'; // Default jika campuran/kosong
    if ($counts['total'] > 0) {
        if ($counts['balita'] > 0 && $counts['remaja'] == 0 && $counts['lansia'] == 0) $heroTheme = 'rose';
        elseif ($counts['remaja'] > 0 && $counts['balita'] == 0 && $counts['lansia'] == 0) $heroTheme = 'sky';
        elseif ($counts['lansia'] > 0 && $counts['balita'] == 0 && $counts['remaja'] == 0) $heroTheme = 'amber';
    }

    $heroClasses = match($heroTheme) {
        'rose' => 'from-rose-500 via-rose-400 to-pink-500 shadow-[0_20px_40px_-12px_rgba(244,63,94,.35)]',
        'sky' => 'from-sky-500 via-sky-400 to-blue-500 shadow-[0_20px_40px_-12px_rgba(14,165,233,.35)]',
        'amber' => 'from-amber-500 via-amber-400 to-yellow-500 shadow-[0_20px_40px_-12px_rgba(245,158,11,.35)]',
        default => 'from-emerald-500 via-teal-500 to-teal-600 shadow-[0_20px_40px_-12px_rgba(20,184,166,.35)]',
    };

    $formatDate = fn ($value, $format = 'd M Y') => $value
        ? Carbon::parse($value)->translatedFormat($format)
        : '-';

    $numberValue = function ($value, $unit = '') {
        if (blank($value)) return '-';
        $formatted = rtrim(rtrim(number_format((float) $value, 1, '.', ''), '0'), '.');
        return trim($formatted . ' ' . $unit);
    };

    // FUNGSI NORMALISASI TINGGI BADAN OTOMATIS (Fix 16 cm -> 160 cm)
    $normalizeHeight = function ($value) {
        if (blank($value)) return null;
        $clean = preg_replace('/[^0-9.]/', '', (string) $value);
        if (blank($clean)) return null;
        $height = (float) $clean;
        if ($height >= 1 && $height <= 2.5) $height *= 100;
        if ($height >= 10 && $height < 50) $height *= 10;
        if ($height < 35 || $height > 250) return null;
        return round($height, 1);
    };

    $heightValue = function ($value) use ($normalizeHeight, $numberValue) {
        $height = $normalizeHeight($value);
        if (! $height) return '-';
        return $numberValue($height, 'cm');
    };

    $ageText = function ($tanggalLahir) {
        if (blank($tanggalLahir)) return '-';
        $diff = Carbon::parse($tanggalLahir)->diff(now());
        return $diff->y > 0 ? $diff->y . ' tahun' : $diff->m . ' bulan';
    };

    $lastCheckDate = function ($item) use ($formatDate) {
        $date = data_get($item, 'pemeriksaan_terakhir.tanggal_periksa')
            ?? data_get($item, 'pemeriksaan_terakhir.created_at')
            ?? data_get($item, 'updated_at');
        return $formatDate($date);
    };

    $getImt = function ($item) use ($normalizeHeight) {
        if (filled(data_get($item, 'pemeriksaan_terakhir.imt'))) return (float) data_get($item, 'pemeriksaan_terakhir.imt');
        $bb = data_get($item, 'pemeriksaan_terakhir.berat_badan') ?? $item->berat_badan ?? null;
        $tbRaw = data_get($item, 'pemeriksaan_terakhir.tinggi_badan') ?? $item->tinggi_badan ?? null;
        if (blank($bb) || blank($tbRaw)) return null;

        $tbNormalized = $normalizeHeight($tbRaw);
        if (!$tbNormalized) return null;

        $meter = $tbNormalized / 100;
        if ($meter <= 0) return null;
        return round(((float) $bb) / ($meter * $meter), 2);
    };

    $imtLabel = function ($imt) {
        if (blank($imt)) return '-';
        return match (true) {
            $imt < 18.5 => 'Kurus',
            $imt < 25 => 'Normal',
            $imt < 30 => 'Berlebih',
            default => 'Obesitas',
        };
    };

    $cards = collect();

    // MENGISI DATA BALITA
    foreach(($balitas ?? collect()) as $item) {
        $cards->push([
            'kategori' => 'Balita',
            'nama' => $item->nama_lengkap ?? '-',
            'meta' => $ageText($item->tanggal_lahir ?? null) . ' • NIK: ' . ($item->nik ?? '-'),
            'href' => $routeTo(['user.balita.show', 'user.monitoring.balita.show'], [$item->id]),
            'icon' => 'child-reaching',
            'tone' => 'rose',
            'search' => Str::lower('balita ' . ($item->nama_lengkap ?? '') . ' ' . ($item->nik ?? '')),
            'metrics' => [
                ['label' => 'BB Terakhir', 'value' => $numberValue(data_get($item, 'pemeriksaan_terakhir.berat_badan'), 'kg')],
                ['label' => 'TB Terakhir', 'value' => $heightValue(data_get($item, 'pemeriksaan_terakhir.tinggi_badan'))],
                ['label' => 'L. Kepala', 'value' => $numberValue(data_get($item, 'pemeriksaan_terakhir.lingkar_kepala'), 'cm')],
                ['label' => 'Kunjungan', 'value' => $lastCheckDate($item)],
            ],
        ]);
    }

    // MENGISI DATA REMAJA
    foreach(($remajas ?? collect()) as $item) {
        $imt = $getImt($item);
        $cards->push([
            'kategori' => 'Remaja',
            'nama' => $item->nama_lengkap ?? '-',
            'meta' => $ageText($item->tanggal_lahir ?? null) . ' • NIK: ' . ($item->nik ?? '-'),
            'href' => $routeTo(['user.remaja.show', 'user.monitoring.remaja.show'], [$item->id]),
            'icon' => 'user-graduate',
            'tone' => 'sky',
            'search' => Str::lower('remaja ' . ($item->nama_lengkap ?? '') . ' ' . ($item->nik ?? '')),
            'metrics' => [
                ['label' => 'Berat (BB)', 'value' => $numberValue(data_get($item, 'pemeriksaan_terakhir.berat_badan') ?? $item->berat_badan ?? null, 'kg')],
                ['label' => 'Tinggi (TB)', 'value' => $heightValue(data_get($item, 'pemeriksaan_terakhir.tinggi_badan') ?? $item->tinggi_badan ?? null)],
                ['label' => 'Status IMT', 'value' => filled($imt) ? $imtLabel($imt) : '-'],
                ['label' => 'Tensi', 'value' => data_get($item, 'pemeriksaan_terakhir.tensi') ?: '-'],
            ],
        ]);
    }

    // MENGISI DATA LANSIA
    foreach(($lansias ?? collect()) as $item) {
        $cards->push([
            'kategori' => 'Lansia',
            'nama' => $item->nama_lengkap ?? '-',
            'meta' => $ageText($item->tanggal_lahir ?? null) . ' • NIK: ' . ($item->nik ?? '-'),
            'href' => $routeTo(['user.lansia.show', 'user.monitoring.lansia.show'], [$item->id]),
            'icon' => 'person-cane',
            'tone' => 'amber',
            'search' => Str::lower('lansia ' . ($item->nama_lengkap ?? '') . ' ' . ($item->nik ?? '')),
            'metrics' => [
                ['label' => 'Cek Tensi', 'value' => data_get($item, 'pemeriksaan_terakhir.tensi') ?? $item->tekanan_darah ?: '-'],
                ['label' => 'Gula Darah', 'value' => $numberValue(data_get($item, 'pemeriksaan_terakhir.gula_darah') ?? data_get($item, 'pemeriksaan_terakhir.gula') ?? $item->gula_darah ?? null, 'mg/dL')],
                ['label' => 'Kolesterol', 'value' => $numberValue(data_get($item, 'pemeriksaan_terakhir.kolesterol'), 'mg/dL')],
                ['label' => 'Kunjungan', 'value' => $lastCheckDate($item)],
            ],
        ]);
    }

    // MAP TEMA DINAMIS UNTUK LIST VIEW
    $toneMap = [
        'rose' => [
            'icon_bg' => 'bg-rose-50',
            'icon_text' => 'text-rose-500',
            'badge' => 'bg-rose-50 text-rose-600 border-rose-200',
            'button' => 'bg-white text-rose-600 border-rose-200 hover:bg-rose-500 hover:text-white hover:border-rose-500',
            'hover_row' => 'hover:bg-rose-50/40'
        ],
        'sky' => [
            'icon_bg' => 'bg-sky-50',
            'icon_text' => 'text-sky-500',
            'badge' => 'bg-sky-50 text-sky-600 border-sky-200',
            'button' => 'bg-white text-sky-600 border-sky-200 hover:bg-sky-500 hover:text-white hover:border-sky-500',
            'hover_row' => 'hover:bg-sky-50/40'
        ],
        'amber' => [
            'icon_bg' => 'bg-amber-50',
            'icon_text' => 'text-amber-500',
            'badge' => 'bg-amber-50 text-amber-600 border-amber-200',
            'button' => 'bg-white text-amber-600 border-amber-200 hover:bg-amber-500 hover:text-white hover:border-amber-500',
            'hover_row' => 'hover:bg-amber-50/40'
        ],
    ];
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

    .hero-grid {
        background-image: radial-gradient(rgba(255,255,255,.45) 1px, transparent 1px);
        background-size: 24px 24px;
    }

    .is-hidden-by-search {
        display: none !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-[1280px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6 space-y-6">

    {{-- 1. HERO SECTION (DYNAMIC THEME) --}}
    <section class="bg-gradient-to-br {{ $heroClasses }} rounded-[2.5rem] p-8 md:p-10 mb-6 relative overflow-hidden border border-white/20 text-center md:text-left flex flex-col md:flex-row items-center justify-between gap-6 transition-all duration-500">
        <div class="hero-grid absolute inset-0 opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[70px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex-1">
            <div class="inline-flex items-center gap-2 text-white/90 text-[10px] font-black uppercase tracking-widest mb-4">
                <span class="bg-white/20 backdrop-blur-md border border-white/20 px-3 py-1.5 rounded-full shadow-sm">Kesehatan Terpadu</span>
                <i class="fas fa-chevron-right text-[8px] opacity-70"></i>
                <span>Semua Rekam Medis</span>
            </div>

            <h1 class="text-3xl md:text-4xl font-black text-white font-poppins tracking-tight">
                Pemantauan Keluarga
            </h1>

            <p class="text-white/80 text-sm font-medium mt-3 leading-relaxed max-w-xl mx-auto md:mx-0">
                Akses cepat seluruh buku rekam medis anggota keluarga Anda yang terdaftar pada sistem Posyandu.
            </p>
        </div>

        <div class="relative z-10 shrink-0">
            <div class="bg-white/20 border border-white/30 backdrop-blur-md rounded-[1.5rem] px-6 py-5 text-center shadow-inner min-w-[140px]">
                <p class="text-[10px] text-white/90 font-black uppercase tracking-widest mb-1">Total Akses</p>
                <p class="text-4xl font-black text-white leading-none">{{ $counts['total'] }}</p>
            </div>
        </div>
    </section>

    @if($hasData)
        {{-- 2. CONTAINER MASTER (ADMIN STYLE LIST) --}}
        <section class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden mb-8">
            
            {{-- Header Kotak & Pencarian --}}
            <div class="bg-slate-50/70 px-6 sm:px-8 py-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h5 class="font-black text-slate-700 text-sm uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-users-viewfinder opacity-50 text-lg"></i>
                    Daftar Anggota Keluarga
                </h5>

                @if($counts['total'] > 1)
                    <div class="w-full md:w-72 relative">
                        <input type="text"
                               id="familySearch"
                               autocomplete="off"
                               placeholder="Cari nama atau NIK..."
                               class="w-full bg-white border border-slate-200 rounded-[14px] px-4 py-2.5 pl-10 text-xs font-bold text-slate-700 outline-none transition-all focus:border-slate-400 focus:ring-2 focus:ring-slate-500/10 shadow-sm">
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Isi Daftar --}}
            <div class="flex flex-col divide-y divide-slate-100" id="familyGrid">
                @foreach($cards as $card)
                    @php $tone = $toneMap[$card['tone']]; @endphp

                    <div class="family-row flex flex-col lg:flex-row lg:items-center p-6 sm:px-8 gap-5 transition-colors duration-200 {{ $tone['hover_row'] }}" data-search="{{ $card['search'] }}">
                        
                        {{-- Kolom 1: Ikon & Identitas --}}
                        <div class="flex items-center gap-4 w-full lg:w-4/12 shrink-0">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-2xl shadow-sm border border-slate-100 {{ $tone['icon_bg'] }} {{ $tone['icon_text'] }}">
                                <i class="fas fa-{{ $card['icon'] }}"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="inline-block px-2.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-widest border {{ $tone['badge'] }} mb-1.5">
                                    {{ $card['kategori'] }}
                                </span>
                                <h3 class="truncate text-base font-black text-slate-800 leading-tight" title="{{ $card['nama'] }}">
                                    {{ $card['nama'] }}
                                </h3>
                                <p class="truncate text-[11px] font-semibold text-slate-500 mt-1">
                                    {{ $card['meta'] }}
                                </p>
                            </div>
                        </div>

                        {{-- Kolom 2: Metrik (Grid 4 Kolom Rapi) --}}
                        <div class="flex-1 w-full lg:w-auto grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50/50 lg:bg-transparent p-4 lg:p-0 rounded-xl lg:rounded-none lg:border-l lg:border-r border-slate-200/60 lg:px-6">
                            @foreach($card['metrics'] as $metric)
                                <div class="text-center lg:text-left flex flex-col justify-center">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5 truncate" title="{{ $metric['label'] }}">
                                        {{ $metric['label'] }}
                                    </p>
                                    <p class="text-[11px] font-black text-slate-700 truncate">
                                        {{ $metric['value'] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>

                        {{-- Kolom 3: Tombol Aksi --}}
                        <div class="w-full lg:w-2/12 flex justify-end shrink-0">
                            <a href="{{ $card['href'] }}" data-no-delay="true" class="w-full lg:w-auto text-center px-5 py-3 rounded-[14px] text-[10px] font-black uppercase tracking-widest border shadow-sm transition-all {{ $tone['button'] }}">
                                <i class="fa-solid fa-folder-open md:mr-1"></i> <span class="hidden md:inline">Buka</span>
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>

            {{-- EMPTY STATE PENCARIAN --}}
            <div id="notFoundState" class="hidden p-12 text-center bg-slate-50/50">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-xl text-slate-400 shadow-sm border border-slate-200 mb-4">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="text-sm font-black text-slate-800">Data Tidak Ditemukan</h3>
                <p class="mx-auto mt-1 max-w-sm text-xs font-semibold leading-6 text-slate-500">
                    Pencarian tidak membuahkan hasil. Pastikan nama atau NIK sudah benar.
                </p>
            </div>
        </section>

    @else
        {{-- EMPTY STATE BELUM ADA DATA --}}
        <section class="bg-white rounded-[2.5rem] border border-slate-100 p-16 text-center shadow-sm mt-8">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[1.5rem] bg-slate-50 border border-slate-100 text-3xl text-slate-300 shadow-inner mb-6">
                <i class="fas fa-user-shield"></i>
            </div>
            <h3 class="text-xl font-black text-slate-800 mb-2">Belum Ada Data Terhubung</h3>
            <p class="mx-auto max-w-md text-sm font-semibold leading-relaxed text-slate-500">
                Data pemantauan akan otomatis muncul di sini setelah akun Anda tersinkron dengan data sasaran Posyandu oleh Bidan atau Kader.
            </p>
        </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('familySearch');
        const rows = Array.from(document.querySelectorAll('.family-row'));
        const notFoundState = document.getElementById('notFoundState');

        if (!searchInput || !rows.length) return;

        searchInput.addEventListener('input', function () {
            const keyword = this.value.trim().toLowerCase();
            let visible = 0;

            rows.forEach(function (row) {
                const haystack = row.dataset.search || '';
                const matched = !keyword || haystack.includes(keyword);

                row.classList.toggle('is-hidden-by-search', !matched);
                if (matched) visible++;
            });

            if (notFoundState) {
                notFoundState.classList.toggle('hidden', visible === 0);
            }
        });
    });
</script>
@endpush