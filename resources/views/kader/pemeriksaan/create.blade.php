@extends('layouts.kader')

@section('title', 'Input Pengukuran Fisik')
@section('page-name', 'Mode Rekam Medis Kader')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    /* ====================================================================
       1. GLOBAL TYPOGRAPHY & OVERRIDES
       ==================================================================== */
    body { font-family: 'Plus Jakarta Sans', sans-serif !important; background-color: #f8fafc; }
    
    .custom-scroll::-webkit-scrollbar { width: 6px; }
    .custom-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
    .custom-scroll::-webkit-scrollbar-track { background-color: #f8fafc; }
    
    /* Hilangkan panah spinner pada input number */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }

    /* ====================================================================
       2. NEXUS PREMIUM INPUTS & TABS
       ==================================================================== */
    .nexus-input {
        width: 100%; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px;
        color: #1e293b; font-weight: 700; font-size: 0.9rem; transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .nexus-input:focus {
        border-color: #10b981; outline: none; background: #ffffff;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
    }
    .nexus-input::placeholder { color: #94a3b8; font-weight: 500; }

    .nexus-label {
        display: block; font-size: 10.5px; font-weight: 900; color: #475569;
        text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; margin-left: 0.25rem;
    }

    .tab-btn { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
    .tab-active { background: #ffffff; color: #059669; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    .tab-inactive { color: #64748b; background: transparent; }
    .tab-inactive:hover { color: #334155; background: rgba(255,255,255,0.5); }

    /* ====================================================================
       3. NEXUS SWEETALERT 2 STYLING (PREMIUM UI)
       ==================================================================== */
    .swal2-container { z-index: 99999 !important; backdrop-filter: blur(4px); }
    .swal2-popup.nexus-alert {
        border-radius: 28px !important; padding: 2.5rem 2rem 2rem !important;
        background: #ffffff !important; box-shadow: 0 25px 50px -12px rgba(16, 185, 129, 0.2) !important;
        border: none !important; width: 26em !important;
    }
    .nexus-alert .swal2-title { font-family: 'Plus Jakarta Sans', sans-serif !important; font-weight: 900 !important; font-size: 1.4rem !important; color: #0f172a !important; padding-top: 0 !important; }
    .nexus-alert .swal2-html-container { font-family: 'Plus Jakarta Sans', sans-serif !important; font-size: 0.9rem !important; color: #64748b !important; line-height: 1.6 !important; }
    
    .btn-emerald-premium {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; color: white !important;
        border-radius: 9999px !important; padding: 12px 32px !important; font-size: 12px !important; font-weight: 800 !important;
        text-transform: uppercase !important; letter-spacing: 0.05em !important; border: none !important;
        box-shadow: 0 6px 15px -3px rgba(16, 185, 129, 0.4) !important; transition: all 0.2s ease !important;
    }
    .btn-emerald-premium:hover { transform: translateY(-2px) !important; box-shadow: 0 8px 20px -4px rgba(16, 185, 129, 0.5) !important; }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6 relative z-10">

    {{-- INTERFACE HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h1 class="text-[26px] md:text-[30px] font-black text-slate-800 tracking-tight leading-none mb-1.5">Input <span class="text-emerald-600">Pengukuran</span></h1>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Sistem Antropometri &bull; Kader Posyandu</p>
        </div>
        <a href="{{ route('kader.pemeriksaan.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200 rounded-[14px] font-bold text-[11px] uppercase tracking-widest transition-all shadow-sm flex items-center gap-2 active:scale-95">
            <i class="ph-bold ph-list-dashes text-[16px]"></i> Log Riwayat
        </a>
    </div>

    <form action="{{ route('kader.pemeriksaan.store') }}" method="POST" id="formPemeriksaan">
        @csrf
        
        {{-- CONTAINER UTAMA PANEL --}}
        <div class="bg-white border border-slate-100 rounded-[28px] shadow-sm p-6 md:p-10 relative">
            
            {{-- 1. SELEKSI KATEGORI (SEGMENTED CONTROL) --}}
            <div class="mb-8">
                <label class="nexus-label">1. Klasifikasi Sasaran</label>
                <input type="hidden" name="kategori" id="kategori_pasien" value="{{ $kategori ?? 'balita' }}">
                
                <div class="grid grid-cols-3 gap-2 bg-slate-100/80 p-1.5 rounded-[20px] border border-slate-200/60">
                    <div class="tab-btn tab-active flex items-center justify-center gap-2 py-3 rounded-[16px] font-black text-[11px] uppercase tracking-widest cursor-pointer select-none" data-target="balita">
                        <i class="ph-fill ph-baby text-[18px]"></i> <span class="hidden sm:inline">Balita</span>
                    </div>
                    <div class="tab-btn tab-inactive flex items-center justify-center gap-2 py-3 rounded-[16px] font-black text-[11px] uppercase tracking-widest cursor-pointer select-none" data-target="remaja">
                        <i class="ph-fill ph-graduation-cap text-[18px]"></i> <span class="hidden sm:inline">Remaja</span>
                    </div>
                    <div class="tab-btn tab-inactive flex items-center justify-center gap-2 py-3 rounded-[16px] font-black text-[11px] uppercase tracking-widest cursor-pointer select-none" data-target="lansia">
                        <i class="ph-fill ph-heartbeat text-[18px]"></i> <span class="hidden sm:inline">Lansia</span>
                    </div>
                </div>
            </div>

            {{-- 2. LOOKUP WARGA & TANGGAL --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8 border-b border-slate-100 pb-8 relative z-[50]">
                <div class="md:col-span-2 relative">
                    <label class="nexus-label">2. Identitas Warga <span class="text-rose-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                            <i class="ph-bold ph-magnifying-glass text-[18px]"></i>
                        </div>
                        <input type="text" id="pasien_search" class="nexus-input pl-11 pr-10 py-3.5" placeholder="Ketik minimal 2 huruf nama warga..." autocomplete="off">
                        
                        {{-- Spinner Loading Search --}}
                        <div id="search_spinner" class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none hidden">
                            <i class="ph-bold ph-spinner-gap animate-spin text-emerald-500 text-[18px]"></i>
                        </div>
                        
                        <input type="hidden" name="pasien_id" id="pasien_id">
                    </div>

                    {{-- PORTAL DROPDOWN SEARCH --}}
                    <div id="comboMenu" class="absolute z-[9999] top-[105%] left-0 right-0 bg-white/95 backdrop-blur-xl border border-slate-200 rounded-[20px] shadow-[0_20px_40px_-15px_rgba(0,0,0,0.1)] custom-scroll max-h-[260px] overflow-y-auto hidden overflow-hidden"></div>
                </div>

                <div class="relative z-10">
                    <label class="nexus-label">3. Tanggal Pemeriksaan <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_periksa" value="{{ date('Y-m-d') }}" class="nexus-input px-4 py-3.5" max="{{ date('Y-m-d') }}">
                </div>
            </div>

            {{-- 3. WORKSPACE PARAMETER FISIK --}}
            <div class="relative z-10">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-[16px] bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shadow-sm shrink-0">
                        <i class="ph-fill ph-clipboard-text text-[24px]"></i>
                    </div>
                    <div>
                        <h2 class="text-[16px] font-black text-slate-800 uppercase tracking-widest" id="workspaceTitle">Antropometri Balita</h2>
                        <p class="text-[11px] font-bold text-slate-400">Parameter fisik adaptif menyesuaikan klaster.</p>
                    </div>
                </div>

                {{-- KELOMPOK INPUT: BALITA --}}
                <div id="box_balita" class="workspace-area">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        <div>
                            <label class="nexus-label">Berat Badan</label>
                            <div class="relative">
                                <input type="number" step="0.1" name="berat_badan" class="nexus-input pl-4 pr-12 py-3.5 text-lg" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-slate-100 text-slate-500 font-black text-[9px] px-2 py-1 rounded-[8px] pointer-events-none">KG</span>
                            </div>
                        </div>
                        <div>
                            <label class="nexus-label">Tinggi Badan</label>
                            <div class="relative">
                                <input type="number" step="0.1" name="tinggi_badan" class="nexus-input pl-4 pr-12 py-3.5 text-lg" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-slate-100 text-slate-500 font-black text-[9px] px-2 py-1 rounded-[8px] pointer-events-none">CM</span>
                            </div>
                        </div>
                        <div>
                            <label class="nexus-label">Lingkar Kepala</label>
                            <div class="relative">
                                <input type="number" step="0.1" name="lingkar_kepala" class="nexus-input pl-4 pr-12 py-3.5 text-lg" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-slate-100 text-slate-500 font-black text-[9px] px-2 py-1 rounded-[8px] pointer-events-none">CM</span>
                            </div>
                        </div>
                        <div>
                            <label class="nexus-label">Lingkar Lengan</label>
                            <div class="relative">
                                <input type="number" step="0.1" name="lingkar_lengan" class="nexus-input pl-4 pr-12 py-3.5 text-lg" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-slate-100 text-slate-500 font-black text-[9px] px-2 py-1 rounded-[8px] pointer-events-none">CM</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KELOMPOK INPUT: REMAJA --}}
                <div id="box_remaja" class="workspace-area hidden">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-5">
                        <div>
                            <label class="nexus-label">Berat Badan</label>
                            <div class="relative">
                                <input type="number" step="0.1" id="bb_remaja" name="berat_badan" class="nexus-input pl-4 pr-12 py-3.5 text-lg" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-slate-100 text-slate-500 font-black text-[9px] px-2 py-1 rounded-[8px] pointer-events-none">KG</span>
                            </div>
                        </div>
                        <div>
                            <label class="nexus-label">Tinggi Badan</label>
                            <div class="relative">
                                <input type="number" step="0.1" id="tb_remaja" name="tinggi_badan" class="nexus-input pl-4 pr-12 py-3.5 text-lg" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-slate-100 text-slate-500 font-black text-[9px] px-2 py-1 rounded-[8px] pointer-events-none">CM</span>
                            </div>
                        </div>
                        <div>
                            <label class="nexus-label">Tensi Darah</label>
                            <input type="text" name="tekanan_darah" class="nexus-input px-4 py-3.5 text-lg tracking-wider" placeholder="120/80">
                        </div>
                        <div class="bg-slate-800 rounded-[16px] p-3 flex flex-col justify-center items-center shadow-inner h-[54px] mt-6 relative overflow-hidden">
                            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-700/50 to-transparent pointer-events-none"></div>
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-0.5 relative z-10">Skor IMT</span>
                            <div id="imt_remaja_screen" class="text-xl font-black text-emerald-400 leading-none relative z-10">0.0</div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="nexus-label">Gula Darah (Opsional)</label>
                            <div class="relative">
                                <input type="number" name="gula_darah" class="nexus-input pl-4 pr-16 py-3.5" placeholder="0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-slate-100 text-slate-500 font-black text-[9px] px-2 py-1 rounded-[8px] pointer-events-none">mg/dL</span>
                            </div>
                        </div>
                        <div>
                            <label class="nexus-label text-amber-600">LiLA (Khusus Putri)</label>
                            <div class="relative">
                                <input type="number" step="0.1" id="lila_remaja" name="lingkar_lengan" class="nexus-input bg-amber-50/30 border-amber-200 focus:border-amber-500 focus:ring-amber-500/15 pl-4 pr-12 py-3.5" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-amber-100 text-amber-700 font-black text-[9px] px-2 py-1 rounded-[8px] pointer-events-none">CM</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KELOMPOK INPUT: LANSIA --}}
                <div id="box_lansia" class="workspace-area hidden">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-5">
                        <div>
                            <label class="nexus-label">Berat Badan</label>
                            <div class="relative">
                                <input type="number" step="0.1" id="bb_lansia" name="berat_badan" class="nexus-input pl-4 pr-12 py-3.5 text-lg" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-slate-100 text-slate-500 font-black text-[9px] px-2 py-1 rounded-[8px] pointer-events-none">KG</span>
                            </div>
                        </div>
                        <div>
                            <label class="nexus-label">Tinggi Badan</label>
                            <div class="relative">
                                <input type="number" step="0.1" id="tb_lansia" name="tinggi_badan" class="nexus-input pl-4 pr-12 py-3.5 text-lg" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-slate-100 text-slate-500 font-black text-[9px] px-2 py-1 rounded-[8px] pointer-events-none">CM</span>
                            </div>
                        </div>
                        <div>
                            <label class="nexus-label">Tensi Darah</label>
                            <input type="text" name="tekanan_darah" class="nexus-input px-4 py-3.5 text-lg tracking-wider" placeholder="130/80">
                        </div>
                        <div class="bg-slate-800 rounded-[16px] p-3 flex flex-col justify-center items-center shadow-inner h-[54px] mt-6 relative overflow-hidden">
                            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-700/50 to-transparent pointer-events-none"></div>
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-0.5 relative z-10">Skor IMT</span>
                            <div id="imt_lansia_screen" class="text-xl font-black text-emerald-400 leading-none relative z-10">0.0</div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        <div>
                            <label class="nexus-label">Gula Darah</label>
                            <div class="relative">
                                <input type="number" name="gula_darah" class="nexus-input pl-4 pr-16 py-3.5" placeholder="0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-slate-100 text-slate-500 font-black text-[9px] px-2 py-1 rounded-[8px] pointer-events-none">mg/dL</span>
                            </div>
                        </div>
                        <div>
                            <label class="nexus-label">Kolesterol</label>
                            <div class="relative">
                                <input type="number" name="kolesterol" class="nexus-input pl-4 pr-16 py-3.5" placeholder="0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-slate-100 text-slate-500 font-black text-[9px] px-2 py-1 rounded-[8px] pointer-events-none">mg/dL</span>
                            </div>
                        </div>
                        <div>
                            <label class="nexus-label">Asam Urat</label>
                            <div class="relative">
                                <input type="number" step="0.1" name="asam_urat" class="nexus-input pl-4 pr-16 py-3.5" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-slate-100 text-slate-500 font-black text-[9px] px-2 py-1 rounded-[8px] pointer-events-none">mg/dL</span>
                            </div>
                        </div>
                        <div>
                            <label class="nexus-label">Lingkar Perut</label>
                            <div class="relative">
                                <input type="number" step="0.1" name="lingkar_perut" class="nexus-input pl-4 pr-12 py-3.5" placeholder="0.0">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 bg-slate-100 text-slate-500 font-black text-[9px] px-2 py-1 rounded-[8px] pointer-events-none">CM</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- GOLD ALERT KEK --}}
                <div id="warn_kek" class="bg-amber-50/80 backdrop-blur border border-amber-200 text-amber-800 p-4 rounded-[16px] mt-6 hidden items-start gap-3 shadow-sm">
                    <i class="ph-fill ph-warning-circle text-[24px] text-amber-500 mt-0.5"></i>
                    <div>
                        <h4 class="text-[12px] font-black uppercase tracking-widest text-amber-600 mb-0.5">Indikasi KEK Terdeteksi</h4>
                        <p class="text-[12px] font-semibold leading-relaxed">Nilai LiLA di bawah 23.5 cm pada Remaja Putri berisiko Kurang Energi Kronis. Data penandaan visual akan diteruskan secara otomatis ke Buku Kesehatan Digital Bidan.</p>
                    </div>
                </div>
            </div>

            {{-- 4. CATATAN / KELUHAN --}}
            <div class="mt-8 border-t border-slate-100 pt-6 relative z-10">
                <label class="nexus-label">Catatan Tambahan (Keluhan Subjektif)</label>
                <textarea name="keluhan" class="nexus-input p-4 text-sm resize-none h-24" placeholder="Tuliskan keluhan yang dirasakan warga atau pesan yang ingin diteruskan ke meja Bidan..."></textarea>
            </div>
        </div>

        {{-- FORM SUBMIT SUB-BAR --}}
        <div class="flex flex-col sm:flex-row justify-end gap-3 mt-6">
            <a href="{{ route('kader.pemeriksaan.index') }}" class="px-8 py-3.5 rounded-full bg-white border border-slate-200 text-slate-600 font-black hover:bg-slate-50 transition-all uppercase tracking-widest text-[11px] flex justify-center items-center shadow-sm">
                Batal
            </a>
            <button type="submit" id="btnSubmit" class="px-10 py-3.5 rounded-full bg-gradient-to-r from-emerald-600 to-emerald-500 text-white font-black hover:from-emerald-500 hover:to-emerald-400 shadow-[0_8px_20px_-6px_rgba(16,185,129,0.5)] transition-all uppercase tracking-widest text-[11px] flex justify-center items-center gap-2 active:scale-95 border border-emerald-400">
                <i class="ph-bold ph-floppy-disk text-[16px] text-emerald-100"></i> Simpan Data Fisik
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // --- 1. Tab Engine Workspace Adaptif ---
        const tabs = document.querySelectorAll('.tab-btn');
        const inputKategori = document.getElementById('kategori_pasien');
        const headerTitle = document.getElementById('workspaceTitle');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                tabs.forEach(t => {
                    t.classList.remove('tab-active');
                    t.classList.add('tab-inactive');
                });
                
                this.classList.remove('tab-inactive');
                this.classList.add('tab-active');
                
                const target = this.dataset.target;
                inputKategori.value = target;

                const titles = { balita: 'Antropometri Balita', remaja: 'Skrining Fisik Remaja', lansia: 'Skrining Fisik Lansia' };
                headerTitle.textContent = titles[target];

                // Sembunyikan & Disable form lain agar bersih
                document.querySelectorAll('.workspace-area').forEach(wa => {
                    wa.classList.add('hidden');
                    wa.querySelectorAll('input').forEach(input => input.disabled = true);
                });

                const currentWorkspace = document.getElementById('box_' + target);
                currentWorkspace.classList.remove('hidden');
                currentWorkspace.querySelectorAll('input').forEach(input => input.disabled = false);

                // Reset Field Pasien untuk keamanan
                document.getElementById('pasien_id').value = '';
                document.getElementById('pasien_search').value = '';
                document.getElementById('warn_kek').classList.add('hidden');
            });
        });

        // Init
        const initKategori = inputKategori.value || 'balita';
        const activeTab = Array.from(tabs).find(t => t.dataset.target === initKategori);
        if (activeTab) activeTab.click();


        // --- 2. Live Search Autocomplete Engine ---
        const searchInput = document.getElementById('pasien_search');
        const comboMenu = document.getElementById('comboMenu');
        const searchSpinner = document.getElementById('search_spinner');
        let debounceTimer;

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();
            const kat = inputKategori.value;

            if (query.length < 2) {
                comboMenu.classList.add('hidden');
                searchSpinner.classList.add('hidden');
                return;
            }

            searchSpinner.classList.remove('hidden');

            debounceTimer = setTimeout(async () => {
                try {
                    const response = await fetch(`{{ route('kader.pemeriksaan.api') }}?kategori=${kat}&search=${encodeURIComponent(query)}`);
                    const res = await response.json();
                    
                    searchSpinner.classList.add('hidden');

                    if (res.status === 'success' && res.data.length > 0) {
                        comboMenu.innerHTML = res.data.map(p => `
                            <div class="px-5 py-4 hover:bg-emerald-50 border-b border-slate-100 cursor-pointer transition-colors flex justify-between items-center portal-option" data-id="${p.id}" data-nama="${p.nama}" data-jk="${p.jenis_kelamin || ''}">
                                <div>
                                    <div class="font-black text-slate-800 text-[13px]">${p.nama}</div>
                                    <div class="text-[10px] font-bold text-emerald-600 mt-1 tracking-widest uppercase"><i class="ph-bold ph-identification-card"></i> ${p.nik || 'N/A'}</div>
                                </div>
                                <i class="ph-bold ph-arrow-right text-emerald-400 text-[16px]"></i>
                            </div>
                        `).join('');
                        comboMenu.classList.remove('hidden');
                        
                        document.querySelectorAll('.portal-option').forEach(row => {
                            row.addEventListener('click', function() {
                                document.getElementById('pasien_id').value = this.dataset.id;
                                document.getElementById('pasien_search').value = this.dataset.nama;
                                document.getElementById('pasien_id').setAttribute('data-jk', this.dataset.jk);
                                comboMenu.classList.add('hidden');
                                // Trigger LILA check jika ada nilainya
                                if(document.getElementById('lila_remaja')) {
                                    document.getElementById('lila_remaja').dispatchEvent(new Event('input'));
                                }
                            });
                        });
                    } else {
                        comboMenu.innerHTML = '<div class="p-6 text-center text-slate-400 text-[11px] uppercase tracking-widest font-black"><i class="ph-bold ph-file-search text-[24px] mb-2 block"></i> Warga Tidak Ditemukan</div>';
                        comboMenu.classList.remove('hidden');
                    }
                } catch (e) {
                    searchSpinner.classList.add('hidden');
                }
            }, 400);
        });

        document.addEventListener('click', function(e) {
            if (!comboMenu.contains(e.target) && e.target !== searchInput) {
                comboMenu.classList.add('hidden');
            }
        });


        // --- 3. Kalkulator IMT Real-time ---
        function calculateIMT(bbId, tbId, screenId) {
            const bb = parseFloat(document.getElementById(bbId).value);
            const tb = parseFloat(document.getElementById(tbId).value) / 100;
            const display = document.getElementById(screenId);

            if (bb > 0 && tb > 0) {
                display.textContent = (bb / (tb * tb)).toFixed(1);
            } else {
                display.textContent = '0.0';
            }
        }

        ['bb_remaja', 'tb_remaja'].forEach(id => {
            const el = document.getElementById(id);
            if(el) el.addEventListener('input', () => calculateIMT('bb_remaja', 'tb_remaja', 'imt_remaja_screen'));
        });
        
        ['bb_lansia', 'tb_lansia'].forEach(id => {
            const el = document.getElementById(id);
            if(el) el.addEventListener('input', () => calculateIMT('bb_lansia', 'tb_lansia', 'imt_lansia_screen'));
        });


        // --- 4. Deteksi Risiko KEK (Remaja Putri) ---
        const noticeKek = document.getElementById('warn_kek');
        const lilaRemaja = document.getElementById('lila_remaja');

        if(lilaRemaja) {
            lilaRemaja.addEventListener('input', function() {
                const lila = parseFloat(this.value);
                const jk = document.getElementById('pasien_id').getAttribute('data-jk');
                
                if (lila > 0 && lila < 23.5 && jk === 'P') {
                    noticeKek.classList.remove('hidden');
                    noticeKek.classList.add('flex');
                } else {
                    noticeKek.classList.add('hidden');
                    noticeKek.classList.remove('flex');
                }
            });
        }


        // --- 5. AJAX FORM SUBMIT (NEXUS SWEETALERT) ---
        document.getElementById('formPemeriksaan').addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!document.getElementById('pasien_id').value) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Identitas Kosong',
                    html: 'Sistem tidak dapat menyimpan data tanpa subjek. Silakan cari dan pilih nama warga pada kolom pencarian.',
                    customClass: { popup: 'nexus-alert', confirmButton: 'btn-emerald-premium' },
                    buttonsStyling: false
                });
                return;
            }

            const btn = document.getElementById('btnSubmit');
            const originalBtnHTML = btn.innerHTML;
            
            // Loading State UI
            btn.disabled = true;
            btn.classList.add('opacity-70', 'cursor-not-allowed');
            btn.innerHTML = '<i class="ph-bold ph-spinner-gap animate-spin text-[16px]"></i> Merekam...';

            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terekam!',
                        text: result.message,
                        customClass: { popup: 'nexus-alert', confirmButton: 'btn-emerald-premium' },
                        buttonsStyling: false
                    }).then(() => {
                        window.location.href = result.redirect;
                    });
                } else {
                    // Extract validation errors dynamically
                    let errMsg = result.message || 'Sistem menolak input Anda.';
                    if (result.errors) {
                        errMsg = Object.values(result.errors).map(msg => `&bull; ${msg}`).join('<br>');
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        html: `<div class="text-left mt-2 text-rose-500 font-medium">${errMsg}</div>`,
                        customClass: { popup: 'nexus-alert', confirmButton: 'btn-emerald-premium' },
                        buttonsStyling: false
                    });
                    
                    // Reset button
                    btn.disabled = false;
                    btn.classList.remove('opacity-70', 'cursor-not-allowed');
                    btn.innerHTML = originalBtnHTML;
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Koneksi Gagal',
                    text: 'Terjadi gangguan jaringan saat menghubungi server inti.',
                    customClass: { popup: 'nexus-alert', confirmButton: 'btn-emerald-premium' },
                    buttonsStyling: false
                });
                
                // Reset button
                btn.disabled = false;
                btn.classList.remove('opacity-70', 'cursor-not-allowed');
                btn.innerHTML = originalBtnHTML;
            }
        });
    });
</script>
@endpush
@endsection