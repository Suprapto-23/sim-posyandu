@extends('layouts.kader')

@section('title', 'Detail Presensi')
@section('page-name', 'Detail Presensi')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $absensi = $absensi ?? null;
    $details = $details ?? collect();
    $semuaSesi = $semuaSesi ?? collect();

    $totalPasien = (int) ($totalPasien ?? $details->count());
    $totalHadir = (int) ($totalHadir ?? $details->where('hadir', true)->count());
    $totalAbsen = (int) ($totalAbsen ?? max(0, $totalPasien - $totalHadir));

    $persenHadir = $totalPasien > 0 ? round(($totalHadir / $totalPasien) * 100) : 0;
    $persenTidak = $totalPasien > 0 ? round(($totalAbsen / $totalPasien) * 100) : 0;

    $routeHas = fn ($name) => Route::has($name);

    $kategori = $absensi?->kategori ?? 'balita';

    $kategoriMenus = [
        'balita' => [
            'label' => 'Balita',
            'icon' => 'fa-child-reaching',
            'tone' => 'emerald',
            'accent' => 'bg-emerald-500',
            'badge' => 'bg-emerald-50 text-emerald-600 border-emerald-200'
        ],
        'remaja' => [
            'label' => 'Remaja',
            'icon' => 'fa-user-graduate',
            'tone' => 'sky',
            'accent' => 'bg-sky-500',
            'badge' => 'bg-sky-50 text-sky-600 border-sky-200'
        ],
        'lansia' => [
            'label' => 'Lansia',
            'icon' => 'fa-person-cane',
            'tone' => 'amber',
            'accent' => 'bg-amber-500',
            'badge' => 'bg-amber-50 text-amber-600 border-amber-200'
        ],
    ];

    if (!array_key_exists($kategori, $kategoriMenus)) {
        $kategori = 'balita';
    }

    $currentKategori = $kategoriMenus[$kategori];
    $kategoriLabel = $currentKategori['label'];

    $formatTanggal = function ($date, $format = 'd M Y') {
        if (!$date) return '-';
        try {
            return Carbon::parse($date)->translatedFormat($format);
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $statusRekap = 'Belum Ada Kehadiran';
    $statusTone = 'slate';

    if ($totalPasien <= 0) {
        $statusRekap = 'Belum Ada Peserta';
        $statusTone = 'slate';
    } elseif ($totalHadir === $totalPasien) {
        $statusRekap = 'Semua Hadir';
        $statusTone = 'emerald';
    } elseif ($totalHadir > 0) {
        $statusRekap = 'Sebagian Hadir';
        $statusTone = 'teal';
    } else {
        $statusRekap = 'Semua Absen';
        $statusTone = 'amber';
    }

    $kodeAbsensi = $absensi?->kode_absensi ?? 'ABS-' . strtoupper(substr($kategori, 0, 3)) . '-' . str_pad($absensi?->id ?? 0, 4, '0', STR_PAD_LEFT);
@endphp

@push('styles')
<style>
    body { 
        background-color: #f8fafc; 
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }
    
    .widget-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 1);
        border-radius: 2rem; 
        box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    @media(min-width: 640px) {
        .widget-card { border-radius: 2.5rem; }
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.25s ease;
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.96); }

    .pc-row {
        transition: all 0.3s ease;
        border: 1px solid #f1f5f9;
        background: #ffffff;
        border-radius: 1.5rem;
    }
    .pc-row:hover {
        background: #f8fafc;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
        transform: translateY(-2px);
    }

    .pc-row.row-present { border-left: 5px solid #10b981; }
    .pc-row.row-absent { border-left: 5px solid #f59e0b; }

    .slim-scroll::-webkit-scrollbar { width: 5px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    .slim-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    
    .animate-pop-in {
        animation: popIn .5s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }
    @keyframes popIn {
        from { opacity: 0; transform: scale(.97) translateY(15px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="px-4 py-6 sm:py-8 sm:px-6 lg:px-8 max-w-[1280px] mx-auto space-y-6 sm:space-y-8 animate-pop-in relative pb-20">

    {{-- 1. HEADER INFO SESI --}}
    <div class="bg-gradient-to-br from-teal-500 via-teal-400 to-emerald-500 rounded-[2rem] sm:rounded-[2.5rem] p-6 sm:p-10 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(20,184,166,.35)] border border-white/20 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-8">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/20 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-full xl:w-1/2 flex flex-col gap-4 text-center xl:text-left">
            <div class="inline-flex justify-center xl:justify-start items-center gap-2 mb-1">
                <span class="btn-pill bg-white/20 border border-white/30 backdrop-blur-md text-white px-3 py-1.5 text-[9px] sm:text-[10px] font-black uppercase tracking-widest shadow-inner flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse relative"></span>
                    Detail Sesi
                </span>
            </div>
            
            <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight leading-tight drop-shadow-sm">
                Rincian Presensi<br class="hidden sm:block xl:hidden"> {{ $kategoriLabel }}.
            </h1>

            <p class="text-teal-50 text-[11px] sm:text-sm font-medium leading-relaxed opacity-90 drop-shadow-sm max-w-xl mx-auto xl:mx-0">
                Menampilkan daftar kehadiran peserta untuk sesi pertemuan Posyandu yang Anda pilih. Anda dapat meninjau atau memperbarui data jika diperlukan.
            </p>

            <div class="flex flex-col sm:flex-row justify-center xl:justify-start gap-3 mt-2 w-full sm:w-auto">
                @if($routeHas('kader.absensi.riwayat'))
                    <a href="{{ route('kader.absensi.riwayat', ['kategori' => $kategori, 'bulan' => $absensi?->bulan ?? now('Asia/Jakarta')->month, 'tahun' => $absensi?->tahun ?? now('Asia/Jakarta')->year]) }}" 
                       class="w-full sm:w-auto btn-pill bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/30 text-white px-6 py-3.5 text-xs sm:text-sm font-bold shadow-sm flex items-center justify-center gap-2 transition-all">
                        <i class="fa-solid fa-arrow-left"></i> Kembali ke Riwayat
                    </a>
                @endif
                
                @if($routeHas('kader.absensi.index'))
                    <a href="{{ route('kader.absensi.index', ['kategori' => $kategori, 'tanggal' => optional($absensi?->tanggal_posyandu)->format('Y-m-d')]) }}" 
                       class="w-full sm:w-auto btn-pill bg-white text-teal-600 hover:bg-slate-50 px-6 py-3.5 text-xs sm:text-sm font-bold shadow-lg flex items-center justify-center gap-2 transition-all">
                        <i class="fa-solid fa-pen-to-square"></i> Update Presensi
                    </a>
                @endif
            </div>
        </div>

        {{-- Widget Info Sesi Kanan --}}
        <div class="relative z-10 w-full xl:w-auto grid grid-cols-2 gap-3 sm:gap-4 shrink-0 mt-4 xl:mt-0">
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl p-4 sm:p-5 shadow-inner">
                <p class="text-[9px] sm:text-[10px] font-bold text-teal-50 uppercase tracking-widest mb-1 opacity-80">Kode Sesi</p>
                <p class="text-sm sm:text-base font-black text-white tracking-wider">{{ $kodeAbsensi }}</p>
            </div>
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl p-4 sm:p-5 shadow-inner">
                <p class="text-[9px] sm:text-[10px] font-bold text-teal-50 uppercase tracking-widest mb-1 opacity-80">Tanggal</p>
                <p class="text-sm sm:text-base font-black text-white">{{ $formatTanggal($absensi?->tanggal_posyandu ?? null, 'd M Y') }}</p>
            </div>
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl p-4 sm:p-5 shadow-inner">
                <p class="text-[9px] sm:text-[10px] font-bold text-teal-50 uppercase tracking-widest mb-1 opacity-80">Pertemuan</p>
                <p class="text-sm sm:text-base font-black text-white">Ke-{{ $absensi?->nomor_pertemuan ?? '-' }}</p>
            </div>
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl p-4 sm:p-5 shadow-inner">
                <p class="text-[9px] sm:text-[10px] font-bold text-teal-50 uppercase tracking-widest mb-1 opacity-80">Pencatat</p>
                <p class="text-sm sm:text-base font-black text-white line-clamp-1">{{ $absensi?->pencatat?->name ?? 'Sistem' }}</p>
            </div>
        </div>
    </div>

    {{-- 2. METRIK RINGKASAN --}}
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="widget-card p-4 sm:p-6 flex flex-col justify-center items-center sm:flex-row sm:justify-between group hover:-translate-y-1 text-center sm:text-left">
            <div class="mb-3 sm:mb-0">
                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-widest text-slate-400">Total Sasaran</p>
                <p class="mt-0.5 sm:mt-1 text-2xl sm:text-3xl font-black text-slate-800">{{ number_format($totalPasien) }}</p>
            </div>
            <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-full sm:rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-lg sm:text-2xl rotate-3 group-hover:rotate-6 transition-transform shadow-inner">
                <i class="fa-solid fa-users-viewfinder"></i>
            </div>
        </div>
        <div class="widget-card p-4 sm:p-6 flex flex-col justify-center items-center sm:flex-row sm:justify-between group hover:-translate-y-1 text-center sm:text-left">
            <div class="mb-3 sm:mb-0">
                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-widest text-emerald-500">Hadir</p>
                <div class="flex items-baseline justify-center sm:justify-start gap-1.5 sm:gap-2 mt-0.5 sm:mt-1">
                    <p class="text-2xl sm:text-3xl font-black text-slate-800 leading-none">{{ number_format($totalHadir) }}</p>
                    <p class="text-[10px] sm:text-sm font-bold text-emerald-500">{{ $persenHadir }}%</p>
                </div>
            </div>
            <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-full sm:rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-500 -rotate-3 group-hover:-rotate-6 transition-transform shadow-[0_0_15px_rgba(16,185,129,0.2)]">
                <i class="fa-solid fa-user-check"></i>
            </div>
        </div>
        <div class="widget-card p-4 sm:p-6 flex flex-col justify-center items-center sm:flex-row sm:justify-between group hover:-translate-y-1 text-center sm:text-left">
            <div class="mb-3 sm:mb-0">
                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-widest text-amber-500">Absen</p>
                <div class="flex items-baseline justify-center sm:justify-start gap-1.5 sm:gap-2 mt-0.5 sm:mt-1">
                    <p class="text-2xl sm:text-3xl font-black text-slate-800 leading-none">{{ number_format($totalAbsen) }}</p>
                    <p class="text-[10px] sm:text-sm font-bold text-amber-500">{{ $persenTidak }}%</p>
                </div>
            </div>
            <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-full sm:rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-500 rotate-3 group-hover:rotate-6 transition-transform shadow-[0_0_15px_rgba(245,158,11,0.2)]">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
        </div>
        <div class="widget-card p-4 sm:p-6 flex flex-col justify-center items-center sm:flex-row sm:justify-between group hover:-translate-y-1 text-center sm:text-left">
            <div class="mb-3 sm:mb-0">
                <p class="text-[9px] sm:text-[10px] font-bold uppercase tracking-widest text-{{ $statusTone }}-500">Capaian</p>
                <p class="mt-0.5 sm:mt-1 text-base sm:text-xl font-black text-slate-800">{{ $statusRekap }}</p>
            </div>
            <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-full sm:rounded-2xl bg-{{ $statusTone }}-50 border border-{{ $statusTone }}-100 flex items-center justify-center text-{{ $statusTone }}-500 -rotate-3 group-hover:-rotate-6 transition-transform shadow-inner">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>
        </div>
    </section>

    {{-- 3. MAIN LIST AREA --}}
    <section class="widget-card p-4 sm:p-8 flex flex-col">
        <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-6 pb-4 border-b border-slate-100">
            <div>
                <p class="text-[10px] sm:text-[11px] font-bold uppercase tracking-widest text-slate-400">Daftar Rincian</p>
                <h2 class="text-base sm:text-lg font-black text-slate-800 mt-1">Sasaran {{ $kategoriLabel }}</h2>
            </div>

            <div class="w-full md:w-80 relative group">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" id="detailSearch" autocomplete="off" placeholder="Cari nama sasaran / NIK..." class="w-full btn-pill border border-slate-200 bg-slate-50 py-3 sm:py-3.5 pl-11 pr-4 text-xs sm:text-sm font-semibold text-slate-800 outline-none transition focus:bg-white focus:border-teal-400 focus:ring-4 focus:ring-teal-500/10 shadow-inner">
            </div>
        </div>

        @if($details->isNotEmpty())
            {{-- Wrapper tinggi maksimal + custom scrollbar --}}
            <div class="max-h-[600px] overflow-y-auto slim-scroll pr-2 pb-2">
                <div class="grid xl:grid-cols-2 gap-3 sm:gap-4">
                    @foreach($details as $index => $detail)
                        @php
                            $hadir = (bool) $detail->hadir;
                            $statusText = $hadir ? 'Hadir' : 'Tidak Hadir';
                            $statusColor = $hadir ? 'emerald' : 'amber';
                            $statusIcon = $hadir ? 'fa-check' : 'fa-xmark';
                            $rowClass = $hadir ? 'row-present' : 'row-absent';

                            $nama = $detail->nama_pasien ?? $detail->pasien?->nama_lengkap ?? $detail->pasien?->nama ?? 'Data sasaran';
                            $nik = $detail->nik_pasien ?? $detail->pasien?->nik ?? '-';
                            $usia = $detail->usia_pasien ?? '-';
                            $keterangan = $detail->keterangan_text ?? ($detail->keterangan ?: '');
                        @endphp

                        <div class="pc-row {{ $rowClass }} p-4" data-search="{{ mb_strtolower($nama . ' ' . $nik . ' ' . $statusText, 'UTF-8') }}">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                
                                {{-- Identitas Kiri --}}
                                <div class="flex items-start sm:items-center gap-3 sm:gap-4 min-w-0 flex-1">
                                    <div class="flex h-10 w-10 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-xl sm:rounded-2xl bg-slate-50 border border-slate-100 text-xs sm:text-sm font-black text-slate-400 shadow-inner">
                                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="line-clamp-1 text-sm sm:text-[15px] font-black text-slate-800 mb-1" title="{{ $nama }}">{{ $nama }}</h3>
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[10px] sm:text-[11px] font-semibold text-slate-500">
                                            <span class="flex items-center gap-1 whitespace-nowrap"><i class="fa-solid fa-id-card text-slate-400"></i> {{ $nik }}</span>
                                            <span class="w-1 h-1 rounded-full bg-slate-200 hidden sm:block"></span>
                                            <span class="flex items-center gap-1 whitespace-nowrap"><i class="fa-solid fa-cake-candles text-slate-400"></i> {{ $usia }}</span>
                                        </div>
                                        @if($keterangan)
                                            <div class="mt-2.5 inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl text-[10px] sm:text-xs font-medium text-slate-600 w-full sm:w-auto overflow-hidden">
                                                <i class="fa-solid fa-note-sticky text-slate-400 shrink-0"></i> <span class="truncate">{{ $keterangan }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Status Kanan --}}
                                <div class="shrink-0 flex justify-start sm:justify-end border-t sm:border-t-0 border-slate-100 pt-3 sm:pt-0">
                                    <span class="btn-pill px-4 sm:px-5 py-2 sm:py-2.5 bg-{{ $statusColor }}-50 border border-{{ $statusColor }}-200 text-{{ $statusColor }}-600 text-[10px] sm:text-xs font-bold shadow-sm inline-flex items-center gap-1.5">
                                        <i class="fa-solid {{ $statusIcon }}"></i> {{ $statusText }}
                                    </span>
                                </div>
                                
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            {{-- Footer Teks Bawah --}}
            <div class="mt-4 sm:mt-6 pt-4 border-t border-slate-100 text-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Total {{ number_format($details->count()) }} Data Sasaran Tersimpan
                </p>
            </div>
        @else
            <div class="flex flex-col items-center justify-center border-2 border-dashed border-slate-200 bg-slate-50 p-8 sm:p-14 text-center rounded-[2rem]">
                <div class="flex h-16 w-16 sm:h-20 sm:w-20 items-center justify-center rounded-full bg-white text-slate-300 mb-4 sm:mb-5 shadow-inner border border-slate-100">
                    <i class="fa-solid fa-user-slash text-2xl sm:text-3xl"></i>
                </div>
                <h3 class="font-black text-slate-800 text-lg sm:text-xl tracking-tight">Detail Kosong</h3>
                <p class="text-[11px] sm:text-sm font-medium text-slate-500 mt-2 max-w-sm">
                    Sesi ini belum memiliki detail peserta tersimpan. Anda bisa mengklik tombol Update Presensi di atas untuk mengisi data.
                </p>
            </div>
        @endif
    </section>

    {{-- 4. BOTTOM GRID: SESI TERDEKAT --}}
    @if($semuaSesi->isNotEmpty() && $semuaSesi->count() > 1)
        <div class="pt-2 sm:pt-4">
            <div class="flex items-center gap-2.5 mb-4 sm:mb-5 px-2">
                <i class="fa-solid fa-clock-rotate-left text-teal-500 text-lg sm:text-xl"></i>
                <h3 class="text-base sm:text-lg font-black text-slate-800 tracking-tight">Sesi Terdekat Lainnya</h3>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                @foreach($semuaSesi->where('id', '!=', $absensi->id)->take(4) as $sesi)
                    <a href="{{ $routeHas('kader.absensi.show') ? route('kader.absensi.show', $sesi->id) : '#' }}" 
                       class="widget-card !rounded-2xl sm:!rounded-3xl p-4 sm:p-5 flex flex-col gap-3 group hover:border-teal-300 hover:-translate-y-1">
                        <div class="flex justify-between items-start">
                            <span class="btn-pill bg-teal-50 border border-teal-100 text-teal-600 px-2.5 py-1 text-[8px] sm:text-[9px] font-black uppercase tracking-widest shadow-inner">
                                Sesi-{{ $sesi->nomor_pertemuan ?? '-' }}
                            </span>
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center group-hover:bg-teal-500 group-hover:text-white transition-colors border border-slate-100 group-hover:border-teal-500">
                                <i class="fa-solid fa-arrow-right text-[9px] sm:text-[10px]"></i>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm font-black text-slate-800 tracking-wider">{{ $sesi->kode_absensi ?? 'ABS-' . str_pad($sesi->id, 4, '0', STR_PAD_LEFT) }}</p>
                            <p class="text-[10px] sm:text-xs font-semibold text-slate-500 mt-1"><i class="fa-solid fa-calendar-day mr-1 opacity-70"></i> {{ $formatTanggal($sesi->tanggal_posyandu ?? null, 'd M Y') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    
    // FUNGSI PENCARIAN REAL-TIME
    const searchInput = document.querySelector('#detailSearch');
    const rows = Array.from(document.querySelectorAll('.pc-row[data-search]'));
    let searchTimer = null;

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);

            searchTimer = setTimeout(() => {
                const keyword = this.value.trim().toLowerCase();

                rows.forEach((row) => {
                    const text = row.dataset.search || '';
                    if (keyword === '' || text.includes(keyword)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }, 200);
        });
    }
});
</script>
@endpush