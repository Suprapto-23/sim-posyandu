@extends('layouts.kader')

@section('title', 'Riwayat Absensi Posyandu')
@section('page-name', 'Arsip Sesi Absensi')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    /* ====================================================================
       1. GLOBAL OPTIMIZATION & ANTI-LAG ENGINE
       ==================================================================== */
    html { scroll-behavior: smooth; }
    body { 
        background-color: #f8fafc; 
        -webkit-font-smoothing: antialiased; 
        text-rendering: optimizeLegibility;
    }
    .gpu-accel { transform: translateZ(0); will-change: transform, opacity; }

    /* ====================================================================
       2. SNAPPY ENTRANCE ANIMATIONS (120 FPS FEEL)
       ==================================================================== */
    @keyframes snappyFadeUp {
        0% { opacity: 0; transform: translateY(15px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    
    .stagger-fast > * {
        opacity: 0;
        animation: snappyFadeUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    
    .stagger-fast > *:nth-child(1) { animation-delay: 40ms; }
    .stagger-fast > *:nth-child(2) { animation-delay: 80ms; }
    .stagger-fast > *:nth-child(3) { animation-delay: 120ms; }
    .stagger-fast > *:nth-child(4) { animation-delay: 160ms; }
    .stagger-fast > *:nth-child(5) { animation-delay: 200ms; }
    .stagger-fast > *:nth-child(6) { animation-delay: 240ms; }
    .stagger-fast > *:nth-child(n+7) { animation-delay: 280ms; }

    /* ====================================================================
       3. NATIVE SELECT & CARD STYLING (PIXEL PERFECT)
       ==================================================================== */
    .nexus-select {
        appearance: none; -webkit-appearance: none;
        background-color: #ffffff; border: 1px solid #e2e8f0; color: #1e293b;
        font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.85rem; font-weight: 700;
        border-radius: 14px; padding: 0.7rem 2.5rem 0.7rem 1.25rem; width: 100%; cursor: pointer;
        transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 1rem center; background-size: 1rem;
    }
    .nexus-select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); outline: none; }
    .nexus-select:hover { border-color: #cbd5e1; }

    /* Desain List Row Terpadu (Sleek List) */
    .history-row {
        background: #ffffff; border: 1px solid #f1f5f9; border-radius: 20px;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex; flex-direction: column;
    }
    @media (min-width: 1024px) {
        .history-row { flex-direction: row; align-items: center; }
    }
    .history-row:hover {
        border-color: #cbd5e1; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
        transform: translateY(-2px); z-index: 10; position: relative;
    }

    /* Progress Bar (Tipis & Elegan) */
    .progress-track { width: 100%; height: 6px; background-color: #f1f5f9; border-radius: 99px; overflow: hidden; }
    .progress-fill { height: 100%; border-radius: 99px; transition: width 1s ease-out; }

    /* ====================================================================
       4. SWEETALERT 2 - CLEAN UI
       ==================================================================== */
    div:where(.swal2-container).swal2-backdrop-show { background: rgba(15, 23, 42, 0.5) !important; backdrop-filter: blur(4px) !important; z-index: 99999 !important; }
    .swal2-popup:not(.swal2-toast) { border-radius: 24px !important; padding: 2.5rem 2rem 2rem !important; background: #ffffff !important; width: 24em !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15) !important; border: none !important; }
    .swal2-title { font-family: 'Poppins', sans-serif !important; font-weight: 900 !important; font-size: 1.3rem !important; color: #1e293b !important; padding-top: 0 !important; }
    .swal2-html-container { font-family: 'Plus Jakarta Sans', sans-serif !important; color: #64748b !important; font-size: 0.85rem !important; line-height: 1.6 !important; margin: 1em 0 0.5em !important; }
    .swal2-actions { gap: 10px !important; margin-top: 1.5rem !important; width: 100% !important; justify-content: center !important; }
    
    .btn-swal-danger { background: #f43f5e !important; color: white !important; border-radius: 9999px !important; padding: 12px 28px !important; font-size: 11px !important; font-weight: 900 !important; text-transform: uppercase !important; letter-spacing: 0.05em !important; box-shadow: 0 4px 15px -3px rgba(244,63,94,0.3) !important; border: none !important; transition: all 0.2s ease !important; }
    .btn-swal-danger:hover { background: #e11d48 !important; transform: translateY(-2px) !important; }
    
    .btn-swal-cancel { background: #f1f5f9 !important; color: #475569 !important; border-radius: 9999px !important; padding: 12px 28px !important; font-size: 11px !important; font-weight: 900 !important; text-transform: uppercase !important; letter-spacing: 0.05em !important; border: none !important; transition: all 0.2s ease !important; }
    .btn-swal-cancel:hover { background: #e2e8f0 !important; color: #1e293b !important; }

    /* TOAST (NOTIFIKASI POJOK KANAN ATAS - SUPER KECIL & PRESISI) */
    div:where(.swal2-container).swal2-top-end { pointer-events: none !important; }
    div:where(.swal2-container).swal2-top-end > .swal2-toast {
        pointer-events: auto !important; background: #ffffff !important; border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important; padding: 12px 20px !important; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1) !important;
        width: auto !important; display: flex !important; align-items: center !important; margin-top: 1rem !important; margin-right: 1rem !important;
    }
    div:where(.swal2-container).swal2-top-end .swal2-icon { transform: scale(0.6) !important; margin: 0 10px 0 -4px !important; }
    div:where(.swal2-container).swal2-top-end .swal2-title { font-family: 'Plus Jakarta Sans', sans-serif !important; font-size: 13px !important; font-weight: 800 !important; color: #1e293b !important; margin: 0 !important; padding: 0 !important; }
</style>
@endpush

@section('content')
{{-- PRELOADER SISTEM (SNAPPY) --}}
<div id="smoothLoader" class="fixed inset-0 bg-slate-50/90 backdrop-blur-sm z-[9999] flex flex-col items-center justify-center transition-all duration-200 opacity-100 pointer-events-auto">
    <div class="w-10 h-10 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin mb-3"></div>
    <p class="text-indigo-900 font-black tracking-widest text-[9px] uppercase font-poppins">MEMUAT...</p>
</div>

<div class="max-w-[1200px] mx-auto pb-16 mt-2 relative z-10 gpu-accel stagger-fast">

    {{-- AURA BACKGROUND (Super Ringan) --}}
    <div class="fixed top-0 right-0 w-full h-full bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-500/5 via-transparent to-transparent pointer-events-none -z-10"></div>

    {{-- TEKS HEADER UTAMA --}}
    <div class="mb-6 px-1">
        <h1 class="text-[22px] md:text-[24px] font-black text-slate-800 tracking-tight font-poppins leading-none mb-1.5">Arsip Sesi Absensi</h1>
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Manajemen &bull; Riwayat Kehadiran</p>
    </div>

    {{-- 1. HEADER (Banner Utama) --}}
    <div class="bg-white rounded-[24px] border border-slate-100 shadow-sm p-6 md:p-8 mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="absolute right-0 top-0 w-64 h-64 bg-indigo-50 rounded-bl-full blur-2xl pointer-events-none z-0"></div>
        
        <div class="flex items-center gap-5 relative z-10">
            <div class="w-14 h-14 rounded-[16px] bg-indigo-600 text-white flex items-center justify-center text-[28px] shadow-lg shadow-indigo-200 shrink-0 transform -rotate-3">
                <i class="ph-fill ph-archive-box"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-[24px] font-black text-slate-800 tracking-tight font-poppins leading-none mb-1">Log Kehadiran Warga</h2>
                <p class="text-slate-500 font-medium text-[12px] flex items-center gap-1.5">Kelola dan tinjau riwayat pendaftaran posyandu.</p>
            </div>
        </div>
    </div>

    {{-- 2. PANEL FILTER (Satu Baris, Memakai Native Select Premium) --}}
    @php
        $reqKategori = request('kategori', '');
        $reqBulanStr = request('bulan');
        $selTahun = $reqBulanStr ? substr($reqBulanStr, 0, 4) : '';
        $selBulan = $reqBulanStr ? substr($reqBulanStr, 5, 2) : '';
        $tahunSaatIni = date('Y');

        $mapBulan = ['01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];
    @endphp

    <div class="bg-white p-4 sm:p-5 rounded-[24px] border border-slate-200 shadow-sm mb-8 flex flex-col xl:flex-row items-center gap-3 relative z-20">
        <form id="filterForm" action="{{ route('kader.absensi.riwayat') }}" method="GET" class="w-full flex flex-col md:flex-row items-center gap-3">
            <input type="hidden" name="bulan" id="hiddenBulan" value="{{ request('bulan') }}">

            <div class="w-full md:w-[35%] relative group">
                <select name="kategori" class="nexus-select group-hover:border-indigo-300">
                    <option value="">Semua Kategori Sasaran</option>
                    <option value="bayi" {{ $reqKategori == 'bayi' ? 'selected' : '' }}>Bayi (<1 Tahun)</option>
                    <option value="balita" {{ $reqKategori == 'balita' ? 'selected' : '' }}>Balita (1-5 Tahun)</option>
                    <option value="ibu_hamil" {{ $reqKategori == 'ibu_hamil' ? 'selected' : '' }}>Ibu Hamil</option>
                    <option value="remaja" {{ $reqKategori == 'remaja' ? 'selected' : '' }}>Remaja</option>
                    <option value="lansia" {{ $reqKategori == 'lansia' ? 'selected' : '' }}>Lansia</option>
                </select>
            </div>

            <div class="w-full md:w-[25%] relative group">
                <select id="valBulan" class="nexus-select group-hover:border-indigo-300">
                    <option value="">Pilih Bulan</option>
                    @foreach($mapBulan as $val => $label)
                        <option value="{{ $val }}" {{ $selBulan == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-[20%] relative group">
                <select id="valTahun" class="nexus-select group-hover:border-indigo-300">
                    <option value="">Tahun</option>
                    @for($y = $tahunSaatIni; $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ $selTahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="w-full md:w-[20%] flex items-center gap-2">
                <button type="submit" onclick="window.showLoader()" class="flex-1 bg-slate-800 text-white font-black text-[11px] uppercase tracking-widest rounded-[14px] py-3.5 hover:bg-indigo-600 hover:-translate-y-0.5 transition-all shadow-sm flex items-center justify-center gap-2 active:scale-95">
                    <i class="ph-bold ph-funnel text-[14px]"></i> Saring
                </button>
                @if(request('kategori') || request('bulan'))
                    <a href="{{ route('kader.absensi.riwayat') }}" onclick="window.showLoader()" class="w-[46px] h-[46px] shrink-0 bg-rose-50 text-rose-500 rounded-[14px] flex items-center justify-center hover:bg-rose-500 hover:text-white transition-colors border border-rose-100 shadow-sm active:scale-95" title="Reset Filter">
                        <i class="ph-bold ph-arrow-counter-clockwise text-[16px]"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- 3. DAFTAR RIWAYAT (Sleek List - Super Rapih) --}}
    @if(count($riwayat) > 0)
        <div class="space-y-4 relative z-10" id="riwayatList">
            @foreach($riwayat as $index => $item)
                @php
                    $totalPasien = $item->details->count();
                    $totalHadir  = $item->details->where('hadir', true)->count();
                    $persentase  = $totalPasien > 0 ? round(($totalHadir / $totalPasien) * 100) : 0;
                    
                    $color = 'emerald'; 
                    if($persentase < 50) $color = 'rose'; 
                    elseif($persentase < 75) $color = 'amber'; 

                    $tgl = \Carbon\Carbon::parse($item->tanggal_posyandu)->locale('id');
                @endphp

                <div class="history-row p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                    
                    {{-- Blok Informasi (Kiri) --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4 flex-1 min-w-0">
                        <div class="w-14 h-14 bg-indigo-50 border border-indigo-100 rounded-[14px] flex flex-col items-center justify-center shrink-0 text-indigo-600 shadow-sm">
                            <span class="text-[18px] font-black leading-none font-poppins">{{ $tgl->format('d') }}</span>
                            <span class="text-[9px] font-bold uppercase tracking-widest mt-1">{{ $tgl->translatedFormat('M y') }}</span>
                        </div>
                        
                        <div class="min-w-0">
                            <h4 class="text-[15px] font-black text-slate-800 font-poppins truncate mb-1.5" title="{{ $item->kode_absensi }}">{{ $item->kode_absensi }}</h4>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border border-slate-200 text-slate-500 bg-slate-50">
                                    {{ str_replace('_', ' ', $item->kategori) }}
                                </span>
                                <span class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border border-indigo-200 text-indigo-600 bg-indigo-50">
                                    Pertemuan #{{ $item->nomor_pertemuan }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-bold ml-1 flex items-center gap-1"><i class="ph-bold ph-clock"></i> {{ $item->created_at->format('H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Progress Bar Tengah --}}
                    <div class="w-full lg:w-[280px] shrink-0 mt-2 lg:mt-0">
                        <div class="flex justify-between items-end mb-2">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kehadiran <span class="text-slate-700 ml-1">{{ $totalHadir }}/{{ $totalPasien }}</span></p>
                            <p class="text-[13px] font-black text-{{ $color }}-500 font-poppins">{{ $persentase }}%</p>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill bg-{{ $color }}-500" style="width: {{ $persentase }}%;"></div>
                        </div>
                    </div>

                    {{-- Aksi Kanan --}}
                    <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto mt-4 lg:mt-0">
                        <a href="{{ route('kader.absensi.show', $item->id) }}" onclick="window.showLoader()" class="flex-1 sm:flex-none px-5 py-2.5 bg-white border border-slate-200 text-slate-600 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-600 font-black text-[10px] uppercase tracking-widest rounded-[12px] transition-colors flex items-center justify-center gap-1.5 shadow-sm active:scale-95">
                            <i class="ph-bold ph-eye text-[14px]"></i> Detail
                        </a>
                        <form action="{{ route('kader.absensi.destroy', $item->id) }}" method="POST" class="delete-form m-0 flex-1 sm:flex-none">
                            @csrf @method('DELETE')
                            <button type="button" class="btn-delete w-full sm:w-[42px] h-[42px] bg-white border border-slate-200 text-slate-400 hover:bg-rose-50 hover:text-rose-500 hover:border-rose-200 rounded-[12px] transition-colors flex items-center justify-center shadow-sm active:scale-95" title="Hapus Riwayat">
                                <i class="ph-bold ph-trash text-[16px]"></i>
                                <span class="sm:hidden ml-2 font-black text-[10px] uppercase tracking-widest">Hapus</span>
                            </button>
                        </form>
                    </div>

                </div>
            @endforeach
        </div>
    @else
        {{-- EMPTY STATE MODERN --}}
        <div class="text-center py-20 px-4 bg-white rounded-[28px] border border-slate-200 shadow-sm relative z-10">
            <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-[16px] flex items-center justify-center text-slate-300 text-[32px] mx-auto mb-4">
                <i class="ph-fill ph-folder-notch-open"></i>
            </div>
            <h4 class="text-[15px] font-black text-slate-700 uppercase tracking-widest mb-1 font-poppins">Arsip Tidak Ditemukan</h4>
            <p class="text-[12px] text-slate-500 font-medium max-w-sm mx-auto">Sistem tidak menemukan log kehadiran yang cocok dengan filter yang Anda berikan.</p>
        </div>
    @endif

    {{-- PAGINASI --}}
    <div class="mt-8">
        {{ $riwayat->links() }}
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // === LOADER ENGINE ===
    window.hideLoader = () => { const l = document.getElementById('smoothLoader'); if(l) { l.classList.remove('opacity-100','pointer-events-auto'); l.classList.add('opacity-0','pointer-events-none'); setTimeout(()=> l.style.display = 'none', 200); } };
    window.showLoader = () => { const l = document.getElementById('smoothLoader'); if(l) { l.style.display = 'flex'; l.classList.remove('opacity-0','pointer-events-none'); l.classList.add('opacity-100','pointer-events-auto'); } };

    window.onload = hideLoader;
    document.addEventListener('DOMContentLoaded', hideLoader);
    window.addEventListener('pageshow', hideLoader);

    // === VALIDASI FILTER ===
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        let m = document.getElementById('valBulan').value;
        let y = document.getElementById('valTahun').value;
        
        if (m && y) {
            document.getElementById('hiddenBulan').value = y + '-' + m;
        } else if (m || y) {
            e.preventDefault();
            hideLoader();
            Swal.fire({
                title: 'Filter Tidak Lengkap',
                html: 'Harap pilih <b class="text-indigo-600 font-bold">Bulan</b> dan <b class="text-indigo-600 font-bold">Tahun</b> sekaligus untuk melakukan pencarian.',
                icon: 'info',
                confirmButtonText: '<i class="ph-bold ph-check mr-1"></i> Mengerti',
                buttonsStyling: false,
                backdrop: true, 
                customClass: { popup: 'swal2-popup', confirmButton: 'btn-swal-cancel' }
            });
        } else {
            document.getElementById('hiddenBulan').value = '';
        }
    });

    // === HAPUS DENGAN SWEETALERT (Aman & Elegan) ===
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('.delete-form');
            Swal.fire({
                title: 'Hapus Permanen?',
                html: 'Arsip presensi pada sesi tersebut beserta detail kehadirannya akan <b class="text-rose-500 font-bold">dihapus dari sistem</b>.',
                icon: 'warning', 
                showCancelButton: true,
                buttonsStyling: false,
                reverseButtons: true,
                confirmButtonText: '<i class="ph-bold ph-trash mr-1"></i> Ya, Hapus',
                cancelButtonText: 'Batal', 
                backdrop: true, 
                customClass: { 
                    popup: 'swal2-popup', 
                    confirmButton: 'btn-swal-danger',
                    cancelButton: 'btn-swal-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoader();
                    form.submit();
                }
            });
        });
    });

    // === NOTIFIKASI TOAST SUKSES (Mungil & Bersih) ===
    @if(session('success'))
        const Toast = Swal.mixin({
            toast: true, 
            position: 'top-end', 
            showConfirmButton: false, 
            timer: 3000,
            backdrop: false,
        });
        
        Toast.fire({ 
            icon: 'success', 
            title: "{{ session('success') }}" 
        });
    @endif
</script>
@endpush
@endsection