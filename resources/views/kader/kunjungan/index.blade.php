@extends('layouts.kader')

@section('title', 'Logbook Kunjungan')
@section('page-name', 'Buku Induk Kunjungan')
@section('page-title', 'Buku Induk Kunjungan Warga')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    $kunjungans = $kunjungans ?? collect();
    $search = $search ?? request('search', '');
    $kategori = $kategori ?? request('kategori', 'semua');

    // MAPPING KATEGORI
    $kategoriOptions = [
        'semua'  => ['label' => 'Semua Kunjungan', 'icon' => 'fa-users-rectangle'],
        'balita' => ['label' => 'Balita', 'icon' => 'fa-baby'],
        'remaja' => ['label' => 'Remaja', 'icon' => 'fa-user-graduate'],
        'lansia' => ['label' => 'Lansia', 'icon' => 'fa-person-cane'],
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
            'balita' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-600', 'border' => 'border-sky-200', 'icon' => 'fa-baby'],
            'remaja' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'border' => 'border-indigo-200', 'icon' => 'fa-user-graduate'],
            'lansia' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200', 'icon' => 'fa-person-cane'],
            default  => ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'icon' => 'fa-user'],
        };
    };

    $formatDateTime = fn($date) => $date ? Carbon::parse($date)->translatedFormat('d M Y, H:i') . ' WIB' : '-';
    
    // SMART STATUS (Mendeteksi apakah pasien sudah diperiksa/diimunisasi oleh Bidan)
    $getLayananStatus = function($kunjungan) {
        $hasPemeriksaan = $kunjungan->pemeriksaan !== null;
        $hasImunisasi = $kunjungan->imunisasis && $kunjungan->imunisasis->count() > 0;

        if ($hasPemeriksaan || $hasImunisasi) {
            return [
                'label' => 'Selesai Dilayani',
                'badge' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                'icon'  => 'fa-check-double'
            ];
        }

        return [
            'label' => 'Menunggu Antrean',
            'badge' => 'bg-amber-100 text-amber-700 border-amber-200',
            'icon'  => 'fa-hourglass-half'
        ];
    };

    $totalData = method_exists($kunjungans, 'total') ? $kunjungans->total() : count($kunjungans);
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
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .input-soft {
        width: 100%; background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: 9999px; padding: 12px 20px 12px 42px; font-size: 13px;
        font-weight: 700; color: #1e293b; outline: none; transition: all .25s ease;
    }
    .input-soft:focus {
        background: #ffffff; border-color: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, .15);
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

<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6 animate-pop-in">

    {{-- 1. HERO BANNER (Konsisten Hijau Emerald) --}}
    <section class="relative overflow-hidden rounded-[3rem] bg-gradient-to-r from-emerald-500 via-teal-500 to-green-500 p-8 sm:p-10 shadow-2xl shadow-emerald-500/20 flex flex-col lg:flex-row justify-between items-center gap-8 border-[6px] border-white/40" style="transform: translateZ(0);">
        <div class="absolute inset-0 bg-white/10 backdrop-blur-[2px]"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-full lg:w-2/3 flex flex-col gap-4 text-center lg:text-left">
            <div class="inline-flex justify-center lg:justify-start items-center gap-2 mb-2">
                <span class="btn-pill bg-white/20 border border-white/30 text-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest backdrop-blur-md shadow-inner flex items-center gap-2">
                    <i class="fa-solid fa-address-book"></i> Registrasi (Meja 1)
                </span>
            </div>
            
            <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight leading-tight">
                Buku Induk Kunjungan
            </h1>

            <p class="text-white/90 text-sm font-medium leading-relaxed max-w-xl mx-auto lg:mx-0">
                Data kehadiran warga yang tercatat secara otomatis ketika Anda memproses pengukuran fisik atau pendaftaran layanan di aplikasi. Bersifat Read-Only untuk integritas data.
            </p>
        </div>

        <div class="relative z-10 w-full lg:w-1/3 flex justify-center lg:justify-end">
            <div class="widget-card !rounded-[2rem] !shadow-none bg-white/20 border border-white/30 backdrop-blur-md px-6 py-4 flex items-center gap-5">
                <div>
                    <span class="block text-[10px] font-black uppercase tracking-widest text-white/90">Total Kehadiran</span>
                    <span class="block text-3xl font-black text-white mt-0.5">{{ $totalData }}</span>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-emerald-500 shadow-lg">
                    <i class="fa-solid fa-users text-2xl"></i>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. TAB KATEGORI & PENCARIAN --}}
    <section class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        
        {{-- Tabs --}}
        <div class="flex flex-wrap items-center gap-2">
            @foreach($kategoriOptions as $key => $option)
                @php $isActive = $kategori === $key; @endphp
                <a href="{{ route('kader.kunjungan.index', ['kategori' => $key, 'search' => $search]) }}" 
                   class="btn-pill px-5 py-3 text-[11px] font-black uppercase tracking-widest border transition-all flex items-center gap-2 shadow-sm
                   {{ $isActive ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-white text-slate-500 border-slate-200 hover:bg-emerald-50 hover:text-emerald-600' }}">
                    <i class="fa-solid {{ $option['icon'] }} text-sm"></i> {{ $option['label'] }}
                </a>
            @endforeach
        </div>

        {{-- Search Form --}}
        <form method="GET" action="{{ route('kader.kunjungan.index') }}" class="w-full xl:w-96 relative shrink-0">
            <input type="hidden" name="kategori" value="{{ $kategori }}">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau NIK warga..." class="input-soft shadow-sm">
            @if($search)
                <a href="{{ route('kader.kunjungan.index', ['kategori' => $kategori]) }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 p-1">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </form>
    </section>

    {{-- 3. TABEL DATA (Presisi max-h-[600px] dengan scrollbar) --}}
    <section class="widget-card overflow-hidden flex flex-col relative">
        <div class="p-6 border-b border-slate-100 bg-white flex items-center gap-3 shrink-0 z-20">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-lg shadow-sm border border-emerald-100"><i class="fa-solid fa-clipboard-list"></i></div>
            <div>
                <h2 class="text-base font-black tracking-tight text-slate-800 uppercase">Daftar Kehadiran</h2>
                <p class="text-[10px] font-bold text-slate-400 tracking-widest uppercase mt-0.5">Filter: {{ $currentKatMeta['label'] }}</p>
            </div>
        </div>

        <div class="min-h-[200px] max-h-[600px] overflow-y-auto overflow-x-auto slim-scroll relative bg-slate-50/30">
            <div class="hidden lg:block w-full min-w-[1000px]">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead class="sticky top-0 z-10 bg-white/95 backdrop-blur-sm border-b border-slate-200 shadow-sm">
                        <tr>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Data Warga</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Kategori Sasaran</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Waktu Kedatangan</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Status Layanan</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($kunjungans as $kunjungan)
                            @php
                                $nama = $getPasienName($kunjungan->pasien);
                                $nik = $getPasienNik($kunjungan->pasien);
                                $typeLabel = $getPasienTypeStr($kunjungan->pasien_type);
                                $theme = $getTypeTheme($typeLabel);
                                $waktu = $formatDateTime($kunjungan->created_at);
                                $status = $getLayananStatus($kunjungan);
                            @endphp

                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-6 py-5 align-middle">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border {{ $theme['bg'] }} {{ $theme['border'] }} {{ $theme['text'] }} shadow-sm">
                                            <i class="fa-solid {{ $theme['icon'] }} text-sm"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">{{ $nama }}</p>
                                            <p class="text-[10px] font-bold text-slate-400 mt-0.5 font-mono"><i class="fa-solid fa-id-card mr-1"></i> {{ $nik }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-[10px] font-black border {{ $theme['bg'] }} {{ $theme['text'] }} {{ $theme['border'] }}">
                                        {{ $typeLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <p class="text-xs font-bold text-slate-700"><i class="fa-solid fa-clock text-slate-400 mr-1.5"></i> {{ $waktu }}</p>
                                </td>
                                <td class="px-6 py-5 align-middle">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-black border {{ $status['badge'] }}">
                                        <i class="fa-solid {{ $status['icon'] }}"></i> {{ $status['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 align-middle text-right">
                                    <a href="{{ route('kader.kunjungan.show', $kunjungan->id) }}" class="btn-pill inline-flex items-center gap-2 border border-slate-200 bg-white text-slate-600 px-4 py-2 text-[10px] font-black uppercase tracking-widest hover:border-emerald-300 hover:text-emerald-600 hover:bg-emerald-50 transition-all shadow-sm">
                                        <i class="fa-solid fa-folder-open"></i> Buka Log
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-14 text-center bg-white">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-slate-50 border border-slate-100 text-slate-300 mb-4 shadow-inner">
                                        <i class="fa-solid fa-clipboard-list text-3xl"></i>
                                    </div>
                                    <h3 class="text-sm font-black text-slate-700">Belum Ada Kunjungan</h3>
                                    <p class="text-xs font-medium text-slate-400 mt-1 max-w-sm mx-auto">Data kunjungan akan tercatat otomatis ketika Anda melakukan pengukuran atau pendaftaran layanan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE VIEW --}}
            <div class="space-y-4 lg:hidden p-4">
                @foreach($kunjungans as $kunjungan)
                    @php
                        $nama = $getPasienName($kunjungan->pasien);
                        $nik = $getPasienNik($kunjungan->pasien);
                        $typeLabel = $getPasienTypeStr($kunjungan->pasien_type);
                        $theme = $getTypeTheme($typeLabel);
                        $waktu = $formatDateTime($kunjungan->created_at);
                        $status = $getLayananStatus($kunjungan);
                    @endphp
                    <article class="rounded-[1.5rem] border border-slate-100 bg-white p-5 shadow-sm">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full border {{ $theme['bg'] }} {{ $theme['border'] }} {{ $theme['text'] }} flex items-center justify-center"><i class="fa-solid {{ $theme['icon'] }}"></i></div>
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">{{ $nama }}</h3>
                                    <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $nik }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2 mb-4 bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <p class="text-[10px] font-bold text-slate-500"><i class="fa-solid fa-clock mr-1.5 w-3 text-center"></i> {{ $waktu }}</p>
                            <p class="text-[10px] font-bold {{ str_contains($status['badge'], 'emerald') ? 'text-emerald-600' : 'text-amber-600' }}"><i class="fa-solid {{ $status['icon'] }} mr-1.5 w-3 text-center"></i> {{ $status['label'] }}</p>
                        </div>
                        <a href="{{ route('kader.kunjungan.show', $kunjungan->id) }}" class="btn-pill w-full inline-flex justify-center items-center bg-slate-900 text-white px-3 py-3 text-[10px] font-black uppercase tracking-widest shadow-sm hover:bg-emerald-600">Lihat Log</a>
                    </article>
                @endforeach
            </div>
        </div>

        {{-- PAGINATION CUSTOM UI (Rounded/Membulat) --}}
        @if(method_exists($kunjungans, 'hasPages') && $kunjungans->hasPages())
            <div class="bg-white p-6 border-t border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shrink-0 rounded-b-[2rem]">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                    Menampilkan <span class="text-slate-700">{{ $kunjungans->firstItem() }}</span> - <span class="text-slate-700">{{ $kunjungans->lastItem() }}</span> dari <span class="text-slate-800">{{ $kunjungans->total() }}</span> data
                </p>
                
                <div class="flex items-center gap-1.5">
                    {{-- Previous --}}
                    @if ($kunjungans->onFirstPage())
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-400 cursor-not-allowed border border-slate-100"><i class="fa-solid fa-chevron-left text-[10px]"></i></span>
                    @else
                        <a href="{{ $kunjungans->appends(request()->query())->previousPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm"><i class="fa-solid fa-chevron-left text-[10px]"></i></a>
                    @endif

                    {{-- Page Numbers --}}
                    @php
                        $start = max(1, $kunjungans->currentPage() - 1);
                        $end = min($kunjungans->lastPage(), $kunjungans->currentPage() + 1);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $kunjungans->appends(request()->query())->url(1) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm text-xs font-bold">1</a>
                        @if($start > 2)<span class="inline-flex h-8 w-8 items-center justify-center text-slate-400 text-xs font-bold">...</span>@endif
                    @endif

                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $kunjungans->currentPage())
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 text-white shadow-md shadow-emerald-500/30 text-xs font-black">{{ $page }}</span>
                        @else
                            <a href="{{ $kunjungans->appends(request()->query())->url($page) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm text-xs font-bold">{{ $page }}</a>
                        @endif
                    @endfor

                    @if($end < $kunjungans->lastPage())
                        @if($end < $kunjungans->lastPage() - 1)<span class="inline-flex h-8 w-8 items-center justify-center text-slate-400 text-xs font-bold">...</span>@endif
                        <a href="{{ $kunjungans->appends(request()->query())->url($kunjungans->lastPage()) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm text-xs font-bold">{{ $kunjungans->lastPage() }}</a>
                    @endif

                    {{-- Next --}}
                    @if ($kunjungans->hasMorePages())
                        <a href="{{ $kunjungans->appends(request()->query())->nextPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm"><i class="fa-solid fa-chevron-right text-[10px]"></i></a>
                    @else
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-400 cursor-not-allowed border border-slate-100"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
                    @endif
                </div>
            </div>
        @endif
    </section>
</div>
@endsection