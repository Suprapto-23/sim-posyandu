@extends('layouts.kader')

@section('title', 'Riwayat Absensi Posyandu')
@section('page-name', 'Arsip Sesi Absensi')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    /* ====================================================================
       1. GLOBAL HEALTHCARE EMERALD THEME
       ==================================================================== */
    html { scroll-behavior: smooth; }
    body { 
        background-color: #f0fdf4; 
        background-image: 
            radial-gradient(at 100% 0%, hsla(148, 100%, 97%, 1) 0, transparent 50%), 
            radial-gradient(at 0% 100%, hsla(45, 100%, 96%, 1) 0, transparent 50%);
        background-attachment: fixed;
        -webkit-font-smoothing: antialiased; 
    }
    .gpu-layer { transform: translateZ(0); will-change: transform, opacity; }

    /* ====================================================================
       2. MODERN GLASSMORPHISM COMPONENTS
       ==================================================================== */
    .glass-panel {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.7);
        box-shadow: 0 12px 40px -12px rgba(6, 78, 59, 0.06);
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.55);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .glass-card:hover {
        background: rgba(255, 255, 255, 0.95);
        border-color: rgba(16, 185, 129, 0.4);
        transform: translateY(-2px);
        box-shadow: 0 14px 28px -6px rgba(6, 78, 59, 0.08);
    }

    /* ====================================================================
       3. PREMIUM NATIVE SELECTS & FILTERS
       ==================================================================== */
    .premium-select {
        appearance: none; -webkit-appearance: none;
        background-color: rgba(255, 255, 255, 0.7); 
        border: 1px solid rgba(16, 185, 129, 0.25); 
        color: #064e3b; font-size: 0.85rem; font-weight: 700;
        border-radius: 14px; padding: 0.7rem 2.5rem 0.7rem 1.25rem; width: 100%; cursor: pointer;
        transition: all 0.25s ease; backdrop-filter: blur(8px);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23059669'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 1rem center; background-size: 1rem;
    }
    .premium-select:focus { background-color: #ffffff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15); outline: none; }

    /* List Row History System */
    .history-list-row {
        background: rgba(255, 255, 255, 0.6); border: 1px solid rgba(255, 255, 255, 0.7); border-radius: 18px;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex; flex-direction: column;
    }
    @media (min-width: 1024px) {
        .history-list-row { flex-direction: row; align-items: center; }
    }
    .history-list-row:hover {
        background: #ffffff; border-color: rgba(16, 185, 129, 0.3);
        box-shadow: 0 12px 24px -6px rgba(6, 78, 59, 0.06); transform: translateY(-2px);
    }

    /* Progress Indicator Bar */
    .bar-track { width: 100%; height: 6px; background-color: #e2e8f0; border-radius: 99px; overflow: hidden; }
    .bar-fill { height: 100%; border-radius: 99px; transition: width 0.8s ease-out; }

    /* ====================================================================
       4. ANIMATIONS STAGGER
       ==================================================================== */
    @keyframes snappyFadeUp {
        0% { opacity: 0; transform: translateY(15px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .stagger-list > * { opacity: 0; animation: snappyFadeUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .stagger-list > *:nth-child(1) { animation-delay: 40ms; }
    .stagger-list > *:nth-child(2) { animation-delay: 80ms; }
    .stagger-list > *:nth-child(3) { animation-delay: 120ms; }
    .stagger-list > *:nth-child(4) { animation-delay: 160ms; }
</style>
@endpush

@section('content')
{{-- PRELOADER ENGINES --}}
<div id="smoothLoader" class="fixed inset-0 bg-emerald-950/20 backdrop-blur-md z-[9999] flex flex-col items-center justify-center transition-all duration-200 opacity-100 pointer-events-auto">
    <div class="w-10 h-10 border-4 border-emerald-200 border-t-emerald-600 rounded-full animate-spin mb-3"></div>
    <p class="text-emerald-900 font-black tracking-widest text-[9px] uppercase font-poppins">Memuat Arsip...</p>
</div>

<div class="max-w-[1300px] mx-auto pb-16 mt-2 relative z-10 gpu-layer stagger-list">

    {{-- HEADER BRAND --}}
    <div class="mb-6 px-1">
        <h1 class="text-[24px] md:text-[26px] font-black text-emerald-950 tracking-tight font-poppins leading-none mb-1.5">Arsip Sesi Absensi</h1>
        <p class="text-[10px] font-bold text-emerald-700/80 uppercase tracking-widest">Manajemen Rekam Kehadiran &bull; Database Historis</p>
    </div>

    {{-- 1. MAIN ACCENT BANNER --}}
    <div class="bg-white rounded-[24px] border border-emerald-100 shadow-sm p-6 md:p-8 mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="absolute right-0 top-0 w-64 h-64 bg-emerald-50 rounded-bl-full blur-2xl pointer-events-none z-0"></div>
        
        <div class="flex items-center gap-4 relative z-10">
            <div class="w-14 h-14 rounded-[16px] bg-gradient-to-br from-emerald-500 to-emerald-600 text-white flex items-center justify-center text-[26px] shadow-md shrink-0">
                <i class="ph-fill ph-archive-box"></i>
            </div>
            <div>
                <h2 class="text-xl font-black text-emerald-950 tracking-tight font-poppins leading-none mb-1.5">Log Registrasi Kehadiran Warga</h2>
                <p class="text-emerald-800/80 font-medium text-[12.5px]">Kelola, saring, dan tinjau seluruh riwayat pendaftaran meja 1 posyandu.</p>
            </div>
        </div>
    </div>

    {{-- 2. PREMIUM FILTER TOOLBAR (Clean Healthcare Pill Tabs Select) --}}
    @php
        $reqKategori = request('kategori', '');
        $reqBulanStr = request('bulan');
        $selTahun = $reqBulanStr ? substr($reqBulanStr, 0, 4) : '';
        $selBulan = $reqBulanStr ? substr($reqBulanStr, 5, 2) : '';
        $tahunSaatIni = date('Y');

        $mapBulan = ['01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];
    @endphp

    <div class="glass-panel p-4 rounded-[20px] mb-6 flex flex-col xl:flex-row items-center gap-3 relative z-20">
        <form id="filterForm" action="{{ route('kader.absensi.riwayat') }}" method="GET" class="w-full flex flex-col md:flex-row items-center gap-3">
            <input type="hidden" name="bulan" id="hiddenBulan" value="{{ request('bulan') }}">

            <div class="w-full md:w-[35%] relative">
                <select name="kategori" class="premium-select">
                    <option value="">Semua Kategori Sasaran</option>
                    <option value="balita" {{ $reqKategori == 'balita' ? 'selected' : '' }}>Balita (12-59 Bulan)</option>
                    <option value="remaja" {{ $reqKategori == 'remaja' ? 'selected' : '' }}>Remaja</option>
                    <option value="lansia" {{ $reqKategori == 'lansia' ? 'selected' : '' }}>Lansia</option>
                </select>
            </div>

            <div class="w-full md:w-[25%] relative">
                <select id="valBulan" class="premium-select">
                    <option value="">Pilih Bulan</option>
                    @foreach($mapBulan as $val => $label)
                        <option value="{{ $val }}" {{ $selBulan == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-[20%] relative">
                <select id="valTahun" class="premium-select">
                    <option value="">Tahun</option>
                    @for($y = $tahunSaatIni; $y >= 2022; $y--)
                        <option value="{{ $y }}" {{ $selTahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="w-full md:w-[20%] flex items-center gap-2">
                <button type="submit" onclick="window.showLoader()" class="flex-1 bg-emerald-700 text-white font-black text-[11px] uppercase tracking-widest rounded-[14px] py-3.5 hover:bg-emerald-800 transition-all shadow-sm flex items-center justify-center gap-1.5 active:scale-95 border border-emerald-600/30">
                    <i class="ph-bold ph-funnel text-[14px]"></i> Saring
                </button>
                @if(request('kategori') || request('bulan'))
                    <a href="{{ route('kader.absensi.riwayat') }}" onclick="window.showLoader()" class="w-[44px] h-[44px] shrink-0 bg-rose-50 text-rose-500 rounded-[14px] flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all border border-rose-100 shadow-sm active:scale-95" title="Reset Filter">
                        <i class="ph-bold ph-arrow-counter-clockwise text-[15px]"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- 3. HISTORY MANIFEST LIST --}}
    @if(count($riwayat) > 0)
        <div class="space-y-3.5 relative z-10" id="riwayatList">
            @foreach($riwayat as $item)
                @php
                    $totalPasien = $item->total_pasien;
                    $totalHadir  = $item->total_hadir;
                    $persentase  = $totalPasien > 0 ? round(($totalHadir / $totalPasien) * 100) : 0;
                    
                    // Dynamic Theme Injector berdasarkan persentase kehadiran
                    $color = 'emerald'; 
                    if($persentase < 50) $color = 'rose'; 
                    elseif($persentase < 75) $color = 'amber'; 

                    $tgl = \Carbon\Carbon::parse($item->tanggal_posyandu)->locale('id');
                @endphp

                <div class="history-list-row p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    
                    {{-- Calendar Block & Meta Info (Kiri) --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4 flex-1 min-w-0">
                        <div class="w-12 h-12 bg-emerald-50 border border-emerald-100/60 rounded-[14px] flex flex-col items-center justify-center shrink-0 text-emerald-800 shadow-sm">
                            <span class="text-[16px] font-black leading-none font-poppins">{{ $tgl->format('d') }}</span>
                            <span class="text-[8.5px] font-black uppercase tracking-widest mt-0.5">{{ $tgl->translatedFormat('M y') }}</span>
                        </div>
                        
                        <div class="min-w-0">
                            <h4 class="text-[14.5px] font-black text-slate-800 font-poppins truncate mb-1" title="{{ $item->kode_absensi }}">{{ $item->kode_absensi }}</h4>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider border border-emerald-200/50 text-emerald-800 bg-emerald-50/60">
                                    {{ str_replace('_', ' ', $item->kategori) }} ({{ $item->kategori === 'balita' ? '12-59 Bln' : 'Sasaran' }})
                                </span>
                                <span class="px-2.5 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider border border-amber-200/60 text-amber-700 bg-amber-50/60">
                                    Pertemuan #{{ $item->nomor_pertemuan }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-bold ml-1 flex items-center gap-1"><i class="ph-bold ph-clock"></i> {{ $item->created_at->format('H:i') }} WIB</span>
                            </div>
                        </div>
                    </div>

                    {{-- Dynamic Progress Fill (Tengah) --}}
                    <div class="w-full lg:w-[260px] shrink-0 mt-1 lg:mt-0">
                        <div class="flex justify-between items-end mb-1.5">
                            <p class="text-[9.5px] font-black text-slate-400 uppercase tracking-wider">Hadir <span class="text-slate-700 ml-0.5">{{ $totalHadir }}/{{ $totalPasien }} Warga</span></p>
                            <p class="text-[13px] font-black text-{{ $color }}-600 font-poppins">{{ $persentase }}%</p>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill {{ $color == 'emerald' ? 'bg-emerald-500' : ($color == 'amber' ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $persentase }}%;"></div>
                        </div>
                    </div>

                    {{-- Action Control Gateways (Kanan) --}}
                    <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto mt-2 lg:mt-0">
                        <a href="{{ route('kader.absensi.show', $item->id) }}" onclick="window.showLoader()" class="flex-1 sm:flex-none px-4 py-2.5 bg-white border border-slate-200 text-slate-600 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 font-black text-[10px] uppercase tracking-widest rounded-[12px] transition-colors flex items-center justify-center gap-1 shadow-sm active:scale-95">
                            <i class="ph-bold ph-eye text-[14px]"></i> Periksa
                        </a>
                        <form action="{{ route('kader.absensi.destroy', $item->id) }}" method="POST" class="delete-form m-0 flex-1 sm:flex-none">
                            @csrf @method('DELETE')
                            <button type="button" class="btn-delete w-full sm:w-[40px] h-[40px] bg-white border border-slate-200 text-slate-400 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 rounded-[12px] transition-colors flex items-center justify-center shadow-sm active:scale-95" title="Eliminasi Arsip">
                                <i class="ph-bold ph-trash text-[15px]"></i>
                                <span class="sm:hidden ml-1.5 font-black text-[10px] uppercase tracking-widest">Hapus Sesi</span>
                            </button>
                        </form>
                    </div>

                </div>
            @endforeach
        </div>
    @else
        {{-- EMPTY STATE HOVER COMPONENT --}}
        <div class="text-center py-16 px-4 bg-white rounded-[20px] border border-emerald-100 shadow-sm relative z-10">
            <div class="w-14 h-14 bg-emerald-50 border border-emerald-100/60 rounded-[14px] flex items-center justify-center text-emerald-400 text-[28px] mx-auto mb-3">
                <i class="ph-fill ph-folder-open"></i>
            </div>
            <h4 class="text-[14px] font-black text-emerald-950 uppercase tracking-widest mb-1 font-poppins">Arsip Nihil</h4>
            <p class="text-[11.5px] text-slate-500 font-medium max-w-sm mx-auto">Sistem tidak menemukan berkas log kehadiran yang sesuai dengan filter filter parameter saringan Anda.</p>
        </div>
    @endif

    {{-- SYSTEM NATIVE PAGINATION --}}
    <div class="mt-6">
        {{ $riwayat->links() }}
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    window.hideLoader = () => { const l = document.getElementById('smoothLoader'); if(l) { l.classList.remove('opacity-100','pointer-events-auto'); l.classList.add('opacity-0','pointer-events-none'); setTimeout(()=> l.style.display = 'none', 200); } };
    window.showLoader = () => { const l = document.getElementById('smoothLoader'); if(l) { l.style.display = 'flex'; l.classList.remove('opacity-0','pointer-events-none'); l.classList.add('opacity-100','pointer-events-auto'); } };

    window.onload = hideLoader;
    document.addEventListener('DOMContentLoaded', hideLoader);
    window.addEventListener('pageshow', hideLoader);

    // FILTER INTEGRITY VALIDATION
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        let m = document.getElementById('valBulan').value;
        let y = document.getElementById('valTahun').value;
        
        if (m && y) {
            document.getElementById('hiddenBulan').value = y + '-' + m;
        } else if (m || y) {
            e.preventDefault();
            hideLoader();
            Swal.fire({
                title: 'Parameter Kurang',
                html: 'Harap tentukan kombinasi <b class="text-emerald-700">Bulan</b> dan <b class="text-emerald-700">Tahun</b> secara berpasangan untuk melakukan filtering data.',
                icon: 'info',
                confirmButtonColor: '#047857',
                confirmButtonText: 'Mengerti',
                customClass: { popup: 'rounded-[20px]' }
            });
        } else {
            document.getElementById('hiddenBulan').value = '';
        }
    });

    // CRITICAL DELETION SAFETY LOCK (SWEETALERT CODES)
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('.delete-form');
            Swal.fire({
                title: 'Hapus Permanen Sesi?',
                html: 'Tindakan ini akan <b class="text-rose-500">menghapus permanen</b> log manifest kehadiran dan seluruh rincian status rekam medis terkait pertemuan ini dari database.',
                icon: 'warning', 
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: { popup: 'rounded-[20px]' }
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoader();
                    form.submit();
                }
            });
        });
    });

    // CORNER NOTIFICATION TOAST ENGINGE
    @if(session('success'))
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
        });
        Toast.fire({ icon: 'success', title: "{{ session('success') }}" });
    @endif
</script>
@endpush
@endsection