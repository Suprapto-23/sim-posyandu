@extends('layouts.kader')

@section('title', 'Presensi Kehadiran Warga')
@section('page-name', 'Buku Kehadiran (Meja 1)')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    /* ====================================================================
       1. GLOBAL OPTIMIZATION & ANTI-LAG ENGINE
       ==================================================================== */
    html { scroll-behavior: smooth; }
    body { 
        background-color: #f4f7fe; /* Warna latar khas Nexus CRM */
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
    
    /* Delay Stagger dinamis untuk performa mulus */
    .stagger-fast > *:nth-child(1) { animation-delay: 40ms; }
    .stagger-fast > *:nth-child(2) { animation-delay: 80ms; }
    .stagger-fast > *:nth-child(3) { animation-delay: 120ms; }
    .stagger-fast > *:nth-child(4) { animation-delay: 160ms; }
    .stagger-fast > *:nth-child(5) { animation-delay: 200ms; }
    .stagger-fast > *:nth-child(6) { animation-delay: 240ms; }
    .stagger-fast > *:nth-child(7) { animation-delay: 280ms; }
    .stagger-fast > *:nth-child(8) { animation-delay: 320ms; }
    .stagger-fast > *:nth-child(n+9) { animation-delay: 360ms; }

    /* ====================================================================
       3. PIXEL PERFECT UI (SESUAI GAMBAR REFERENSI)
       ==================================================================== */
    .crm-search {
        width: 100%; background-color: #ffffff; border: 1px solid #e2e8f0; color: #1e293b;
        font-size: 0.85rem; font-weight: 600; font-family: 'Plus Jakarta Sans', sans-serif;
        border-radius: 9999px; padding: 0.6rem 1.2rem 0.6rem 2.5rem;
        outline: none; transition: all 0.2s ease;
    }
    .crm-search:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }

    .warga-card { 
        background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex; flex-direction: column;
    }
    .warga-card:hover { 
        border-color: #cbd5e1; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05); 
        transform: translateY(-2px); z-index: 10;
    }
    
    /* RADIO BUTTON (Tombol Terpisah) */
    .radio-hidden { display: none; }
    .status-btn { 
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); cursor: pointer; 
        background-color: #ffffff; color: #64748b; border: 1px solid #e2e8f0;
        border-radius: 8px; padding: 0.4rem 1.2rem;
    }
    .status-btn:hover { background-color: #f8fafc; color: #475569; }
    
    .radio-hadir:checked + .status-btn { 
        background-color: #10b981; color: white; border-color: #059669;
        box-shadow: 0 4px 10px -2px rgba(16, 185, 129, 0.3);
    }
    .radio-absen:checked + .status-btn { 
        background-color: #f43f5e; color: white; border-color: #e11d48;
        box-shadow: 0 4px 10px -2px rgba(244, 63, 94, 0.3);
    }

    /* KOTAK KETERANGAN */
    .ket-box { max-height: 0; opacity: 0; overflow: hidden; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); width: 100%; }
    .ket-box.open { max-height: 60px; opacity: 1; margin-top: 0.75rem; overflow: visible;}

    .custom-scrollbar::-webkit-scrollbar { height: 0px; width: 0px; display: none; }

    /* ==========================================================
       4. SWEETALERT 2 - CLEAN UI
       ========================================================== */
    div:where(.swal2-container).swal2-backdrop-show { background: rgba(15, 23, 42, 0.5) !important; backdrop-filter: blur(4px) !important; z-index: 99999 !important; }
    .swal2-popup:not(.swal2-toast) { border-radius: 24px !important; padding: 2.5rem 2rem 2rem !important; background: #ffffff !important; width: 24em !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15) !important; border: none !important; }
    .swal2-title { font-family: 'Poppins', sans-serif !important; font-weight: 900 !important; font-size: 1.3rem !important; color: #1e293b !important; padding-top: 0 !important; }
    .swal2-html-container { font-family: 'Plus Jakarta Sans', sans-serif !important; color: #64748b !important; font-size: 0.85rem !important; line-height: 1.6 !important; margin: 1em 0 0.5em !important; }
    .swal2-actions { gap: 10px !important; margin-top: 1.5rem !important; width: 100% !important; justify-content: center !important; }
    
    .swal-btn-confirm-emerald { background: #10b981 !important; color: #ffffff !important; border-radius: 9999px !important; padding: 12px 28px !important; font-size: 11px !important; font-weight: 900 !important; text-transform: uppercase !important; box-shadow: 0 4px 15px -3px rgba(16,185,129,0.3) !important; border: none !important; transition: all 0.2s ease !important; }
    .swal-btn-confirm-indigo { background: #4f46e5 !important; color: #ffffff !important; border-radius: 9999px !important; padding: 12px 28px !important; font-size: 11px !important; font-weight: 900 !important; text-transform: uppercase !important; box-shadow: 0 4px 15px -3px rgba(79,70,229,0.3) !important; border: none !important; transition: all 0.2s ease !important; }
    .swal-btn-cancel { background: #f1f5f9 !important; color: #475569 !important; border-radius: 9999px !important; padding: 12px 28px !important; font-size: 11px !important; font-weight: 900 !important; text-transform: uppercase !important; border: none !important; transition: all 0.2s ease !important; }
</style>
@endpush

@section('content')
{{-- PRELOADER SISTEM --}}
<div id="smoothLoader" class="fixed inset-0 bg-slate-50/90 backdrop-blur-sm z-[9999] flex flex-col items-center justify-center transition-all duration-200 opacity-100 pointer-events-auto">
    <div class="w-10 h-10 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin mb-3"></div>
    <p class="text-indigo-900 font-black tracking-widest text-[9px] uppercase font-poppins">MEMUAT...</p>
</div>

<div class="max-w-[1400px] mx-auto relative z-10 pb-16 mt-2 gpu-accel">

    {{-- TEKS HEADER UTAMA --}}
    <div class="mb-6 px-1">
        <h1 class="text-[22px] md:text-[24px] font-black text-slate-800 tracking-tight font-poppins leading-none mb-1.5">Buku Kehadiran (Meja 1)</h1>
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Workspace &bull; Sistem Berjalan</p>
    </div>

    {{-- 1. BANNER UTAMA --}}
    <div class="bg-white rounded-[24px] border border-slate-100 shadow-sm p-6 md:p-8 mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 stagger-fast">
        
        <div class="flex items-center gap-5 w-full md:w-auto">
            <div class="w-16 h-16 rounded-[20px] bg-indigo-600 text-white flex items-center justify-center text-[32px] shadow-lg shadow-indigo-200 shrink-0">
                <i class="ph-fill ph-calendar-check"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Sesi Posyandu Hari Ini</span>
                </div>
                <h2 class="text-xl md:text-[28px] font-black text-slate-800 tracking-tight font-poppins leading-none mb-2">Registrasi Warga</h2>
                {{-- Format Tanggal Paksa Bahasa Indonesia --}}
                <p class="text-slate-500 font-medium text-[12px] flex items-center gap-1.5"><i class="ph-bold ph-calendar-blank"></i> {{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>

        <div class="shrink-0 bg-slate-50 p-4 rounded-[20px] border border-slate-100 flex items-center gap-5 w-full md:w-auto justify-between md:justify-center">
            <div class="text-left md:text-right">
                <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1">Pertemuan Ke</p>
                <p class="text-3xl font-black text-indigo-600 font-poppins leading-none">#{{ $pertemuanBerikutnya ?? 1 }}</p>
            </div>
            <div class="w-12 h-12 rounded-[14px] bg-white text-indigo-400 flex items-center justify-center text-[24px] shadow-sm"><i class="ph-bold ph-hash"></i></div>
        </div>
    </div>

    {{-- 2. NAVIGASI KATEGORI --}}
    <div class="flex gap-2.5 mb-8 overflow-x-auto custom-scrollbar pb-2 stagger-fast w-full px-1">
        @php
            $tabs = [
                'bayi'      => ['label' => 'Bayi (<1 Thn)', 'icon' => 'ph-baby', 'color' => 'blue'],
                'balita'    => ['label' => 'Balita (1-5 Thn)', 'icon' => 'ph-smiley', 'color' => 'indigo'],
                'ibu_hamil' => ['label' => 'Ibu Hamil', 'icon' => 'ph-person', 'color' => 'pink'],
                'remaja'    => ['label' => 'Remaja', 'icon' => 'ph-graduation-cap', 'color' => 'violet'],
                'lansia'    => ['label' => 'Lansia', 'icon' => 'ph-heartbeat', 'color' => 'emerald'],
            ];
        @endphp

        @foreach($tabs as $key => $tab)
            @php $isActive = $kategori === $key; @endphp
            <a href="{{ route('kader.absensi.index', ['kategori' => $key]) }}" onclick="window.showLoader()"
               class="flex items-center justify-center gap-2 px-6 py-2.5 rounded-full text-[11px] font-black tracking-widest uppercase transition-all shrink-0 border
               {{ $isActive ? "bg-{$tab['color']}-600 border-{$tab['color']}-600 text-white shadow-md shadow-{$tab['color']}-200" : "bg-white border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700" }}">
                <i class="ph-fill {{ $tab['icon'] }} {{ $isActive ? "text-white" : "text-slate-400" }} text-[18px]"></i> 
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    {{-- 3. KOTAK UTAMA ABSENSI --}}
    <form action="{{ route('kader.absensi.store') }}" method="POST" id="formAbsensi">
        @csrf
        <input type="hidden" name="kategori" value="{{ $kategori }}">

        <div class="bg-white rounded-[28px] border border-slate-100 shadow-sm p-5 md:p-8 mb-8">
            
            {{-- TOOLBAR ATAS --}}
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6 bg-slate-50/80 p-3.5 rounded-[20px] border border-slate-100">
                <div class="flex items-center gap-3 w-full md:w-auto px-2">
                    <div class="w-10 h-10 bg-indigo-100 rounded-[12px] text-indigo-600 flex items-center justify-center text-[20px] shrink-0"><i class="ph-fill ph-clipboard-text"></i></div>
                    <div>
                        <h3 class="font-black text-slate-800 text-[13px] uppercase tracking-widest font-poppins leading-tight">Daftar Sasaran</h3>
                        <p class="text-[10px] font-bold text-slate-500 mt-0.5">Total: {{ count($pasiens) }} Orang</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                    @if(count($pasiens) > 0)
                        <button type="button" id="btnHadirSemua" class="w-full sm:w-auto px-5 py-2.5 bg-emerald-500 text-white hover:bg-emerald-600 font-black text-[10px] uppercase tracking-widest rounded-full transition-colors flex items-center justify-center gap-1.5 shadow-[0_4px_10px_-2px_rgba(16,185,129,0.3)] active:scale-95">
                            <i class="ph-bold ph-checks text-[14px]"></i> Hadir Semua
                        </button>
                    @endif

                    <div class="relative w-full sm:w-[260px] group">
                        <i class="ph-bold ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[14px] group-focus-within:text-indigo-500 transition-colors"></i>
                        <input type="text" id="searchInput" placeholder="Cari nama atau NIK..." class="crm-search">
                    </div>
                </div>
            </div>

            {{-- LIST KARTU WARGA (GRID 2 KOLOM) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 stagger-fast" id="wargaList">
                @forelse($pasiens as $index => $p)
                    @php
                        // 🔥 BULLETPROOF NULL CHECKER
                        // Menangani tipe data Object (dari relasi) maupun Array dengan ketat
                        $absenItem = $absensiData[$p->id] ?? null;
                        
                        $checkedHadir = '';
                        $checkedAbsen = '';
                        $keterangan   = '';

                        if ($absenItem) {
                            $status = is_object($absenItem) ? ($absenItem->hadir ?? null) : ($absenItem['hadir'] ?? null);
                            $keterangan = is_object($absenItem) ? ($absenItem->keterangan ?? '') : ($absenItem['keterangan'] ?? '');

                            // Strict Check: Pastikan nilainya benar-benar angka 1 atau 0 (Bukan Null)
                            if ($status !== null) {
                                if ((int)$status === 1) {
                                    $checkedHadir = 'checked';
                                } elseif ((int)$status === 0) {
                                    $checkedAbsen = 'checked';
                                }
                            }
                        }
                    @endphp
                    
                    <div class="warga-card p-4 sm:p-5">
                        
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            {{-- Identitas Kiri --}}
                            <div class="flex items-center gap-4 flex-1 min-w-0">
                                <div class="w-10 h-10 rounded-[10px] bg-slate-50 border border-slate-100 text-slate-400 flex items-center justify-center font-black text-[12px] shrink-0 font-poppins">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="warga-nama text-[14px] font-black text-slate-800 font-poppins truncate tracking-tight mb-0.5" title="{{ $p->nama_lengkap }}">{{ $p->nama_lengkap }}</h4>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <i class="ph-fill ph-identification-card text-slate-300 text-[14px]"></i> 
                                        <span class="text-[10px] font-bold text-slate-400 font-mono tracking-widest warga-nik">{{ $p->nik ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Tombol Aksi Kanan --}}
                            <div class="shrink-0 flex items-center gap-2 w-full sm:w-auto mt-3 sm:mt-0">
                                <input type="radio" name="kehadiran[{{ $p->id }}]" id="hadir_{{ $p->id }}" value="1" class="radio-hidden radio-hadir logic-radio" data-id="{{ $p->id }}" {{ $checkedHadir }} required>
                                <label for="hadir_{{ $p->id }}" class="status-btn flex-1 sm:flex-none flex items-center justify-center gap-1.5 text-[10px] font-black tracking-widest uppercase">
                                    <i class="ph-bold ph-check text-[14px]"></i> Hadir
                                </label>
                                
                                <input type="radio" name="kehadiran[{{ $p->id }}]" id="absen_{{ $p->id }}" value="0" class="radio-hidden radio-absen logic-radio" data-id="{{ $p->id }}" {{ $checkedAbsen }} required>
                                <label for="absen_{{ $p->id }}" class="status-btn flex-1 sm:flex-none flex items-center justify-center gap-1.5 text-[10px] font-black tracking-widest uppercase">
                                    <i class="ph-bold ph-x text-[14px]"></i> Absen
                                </label>
                            </div>
                        </div>

                        {{-- Keterangan Box --}}
                        <div id="ketBox_{{ $p->id }}" class="ket-box {{ $checkedAbsen === 'checked' ? 'open' : '' }}">
                            <input type="text" name="keterangan[{{ $p->id }}]" value="{{ $keterangan }}" placeholder="Tulis alasan absen..." class="w-full bg-rose-50/30 border border-rose-200 text-rose-600 text-[11px] font-bold px-4 py-3 rounded-[10px] outline-none focus:border-rose-400 focus:bg-white placeholder:text-rose-300 transition-colors shadow-sm mt-1">
                        </div>
                    </div>
                @empty
                    <div class="lg:col-span-2 text-center py-16 px-4 border border-dashed border-slate-200 rounded-[24px] bg-slate-50">
                        <div class="w-14 h-14 bg-white rounded-[16px] flex items-center justify-center text-slate-300 text-[28px] mx-auto mb-3 shadow-sm border border-slate-100"><i class="ph-fill ph-tray"></i></div>
                        <h4 class="text-[14px] font-black text-slate-700 uppercase tracking-widest mb-1 font-poppins">Daftar Kosong</h4>
                        <p class="text-[11px] font-medium text-slate-400 max-w-xs mx-auto">Tidak ada sasaran pada kategori ini.</p>
                    </div>
                @endforelse
            </div>

            {{-- ACTION BAR BAWAH --}}
            @if(count($pasiens) > 0)
            <div class="bg-white border border-slate-100 shadow-[0_-10px_20px_-10px_rgba(0,0,0,0.02)] rounded-[20px] p-5 mt-8 flex flex-col md:flex-row items-center justify-between gap-5 relative z-20">
                
                {{-- Indikator Counter --}}
                <div class="flex items-center justify-center md:justify-start gap-6 w-full md:w-auto px-2">
                    <div class="text-center md:text-left">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Diselesaikan</p>
                        <p class="text-[24px] font-black text-blue-600 font-poppins leading-none" id="countSudah">0</p>
                    </div>
                    <div class="w-px h-8 bg-slate-200 hidden md:block"></div>
                    <div class="text-center md:text-left">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Belum Selesai</p>
                        <p class="text-[24px] font-black text-rose-500 font-poppins leading-none transition-colors duration-300" id="countSisa">{{ count($pasiens) }}</p>
                    </div>
                </div>

                {{-- TOMBOL SIMPAN --}}
                <button type="submit" id="btnSubmit" class="w-full md:w-auto px-8 py-3.5 bg-blue-600 text-white font-black text-[11px] uppercase tracking-widest rounded-full shadow-[0_8px_20px_-5px_rgba(37,99,235,0.4)] hover:bg-blue-700 hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95">
                    <i class="ph-bold ph-floppy-disk text-[16px]"></i> Simpan Presensi
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

    window.hideLoader = () => { const l = document.getElementById('smoothLoader'); if(l) { l.classList.remove('opacity-100','pointer-events-auto'); l.classList.add('opacity-0','pointer-events-none'); setTimeout(()=> l.style.display = 'none', 200); } };
    window.showLoader = () => { const l = document.getElementById('smoothLoader'); if(l) { l.style.display = 'flex'; l.classList.remove('opacity-0','pointer-events-none'); l.classList.add('opacity-100','pointer-events-auto'); } };

    // 1. ENGINE PENGHITUNG REAL-TIME
    function updateCounters() {
        const answered = document.querySelectorAll('.logic-radio:checked').length;
        const sisa = totalPasiens - answered;
        
        if(countSudahEl) countSudahEl.textContent = answered;
        if(countSisaEl) {
            countSisaEl.textContent = sisa;
            if (sisa === 0) {
                countSisaEl.classList.remove('text-rose-500'); countSisaEl.classList.add('text-emerald-500');
            } else {
                countSisaEl.classList.add('text-rose-500'); countSisaEl.classList.remove('text-emerald-500');
            }
        }
    }

    // 2. ENGINE TOGGLE KETERANGAN ABSEN
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            const id = this.dataset.id;
            const ketBox = document.getElementById('ketBox_' + id);
            
            if (this.value == '0') {
                ketBox.classList.add('open');
                setTimeout(() => ketBox.querySelector('input').focus(), 100);
            } else {
                ketBox.classList.remove('open');
                ketBox.querySelector('input').value = '';
            }
            updateCounters();
        });
    });

    updateCounters();

    // 3. HADIR SEMUA ENGINE
    document.getElementById('btnHadirSemua')?.addEventListener('click', function() {
        Swal.fire({
            title: 'Tandai Hadir Semua?',
            html: 'Seluruh warga akan ditandai <b class="text-emerald-500 font-bold">Hadir</b>. Klik Simpan di bawah untuk memperbarui database.',
            icon: 'question',
            showCancelButton: true,
            buttonsStyling: false,
            reverseButtons: true, 
            confirmButtonText: '<i class="ph-bold ph-checks text-[14px] mr-1"></i> Ya, Hadirkan',
            cancelButtonText: 'Batal',
            customClass: { 
                popup: 'swal2-popup',
                confirmButton: 'swal-btn-confirm-emerald',
                cancelButton: 'swal-btn-cancel'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelectorAll('.radio-hadir').forEach(radio => {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                });
            }
        });
    });

    // 4. LIVE SEARCH INSTAN
    const searchInput = document.getElementById('searchInput');
    if(searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('.warga-card');
            
            rows.forEach(row => {
                const nama = row.querySelector('.warga-nama').textContent.toLowerCase();
                const nik = row.querySelector('.warga-nik').textContent.toLowerCase();
                if (nama.includes(filter) || nik.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // 5. VALIDASI SEBELUM SIMPAN & LOADER
    document.getElementById('formAbsensi')?.addEventListener('submit', function(e) {
        const answered = document.querySelectorAll('.logic-radio:checked').length;
        
        if (answered < totalPasiens) {
            e.preventDefault();
            Swal.fire({
                title: 'Belum Selesai',
                html: `Terdapat <b class="text-rose-500 font-bold">${totalPasiens - answered} warga</b> yang belum ditentukan statusnya.`,
                icon: 'warning',
                buttonsStyling: false,
                confirmButtonText: '<i class="ph-bold ph-pencil-simple text-[14px] mr-1"></i> Lanjutkan',
                customClass: { 
                    popup: 'swal2-popup',
                    confirmButton: 'swal-btn-confirm-indigo'
                }
            });
            return;
        }

        const btn = document.getElementById('btnSubmit');
        btn.innerHTML = '<i class="ph-bold ph-spinner-gap animate-spin text-[16px]"></i> Menyimpan...';
        btn.classList.add('opacity-75', 'cursor-wait', 'scale-95');
        showLoader();
    });

    window.onload = hideLoader;
    document.addEventListener('DOMContentLoaded', hideLoader);
    window.addEventListener('pageshow', hideLoader);
</script>
@endpush
@endsection