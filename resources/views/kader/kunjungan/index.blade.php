@extends('layouts.kader')

@section('title', 'Logbook Kunjungan')
@section('page-name', 'Buku Induk Kunjungan')
@section('page-title', 'Buku Induk Kunjungan Warga')

@php
    use Carbon\Carbon;

    Carbon::setLocale('id');

    $kunjungans = $kunjungans ?? collect();
    $search = $search ?? request('search', '');
    $kategori = $kategori ?? request('kategori', 'semua');

    // MAPPING KATEGORI
    $kategoriOptions = [
        'semua'  => ['label' => 'Semua Kunjungan', 'icon' => 'fa-users-rectangle'],
        'balita' => ['label' => 'Balita', 'icon' => 'fa-child-reaching', 'tone' => 'emerald'],
        'remaja' => ['label' => 'Remaja', 'icon' => 'fa-user-graduate', 'tone' => 'amber'],
        'lansia' => ['label' => 'Lansia', 'icon' => 'fa-person-cane', 'tone' => 'blue'],
    ];

    $currentKatMeta = $kategoriOptions[$kategori] ?? $kategoriOptions['semua'];

    // HELPER FUNCTIONS
    $getPasienName = function($pasien) {
        if (!$pasien) return 'Pasien Tidak Diketahui';
        return $pasien->nama_lengkap ?? $pasien->nama ?? $pasien->nama_balita ?? 'Tanpa Nama';
    };

    $getPasienNik = function($pasien) {
        if (!$pasien) return '-';
        return $pasien->nik ?? $pasien->nik_anak ?? '-';
    };

    $getPasienTypeStr = function($pasienType) {
        if (!$pasienType) return 'Umum';
        $type = strtolower(class_basename($pasienType));
        return match($type) {
            'balita' => 'Balita',
            'remaja' => 'Remaja',
            'lansia' => 'Lansia',
            default => ucfirst($type),
        };
    };

    $getTypeTheme = function($type) {
        return match(strtolower($type)) {
            'balita' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200', 'icon' => 'fa-child-reaching'],
            'remaja' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-200', 'icon' => 'fa-user-graduate'],
            'lansia' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-200', 'icon' => 'fa-person-cane'],
            default  => ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'icon' => 'fa-user'],
        };
    };

    $formatDateTime = fn($date) => $date ? Carbon::parse($date)->translatedFormat('d M Y, H:i') . ' WIB' : '-';
    
    // SMART STATUS
    $getLayananStatus = function($kunjungan) {
        $hasPemeriksaan = $kunjungan->pemeriksaan !== null;
        $hasImunisasi = $kunjungan->imunisasis && $kunjungan->imunisasis->count() > 0;

        if ($hasPemeriksaan || $hasImunisasi) {
            return [
                'label' => 'Selesai Dilayani',
                'badge' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                'icon'  => 'fa-check-double'
            ];
        }

        return [
            'label' => 'Menunggu Antrean',
            'badge' => 'bg-amber-50 text-amber-600 border-amber-200',
            'icon'  => 'fa-hourglass-half'
        ];
    };

    $totalData = method_exists($kunjungans, 'total') ? $kunjungans->total() : count($kunjungans);
@endphp

@push('styles')
<style>
    html { scroll-behavior: smooth; }
    
    body { 
        background-color: #f1f5f9; 
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(150, 100%, 94%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }

    .widget-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: 2rem;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Hover effect for tables and lists */
    .pc-row-hover:hover {
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 8px 25px rgba(20, 184, 166, 0.06);
        transform: translateY(-1px);
        z-index: 10;
        position: relative;
    }

    .input-soft {
        width: 100%; background: rgba(255,255,255,0.6); border: 1px solid #e2e8f0;
        border-radius: 9999px; padding: 12px 40px 12px 42px; font-size: 13px;
        font-weight: 700; color: #1e293b; outline: none; transition: all .25s ease;
    }
    .input-soft:focus {
        background: #ffffff; border-color: #14b8a6; box-shadow: 0 0 0 4px rgba(20, 184, 166, .15);
    }
    
    .btn-pill { border-radius: 9999px; transition: all 0.2s ease; cursor: pointer; }
    .btn-pill:active { transform: scale(0.95); }

    .slim-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: rgba(20, 184, 166, 0.3); border-radius: 9999px; }
    .slim-scroll::-webkit-scrollbar-thumb:hover { background: rgba(20, 184, 166, 0.6); }

    .animate-pop-in { animation: popIn .4s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    
    .fade-update { animation: fadeIn 0.25s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0.5; } to { opacity: 1; } }
</style>
@endpush

@section('content')
<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6 animate-pop-in relative">

    {{-- 1. HERO BANNER --}}
    <section class="relative overflow-hidden rounded-[2.5rem] sm:rounded-[3rem] bg-gradient-to-r from-teal-500 via-teal-400 to-emerald-400 p-6 sm:p-10 shadow-2xl shadow-teal-500/30 flex flex-col lg:flex-row justify-between items-center gap-6 sm:gap-8 border-[4px] border-white/50">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-50"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>

        <div class="relative z-10 w-full lg:w-2/3 flex flex-col gap-3 sm:gap-4 text-center lg:text-left">
            <div class="inline-flex justify-center lg:justify-start items-center gap-2 mb-1">
                <span class="btn-pill bg-white/20 border border-white/30 text-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest backdrop-blur-md shadow-inner flex items-center gap-2">
                    <i class="fa-solid fa-address-book"></i> Registrasi (Meja 1)
                </span>
            </div>
            
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight leading-tight drop-shadow-md">
                Buku Induk Kunjungan
            </h1>

            <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-xl mx-auto lg:mx-0 drop-shadow-sm">
                Data kehadiran warga yang tercatat secara otomatis. Layar ini disinkronisasi secara <strong class="font-black text-white">realtime</strong>, mencegah tumpang tindih antrean.
            </p>
        </div>

        <div class="relative z-10 w-full lg:w-1/3 flex justify-center lg:justify-end">
            <div class="widget-card !rounded-[2rem] !shadow-lg bg-white/90 border border-white px-6 py-5 flex items-center gap-5 min-w-[220px]">
                <div class="w-14 h-14 rounded-full bg-teal-100 text-teal-500 flex items-center justify-center text-2xl shadow-inner shrink-0">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <span class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Total Kehadiran</span>
                    <span id="total-kehadiran" class="block text-3xl font-black text-slate-800 tracking-tight mt-0.5">{{ number_format($totalData) }}</span>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. TAB KATEGORI & PENCARIAN SPA --}}
    <section class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 bg-white/50 backdrop-blur-sm p-3 rounded-[2rem] border border-slate-200">
        
        {{-- Tabs SPA --}}
        <div class="flex flex-wrap items-center gap-2" id="kategori-tabs-container">
            @foreach($kategoriOptions as $key => $option)
                @php $isActive = $kategori === $key; @endphp
                <button type="button" data-kategori="{{ $key }}"
                   class="kategori-tab btn-pill px-4 sm:px-5 py-2.5 sm:py-3 text-[10px] sm:text-[11px] font-black uppercase tracking-widest border transition-all flex items-center gap-2 shadow-sm
                   {{ $isActive ? 'bg-teal-500 text-white border-teal-400' : 'bg-white text-slate-500 border-slate-200 hover:bg-teal-50 hover:text-teal-600' }}">
                    <i class="fa-solid {{ $option['icon'] }} text-xs sm:text-sm pointer-events-none"></i> <span class="pointer-events-none">{{ $option['label'] }}</span>
                </button>
            @endforeach
        </div>

        {{-- Live Search Form --}}
        <form id="filterForm" class="w-full xl:w-96 relative shrink-0">
            <input type="hidden" name="kategori" id="kategori-input" value="{{ $kategori }}">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 z-10"></i>
            <input type="text" name="search" id="search-input" value="{{ $search }}" placeholder="Cari nama atau NIK warga..." class="input-soft shadow-inner relative z-0" autocomplete="off">
            <button type="button" id="clear-search" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 p-1 z-20 rounded-full transition-colors {{ $search ? '' : 'hidden' }}">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </form>
    </section>

    {{-- 3. TABEL DATA REALTIME --}}
    <section class="widget-card overflow-hidden flex flex-col relative !bg-white/80">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shrink-0 z-20 bg-white/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-teal-50 text-teal-500 flex items-center justify-center text-lg shadow-sm border border-teal-100"><i class="fa-solid fa-clipboard-list"></i></div>
                <div>
                    <h2 class="text-base font-black tracking-tight text-slate-800 uppercase">Daftar Kehadiran</h2>
                    <p id="label-filter-aktif" class="text-[10px] font-bold text-slate-400 tracking-widest uppercase mt-0.5">Filter: {{ $currentKatMeta['label'] }}</p>
                </div>
            </div>
            
            <div class="btn-pill inline-flex items-center gap-2 border border-teal-200 bg-teal-50 px-3 py-1.5 text-[9px] font-black uppercase tracking-wider text-teal-700 shadow-sm shrink-0">
                <span id="live-indicator" class="h-2 w-2 rounded-full bg-teal-500 animate-pulse"></span>
                Sinkronisasi Live
            </div>
        </div>

        <div id="data-container" class="min-h-[250px] overflow-y-auto overflow-x-auto slim-scroll relative">
            <div class="hidden lg:block w-full min-w-[1000px]">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead class="bg-slate-50/80 border-b border-slate-200 shadow-sm">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Data Warga</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Kategori Sasaran</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Waktu Kedatangan</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Status Layanan</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="table-body" class="divide-y divide-slate-100 bg-white/60">
                        @forelse($kunjungans as $kunjungan)
                            @php
                                $nama = $getPasienName($kunjungan->pasien);
                                $nik = $getPasienNik($kunjungan->pasien);
                                $typeLabel = $getPasienTypeStr($kunjungan->pasien_type);
                                $theme = $getTypeTheme($typeLabel);
                                $waktu = $formatDateTime($kunjungan->created_at);
                                $status = $getLayananStatus($kunjungan);
                            @endphp
                            <tr class="pc-row-hover transition-all group">
                                <td class="px-6 py-4 align-middle">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border {{ $theme['bg'] }} {{ $theme['border'] }} {{ $theme['text'] }} shadow-sm">
                                            <i class="fa-solid {{ $theme['icon'] }} text-sm"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-black text-slate-800 group-hover:text-teal-600 transition-colors">{{ $nama }}</p>
                                            <p class="text-[10px] font-bold text-slate-500 mt-0.5 font-mono"><i class="fa-solid fa-id-card mr-1 opacity-70"></i> {{ $nik }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest border {{ $theme['bg'] }} {{ $theme['text'] }} {{ $theme['border'] }} shadow-sm">
                                        {{ $typeLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <p class="text-xs font-bold text-slate-700"><i class="fa-solid fa-clock text-slate-400 mr-1.5"></i> {{ $waktu }}</p>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-black uppercase tracking-widest border {{ $status['badge'] }} shadow-sm">
                                        <i class="fa-solid {{ $status['icon'] }}"></i> {{ $status['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 align-middle text-right">
                                    <a href="{{ route('kader.kunjungan.show', $kunjungan->id) }}" class="btn-pill inline-flex items-center gap-2 border border-slate-200 bg-white text-slate-600 px-4 py-2 text-[10px] font-black uppercase tracking-widest hover:border-teal-300 hover:text-teal-600 hover:bg-teal-50 transition-all shadow-sm">
                                        <i class="fa-solid fa-folder-open"></i> Log
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-16 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 border border-slate-200 text-slate-400 mb-4 shadow-inner">
                                        <i class="fa-solid fa-clipboard-list text-3xl"></i>
                                    </div>
                                    <h3 class="text-sm font-black text-slate-700">Belum Ada Data</h3>
                                    <p class="text-xs font-medium text-slate-500 mt-1 max-w-sm mx-auto">Data yang dicari atau difilter tidak ditemukan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE VIEW --}}
            <div id="mobile-list" class="space-y-4 lg:hidden p-4 sm:p-6 bg-white/60">
                @forelse($kunjungans as $kunjungan)
                    @php
                        $nama = $getPasienName($kunjungan->pasien);
                        $nik = $getPasienNik($kunjungan->pasien);
                        $typeLabel = $getPasienTypeStr($kunjungan->pasien_type);
                        $theme = $getTypeTheme($typeLabel);
                        $waktu = $formatDateTime($kunjungan->created_at);
                        $status = $getLayananStatus($kunjungan);
                    @endphp
                    <article class="rounded-[1.5rem] border border-slate-200 bg-white p-4 sm:p-5 shadow-sm transition-transform active:scale-95">
                        <div class="flex items-center gap-3 mb-4 border-b border-slate-100 pb-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full border {{ $theme['bg'] }} {{ $theme['border'] }} {{ $theme['text'] }} flex items-center justify-center shrink-0 shadow-sm"><i class="fa-solid {{ $theme['icon'] }} text-sm sm:text-base"></i></div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-sm sm:text-base font-black text-slate-900 truncate">{{ $nama }}</h3>
                                <p class="text-[10px] sm:text-[11px] text-slate-500 font-mono mt-0.5"><i class="fa-solid fa-id-card opacity-70 mr-1"></i> {{ $nik }}</p>
                            </div>
                            <span class="btn-pill {{ $theme['bg'] }} {{ $theme['text'] }} px-2 py-1 text-[9px] font-black uppercase tracking-widest shrink-0">{{ $typeLabel }}</span>
                        </div>
                        <div class="flex flex-col gap-2 mb-4">
                            <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kedatangan</span>
                                <span class="text-[10px] font-bold text-slate-700"><i class="fa-regular fa-clock mr-1 text-slate-400"></i> {{ $waktu }}</span>
                            </div>
                            <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Status</span>
                                <span class="text-[10px] font-black uppercase tracking-widest {{ str_contains($status['badge'], 'emerald') ? 'text-emerald-600' : 'text-amber-600' }}">
                                    <i class="fa-solid {{ $status['icon'] }} mr-1"></i> {{ $status['label'] }}
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('kader.kunjungan.show', $kunjungan->id) }}" class="btn-pill w-full flex justify-center items-center gap-2 bg-gradient-to-r from-teal-500 to-emerald-500 text-white px-4 py-3 text-[11px] sm:text-xs font-bold uppercase tracking-widest shadow-md hover:from-teal-600 hover:to-emerald-600">
                            <i class="fa-solid fa-folder-open"></i> Lihat Log
                        </a>
                    </article>
                @empty
                    <div class="p-10 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 border border-slate-200 text-slate-400 mb-4 shadow-inner">
                            <i class="fa-solid fa-clipboard-list text-2xl"></i>
                        </div>
                        <h3 class="text-sm font-black text-slate-700">Belum Ada Data</h3>
                        <p class="text-[11px] font-medium text-slate-500 mt-1 max-w-sm mx-auto">Tidak ada kunjungan yang sesuai dengan filter.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- PAGINATION CONTAINER --}}
        <div id="pagination-container">
            @if(method_exists($kunjungans, 'hasPages') && $kunjungans->hasPages())
                <div class="bg-white/90 p-5 sm:p-6 border-t border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shrink-0 rounded-b-[2rem]">
                    <p class="text-[10px] sm:text-[11px] font-bold uppercase tracking-widest text-slate-500 text-center md:text-left">
                        Menampilkan <span class="text-slate-800 font-black">{{ $kunjungans->firstItem() }}</span> - <span class="text-slate-800 font-black">{{ $kunjungans->lastItem() }}</span> dari <span class="text-slate-800 font-black">{{ $kunjungans->total() }}</span> data
                    </p>
                    
                    <div class="flex items-center justify-center gap-1.5">
                        {{-- Previous --}}
                        @if ($kunjungans->onFirstPage())
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-400 cursor-not-allowed border border-slate-100"><i class="fa-solid fa-chevron-left text-[10px]"></i></span>
                        @else
                            <a href="{{ $kunjungans->appends(request()->query())->previousPageUrl() }}" class="spa-page-link inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 transition-all shadow-sm"><i class="fa-solid fa-chevron-left text-[10px]"></i></a>
                        @endif

                        {{-- Logika 5 Angka --}}
                        @php
                            $window = 2; 
                            $start = max(1, $kunjungans->currentPage() - $window);
                            $end = min($kunjungans->lastPage(), $kunjungans->currentPage() + $window);

                            if ($kunjungans->currentPage() <= $window) {
                                $end = min($kunjungans->lastPage(), 1 + ($window * 2));
                            }
                            if ($kunjungans->currentPage() > $kunjungans->lastPage() - $window) {
                                $start = max(1, $kunjungans->lastPage() - ($window * 2));
                            }
                        @endphp

                        @for ($page = $start; $page <= $end; $page++)
                            @if ($page == $kunjungans->currentPage())
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-teal-500 text-white shadow-md shadow-teal-500/30 text-xs font-black">{{ $page }}</span>
                            @else
                                <a href="{{ $kunjungans->appends(request()->query())->url($page) }}" class="spa-page-link inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 transition-all shadow-sm text-xs font-bold">{{ $page }}</a>
                            @endif
                        @endfor

                        {{-- Next --}}
                        @if ($kunjungans->hasMorePages())
                            <a href="{{ $kunjungans->appends(request()->query())->nextPageUrl() }}" class="spa-page-link inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 transition-all shadow-sm"><i class="fa-solid fa-chevron-right text-[10px]"></i></a>
                        @else
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-400 cursor-not-allowed border border-slate-100"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
/**
 * SPA (SINGLE PAGE APPLICATION) ENGINE
 * Skrip ini mengatur Live Search, Tab Kategori, dan Pagination tanpa refresh halaman.
 */
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-input');
    const clearBtn = document.getElementById('clear-search');
    const kategoriInput = document.getElementById('kategori-input');
    const filterForm = document.getElementById('filterForm');
    const indicator = document.getElementById('live-indicator');
    
    let isFetching = false;
    let abortController = null;
    let debounceTimer = null;
    let pollTimer = null;
    const pollInterval = 5000; // Sinkronisasi background setiap 5 detik

    // Mencegah form tersubmit jika dienter manual
    filterForm.addEventListener('submit', (e) => e.preventDefault());

    // FUNGSI UTAMA: Mengambil & Menukar Data (DOM Diffing)
    const fetchLiveUpdates = async (overrideUrl = null, pushHistory = false) => {
        if (isFetching) return;
        isFetching = true;
        
        if(indicator) {
            indicator.classList.remove('bg-teal-500');
            indicator.classList.add('bg-amber-400'); // Warna kuning tanda sedang loading
        }

        if (abortController) abortController.abort();
        abortController = new AbortController();

        try {
            // Bangun URL dengan State Inputan Saat Ini (Mencegah Bug Salah Kategori)
            let fetchUrl;
            if (overrideUrl) {
                fetchUrl = new URL(overrideUrl);
            } else {
                fetchUrl = new URL(window.location.origin + window.location.pathname);
                fetchUrl.searchParams.set('kategori', kategoriInput.value);
                if (searchInput.value.trim() !== '') {
                    fetchUrl.searchParams.set('search', searchInput.value.trim());
                }
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('page') && !overrideUrl) {
                    fetchUrl.searchParams.set('page', urlParams.get('page'));
                }
            }

            // Tambah parameter agar browser tidak caching request
            const targetUrl = fetchUrl.href;
            fetchUrl.searchParams.set('_r', Date.now());

            const response = await fetch(fetchUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: abortController.signal
            });

            if (!response.ok) throw new Error('Network Error');
            const html = await response.text();
            
            // Ekstrak elemen-elemen baru
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const updateDOM = (id) => {
                const newEl = doc.getElementById(id);
                const currentEl = document.getElementById(id);
                if (newEl && currentEl && newEl.innerHTML !== currentEl.innerHTML) {
                    currentEl.innerHTML = newEl.innerHTML;
                    // Animasi halus
                    currentEl.classList.remove('fade-update');
                    void currentEl.offsetWidth; 
                    currentEl.classList.add('fade-update');
                }
            };

            // Terapkan perubahan ke layar
            updateDOM('total-kehadiran');
            updateDOM('table-body');
            updateDOM('mobile-list');
            updateDOM('pagination-container');
            
            // Ubah Label Kategori Aktif
            const labelEl = doc.getElementById('label-filter-aktif');
            if (labelEl && document.getElementById('label-filter-aktif')) {
                 document.getElementById('label-filter-aktif').innerHTML = labelEl.innerHTML;
            }

            // Update URL bar browser (jika hasil klik user)
            if (pushHistory) window.history.pushState({}, '', targetUrl);

        } catch (error) {
            if (error.name !== 'AbortError') console.warn('Koneksi sinkronisasi terputus sementara.');
        } finally {
            isFetching = false;
            if(indicator) {
                indicator.classList.remove('bg-amber-400');
                indicator.classList.add('bg-teal-500'); // Hijau lagi
            }
        }
    };

    // 1. LIVE SEARCH EVENT (Instan tanpa Enter)
    if(searchInput) {
        searchInput.addEventListener('input', () => {
            // Tampilkan/Sembunyikan tombol X
            if(clearBtn) clearBtn.classList.toggle('hidden', searchInput.value.trim() === '');
            
            // Debounce: Tunggu user berhenti mengetik 300ms agar tidak spam server
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                // Reset URL param page ke 1 setiap mencari kata baru
                const url = new URL(window.location.origin + window.location.pathname);
                if(kategoriInput) url.searchParams.set('kategori', kategoriInput.value);
                if (searchInput.value.trim() !== '') url.searchParams.set('search', searchInput.value.trim());
                fetchLiveUpdates(url.href, true);
            }, 300); 
        });
    }

    // Tombol X untuk clear search
    if(clearBtn) {
        clearBtn.addEventListener('click', () => {
            if(searchInput) {
                searchInput.value = '';
                searchInput.focus();
            }
            clearBtn.classList.add('hidden');
            clearTimeout(debounceTimer);
            
            const url = new URL(window.location.origin + window.location.pathname);
            if(kategoriInput) url.searchParams.set('kategori', kategoriInput.value);
            fetchLiveUpdates(url.href, true);
        });
    }

    // 2. TAB KATEGORI EVENT (SPA CLICK)
    document.querySelectorAll('.kategori-tab').forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            // Reset UI Tabs
            document.querySelectorAll('.kategori-tab').forEach(t => {
                t.classList.remove('bg-teal-500', 'text-white', 'border-teal-400');
                t.classList.add('bg-white', 'text-slate-500', 'border-slate-200');
            });
            // Set Tab Aktif
            tab.classList.remove('bg-white', 'text-slate-500', 'border-slate-200');
            tab.classList.add('bg-teal-500', 'text-white', 'border-teal-400');

            if(kategoriInput) kategoriInput.value = tab.dataset.kategori;
            
            // Build URL
            const url = new URL(window.location.origin + window.location.pathname);
            if(kategoriInput) url.searchParams.set('kategori', kategoriInput.value);
            if (searchInput && searchInput.value.trim() !== '') url.searchParams.set('search', searchInput.value.trim());
            
            fetchLiveUpdates(url.href, true);
        });
    });

    // 3. PAGINATION EVENT (SPA CLICK)
    document.addEventListener('click', (e) => {
        const pageLink = e.target.closest('.spa-page-link');
        if (pageLink) {
            e.preventDefault();
            fetchLiveUpdates(pageLink.href, true);
            // Gulung ke atas perlahan saat ganti halaman
            const container = document.getElementById('data-container');
            if(container) container.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    // 4. BACKGROUND POLLING (Sembunyi-sembunyi update jika ada data dari Bidan)
    const startPolling = () => {
        pollTimer = setInterval(() => fetchLiveUpdates(), pollInterval);
    };
    
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(pollTimer); // Hemat RAM & Database
        } else {
            fetchLiveUpdates(); 
            startPolling();
        }
    });

    startPolling();
});
</script>
@endpush