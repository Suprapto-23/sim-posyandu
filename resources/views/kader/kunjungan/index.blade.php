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
    
    // SMART STATUS
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
    }

    .input-soft {
        width: 100%; background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: 9999px; padding: 12px 40px 12px 42px; font-size: 13px;
        font-weight: 700; color: #1e293b; outline: none; transition: all .25s ease;
    }
    .input-soft:focus {
        background: #ffffff; border-color: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, .15);
    }
    
    .btn-pill { border-radius: 9999px; transition: all 0.2s ease; cursor: pointer; }
    .btn-pill:active { transform: scale(0.95); }

    .slim-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: rgba(16, 185, 129, 0.3); border-radius: 9999px; }
    .slim-scroll::-webkit-scrollbar-thumb:hover { background: rgba(16, 185, 129, 0.5); }

    .animate-pop-in { animation: popIn .4s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    
    .fade-update { animation: fadeIn 0.25s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0.5; } to { opacity: 1; } }
</style>
@endpush

@section('content')
<div class="bg-mesh-fixed"></div>

<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6 animate-pop-in">

    {{-- 1. HERO BANNER --}}
    <section class="relative overflow-hidden rounded-[3rem] bg-gradient-to-r from-emerald-500 via-teal-500 to-green-500 p-8 sm:p-10 shadow-2xl shadow-emerald-500/20 flex flex-col lg:flex-row justify-between items-center gap-8 border-[6px] border-white/40">
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
                Data kehadiran warga yang tercatat secara otomatis. Layar ini disinkronisasi secara <strong class="font-black text-white">realtime</strong>, mencegah tumpang tindih antrean.
            </p>
        </div>

        <div class="relative z-10 w-full lg:w-1/3 flex justify-center lg:justify-end">
            <div class="widget-card !rounded-[2rem] !shadow-none bg-white/20 border border-white/30 backdrop-blur-md px-6 py-4 flex items-center gap-5">
                <div>
                    <span class="block text-[10px] font-black uppercase tracking-widest text-white/90">Total Kehadiran</span>
                    <span id="total-kehadiran" class="block text-3xl font-black text-white mt-0.5">{{ $totalData }}</span>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-emerald-500 shadow-lg">
                    <i class="fa-solid fa-users text-2xl"></i>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. TAB KATEGORI & PENCARIAN SPA --}}
    <section class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
        
        {{-- Tabs SPA --}}
        <div class="flex flex-wrap items-center gap-2" id="kategori-tabs-container">
            @foreach($kategoriOptions as $key => $option)
                @php $isActive = $kategori === $key; @endphp
                <button type="button" data-kategori="{{ $key }}"
                   class="kategori-tab btn-pill px-5 py-3 text-[11px] font-black uppercase tracking-widest border transition-all flex items-center gap-2 shadow-sm
                   {{ $isActive ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-white text-slate-500 border-slate-200 hover:bg-emerald-50 hover:text-emerald-600' }}">
                    <i class="fa-solid {{ $option['icon'] }} text-sm pointer-events-none"></i> {{ $option['label'] }}
                </button>
            @endforeach
        </div>

        {{-- Live Search Form --}}
        <form id="filterForm" class="w-full xl:w-96 relative shrink-0">
            <input type="hidden" name="kategori" id="kategori-input" value="{{ $kategori }}">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" name="search" id="search-input" value="{{ $search }}" placeholder="Cari nama atau NIK warga..." class="input-soft shadow-sm" autocomplete="off">
            <button type="button" id="clear-search" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 p-1 {{ $search ? '' : 'hidden' }}">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </form>
    </section>

    {{-- 3. TABEL DATA REALTIME --}}
    <section class="widget-card overflow-hidden flex flex-col relative">
        <div class="p-6 border-b border-slate-100 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 shrink-0 z-20">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-lg shadow-sm border border-emerald-100"><i class="fa-solid fa-clipboard-list"></i></div>
                <div>
                    <h2 class="text-base font-black tracking-tight text-slate-800 uppercase">Daftar Kehadiran</h2>
                    <p id="label-filter-aktif" class="text-[10px] font-bold text-slate-400 tracking-widest uppercase mt-0.5">Filter: {{ $currentKatMeta['label'] }}</p>
                </div>
            </div>
            
            <div class="btn-pill inline-flex items-center gap-2 border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-[9px] font-black uppercase tracking-wider text-emerald-700 shadow-sm">
                <span id="live-indicator" class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Sinkronisasi Live
            </div>
        </div>

        <div id="data-container" class="min-h-[200px] overflow-y-auto overflow-x-auto slim-scroll relative bg-slate-50/30">
            <div class="hidden lg:block w-full min-w-[1000px]">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead class="bg-white/95 backdrop-blur-sm border-b border-slate-200 shadow-sm">
                        <tr>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Data Warga</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Kategori Sasaran</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Waktu Kedatangan</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400">Status Layanan</th>
                            <th class="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-slate-400 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="table-body" class="divide-y divide-slate-100 bg-white">
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
                                        <i class="fa-solid fa-folder-open"></i> Log
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-14 text-center bg-white">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-slate-50 border border-slate-100 text-slate-300 mb-4 shadow-inner">
                                        <i class="fa-solid fa-clipboard-list text-3xl"></i>
                                    </div>
                                    <h3 class="text-sm font-black text-slate-700">Belum Ada Data</h3>
                                    <p class="text-xs font-medium text-slate-400 mt-1 max-w-sm mx-auto">Data yang dicari atau difilter tidak ditemukan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE VIEW --}}
            <div id="mobile-list" class="space-y-4 lg:hidden p-4">
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
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full border {{ $theme['bg'] }} {{ $theme['border'] }} {{ $theme['text'] }} flex items-center justify-center"><i class="fa-solid {{ $theme['icon'] }}"></i></div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $nama }}</h3>
                                <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $nik }}</p>
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

        {{-- PAGINATION CONTAINER (MAKSIMAL 5 ANGKA PILL) --}}
        <div id="pagination-container">
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
                            <a href="{{ $kunjungans->appends(request()->query())->previousPageUrl() }}" class="spa-page-link inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm"><i class="fa-solid fa-chevron-left text-[10px]"></i></a>
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
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 text-white shadow-md shadow-emerald-500/30 text-xs font-black">{{ $page }}</span>
                            @else
                                <a href="{{ $kunjungans->appends(request()->query())->url($page) }}" class="spa-page-link inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm text-xs font-bold">{{ $page }}</a>
                            @endif
                        @endfor

                        {{-- Next --}}
                        @if ($kunjungans->hasMorePages())
                            <a href="{{ $kunjungans->appends(request()->query())->nextPageUrl() }}" class="spa-page-link inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm"><i class="fa-solid fa-chevron-right text-[10px]"></i></a>
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
        indicator.classList.remove('bg-emerald-500');
        indicator.classList.add('bg-amber-400'); // Warna kuning tanda sedang loading

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
            indicator.classList.remove('bg-amber-400');
            indicator.classList.add('bg-emerald-500'); // Hijau lagi
        }
    };

    // 1. LIVE SEARCH EVENT (Instan tanpa Enter)
    searchInput.addEventListener('input', () => {
        // Tampilkan/Sembunyikan tombol X
        clearBtn.classList.toggle('hidden', searchInput.value.trim() === '');
        
        // Debounce: Tunggu user berhenti mengetik 300ms agar tidak spam server
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            // Reset URL param page ke 1 setiap mencari kata baru
            const url = new URL(window.location.origin + window.location.pathname);
            url.searchParams.set('kategori', kategoriInput.value);
            if (searchInput.value.trim() !== '') url.searchParams.set('search', searchInput.value.trim());
            fetchLiveUpdates(url.href, true);
        }, 300); 
    });

    // Tombol X untuk clear search
    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        clearBtn.classList.add('hidden');
        searchInput.focus();
        clearTimeout(debounceTimer);
        
        const url = new URL(window.location.origin + window.location.pathname);
        url.searchParams.set('kategori', kategoriInput.value);
        fetchLiveUpdates(url.href, true);
    });

    // 2. TAB KATEGORI EVENT (SPA CLICK)
    document.querySelectorAll('.kategori-tab').forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            // Reset UI Tabs
            document.querySelectorAll('.kategori-tab').forEach(t => {
                t.classList.remove('bg-emerald-600', 'text-white', 'border-emerald-500');
                t.classList.add('bg-white', 'text-slate-500', 'border-slate-200');
            });
            // Set Tab Aktif
            tab.classList.remove('bg-white', 'text-slate-500', 'border-slate-200');
            tab.classList.add('bg-emerald-600', 'text-white', 'border-emerald-500');

            kategoriInput.value = tab.dataset.kategori;
            
            // Build URL
            const url = new URL(window.location.origin + window.location.pathname);
            url.searchParams.set('kategori', kategoriInput.value);
            if (searchInput.value.trim() !== '') url.searchParams.set('search', searchInput.value.trim());
            
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
            document.getElementById('data-container').scrollTo({ top: 0, behavior: 'smooth' });
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