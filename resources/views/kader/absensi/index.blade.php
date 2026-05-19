@extends('layouts.kader')

@section('title', 'Presensi Kehadiran Warga')
@section('page-name', 'Buku Kehadiran (Meja 1)')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    /* ====================================================================
       1. GLOBAL HEALTHCARE THEME & ANTI-LAG
       ==================================================================== */
    html { scroll-behavior: smooth; }
    body { 
        background-color: #f0fdf4; /* Soft Emerald Tint */
        background-image: 
            radial-gradient(at 0% 0%, hsla(148, 100%, 97%, 1) 0, transparent 50%), 
            radial-gradient(at 100% 0%, hsla(45, 100%, 96%, 1) 0, transparent 50%);
        background-attachment: fixed;
        -webkit-font-smoothing: antialiased; 
        text-rendering: optimizeLegibility;
    }
    .gpu-layer { transform: translateZ(0); will-change: transform, opacity; }

    /* ====================================================================
       2. MODERN PREMIUM GLASSMORPHISM
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
        box-shadow: 0 4px 20px -4px rgba(6, 78, 59, 0.02);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .glass-card:hover {
        background: rgba(255, 255, 255, 0.95);
        border-color: rgba(16, 185, 129, 0.4);
        transform: translateY(-2px);
        box-shadow: 0 16px 32px -6px rgba(6, 78, 59, 0.08);
    }

    /* ====================================================================
       3. RESPONSIVE ANIMATIONS
       ==================================================================== */
    @keyframes snappyFadeUp {
        0% { opacity: 0; transform: translateY(12px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .stagger-grid > * { opacity: 0; animation: snappyFadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .stagger-grid > *:nth-child(1) { animation-delay: 40ms; }
    .stagger-grid > *:nth-child(2) { animation-delay: 80ms; }
    .stagger-grid > *:nth-child(3) { animation-delay: 120ms; }
    .stagger-grid > *:nth-child(4) { animation-delay: 160ms; }

    /* ====================================================================
       4. PREMIUM COMPONENT INPUTS & BUTTONS
       ==================================================================== */
    .search-pill {
        width: 100%; background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(8px);
        border: 1px solid rgba(16, 185, 129, 0.25); color: #064e3b;
        font-size: 0.85rem; font-weight: 600; border-radius: 9999px; 
        padding: 0.75rem 1.25rem 0.75rem 2.75rem; outline: none; transition: all 0.25s ease;
    }
    .search-pill:focus { background: #ffffff; border-color: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12); }

    .radio-native { display: none; }
    .btn-toggle-status { 
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); cursor: pointer; 
        background: rgba(255, 255, 255, 0.6); color: #64748b; border: 1px solid #e2e8f0;
        border-radius: 12px; padding: 0.5rem 1.25rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;
    }
    .btn-toggle-status:hover { background: #ffffff; color: #1e293b; border-color: #cbd5e1; }
    
    /* Emerald Active State (Hadir) */
    .radio-hadir:checked + .btn-toggle-status { 
        background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; border-color: #059669;
        box-shadow: 0 4px 14px -3px rgba(16, 185, 129, 0.4);
    }
    /* Rose Active State (Absen) */
    .radio-absen:checked + .btn-toggle-status { 
        background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); color: #ffffff; border-color: #e11d48;
        box-shadow: 0 4px 14px -3px rgba(244, 63, 94, 0.4);
    }

    /* Accordion Keterangan Absen (Gold Accent Layout) */
    .accordion-ket-box { max-height: 0; opacity: 0; overflow: hidden; transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1); width: 100%; }
    .accordion-ket-box.open { max-height: 80px; opacity: 1; margin-top: 0.75rem; }
    
    .gold-input-accent {
        background: rgba(254, 252, 232, 0.6); border: 1px solid rgba(234, 179, 8, 0.3);
        color: #713f12; font-weight: 600;
    }
    .gold-input-accent:focus { background: #ffffff; border-color: #eab308; box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.15); }
    .gold-input-accent::placeholder { color: #ca8a04; opacity: 0.6; }

    .hide-scrollbar::-webkit-scrollbar { display: none; }
</style>
@endpush

@section('content')
{{-- SCREEN PRELOADER (Dynamic Emerald Smooth) --}}
<div id="smoothLoader" class="fixed inset-0 bg-emerald-950/20 backdrop-blur-md z-[9999] flex flex-col items-center justify-center transition-all duration-300 opacity-100 pointer-events-auto">
    <div class="relative w-12 h-12 mb-3">
        <div class="absolute inset-0 border-4 border-emerald-200/50 rounded-full"></div>
        <div class="absolute inset-0 border-4 border-emerald-500 rounded-full border-t-transparent animate-spin"></div>
    </div>
    <p class="text-emerald-900 font-black tracking-widest text-[9px] uppercase font-poppins">Sinkronisasi Halaman...</p>
</div>

<div class="max-w-[1400px] mx-auto relative z-10 pb-20 mt-2 gpu-layer">

    {{-- HEADER UTAMA --}}
    <div class="mb-6 px-1">
        <h1 class="text-[24px] md:text-[28px] font-black text-emerald-950 tracking-tight font-poppins leading-none mb-1.5 flex items-center gap-2.5">
            <i class="ph-fill ph-book-open text-amber-500"></i> Pencatatan Presensi Kehadiran
        </h1>
        <p class="text-[10px] font-bold text-emerald-700/80 uppercase tracking-widest">Registrasi Terpadu &bull; Validasi Meja 1</p>
    </div>

    {{-- 1. MONITORING SESSIONS BANNER --}}
    <div class="glass-panel rounded-[24px] p-6 mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 overflow-hidden relative">
        <div class="absolute top-0 right-0 w-72 h-72 bg-amber-200/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex items-center gap-4 relative z-10">
            <div class="w-14 h-14 rounded-[18px] bg-gradient-to-br from-emerald-500 to-emerald-600 text-white flex items-center justify-center text-[28px] shadow-md shrink-0 border border-emerald-400/30">
                <i class="ph-fill ph-calendar-check"></i>
            </div>
            <div>
                <div class="flex items-center gap-1.5 mb-1">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span class="text-[9px] font-black text-amber-600 uppercase tracking-widest">Sesi Aktif Lapangan</span>
                </div>
                <h2 class="text-lg md:text-2xl font-black text-emerald-950 tracking-tight font-poppins leading-none mb-1.5">Buku Kendali Absensi</h2>
                <p class="text-emerald-800 font-semibold text-[12px] flex items-center gap-1.5">
                    <i class="ph-bold ph-calendar-blank text-emerald-600"></i> {{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y') }}
                </p>
            </div>
        </div>

        <div class="shrink-0 bg-white/80 border border-white/60 p-3.5 rounded-[18px] flex items-center gap-4 shadow-sm relative z-10">
            <div class="text-left md:text-right">
                <p class="text-[9px] font-black text-emerald-700/70 uppercase tracking-widest mb-0.5">Pertemuan Ke</p>
                <p class="text-2xl font-black text-amber-500 font-poppins leading-none">#{{ $pertemuanBerikutnya }}</p>
            </div>
            <div class="w-10 h-10 rounded-[12px] bg-amber-50 text-amber-500 flex items-center justify-center text-[20px] border border-amber-100"><i class="ph-bold ph-hash"></i></div>
        </div>
    </div>

    {{-- 2. TABS PILAR UTAMA KATEGORI SASARAN --}}
    <div class="flex gap-2.5 mb-6 overflow-x-auto hide-scrollbar pb-1 w-full px-1">
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
               class="flex items-center justify-center gap-2 px-6 py-3 rounded-full text-[11px] font-black tracking-wider uppercase transition-all shrink-0 border
               {{ $isActive ? 'bg-gradient-to-r from-emerald-600 to-emerald-500 border-emerald-500 text-white shadow-md scale-[1.02]' : 'glass-card text-emerald-800 hover:bg-white/80' }}">
                <i class="ph-fill {{ $tab['icon'] }} {{ $isActive ? 'text-amber-300' : 'text-emerald-500' }} text-[18px]"></i> 
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    {{-- 3. INTERACTIVE FORMS LAYOUT --}}
    <form action="{{ route('kader.absensi.store') }}" method="POST" id="formAbsensi" class="relative z-20">
        @csrf
        <input type="hidden" name="kategori" value="{{ $kategori }}">

        <div class="glass-panel rounded-[24px] p-4 md:p-6 mb-6">
            
            {{-- ACTIONS CONTROLLER BAR --}}
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6 bg-white/50 p-3.5 rounded-[18px] border border-white shadow-sm">
                <div class="flex items-center gap-3 px-1">
                    <div class="w-10 h-10 bg-white rounded-[12px] text-emerald-600 flex items-center justify-center text-[20px] shrink-0 border border-emerald-50 shadow-sm"><i class="ph-fill ph-users-three"></i></div>
                    <div>
                        <h3 class="font-black text-emerald-950 text-[13px] uppercase tracking-wider font-poppins leading-tight">Manifes Warga</h3>
                        <p class="text-[10.5px] font-bold text-emerald-700">Terdaftar Aktif: <span class="text-amber-500">{{ count($pasiens) }}</span> Jiwa</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-2.5 w-full md:w-auto">
                    @if(count($pasiens) > 0)
                        <button type="button" id="btnHadirSemua" class="w-full sm:w-auto px-5 py-2.5 bg-gradient-to-r from-amber-400 to-amber-500 text-white hover:from-amber-500 hover:to-amber-600 font-black text-[10.5px] uppercase tracking-wider rounded-full transition-all flex items-center justify-center gap-1.5 shadow-sm border border-amber-300/40">
                            <i class="ph-bold ph-checks text-[15px]"></i> Set Hadir Semua
                        </button>
                    @endif

                    <div class="relative w-full sm:w-[260px]">
                        <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-600 text-[15px]"></i>
                        <input type="text" id="searchInput" placeholder="Cari nama atau NIK..." class="search-pill">
                    </div>
                </div>
            </div>

            {{-- LIST GRID ROW MANAGEMENT --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 stagger-grid" id="wargaList">
                @forelse($pasiens as $index => $p)
                    @php
                        $absenItem = $absensiData[$p->id] ?? null;
                        $checkedHadir = '';
                        $checkedAbsen = '';
                        $keterangan   = '';

                        if ($absenItem) {
                            $status = $absenItem['hadir'] ?? null;
                            $keterangan = $absenItem['keterangan'] ?? '';

                            if ($status !== null) {
                                if ((int)$status === 1) $checkedHadir = 'checked';
                                elseif ((int)$status === 0) $checkedAbsen = 'checked';
                            }
                        }
                    @endphp
                    
                    <div class="glass-card rounded-[16px] p-4 flex flex-col warga-card border border-white/80">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            
                            {{-- INDIVIDUAL IDENTITY LAYOUT --}}
                            <div class="flex items-center gap-3.5 flex-1 min-w-0">
                                <div class="w-9 h-9 rounded-[10px] bg-emerald-50/80 border border-emerald-100 text-emerald-700 flex items-center justify-center font-black text-[12px] shrink-0 font-poppins shadow-sm">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="warga-nama text-[14px] font-black text-slate-800 font-poppins truncate tracking-tight mb-0.5" title="{{ $p->nama_lengkap }}">{{ $p->nama_lengkap }}</h4>
                                    <div class="flex items-center gap-1.5">
                                        <i class="ph-fill ph-identification-card text-emerald-500/70 text-[14px]"></i> 
                                        <span class="text-[10.5px] font-bold text-slate-500 font-mono tracking-wider warga-nik">{{ $p->nik ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- TOGGLE CONTROLLER INLINE BUTTONS --}}
                            <div class="shrink-0 flex items-center gap-1.5 p-1 bg-white/60 rounded-[10px] border border-white/80">
                                <input type="radio" name="kehadiran[{{ $p->id }}]" id="hadir_{{ $p->id }}" value="1" class="radio-native radio-hadir logic-radio" data-id="{{ $p->id }}" {{ $checkedHadir }} required>
                                <label for="hadir_{{ $p->id }}" class="btn-toggle-status flex-1 sm:flex-none flex items-center justify-center gap-1 text-[10px]">
                                    <i class="ph-bold ph-check text-[13px]"></i> Hadir
                                </label>
                                
                                <input type="radio" name="kehadiran[{{ $p->id }}]" id="absen_{{ $p->id }}" value="0" class="radio-native radio-absen logic-radio" data-id="{{ $p->id }}" {{ $checkedAbsen }} required>
                                <label for="absen_{{ $p->id }}" class="btn-toggle-status flex-1 sm:flex-none flex items-center justify-center gap-1 text-[10px]">
                                    <i class="ph-bold ph-x text-[13px]"></i> Absen
                                </label>
                            </div>
                        </div>

                        {{-- GOLD ACCENTED ACCORDION NOTES INPUT --}}
                        <div id="ketBox_{{ $p->id }}" class="accordion-ket-box {{ $checkedAbsen === 'checked' ? 'open' : '' }}">
                            <div class="relative">
                                <i class="ph-fill ph-warning-circle absolute left-3.5 top-1/2 -translate-y-1/2 text-amber-500 text-[15px]"></i>
                                <input type="text" name="keterangan[{{ $p->id }}]" value="{{ $keterangan }}" placeholder="Alasan tidak hadir (Sakit/Izin/Luar Kota)..." class="gold-input-accent w-full text-[11px] pl-9 pr-3.5 py-2.5 rounded-[10px] outline-none transition-all">
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="lg:col-span-2 text-center py-16 px-4 glass-card border border-dashed border-emerald-200 rounded-[20px]">
                        <div class="w-14 h-14 bg-white rounded-[16px] flex items-center justify-center text-amber-400 text-[28px] mx-auto mb-3 shadow-sm border border-emerald-50"><i class="ph-fill ph-folder-open"></i></div>
                        <h4 class="text-[14px] font-black text-emerald-950 uppercase tracking-widest mb-1 font-poppins">Data Kosong</h4>
                        <p class="text-[11.5px] font-medium text-emerald-700/80 max-w-xs mx-auto">Tidak ditemukan sasaran dengan kriteria umur aktif pada kategori ini.</p>
                    </div>
                @endforelse
            </div>

            {{-- FIXED STATUS COUNTER ACTION BAR --}}
            @if(count($pasiens) > 0)
            <div class="bg-white/90 backdrop-blur-xl border border-white shadow-[0_-12px_30px_-10px_rgba(6,78,59,0.08)] rounded-[20px] p-4 mt-8 flex flex-col md:flex-row items-center justify-between gap-4 sticky bottom-4 z-50">
                
                <div class="flex items-center justify-center md:justify-start gap-8 w-full md:w-auto px-2">
                    <div class="text-center md:text-left">
                        <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mb-0.5 flex items-center justify-center md:justify-start gap-1"><i class="ph-fill ph-check-circle text-emerald-500"></i> Terisi</p>
                        <p class="text-2xl font-black text-emerald-800 font-poppins leading-none" id="countSudah">0</p>
                    </div>
                    <div class="w-px h-8 bg-emerald-100 hidden md:block"></div>
                    <div class="text-center md:text-left">
                        <p class="text-[9px] font-black text-amber-600 uppercase tracking-widest mb-0.5 flex items-center justify-center md:justify-start gap-1"><i class="ph-fill ph-warning-circle text-amber-500"></i> Sisa</p>
                        <p class="text-2xl font-black text-amber-600 font-poppins leading-none transition-colors duration-300" id="countSisa">{{ count($pasiens) }}</p>
                    </div>
                </div>

                {{-- SUBMIT BUTTON PREMIUM --}}
                <button type="submit" id="btnSubmit" class="w-full md:w-auto px-8 py-3.5 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white font-black text-[11px] uppercase tracking-widest rounded-full shadow-md hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-1.5 border border-emerald-400/20 group relative overflow-hidden">
                    <i class="ph-bold ph-cloud-arrow-up text-[16px] text-amber-300"></i> 
                    <span>Simpan Sesi Presensi</span>
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

    function updateCounters() {
        const answered = document.querySelectorAll('.logic-radio:checked').length;
        const sisa = totalPasiens - answered;
        
        if(countSudahEl) countSudahEl.textContent = answered;
        if(countSisaEl) {
            countSisaEl.textContent = sisa;
            if (sisa === 0) {
                countSisaEl.className = 'text-2xl font-black text-emerald-600 font-poppins leading-none';
                countSisaEl.previousElementSibling.className = 'text-[9px] font-black text-emerald-600 uppercase tracking-widest mb-0.5 flex items-center gap-1';
                countSisaEl.previousElementSibling.querySelector('i').className = 'ph-fill ph-check-circle text-emerald-500';
            } else {
                countSisaEl.className = 'text-2xl font-black text-amber-600 font-poppins leading-none';
                countSisaEl.previousElementSibling.className = 'text-[9px] font-black text-amber-600 uppercase tracking-widest mb-0.5 flex items-center gap-1';
                countSisaEl.previousElementSibling.querySelector('i').className = 'ph-fill ph-warning-circle text-amber-500';
            }
        }
    }

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

    document.getElementById('btnHadirSemua')?.addEventListener('click', function() {
        document.querySelectorAll('.radio-hadir').forEach(radio => {
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
        });
    });

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

    document.getElementById('formAbsensi')?.addEventListener('submit', function(e) {
        const answered = document.querySelectorAll('.logic-radio:checked').length;
        
        if (answered < totalPasiens) {
            e.preventDefault();
            Swal.fire({
                title: 'Data Belum Lengkap',
                html: `Terdapat <b class="text-rose-500">${totalPasiens - answered} warga</b> yang belum ditentukan status kehadirannya.`,
                icon: 'warning',
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Lengkapi Data',
                customClass: { popup: 'rounded-[20px]' }
            });
            return;
        }

        const btn = document.getElementById('btnSubmit');
        btn.innerHTML = '<i class="ph-bold ph-spinner-gap animate-spin text-[16px] text-amber-300"></i> <span>Sinkronisasi Data...</span>';
        btn.disabled = true;
        showLoader();
    });

    window.onload = hideLoader;
    document.addEventListener('DOMContentLoaded', hideLoader);
    window.addEventListener('pageshow', hideLoader);
</script>
@endpush
@endsection