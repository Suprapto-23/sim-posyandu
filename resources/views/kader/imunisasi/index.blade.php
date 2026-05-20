@extends('layouts.kader')

@section('title', 'Tracker Imunisasi')
@section('page-name', 'Log Vaksinasi Warga')

@push('styles')
{{-- Engine Ikon Phosphor --}}
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    /* ====================================================================
       1. ULTRA-SMOOTH CHOREOGRAPHY (120FPS ANIMATION)
       ==================================================================== */
    .layer-gpu { transform: translateZ(0); backface-visibility: hidden; will-change: transform, opacity; }
    
    @keyframes swoopIn {
        0% { opacity: 0; transform: translateY(20px) scale(0.995); }
        100% { opacity: 1; transform: translateY(0) scale(1); }
    }

    .stagger-nexus > * { opacity: 0; animation: swoopIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .stagger-nexus > *:nth-child(1) { animation-delay: 40ms; }
    .stagger-nexus > *:nth-child(2) { animation-delay: 100ms; }
    .stagger-nexus > *:nth-child(3) { animation-delay: 160ms; }
    .stagger-nexus > *:nth-child(4) { animation-delay: 220ms; }

    /* ====================================================================
       2. PREMIUM SAAS COMPONENTS (LIGHT & CLEAN)
       ==================================================================== */
    .nexus-panel {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 4px 20px -5px rgba(15, 23, 42, 0.03), 0 0 0 1px rgba(226, 232, 240, 0.5);
        transition: box-shadow 0.3s ease;
    }
    
    .nexus-input {
        width: 100%; padding: 0.85rem 1.25rem 0.85rem 2.75rem;
        background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: 14px; color: #334155; font-size: 13px; font-weight: 500;
        transition: all 0.2s ease; outline: none;
    }
    .nexus-input:focus { background: #ffffff; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }

    /* iOS Segmented Control */
    .ios-segment { display: flex; background: #f1f5f9; padding: 4px; border-radius: 16px; overflow-x: auto; }
    .ios-segment::-webkit-scrollbar { display: none; }
    .ios-btn { flex: 1; text-align: center; padding: 9px 16px; font-size: 12px; font-weight: 600; color: #64748b; transition: all 0.2s ease; white-space: nowrap; border-radius: 12px; cursor: pointer; border: 1px solid transparent; }
    .ios-btn.active { background: #ffffff; color: #4f46e5; font-weight: 700; box-shadow: 0 2px 6px rgba(0,0,0,0.04); border-color: #e2e8f0; }
    .ios-btn:hover:not(.active) { color: #334155; background: rgba(255,255,255,0.4); }

    /* ====================================================================
       3. MASTERPIECE TABLE & MICRO-BADGES
       ==================================================================== */
    .tr-master { transition: all 0.2s ease; border-bottom: 1px solid #f1f5f9; }
    .tr-master:hover { background-color: #f8fafc; transform: translateY(-1px); }
    
    .med-badge {
        display: inline-flex; align-items: center; padding: 0.25rem 0.5rem; border-radius: 6px; 
        font-size: 11px; font-weight: 600; white-space: nowrap; border: 1px solid transparent; 
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .c-scroll::-webkit-scrollbar { height: 6px; width: 6px; }
    .c-scroll::-webkit-scrollbar-track { background: transparent; }
    .c-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .c-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    /* AJAX Loader Mulus */
    #mainContentArea { transition: opacity 0.3s ease, transform 0.3s ease; }
    .is-loading { opacity: 0.5; transform: scale(0.995); pointer-events: none; }
</style>
@endpush

@section('content')
<div class="max-w-[1450px] mx-auto stagger-nexus pb-20 layer-gpu mt-2">

    {{-- AURA BACKGROUND (Soft Ambient) --}}
    <div class="fixed top-0 right-0 w-[500px] h-[500px] bg-indigo-500/5 rounded-full blur-[120px] pointer-events-none -z-10"></div>
    <div class="fixed bottom-0 left-0 w-[400px] h-[400px] bg-emerald-500/5 rounded-full blur-[120px] pointer-events-none -z-10"></div>

    {{-- 1. HERO HEADER & STATS WIDGETS --}}
    <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-8 mb-8 px-2">
        
        {{-- Judul Halaman --}}
        <div>
            <div class="inline-flex items-center gap-2 mb-1.5 px-3 py-1.5 bg-rose-50 border border-rose-100 rounded-lg">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                <span class="text-[9.5px] font-bold text-rose-600 uppercase tracking-widest">Wewenang Bidan (Read-Only)</span>
            </div>
            <div class="flex items-center gap-4 mt-2">
                <div class="w-12 h-12 rounded-[14px] bg-gradient-to-br from-indigo-50 to-blue-50 text-indigo-600 flex items-center justify-center text-[24px] shrink-0 border border-indigo-100 shadow-sm">
                    <i class="ph-fill ph-syringe"></i>
                </div>
                <div>
                    <h1 class="text-[26px] md:text-[30px] font-bold text-slate-800 tracking-tight font-poppins leading-none mb-1">Cakupan Imunisasi</h1>
                    <p class="text-slate-500 font-medium text-[13.5px]">Monitoring riwayat vaksinasi balita.</p>
                </div>
            </div>
        </div>

        {{-- Widget Statistik --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 w-full xl:w-auto shrink-0">
            <div class="nexus-panel !rounded-[20px] p-4 flex items-center gap-4 min-w-[180px]">
                <div class="w-11 h-11 rounded-[12px] bg-indigo-50 text-indigo-600 flex items-center justify-center text-[22px] shrink-0"><i class="ph-fill ph-calendar-check"></i></div>
                <div>
                    <p class="text-[9.5px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Bulan Ini</p>
                    <p class="text-[20px] font-bold text-slate-800 font-poppins leading-none">{{ $statBulanIni ?? 0 }} <span class="text-[10px] font-semibold text-slate-400 normal-case ml-0.5">Dosis</span></p>
                </div>
            </div>
            <div class="nexus-panel !rounded-[20px] p-4 flex items-center gap-4 min-w-[180px]">
                <div class="w-11 h-11 rounded-[12px] bg-sky-50 text-sky-600 flex items-center justify-center text-[22px] shrink-0"><i class="ph-fill ph-baby"></i></div>
                <div>
                    <p class="text-[9.5px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Cakupan Balita</p>
                    <p class="text-[20px] font-bold text-slate-800 font-poppins leading-none">{{ $statBalita ?? 0 }} <span class="text-[10px] font-semibold text-slate-400 normal-case ml-0.5">Anak</span></p>
                </div>
            </div>
            <div class="nexus-panel !rounded-[20px] p-4 flex items-center gap-4 min-w-[180px]">
                <div class="w-11 h-11 rounded-[12px] bg-pink-50 text-pink-600 flex items-center justify-center text-[22px] shrink-0"><i class="ph-fill ph-person-simple-walk"></i></div>
                <div>
                    <p class="text-[9.5px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Vaksin TT Bumil</p>
                    <p class="text-[20px] font-bold text-slate-800 font-poppins leading-none">{{ $statBumil ?? 0 }} <span class="text-[10px] font-semibold text-slate-400 normal-case ml-0.5">Ibu</span></p>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. SMART FILTER BAR (AJAX ENABLED) --}}
    <div class="nexus-panel p-3 mb-6">
        <form id="filterForm" action="{{ route('kader.imunisasi.index') }}" method="GET" class="flex flex-col lg:flex-row gap-3 items-stretch">
            <input type="hidden" name="kategori" id="hiddenKategori" value="{{ request('kategori', 'semua') }}">
            
            {{-- iOS Segmented Control --}}
            <div class="ios-segment flex-1 lg:max-w-[450px]">
                @php $reqKat = request('kategori', 'semua'); @endphp
                <div class="ios-btn segment-btn {{ $reqKat === 'semua' ? 'active' : '' }}" data-kategori="semua">
                    Semua Program
                </div>
                <div class="ios-btn segment-btn {{ $reqKat === 'balita' ? 'active' : '' }}" data-kategori="balita">
                    Balita
                </div>
            </div>

            {{-- Live Search Input --}}
            <div class="flex-1 relative flex items-center min-w-[280px]">
                <i class="ph-bold ph-magnifying-glass absolute left-4 text-slate-400 text-[16px]"></i>
                <input type="text" name="search" id="liveSearchInput" value="{{ request('search') }}" placeholder="Cari nama warga, NIK, atau vaksin..." class="nexus-input w-full !bg-white border-slate-200 focus:!border-indigo-500" autocomplete="off">
                <div id="searchSpinner" class="absolute right-4 top-1/2 -translate-y-1/2 hidden text-indigo-500">
                    <i class="ph-bold ph-spinner-gap animate-spin text-[16px]"></i>
                </div>
            </div>
        </form>
    </div>

    {{-- 3. KANVAS TABEL / EMPTY STATE (DYNAMIC AREA) --}}
    <div id="mainContentArea" class="nexus-panel overflow-hidden flex flex-col">
        
        @if(isset($imunisasis) && $imunisasis->count() > 0)
            <div class="overflow-x-auto c-scroll bg-white rounded-[24px] min-h-[400px]">
                <table class="w-full text-left border-collapse min-w-[950px]">
                    <thead>
                        <tr>
                            <th class="py-4 px-6 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-[15%] text-center border-b border-slate-100">Waktu Eksekusi</th>
                            <th class="py-4 px-6 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-[28%] border-b border-slate-100">Identitas Penerima</th>
                            <th class="py-4 px-6 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-[28%] border-b border-slate-100">Detail Vaksinasi</th>
                            <th class="py-4 px-6 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-[20%] border-b border-slate-100">Otoritas Medis</th>
                            <th class="py-4 px-6 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-[9%] text-right border-b border-slate-100">Arsip</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($imunisasis as $imun)
                        @php
                            $nama = $imun->nama_penerima;
                            $nik = $imun->nik_penerima;
                            $kategori = ucwords(str_replace('_', ' ', $imun->kategori_sasaran)); 
                            $badgeColor = $imun->kategori_vaksin_badge; // "sky", "pink", "indigo"
                            $icon = match($badgeColor) { 'sky' => 'ph-baby', 'pink' => 'ph-person-simple-walk', default => 'ph-syringe' };
                        @endphp
                        
                        <tr class="tr-master group/row">
                            {{-- 1. WAKTU --}}
                            <td class="py-4 px-6 text-center align-middle">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="text-[13.5px] font-bold text-slate-700">{{ $imun->tanggal_imunisasi->format('d M Y') }}</span>
                                    <span class="text-[11px] font-medium text-slate-400 mt-0.5 flex items-center gap-1"><i class="ph-fill ph-clock"></i> {{ $imun->created_at->format('H:i') }} WIB</span>
                                </div>
                            </td>

                            {{-- 2. IDENTITAS --}}
                            <td class="py-4 px-6 align-middle">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-10 h-10 rounded-full bg-slate-50 border border-slate-100 text-slate-500 flex items-center justify-center font-bold text-[16px] shrink-0 font-poppins group-hover/row:bg-indigo-50 group-hover/row:text-indigo-600 transition-colors">
                                        {{ strtoupper(substr($nama, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex flex-col items-start">
                                        <h4 class="text-[14px] font-bold text-slate-800 truncate group-hover/row:text-indigo-600 transition-colors mb-0.5" title="{{ $nama }}">{{ $nama }}</h4>
                                        <div class="flex items-center gap-1.5 text-[11px]">
                                            <span class="text-slate-500 font-medium">{{ $kategori }}</span>
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span class="text-slate-400 font-mono">{{ $nik }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- 3. DETAIL VAKSIN --}}
                            <td class="py-4 px-6 align-middle">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-{{ $badgeColor }}-50 text-{{ $badgeColor }}-600 flex items-center justify-center text-[18px] shrink-0">
                                        <i class="ph-fill {{ $icon }}"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 mb-0.5">
                                            <h4 class="text-[13.5px] font-bold text-slate-800 truncate">{{ $imun->vaksin }}</h4>
                                            <span class="text-[9px] font-bold text-{{ $badgeColor }}-700 bg-{{ $badgeColor }}-100/50 border border-{{ $badgeColor }}-200 px-2 py-0.5 rounded-md uppercase tracking-widest">Dosis {{ $imun->dosis }}</span>
                                        </div>
                                        <p class="text-[11px] font-medium text-slate-500 truncate">{{ $imun->jenis_imunisasi }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- 4. OTORITAS --}}
                            <td class="py-4 px-6 align-middle">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-500 text-[11px] font-bold shrink-0 font-poppins">
                                        {{ strtoupper(substr($imun->kunjungan?->petugas?->name ?? 'B', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex flex-col items-start">
                                        <p class="text-[12px] font-bold text-slate-700 truncate mb-0.5">{{ $imun->kunjungan?->petugas?->name ?? 'Bidan Desa' }}</p>
                                        <span class="inline-block bg-slate-100 text-slate-500 text-[8.5px] font-bold uppercase px-2 py-0.5 rounded-md tracking-widest">
                                            {{ $imun->penyelenggara }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- 5. AKSI (READ ONLY) --}}
                            <td class="py-4 px-6 text-right align-middle">
                                <a href="{{ route('kader.imunisasi.show', $imun->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-[8px] bg-white border border-slate-200 text-indigo-500 hover:text-white hover:bg-indigo-600 hover:border-indigo-600 transition-all shadow-sm" title="Lihat Sertifikat Vaksin">
                                    <i class="ph-bold ph-file-text text-[15px]"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginasi --}}
            @if($imunisasis->hasPages())
            <div id="paginationArea" class="p-6 border-t border-slate-100 bg-slate-50/30 flex justify-end">
                {{ $imunisasis->links() }}
            </div>
            @endif

        @else
            {{-- EMPTY STATE (Bersih & Elegan) --}}
            <div class="py-24 text-center bg-transparent">
                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                    <div class="w-20 h-20 bg-slate-50 rounded-full border border-slate-100 flex items-center justify-center text-slate-300 text-[40px] mb-4 relative">
                        <div class="absolute -top-1 -right-1 w-6 h-6 bg-white rounded-full flex items-center justify-center shadow-sm text-indigo-400 text-sm animate-bounce"><i class="ph-fill ph-magnifying-glass"></i></div>
                        <i class="ph-fill ph-syringe"></i>
                    </div>
                    <h4 class="text-[16px] font-bold text-slate-700 font-poppins mb-1">Riwayat Kosong</h4>
                    <p class="text-[13px] text-slate-500 font-medium">Sistem tidak menemukan arsip imunisasi yang cocok dengan filter atau kata pencarian Anda.</p>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let typingTimer;
    const searchInput = document.getElementById('liveSearchInput');
    const contentArea = document.getElementById('mainContentArea');
    const spinner = document.getElementById('searchSpinner');
    const form = document.getElementById('filterForm');
    const hiddenKategori = document.getElementById('hiddenKategori');

    // ENGINE AJAX SUPER MULUS
    async function fetchRealTimeData(url, isSearch = false) {
        if(isSearch) spinner.classList.remove('hidden');
        contentArea.classList.add('is-loading');
        
        try {
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
            const html = await response.text();
            
            const doc = new DOMParser().parseFromString(html, 'text/html');
            contentArea.innerHTML = doc.getElementById('mainContentArea').innerHTML;
            
            window.history.pushState({}, '', url);
            bindPagination();
        } catch (error) {
            console.error("Gagal memuat data EHR:", error);
        } finally {
            if(isSearch) spinner.classList.add('hidden');
            contentArea.classList.remove('is-loading');
        }
    }

    // PENCARIAN KETIK (DEBOUNCE)
    if(searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                const url = new URL(form.action);
                url.searchParams.set('search', e.target.value);
                url.searchParams.set('kategori', hiddenKategori.value);
                fetchRealTimeData(url.toString(), true);
            }, 350); 
        });
    }

    // EVENT KLIK TAB KATEGORI (SEGMENTED CONTROL)
    document.querySelectorAll('.segment-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            document.querySelectorAll('.segment-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const kat = this.dataset.kategori;
            hiddenKategori.value = kat;
            
            const url = new URL(form.action);
            url.searchParams.set('kategori', kat);
            url.searchParams.set('search', searchInput ? searchInput.value : '');
            fetchRealTimeData(url.toString(), false);
        });
    });

    // EVENT PAGINATION AJAX
    function bindPagination() {
        document.querySelectorAll('#paginationArea a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault(); e.stopPropagation();
                fetchRealTimeData(this.href, false);
            });
        });
    }
    
    window.addEventListener('popstate', function() { fetchRealTimeData(window.location.href, false); });
    bindPagination();
});
</script>
@endpush
@endsection