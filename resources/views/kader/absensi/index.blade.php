@extends('layouts.kader')

@section('title', 'Presensi Kehadiran Warga')
@section('page-name', 'Buku Kehadiran (Meja 1)')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    /* ====================================================================
       1. GLOBAL OPTIMIZATION & BACKGROUND (SOFT HEALTHCARE)
       ==================================================================== */
    html { scroll-behavior: smooth; }
    body { 
        background-color: #f0fdf6; 
        background-image: 
            radial-gradient(at 0% 0%, hsla(152, 100%, 96%, 1) 0, transparent 50%), 
            radial-gradient(at 100% 0%, hsla(43, 100%, 96%, 1) 0, transparent 50%);
        background-attachment: fixed;
        -webkit-font-smoothing: antialiased; 
        text-rendering: optimizeLegibility;
    }
    .gpu-accel { transform: translateZ(0); will-change: transform, opacity; }

    /* ====================================================================
       2. MODERN GLASSMORPHISM (PREMIUM UI)
       ==================================================================== */
    .glass-panel {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        box-shadow: 0 10px 40px -10px rgba(4, 120, 87, 0.08);
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        box-shadow: 0 4px 20px -2px rgba(4, 120, 87, 0.03);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .glass-card:hover {
        background: rgba(255, 255, 255, 0.9);
        border-color: rgba(16, 185, 129, 0.3);
        transform: translateY(-3px);
        box-shadow: 0 15px 30px -5px rgba(4, 120, 87, 0.08);
        z-index: 10;
    }

    /* ====================================================================
       3. ANIMATIONS & DELAYS
       ==================================================================== */
    @keyframes snappyFadeUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .stagger-fast > * { opacity: 0; animation: snappyFadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .stagger-fast > *:nth-child(1) { animation-delay: 50ms; }
    .stagger-fast > *:nth-child(2) { animation-delay: 100ms; }
    .stagger-fast > *:nth-child(3) { animation-delay: 150ms; }
    .stagger-fast > *:nth-child(4) { animation-delay: 200ms; }

    /* ====================================================================
       4. CUSTOM INPUTS & BUTTONS
       ==================================================================== */
    .glass-search {
        width: 100%; background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(8px);
        border: 1px solid rgba(16, 185, 129, 0.2); color: #064e3b;
        font-size: 0.85rem; font-weight: 600; font-family: 'Plus Jakarta Sans', sans-serif;
        border-radius: 9999px; padding: 0.7rem 1.2rem 0.7rem 2.8rem;
        outline: none; transition: all 0.3s ease;
    }
    .glass-search:focus { background: #fff; border-color: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15); }

    .radio-hidden { display: none; }
    .status-btn { 
        transition: all 0.2s ease; cursor: pointer; 
        background: rgba(255,255,255,0.5); color: #64748b; border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 10px; padding: 0.4rem 1.2rem;
    }
    .status-btn:hover { background: #fff; color: #334155; }
    
    .radio-hadir:checked + .status-btn { 
        background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-color: #059669;
        box-shadow: 0 6px 15px -3px rgba(16, 185, 129, 0.4);
    }
    .radio-absen:checked + .status-btn { 
        background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); color: white; border-color: #e11d48;
        box-shadow: 0 6px 15px -3px rgba(244, 63, 94, 0.4);
    }

    .ket-box { max-height: 0; opacity: 0; overflow: hidden; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); width: 100%; }
    .ket-box.open { max-height: 70px; opacity: 1; margin-top: 0.75rem; overflow: visible;}
    
    .gold-input {
        background: rgba(254, 252, 232, 0.6); border: 1px solid rgba(250, 204, 21, 0.4);
        color: #854d0e; box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.02);
    }
    .gold-input:focus { background: #fff; border-color: #eab308; box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.2); }
    .gold-input::placeholder { color: #ca8a04; opacity: 0.6; }

    .custom-scrollbar::-webkit-scrollbar { height: 0px; width: 0px; display: none; }
</style>
@endpush

@section('content')
<div id="smoothLoader" class="fixed inset-0 bg-slate-50/80 backdrop-blur-md z-[9999] flex flex-col items-center justify-center transition-all duration-300 opacity-100 pointer-events-auto">
    <div class="relative w-14 h-14 mb-4">
        <div class="absolute inset-0 border-4 border-emerald-100 rounded-full"></div>
        <div class="absolute inset-0 border-4 border-emerald-500 rounded-full border-t-transparent animate-spin"></div>
        <div class="absolute inset-2 border-4 border-amber-400 rounded-full border-b-transparent animate-spin" style="animation-direction: reverse; animation-duration: 1.5s;"></div>
    </div>
    <p class="text-emerald-800 font-black tracking-widest text-[10px] uppercase font-poppins">NEXUS MENGHUBUNGKAN...</p>
</div>

<div class="max-w-[1400px] mx-auto relative z-10 pb-20 mt-2 gpu-accel">

    {{-- HEADER TEKS --}}
    <div class="mb-8 px-2">
        <h1 class="text-[26px] md:text-[30px] font-black text-emerald-900 tracking-tight font-poppins leading-none mb-1.5 flex items-center gap-3">
            <i class="ph-fill ph-book-bookmark text-amber-500"></i> Buku Kehadiran
        </h1>
        <p class="text-[11px] font-bold text-emerald-600/70 uppercase tracking-widest">Meja 1 &bull; Registrasi Utama</p>
    </div>

    {{-- 1. BANNER UTAMA --}}
    <div class="glass-panel rounded-[28px] p-6 md:p-8 mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 stagger-fast relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-amber-300/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-emerald-400/20 rounded-full blur-3xl translate-y-1/3 -translate-x-1/4"></div>

        <div class="flex items-center gap-5 w-full md:w-auto relative z-10">
            <div class="w-16 h-16 rounded-[22px] bg-gradient-to-br from-emerald-400 to-emerald-600 text-white flex items-center justify-center text-[32px] shadow-[0_10px_25px_-5px_rgba(16,185,129,0.5)] shrink-0 border border-emerald-300/50">
                <i class="ph-fill ph-calendar-check"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-[0_0_10px_rgba(251,191,36,0.8)] animate-pulse"></span>
                    <span class="text-[10px] font-black text-amber-600 uppercase tracking-widest">Sesi Berjalan</span>
                </div>
                <h2 class="text-xl md:text-[28px] font-black text-emerald-950 tracking-tight font-poppins leading-none mb-2">Presensi Warga</h2>
                <p class="text-emerald-700 font-semibold text-[13px] flex items-center gap-1.5"><i class="ph-bold ph-calendar-blank"></i> {{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>

        <div class="shrink-0 bg-white/60 backdrop-blur-md p-4 rounded-[22px] border border-white flex items-center gap-5 w-full md:w-auto justify-between md:justify-center relative z-10 shadow-sm">
            <div class="text-left md:text-right">
                <p class="text-[10px] font-black text-emerald-600/80 uppercase tracking-widest mb-1">Pertemuan Ke</p>
                <p class="text-3xl font-black text-amber-500 font-poppins leading-none" style="text-shadow: 0 2px 10px rgba(245,158,11,0.2);">#{{ $pertemuanBerikutnya ?? 1 }}</p>
            </div>
            <div class="w-12 h-12 rounded-[16px] bg-amber-50 text-amber-500 flex items-center justify-center text-[24px] border border-amber-100"><i class="ph-bold ph-hash"></i></div>
        </div>
    </div>

    {{-- 2. NAVIGASI KATEGORI --}}
    <div class="flex gap-3 mb-8 overflow-x-auto custom-scrollbar pb-2 stagger-fast w-full px-2">
        @php
            $tabs = [
                'balita' => ['label' => 'Balita (12-59 Bln)', 'icon' => 'ph-baby'],
                'remaja' => ['label' => 'Remaja', 'icon' => 'ph-graduation-cap'],
                'lansia' => ['label' => 'Lansia', 'icon' => 'ph-heartbeat'],
            ];
        @endphp

        @foreach($tabs as $key => $tab)
            @php $isActive = $kategori === $key; @endphp
            <a href="{{ route('kader.absensi.index', ['kategori' => $key]) }}" onclick="window.showLoader()"
               class="flex items-center justify-center gap-2 px-7 py-3.5 rounded-full text-[11.5px] font-black tracking-widest uppercase transition-all shrink-0 border
               {{ $isActive ? "bg-gradient-to-r from-emerald-600 to-emerald-500 border-emerald-500 text-white shadow-[0_8px_20px_-6px_rgba(16,185,129,0.5)] scale-105" : "glass-card text-emerald-700 hover:bg-white" }}">
                <i class="ph-fill {{ $tab['icon'] }} {{ $isActive ? "text-amber-300" : "text-emerald-400" }} text-[20px]"></i> 
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    {{-- 3. KOTAK UTAMA ABSENSI --}}
    <form action="{{ route('kader.absensi.store') }}" method="POST" id="formAbsensi" class="relative z-20">
        @csrf
        <input type="hidden" name="kategori" value="{{ $kategori }}">

        <div class="glass-panel rounded-[32px] p-5 md:p-8 mb-8">
            
            {{-- FITUR SMART UPDATE: BANNER MODE EDIT --}}
            @if($sesiHariIni)
                <div class="mb-6 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 p-5 rounded-[20px] flex items-start sm:items-center gap-4 shadow-sm relative overflow-hidden">
                    <div class="w-12 h-12 bg-amber-100 text-amber-500 rounded-[14px] flex items-center justify-center shrink-0 border border-amber-200">
                        <i class="ph-fill ph-warning-circle text-[24px]"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-black text-amber-900 text-[13px] uppercase tracking-widest font-poppins mb-1">Mode Perbarui Presensi</h4>
                        <p class="text-amber-700/80 font-semibold text-[11.5px] leading-relaxed max-w-2xl">Sesi absensi untuk kategori ini <b class="text-amber-800">sudah tersimpan</b> pada hari ini. Anda saat ini berada dalam Mode Edit. Ubah status dan simpan kembali jika ada warga tambahan yang datang.</p>
                    </div>
                </div>
            @endif
            
            {{-- TOOLBAR ATAS --}}
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-8 bg-white/40 p-4 rounded-[24px] border border-white shadow-[inset_0_2px_4px_rgba(255,255,255,0.6)]">
                <div class="flex items-center gap-4 w-full md:w-auto px-2">
                    <div class="w-12 h-12 bg-white rounded-[16px] text-emerald-500 flex items-center justify-center text-[22px] shrink-0 shadow-sm border border-emerald-50"><i class="ph-fill ph-users-three"></i></div>
                    <div>
                        <h3 class="font-black text-emerald-950 text-[14px] uppercase tracking-widest font-poppins leading-tight">Daftar Sasaran</h3>
                        <p class="text-[11px] font-bold text-emerald-600 mt-0.5">Total: <span class="text-amber-500">{{ count($pasiens) }}</span> Warga terdaftar</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                    
                    <a href="{{ route('kader.absensi.riwayat') }}" onclick="window.showLoader()" class="w-full sm:w-auto px-6 py-3.5 bg-white border border-emerald-200 text-emerald-700 hover:bg-emerald-50 hover:border-emerald-300 font-black text-[11px] uppercase tracking-widest rounded-full transition-all flex items-center justify-center gap-2 shadow-sm active:scale-95 group">
                        <i class="ph-bold ph-archive-box text-[16px] text-emerald-500 group-hover:text-amber-500 transition-colors"></i> 
                        Riwayat Arsip
                    </a>

                    {{-- FITUR SMART UPDATE: SEMBUNYIKAN HADIR SEMUA SAAT MODE EDIT --}}
                    @if(count($pasiens) > 0 && !$sesiHariIni)
                        <button type="button" id="btnHadirSemua" class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-amber-400 to-amber-500 text-white hover:from-amber-500 hover:to-amber-600 font-black text-[11px] uppercase tracking-widest rounded-full transition-all flex items-center justify-center gap-2 shadow-[0_6px_15px_-3px_rgba(245,158,11,0.4)] hover:-translate-y-0.5 active:scale-95 border border-amber-300">
                            <i class="ph-bold ph-checks text-[16px]"></i> Hadir Semua
                        </button>
                    @endif

                    <div class="relative w-full sm:w-[280px] group">
                        <i class="ph-bold ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-emerald-500 text-[16px]"></i>
                        <input type="text" id="searchInput" placeholder="Cari nama atau NIK..." class="glass-search">
                    </div>
                </div>
            </div>

            {{-- LIST KARTU WARGA (GRID 2 KOLOM) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 stagger-fast" id="wargaList">
                @forelse($pasiens as $index => $p)
                    @php
                        $absenItem = $absensiData[$p->id] ?? null;
                        $checkedHadir = '';
                        $checkedAbsen = '';
                        $keterangan   = '';

                        if ($absenItem) {
                            $status = is_object($absenItem) ? ($absenItem->hadir ?? null) : ($absenItem['hadir'] ?? null);
                            $keterangan = is_object($absenItem) ? ($absenItem->keterangan ?? '') : ($absenItem['keterangan'] ?? '');

                            if ($status !== null) {
                                if ((int)$status === 1) $checkedHadir = 'checked';
                                elseif ((int)$status === 0) $checkedAbsen = 'checked';
                            }
                        }
                    @endphp
                    
                    <div class="glass-card rounded-[20px] p-4 sm:p-5 flex flex-col warga-card border border-white">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            
                            {{-- Identitas Kiri --}}
                            <div class="flex items-center gap-4 flex-1 min-w-0">
                                <div class="w-11 h-11 rounded-[14px] bg-emerald-50/80 border border-emerald-100 text-emerald-600 flex items-center justify-center font-black text-[13px] shrink-0 font-poppins shadow-sm">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="warga-nama text-[15px] font-black text-slate-800 font-poppins truncate tracking-tight mb-0.5" title="{{ $p->nama_lengkap }}">{{ $p->nama_lengkap }}</h4>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <i class="ph-fill ph-identification-card text-emerald-400 text-[15px]"></i> 
                                        <span class="text-[11px] font-bold text-slate-500 font-mono tracking-widest warga-nik">{{ $p->nik ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Tombol Radio (Hadir / Absen) --}}
                            <div class="shrink-0 flex items-center gap-2 w-full sm:w-auto mt-3 sm:mt-0 p-1 bg-white/50 rounded-[12px] border border-white">
                                <input type="radio" name="kehadiran[{{ $p->id }}]" id="hadir_{{ $p->id }}" value="1" class="radio-hidden radio-hadir logic-radio" data-id="{{ $p->id }}" {{ $checkedHadir }} required>
                                <label for="hadir_{{ $p->id }}" class="status-btn flex-1 sm:flex-none flex items-center justify-center gap-1.5 text-[10.5px] font-black tracking-widest uppercase">
                                    <i class="ph-bold ph-check text-[14px]"></i> Hadir
                                </label>
                                
                                <input type="radio" name="kehadiran[{{ $p->id }}]" id="absen_{{ $p->id }}" value="0" class="radio-hidden radio-absen logic-radio" data-id="{{ $p->id }}" {{ $checkedAbsen }} required>
                                <label for="absen_{{ $p->id }}" class="status-btn flex-1 sm:flex-none flex items-center justify-center gap-1.5 text-[10.5px] font-black tracking-widest uppercase">
                                    <i class="ph-bold ph-x text-[14px]"></i> Absen
                                </label>
                            </div>
                        </div>

                        {{-- Kotak Keterangan Akordeon --}}
                        <div id="ketBox_{{ $p->id }}" class="ket-box {{ $checkedAbsen === 'checked' ? 'open' : '' }}">
                            <div class="relative mt-2">
                                <i class="ph-fill ph-warning-circle absolute left-3 top-1/2 -translate-y-1/2 text-amber-500 text-[16px]"></i>
                                <input type="text" name="keterangan[{{ $p->id }}]" value="{{ $keterangan }}" placeholder="Tulis alasan tidak hadir (Izin/Sakit/Luar Kota)..." class="gold-input w-full text-[11.5px] font-bold pl-10 pr-4 py-3 rounded-[12px] outline-none transition-all">
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="lg:col-span-2 text-center py-20 px-4 glass-card border border-dashed border-emerald-200 rounded-[28px]">
                        <div class="w-16 h-16 bg-white rounded-[20px] flex items-center justify-center text-amber-400 text-[32px] mx-auto mb-4 shadow-sm border border-emerald-50"><i class="ph-fill ph-folder-open"></i></div>
                        <h4 class="text-[16px] font-black text-emerald-900 uppercase tracking-widest mb-1.5 font-poppins">Daftar Kosong</h4>
                        <p class="text-[12px] font-medium text-emerald-600 max-w-xs mx-auto">Sistem tidak menemukan data sasaran aktif pada kategori ini.</p>
                    </div>
                @endforelse
            </div>

            {{-- ACTION BAR BAWAH --}}
            @if(count($pasiens) > 0)
            <div class="bg-white/80 backdrop-blur-xl border border-white shadow-[0_-15px_30px_-15px_rgba(4,120,87,0.1)] rounded-[24px] p-5 mt-10 flex flex-col md:flex-row items-center justify-between gap-5 sticky bottom-4 z-50">
                
                {{-- Indikator Counter --}}
                <div class="flex items-center justify-center md:justify-start gap-8 w-full md:w-auto px-4">
                    <div class="text-center md:text-left">
                        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-0.5 flex items-center justify-center md:justify-start gap-1"><i class="ph-fill ph-check-circle text-emerald-400"></i> Terisi</p>
                        <p class="text-[26px] font-black text-emerald-700 font-poppins leading-none" id="countSudah">0</p>
                    </div>
                    <div class="w-px h-10 bg-emerald-100 hidden md:block"></div>
                    <div class="text-center md:text-left">
                        <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-0.5 flex items-center justify-center md:justify-start gap-1"><i class="ph-fill ph-warning-circle text-amber-400"></i> Belum</p>
                        <p class="text-[26px] font-black text-amber-600 font-poppins leading-none transition-colors duration-300" id="countSisa">{{ count($pasiens) }}</p>
                    </div>
                </div>

                {{-- FITUR SMART UPDATE: PERUBAHAN TOMBOL SIMPAN --}}
                <button type="submit" id="btnSubmit" class="w-full md:w-auto px-10 py-4 {{ $sesiHariIni ? 'bg-gradient-to-r from-amber-500 to-amber-600 shadow-amber-500/40 border-amber-400' : 'bg-gradient-to-r from-emerald-600 to-emerald-500 shadow-emerald-500/40 border-emerald-400' }} text-white font-black text-[12px] uppercase tracking-widest rounded-full shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-2 border relative overflow-hidden group">
                    <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
                    <i class="ph-bold {{ $sesiHariIni ? 'ph-arrows-clockwise text-white' : 'ph-floppy-disk text-amber-300' }} text-[18px] relative z-10"></i> 
                    <span class="relative z-10">{{ $sesiHariIni ? 'Perbarui Presensi' : 'Simpan Presensi' }}</span>
                </button>
            </div>
            @endif

        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const totalPasiens = {{ count($pasiens) }};
    const countSudahEl = document.getElementById('countSudah');
    const countSisaEl  = document.getElementById('countSisa');
    const radios       = document.querySelectorAll('.logic-radio');

    window.hideLoader = () => { const l = document.getElementById('smoothLoader'); if(l) { l.classList.remove('opacity-100','pointer-events-auto'); l.classList.add('opacity-0','pointer-events-none'); setTimeout(()=> l.style.display = 'none', 300); } };
    window.showLoader = () => { const l = document.getElementById('smoothLoader'); if(l) { l.style.display = 'flex'; l.classList.remove('opacity-0','pointer-events-none'); l.classList.add('opacity-100','pointer-events-auto'); } };

    // Penghitung Realtime
    function updateCounters() {
        const answered = document.querySelectorAll('.logic-radio:checked').length;
        const sisa = totalPasiens - answered;
        
        if(countSudahEl) countSudahEl.textContent = answered;
        if(countSisaEl) {
            countSisaEl.textContent = sisa;
            if (sisa === 0) {
                countSisaEl.classList.remove('text-amber-600'); countSisaEl.classList.add('text-emerald-500');
                countSisaEl.previousElementSibling.classList.remove('text-amber-500'); countSisaEl.previousElementSibling.classList.add('text-emerald-500');
                countSisaEl.previousElementSibling.querySelector('i').classList.replace('ph-warning-circle', 'ph-check-circle');
                countSisaEl.previousElementSibling.querySelector('i').classList.replace('text-amber-400', 'text-emerald-400');
            } else {
                countSisaEl.classList.add('text-amber-600'); countSisaEl.classList.remove('text-emerald-500');
                countSisaEl.previousElementSibling.classList.add('text-amber-500'); countSisaEl.previousElementSibling.classList.remove('text-emerald-500');
                countSisaEl.previousElementSibling.querySelector('i').classList.replace('ph-check-circle', 'ph-warning-circle');
                countSisaEl.previousElementSibling.querySelector('i').classList.replace('text-emerald-400', 'text-amber-400');
            }
        }
    }

    // Toggle Kotak Keterangan (Akordeon)
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            const id = this.dataset.id;
            const ketBox = document.getElementById('ketBox_' + id);
            
            if (this.value == '0') {
                ketBox.classList.add('open');
                setTimeout(() => ketBox.querySelector('input').focus(), 150);
            } else {
                ketBox.classList.remove('open');
                ketBox.querySelector('input').value = '';
            }
            updateCounters();
        });
    });

    updateCounters();

    // Hadir Semua (Aman, akan null jika tidak dirender saat mode edit)
    document.getElementById('btnHadirSemua')?.addEventListener('click', function() {
        document.querySelectorAll('.radio-hadir').forEach(radio => {
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
        });
    });

    // Pencarian Cepat
    const searchInput = document.getElementById('searchInput');
    if(searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('.warga-card');
            
            rows.forEach(row => {
                const nama = row.querySelector('.warga-nama').textContent.toLowerCase();
                const nik = row.querySelector('.warga-nik').textContent.toLowerCase();
                if (nama.includes(filter) || nik.includes(filter)) {
                    row.parentElement.style.display = '';
                } else {
                    row.parentElement.style.display = 'none';
                }
            });
        });
    }

    // Validasi sebelum simpan
    document.getElementById('formAbsensi')?.addEventListener('submit', function(e) {
        const answered = document.querySelectorAll('.logic-radio:checked').length;
        
        if (answered < totalPasiens) {
            e.preventDefault();
            Swal.fire({
                title: 'Data Belum Lengkap',
                html: `Terdapat <b style="color: #ea580c;">${totalPasiens - answered} warga</b> yang belum dikonfirmasi status kehadirannya.`,
                icon: 'warning',
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Lengkapi Sekarang',
                customClass: { popup: 'rounded-[24px]' }
            });
            return;
        }

        const btn = document.getElementById('btnSubmit');
        // Sesuaikan indikator loading dengan status mode
        const isEditMode = {{ $sesiHariIni ? 'true' : 'false' }};
        const textLoad = isEditMode ? 'Memperbarui...' : 'Menyimpan...';
        
        btn.innerHTML = `<i class="ph-bold ph-spinner-gap animate-spin text-[18px] text-white"></i> <span class="relative z-10">${textLoad}</span>`;
        btn.classList.add('opacity-80', 'cursor-wait', 'scale-95');
        showLoader();
    });

    window.onload = hideLoader;
    document.addEventListener('DOMContentLoaded', hideLoader);
    window.addEventListener('pageshow', hideLoader);
</script>
@endpush
@endsection