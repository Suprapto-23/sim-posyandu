@extends('layouts.user')

@section('title', 'Agenda Posyandu')
@section('page-title', 'Jadwal Kegiatan')

@php
    // LOGIKA CERDAS: Hitung berapa banyak kategori spesifik yang dimiliki user
    $specificAccess = array_filter($hakAkses, fn($h) => $h !== 'semua');
    $hasMultipleAccess = count($specificAccess) > 1;
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
        border-radius: 2rem; 
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
        transition: all 0.3s ease;
    }
    .widget-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 40px -10px rgba(16, 185, 129, 0.12);
        border-color: rgba(16, 185, 129, 0.3);
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.96); }

    /* Custom Scrollbar untuk Tabs */
    .custom-scrollbar::-webkit-scrollbar { height: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(16, 185, 129, 0.2); border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(16, 185, 129, 0.5); }

    /* Dot Pulse Indicator */
    .live-indicator {
        display: inline-block;
        width: 8px; height: 8px;
        background-color: #10b981;
        border-radius: 50%;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulse-emerald 2s infinite;
    }
    @keyframes pulse-emerald {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
</style>
@endpush

@section('content')
<div class="max-w-[1280px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6 space-y-6">

    {{-- 1. HERO WIDGET --}}
    <section class="bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-600 rounded-[3rem] p-8 md:p-10 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(16,185,129,.35)] border border-white/20">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/15 blur-[80px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row gap-8 lg:items-center justify-between">
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 text-white/90 text-[10px] font-black uppercase tracking-widest mb-4">
                    <span class="bg-white/20 backdrop-blur-md border border-white/30 px-3 py-1.5 rounded-full shadow-sm flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-300 animate-pulse"></span>
                        Sinkronisasi Real-time Aktif
                    </span>
                </div>

                <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-3">
                    Agenda & Jadwal Posyandu
                </h1>

                <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    Berikut adalah daftar jadwal Posyandu yang relevan dengan Anda dan keluarga. Informasi lokasi dan waktu akan diperbarui secara otomatis jika ada perubahan dari pihak Bidan atau Kader.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto shrink-0 justify-center">
                <div class="bg-white/20 border border-white/30 backdrop-blur-md rounded-[2rem] p-5 text-center shadow-inner">
                    <p class="text-[10px] text-teal-100 font-black uppercase tracking-widest mb-1">Total Agenda Anda</p>
                    <p class="text-4xl font-black text-white" id="summary-bulan-ini">{{ $summary['semua'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. TABS FILTERATAU LABEL SASARAN --}}
    <div id="jadwal-tabs-container">
        @if($hasMultipleAccess)
            <div id="jadwal-tabs" class="flex overflow-x-auto custom-scrollbar pb-2 pt-2 gap-3 justify-start lg:justify-center">
                
                <a href="{{ route('user.jadwal.index', ['filter' => 'semua']) }}" 
                   data-no-delay="true"
                   class="whitespace-nowrap flex items-center gap-2 px-6 py-3 rounded-2xl border font-black text-xs transition-all {{ $filterTarget === 'semua' ? 'bg-teal-600 text-white border-teal-600 shadow-md shadow-teal-500/20' : 'bg-white text-slate-500 border-slate-200 hover:border-teal-300 hover:bg-teal-50' }}">
                    Semua Jadwal
                    <span class="px-2 py-0.5 rounded-lg text-[10px] {{ $filterTarget === 'semua' ? 'bg-teal-500 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $summary['semua'] }}</span>
                </a>

                @if(in_array('balita', $hakAkses))
                    <a href="{{ route('user.jadwal.index', ['filter' => 'balita']) }}" 
                       data-no-delay="true"
                       class="whitespace-nowrap flex items-center gap-2 px-6 py-3 rounded-2xl border font-black text-xs transition-all {{ $filterTarget === 'balita' ? 'bg-rose-500 text-white border-rose-500 shadow-md shadow-rose-500/20' : 'bg-white text-slate-500 border-slate-200 hover:border-rose-300 hover:bg-rose-50' }}">
                        <i class="fas fa-child-reaching {{ $filterTarget === 'balita' ? 'text-white' : 'text-rose-500' }}"></i> Balita
                        <span class="px-2 py-0.5 rounded-lg text-[10px] {{ $filterTarget === 'balita' ? 'bg-rose-400 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $summary['balita'] }}</span>
                    </a>
                @endif

                @if(in_array('remaja', $hakAkses))
                    <a href="{{ route('user.jadwal.index', ['filter' => 'remaja']) }}" 
                       data-no-delay="true"
                       class="whitespace-nowrap flex items-center gap-2 px-6 py-3 rounded-2xl border font-black text-xs transition-all {{ $filterTarget === 'remaja' ? 'bg-sky-500 text-white border-sky-500 shadow-md shadow-sky-500/20' : 'bg-white text-slate-500 border-slate-200 hover:border-sky-300 hover:bg-sky-50' }}">
                        <i class="fas fa-user-graduate {{ $filterTarget === 'remaja' ? 'text-white' : 'text-sky-500' }}"></i> Remaja
                        <span class="px-2 py-0.5 rounded-lg text-[10px] {{ $filterTarget === 'remaja' ? 'bg-sky-400 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $summary['remaja'] }}</span>
                    </a>
                @endif

                @if(in_array('lansia', $hakAkses))
                    <a href="{{ route('user.jadwal.index', ['filter' => 'lansia']) }}" 
                       data-no-delay="true"
                       class="whitespace-nowrap flex items-center gap-2 px-6 py-3 rounded-2xl border font-black text-xs transition-all {{ $filterTarget === 'lansia' ? 'bg-amber-500 text-white border-amber-500 shadow-md shadow-amber-500/20' : 'bg-white text-slate-500 border-slate-200 hover:border-amber-300 hover:bg-amber-50' }}">
                        <i class="fas fa-person-cane {{ $filterTarget === 'lansia' ? 'text-white' : 'text-amber-500' }}"></i> Lansia
                        <span class="px-2 py-0.5 rounded-lg text-[10px] {{ $filterTarget === 'lansia' ? 'bg-amber-400 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $summary['lansia'] }}</span>
                    </a>
                @endif
            </div>
        @else
            {{-- PERUBAHAN: Dipindah ke kiri (justify-start) agar bertindak seperti judul bagian --}}
            @if(count($specificAccess) === 1)
                @php
                    $singleAccess = array_values($specificAccess)[0];
                    $singleLabel = match($singleAccess) {
                        'balita' => 'Jadwal Khusus Balita',
                        'remaja' => 'Jadwal Khusus Remaja',
                        'lansia' => 'Jadwal Khusus Lansia',
                        default => 'Jadwal Umum'
                    };
                    $singleIcon = match($singleAccess) {
                        'balita' => 'fa-child-reaching',
                        'remaja' => 'fa-user-graduate',
                        'lansia' => 'fa-person-cane',
                        default => 'fa-users'
                    };
                @endphp
                <div class="flex justify-start mb-4 mt-2 px-1">
                    <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white border border-slate-200 text-xs font-black text-slate-600 shadow-sm cursor-default">
                        <i class="fa-solid {{ $singleIcon }} text-teal-500 text-sm"></i> {{ $singleLabel }}
                    </span>
                </div>
            @endif
        @endif
    </div>

    {{-- 3. GRID JADWAL (Akan di-update via AJAX) --}}
    <div id="jadwal-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative">
        
        {{-- Loader Indikator (Disembunyikan, dipanggil via JS) --}}
        <div id="live-loader" class="absolute top-0 right-0 -mt-8 mr-2 hidden items-center gap-2">
            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-500">Memperbarui...</span>
            <span class="live-indicator"></span>
        </div>

        @forelse($jadwalCards as $card)
            @php
                // Mapping warna berdasarkan data Controller (status_tone & target_tone)
                $statusColor = match($card['status_tone']) {
                    'emerald' => 'bg-emerald-500 text-white',
                    'sky'     => 'bg-sky-500 text-white',
                    'amber'   => 'bg-amber-500 text-white',
                    default   => 'bg-slate-200 text-slate-500',
                };

                $targetColor = match($card['target_tone']) {
                    'rose'  => 'text-rose-600 bg-rose-50 border-rose-100',
                    'sky'   => 'text-sky-600 bg-sky-50 border-sky-100',
                    'amber' => 'text-amber-600 bg-amber-50 border-amber-100',
                    default => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                };
            @endphp

            <div class="widget-card flex flex-col relative group h-full overflow-hidden {{ $card['is_past'] ? 'opacity-75 grayscale-[20%]' : '' }}">
                
                {{-- Badge Status Hari --}}
                <div class="absolute top-0 right-0 {{ $statusColor }} text-[9px] font-black uppercase tracking-widest px-4 py-1.5 rounded-bl-2xl z-10 shadow-sm">
                    {{ $card['status_label'] }}
                </div>

                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex items-start gap-4 mb-6">
                        {{-- Ikon Kalender --}}
                        <div class="w-16 h-16 rounded-[1rem] {{ $card['is_today'] ? 'bg-emerald-500 text-white shadow-md' : ($card['is_past'] ? 'bg-slate-100 text-slate-400' : 'bg-slate-50 text-slate-700 border border-slate-200') }} flex flex-col items-center justify-center shrink-0">
                            <span class="text-[10px] font-black uppercase tracking-widest">{{ $card['bulan'] }}</span>
                            <span class="text-2xl font-black leading-none mt-0.5">{{ $card['hari'] }}</span>
                        </div>
                        
                        <div class="min-w-0 pr-6">
                            <span class="inline-block px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest mb-2 border {{ $targetColor }}">
                                {{ $card['target_label'] }}
                            </span>
                            <h3 class="text-base font-black text-slate-800 leading-tight group-hover:text-teal-600 transition-colors truncate" title="{{ $card['judul'] }}">
                                {{ $card['judul'] }}
                            </h3>
                        </div>
                    </div>

                    {{-- Informasi Waktu & Lokasi Presisi di Bawah --}}
                    <div class="space-y-3 mt-auto pt-5 border-t border-slate-100/80">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 shrink-0">
                                <i class="far fa-clock text-xs"></i>
                            </div>
                            <div class="pt-0.5">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Waktu</p>
                                <p class="text-xs font-bold text-slate-700 mt-0.5">{{ $card['waktu'] }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 shrink-0">
                                <i class="fas fa-map-marker-alt text-xs"></i>
                            </div>
                            <div class="pt-0.5 min-w-0">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Lokasi</p>
                                <p class="text-xs font-bold text-slate-700 mt-0.5 truncate" title="{{ $card['lokasi'] }}">{{ $card['lokasi'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 flex flex-col items-center justify-center bg-white/50 backdrop-blur-sm rounded-[3rem] border-2 border-dashed border-slate-200">
                <div class="w-20 h-20 bg-white text-slate-300 rounded-[1.5rem] flex items-center justify-center text-3xl mb-5 shadow-sm border border-slate-100">
                    <i class="far fa-calendar-times"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800 mb-1">Jadwal Belum Tersedia</h3>
                <p class="text-sm font-medium text-slate-500 text-center max-w-md leading-relaxed">Belum ada agenda Posyandu untuk kategori yang Anda pilih saat ini.</p>
                @if($filterTarget !== 'semua' && $hasMultipleAccess)
                    <a href="{{ route('user.jadwal.index') }}" data-no-delay="true" class="mt-6 btn-pill font-black text-xs text-white bg-teal-500 hover:bg-teal-600 px-6 py-3 shadow-md transition-colors">
                        Lihat Semua Jadwal
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    {{-- 4. PAGINATION (Akan di-update via AJAX) --}}
    <div id="jadwal-pagination">
        @if($jadwalKegiatan->hasPages())
            <div class="mt-8 flex justify-center bg-white/50 backdrop-blur-sm py-3 px-6 rounded-full border border-slate-200 shadow-sm w-fit mx-auto">
                {{ $jadwalKegiatan->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const url = new URL(window.location.href);
    const liveLoader = document.getElementById('live-loader');

    // FUNGSI POLLING REAL-TIME (SILENT FETCH)
    async function fetchLatestJadwal() {
        try {
            // Tampilkan indikator "Memperbarui..." secara halus
            if(liveLoader) {
                liveLoader.style.display = 'flex';
                liveLoader.style.opacity = '0';
                setTimeout(() => liveLoader.style.opacity = '1', 50);
            }

            // Fetch data HTML dari server tanpa reload browser
            const response = await fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (!response.ok) return;

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Ekstrak elemen-elemen penting
            const newGrid = doc.getElementById('jadwal-grid');
            const newTabsContainer = doc.getElementById('jadwal-tabs-container');
            const newPagination = doc.getElementById('jadwal-pagination');
            const newSummaryBulanIni = doc.getElementById('summary-bulan-ini');

            // Inject (Replace) isi HTML jika ditemukan, tanpa berkedip!
            if (newGrid) document.getElementById('jadwal-grid').innerHTML = newGrid.innerHTML;
            if (newTabsContainer && document.getElementById('jadwal-tabs-container')) {
                document.getElementById('jadwal-tabs-container').innerHTML = newTabsContainer.innerHTML;
            }
            if (newPagination) document.getElementById('jadwal-pagination').innerHTML = newPagination.innerHTML;
            
            if (newSummaryBulanIni && document.getElementById('summary-bulan-ini')) {
                document.getElementById('summary-bulan-ini').innerHTML = newSummaryBulanIni.innerHTML;
            }

            // Sembunyikan indikator kembali
            if(liveLoader) {
                liveLoader.style.opacity = '0';
                setTimeout(() => liveLoader.style.display = 'none', 300);
            }

        } catch (error) {
            console.warn('Silent update gagal:', error);
            if(liveLoader) liveLoader.style.display = 'none';
        }
    }

    // Jalankan polling setiap 30 detik
    setInterval(fetchLatestJadwal, 30000);
});
</script>
@endpush