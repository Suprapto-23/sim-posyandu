@extends('layouts.kader')

@section('title', 'Riwayat Absensi')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $riwayats = $riwayats ?? collect();
    $bulanAktif = (int) ($bulan ?? now('Asia/Jakarta')->month);
    $tahunAktif = (int) ($tahun ?? now('Asia/Jakarta')->year);
    $kategoriAktif = $kategori ?? request('kategori', '');
    $searchAktif = $search ?? request('search', '');

    $bulanList = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    $tahunSekarang = (int) now('Asia/Jakarta')->year;
    $tahunList = range($tahunSekarang + 1, max(2024, $tahunSekarang - 4));

    // Kategori disamakan dengan palette
    $kategoriMenus = [
        '' => [
            'label' => 'Semua',
            'icon' => 'fa-layer-group',
            'tone' => 'slate',
        ],
        'balita' => [
            'label' => 'Balita',
            'icon' => 'fa-child-reaching',
            'tone' => 'emerald',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'icon' => 'fa-user-graduate',
            'tone' => 'amber',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'icon' => 'fa-person-cane',
            'tone' => 'blue',
        ],
    ];

    $routeHas = fn ($name) => Route::has($name);
    $routeUrl = fn ($name, $params = []) => Route::has($name) ? route($name, $params) : '#';

    $kategoriLabel = function ($kategori) {
        return match ($kategori) {
            'balita' => 'Balita',
            'remaja' => 'Remaja',
            'lansia' => 'Lansia',
            default => 'Semua Kategori',
        };
    };

    $formatTanggal = function ($date, $format = 'd M Y') {
        if (!$date) return '-';
        try {
            return Carbon::parse($date)->translatedFormat($format);
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $toneClass = function ($tone, $type = 'light') {
        return match ($tone) {
            'amber' => match ($type) {
                'solid' => 'bg-amber-500 text-white',
                'light' => 'bg-amber-50 text-amber-600 border-amber-200',
                default => 'bg-amber-50 text-amber-600',
            },
            'blue' => match ($type) {
                'solid' => 'bg-blue-500 text-white',
                'light' => 'bg-blue-50 text-blue-600 border-blue-200',
                default => 'bg-blue-50 text-blue-600',
            },
            'slate' => match ($type) {
                'solid' => 'bg-slate-700 text-white',
                'light' => 'bg-slate-100 text-slate-600 border-slate-200',
                default => 'bg-slate-50 text-slate-600',
            },
            default => match ($type) {
                'solid' => 'bg-emerald-500 text-white',
                'light' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                default => 'bg-emerald-50 text-emerald-600',
            },
        };
    };

    $totalSesi = $riwayats->count();
    $totalPeserta = $riwayats->sum(fn ($item) => (int) ($item->total_peserta ?? 0));
    $totalHadir = $riwayats->sum(fn ($item) => (int) ($item->total_hadir ?? 0));
    $persenHadir = $totalPeserta > 0 ? round(($totalHadir / $totalPeserta) * 100) : 0;
@endphp

@push('styles')
<style>
    /* Styling Identik dengan Index & Success */
    body { 
        background-color: #f1f5f9; 
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(150, 100%, 94%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }
    
    .widget-card {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: 2rem; 
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }
    .btn-pill:active {
        transform: scale(0.95);
    }

    .pc-row {
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.8);
        background: rgba(255, 255, 255, 0.6);
        border-radius: 1.5rem;
    }
    .pc-row:hover {
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.04);
        transform: translateY(-2px);
    }

    .slim-scroll::-webkit-scrollbar { width: 6px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: rgba(20, 184, 166, 0.3); border-radius: 9999px; }
    .slim-scroll::-webkit-scrollbar-thumb:hover { background: rgba(20, 184, 166, 0.6); }
</style>
@endpush

@section('content')
<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6 relative">

    {{-- SYSTEM ALERT --}}
    @if(session('success') || session('error'))
        <div class="rounded-[2rem] p-6 shadow-sm border-2 flex items-center gap-4 {{ session('success') ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }}">
            <div class="bg-white rounded-full w-12 h-12 flex items-center justify-center shrink-0 shadow-inner">
                <i class="fa-solid {{ session('success') ? 'fa-circle-check text-emerald-500' : 'fa-triangle-exclamation text-rose-500' }} text-xl"></i>
            </div>
            <div>
                <h3 class="font-black text-lg">{{ session('success') ? 'Berhasil' : 'Peringatan' }}</h3>
                <p class="font-medium text-sm mt-1 opacity-80">{{ session('success') ?? session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- 1. HEADER & QUICK STATS (FULL WIDTH) --}}
    <div class="widget-card p-6 lg:p-8 flex flex-col lg:flex-row justify-between items-center gap-8 bg-gradient-to-r from-white/90 to-white/50 relative overflow-hidden">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-teal-400/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex-1 text-center lg:text-left z-10 w-full">
            <div class="inline-flex items-center gap-2 mb-3">
                <span class="btn-pill bg-teal-50 border border-teal-100 text-teal-600 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest shadow-inner flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500 relative"></span>
                    Archive & History
                </span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-slate-800 tracking-tight">Riwayat Presensi.</h1>
            <p class="text-sm font-medium text-slate-500 mt-2 max-w-lg mx-auto lg:mx-0">
                Tinjau kembali seluruh log presensi yang telah tersimpan. Data ditampilkan berdasarkan filter periode dan kategori.
            </p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto z-10 shrink-0">
            <div class="bg-white/80 border border-slate-100 rounded-3xl p-5 flex items-center gap-5 shadow-sm flex-1 sm:flex-none">
                <div class="w-12 h-12 rounded-full bg-teal-50 text-teal-500 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Sesi</p>
                    <p class="text-2xl font-black text-slate-800 leading-none mt-1">{{ number_format($totalSesi) }}</p>
                </div>
            </div>
            <div class="bg-white/80 border border-slate-100 rounded-3xl p-5 flex items-center gap-5 shadow-sm flex-1 sm:flex-none">
                <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kehadiran</p>
                    <p class="text-2xl font-black text-slate-800 leading-none mt-1">{{ $persenHadir }}%</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. HORIZONTAL FILTER BAR --}}
    <form method="GET" action="{{ $routeUrl('kader.absensi.riwayat') }}" class="widget-card p-4 sm:p-6 flex flex-col xl:flex-row gap-4 items-center justify-between z-10 relative">
        
        {{-- Kategori Tabs --}}
        <div class="flex flex-wrap gap-2 w-full xl:w-auto justify-center xl:justify-start">
            @foreach($kategoriMenus as $key => $item)
                <label class="cursor-pointer relative">
                    <input type="radio" name="kategori" value="{{ $key }}" class="peer sr-only" {{ $kategoriAktif == $key ? 'checked' : '' }} onchange="this.form.submit()">
                    <div class="btn-pill border border-slate-200 bg-white/50 px-4 py-2.5 text-xs font-bold text-slate-500 peer-checked:bg-teal-500 peer-checked:text-white peer-checked:border-teal-500 peer-checked:shadow-md transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid {{ $item['icon'] }}"></i> {{ $item['label'] }}
                    </div>
                </label>
            @endforeach
        </div>

        {{-- Input Filters --}}
        <div class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto">
            <div class="relative flex-1 sm:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ $searchAktif }}" placeholder="Cari kode atau nama..." class="w-full btn-pill border border-slate-200 bg-white/50 py-2.5 pl-11 pr-4 text-sm font-semibold text-slate-800 outline-none transition focus:bg-white focus:border-teal-400 focus:ring-4 focus:ring-teal-500/10 shadow-inner">
            </div>

            <div class="flex gap-2">
                <select name="bulan" class="btn-pill border border-slate-200 bg-white/50 px-4 py-2.5 text-sm font-bold text-slate-700 outline-none transition focus:bg-white focus:border-teal-400 shadow-inner appearance-none cursor-pointer" onchange="this.form.submit()">
                    @foreach($bulanList as $number => $label)
                        <option value="{{ $number }}" @selected($bulanAktif === $number)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="tahun" class="btn-pill border border-slate-200 bg-white/50 px-4 py-2.5 text-sm font-bold text-slate-700 outline-none transition focus:bg-white focus:border-teal-400 shadow-inner appearance-none cursor-pointer" onchange="this.form.submit()">
                    @foreach($tahunList as $year)
                        <option value="{{ $year }}" @selected($tahunAktif === $year)>{{ $year }}</option>
                    @endforeach
                </select>
                
                <a href="{{ $routeUrl('kader.absensi.riwayat') }}" class="btn-pill w-11 h-11 flex items-center justify-center border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 hover:text-rose-500 shadow-sm" title="Reset Filter">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </div>
    </form>

    {{-- 3. MAIN LIST AREA --}}
    <section class="widget-card p-6 flex flex-col">
        <div class="flex justify-between items-center mb-6 px-2">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Menampilkan {{ number_format($totalSesi) }} sesi presensi</p>
            @if($routeHas('kader.absensi.index'))
                <a href="{{ route('kader.absensi.index', ['kategori' => $kategoriAktif ?: 'balita']) }}" class="text-[11px] font-bold text-teal-600 hover:text-teal-700 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i> Input Baru
                </a>
            @endif
        </div>

        @if($riwayats->isNotEmpty())
            <div class="space-y-3">
                @foreach($riwayats as $index => $item)
                    @php
                        $peserta = (int) ($item->total_peserta ?? 0);
                        $hadir = (int) ($item->total_hadir ?? 0);
                        $persen = $peserta > 0 ? round(($hadir / $peserta) * 100) : 0;

                        $kategoriItem = $item->kategori ?? '-';
                        $kategoriTone = match ($kategoriItem) {
                            'remaja' => 'amber',
                            'lansia' => 'blue',
                            default => 'emerald',
                        };
                    @endphp

                    <div class="pc-row p-4 sm:p-5">
                        <div class="flex flex-col lg:flex-row justify-between lg:items-center gap-5">
                            
                            {{-- Info Kiri --}}
                            <div class="flex items-center gap-4 flex-1">
                                <div class="flex h-12 w-12 sm:h-14 sm:w-14 shrink-0 items-center justify-center rounded-full border {{ $toneClass($kategoriTone, 'light') }} shadow-inner">
                                    <i class="fa-solid {{ $kategoriMenus[$kategoriItem]['icon'] ?? 'fa-users' }} text-lg sm:text-xl"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <h3 class="text-base sm:text-lg font-black text-slate-800">{{ $item->kode_absensi ?? 'ABS-' . str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</h3>
                                        <span class="btn-pill border px-2.5 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $toneClass($kategoriTone, 'light') }}">{{ $kategoriLabel($kategoriItem) }}</span>
                                    </div>
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-[11px] sm:text-xs font-semibold text-slate-500">
                                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-calendar-day text-slate-400"></i> {{ $formatTanggal($item->tanggal_posyandu ?? null, 'd M Y') }}</span>
                                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-hashtag text-slate-400"></i> Pertemuan ke-{{ $item->nomor_pertemuan ?? '-' }}</span>
                                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-user-pen text-slate-400"></i> {{ $item->kader?->name ?? 'Kader' }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Stats & Aksi Kanan --}}
                            <div class="flex flex-wrap sm:flex-nowrap justify-between sm:justify-end items-center gap-6 lg:gap-8 lg:pl-6 lg:border-l border-slate-100">
                                
                                <div class="flex gap-6 items-center">
                                    <div class="text-center">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Sasaran</p>
                                        <p class="text-sm font-black text-slate-800">{{ number_format($peserta) }}</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-500">Hadir</p>
                                        <p class="text-sm font-black text-slate-800">{{ number_format($hadir) }}</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-teal-500">%</p>
                                        <p class="text-sm font-black text-slate-800">{{ $persen }}%</p>
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    @if($routeHas('kader.absensi.show'))
                                        <a href="{{ route('kader.absensi.show', $item->id) }}" class="btn-pill w-11 h-11 flex items-center justify-center border border-teal-200 bg-teal-50 text-teal-600 hover:bg-teal-500 hover:text-white transition-all shadow-sm" title="Lihat Detail">
                                            <i class="fa-solid fa-eye text-sm"></i>
                                        </a>
                                    @endif

                                    @if($routeHas('kader.absensi.destroy'))
                                        <form method="POST" action="{{ route('kader.absensi.destroy', $item->id) }}" class="delete-history-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-pill w-11 h-11 flex items-center justify-center border border-rose-200 bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all shadow-sm" title="Hapus Riwayat">
                                                <i class="fa-solid fa-trash-can text-sm"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center btn-pill border-2 border-dashed border-slate-200 bg-white/50 p-12 text-center rounded-[2.5rem]">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-5 shadow-inner">
                    <i class="fa-solid fa-folder-open text-3xl"></i>
                </div>
                <h3 class="font-black text-slate-800 text-xl">Tidak Ada Riwayat</h3>
                <p class="text-sm font-medium text-slate-500 mt-2 max-w-sm">
                    Belum ada sesi absensi yang tercatat untuk filter ini. Silakan ubah pencarian atau tambahkan presensi baru.
                </p>
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    document.addEventListener('submit', function (event) {
        const form = event.target.closest('.delete-history-form');
        if (!form) return;

        const confirmed = confirm('Apakah Anda yakin ingin menghapus riwayat absensi ini? Seluruh detail kehadiran di dalamnya juga akan terhapus secara permanen.');

        if (!confirmed) {
            event.preventDefault();
            return;
        }

        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
            button.classList.add('opacity-70', 'cursor-not-allowed');
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-sm"></i>';
        }
    });
})();
</script>
@endpush