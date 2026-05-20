@extends('layouts.kader')
@section('title', 'Koreksi Pemeriksaan Fisik')
@section('page-name', 'Mode Koreksi Antropometri')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    /* =================================================================
       NEXUS SAAS DESIGN SYSTEM (KONSISTEN DENGAN CREATE)
       ================================================================= */
    
    /* Animasi Masuk */
    .animate-fade-in { opacity: 0; animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .delay-100 { animation-delay: 0.1s; } .delay-200 { animation-delay: 0.15s; } .delay-300 { animation-delay: 0.2s; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    /* Kartu Utama (Sama persis dengan Create) */
    .nexus-card {
        background: #ffffff; border: 1px solid #e2e8f0; border-radius: 24px;
        box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.05); overflow: hidden; margin-bottom: 1.5rem;
    }
    .nexus-card-header {
        background: #f8fafc; border-bottom: 1px solid #f1f5f9; padding: 1.25rem 1.5rem;
        display: flex; align-items: center; gap: 1rem;
    }
    .nexus-card-body { padding: 1.5rem 2rem; }
    @media (min-width: 768px) { .nexus-card-body { padding: 2rem; } }
    
    /* Label & Input Form (Tegas & Rapi) */
    .form-label { display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 0.5rem; }
    .form-input, .form-textarea {
        width: 100%; background-color: #f8fafc; border: 2px solid #e2e8f0; color: #0f172a; 
        font-family: 'Inter', sans-serif; font-size: 0.875rem; font-weight: 600;
        border-radius: 12px; padding: 0.75rem 1rem; outline: none; transition: all 0.2s ease;
    }
    .form-input:focus, .form-textarea:focus { background-color: #ffffff; border-color: #f59e0b; box-shadow: 0 4px 15px -3px rgba(245, 158, 11, 0.15); transform: translateY(-1px); }
    .form-input::placeholder, .form-textarea::placeholder { color: #94a3b8; font-weight: 400; }
    
    /* State Terkunci / Readonly */
    .form-input:disabled { background-color: #f1f5f9; border-color: #e2e8f0; color: #64748b; cursor: not-allowed; box-shadow: none; opacity: 1; }
    
    /* Input Unit Wrapper (cm, kg) */
    .input-wrapper { position: relative; display: flex; align-items: center; }
    .input-wrapper .unit { position: absolute; right: 1rem; font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; pointer-events: none; }
    .input-wrapper .form-input { padding-right: 2.5rem; text-align: left; }

    /* SweetAlert Anti-Bug (Persis Create) */
    body.swal2-shown:not(.swal2-toast-shown) .swal2-container { z-index: 10000 !important; backdrop-filter: blur(4px) !important; background: rgba(15, 23, 42, 0.4) !important; }
    .nexus-modal { border-radius: 28px !important; padding: 2rem !important; background: #ffffff !important; width: 26em !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25) !important; border: 1px solid #f1f5f9 !important; }
    .nexus-modal .swal2-title { font-family: 'Poppins', sans-serif !important; font-weight: 800 !important; font-size: 1.25rem !important; color: #0f172a !important; margin-bottom: 0.5rem !important; }
    .btn-swal-primary { background: #f59e0b !important; color: white !important; border-radius: 100px !important; padding: 12px 24px !important; font-weight: 700 !important; font-size: 11px !important; text-transform: uppercase !important; letter-spacing: 0.05em !important; border: none !important; transition: 0.2s !important; }
    .btn-swal-primary:hover { background: #d97706 !important; }
</style>
@endpush

@section('content')
<div class="max-w-[850px] mx-auto animate-fade-in pb-20 relative z-10 mt-2">

    {{-- AURA BACKGROUND (Konsisten dengan Create, tapi diberi sentuhan Amber untuk Edit) --}}
    <div class="fixed top-0 right-0 w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-[120px] pointer-events-none -z-10"></div>
    <div class="fixed bottom-0 left-0 w-[400px] h-[400px] bg-orange-500/10 rounded-full blur-[120px] pointer-events-none -z-10"></div>

    {{-- HEADER (Identik dengan Create) --}}
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-5">
            <a href="{{ route('kader.pemeriksaan.index') }}" class="w-12 h-12 rounded-2xl bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-slate-50 hover:text-amber-600 transition-colors shadow-sm shrink-0">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-md border border-amber-200 bg-amber-50 text-amber-600 text-[9px] font-black uppercase tracking-widest shadow-sm animate-pulse"><i class="fas fa-pen-nib mr-1"></i> Mode Koreksi Data</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight font-poppins">Koreksi Pemeriksaan Fisik</h1>
                <p class="text-slate-500 font-medium text-[13px] mt-1">Ubah data antropometri pasien yang terdapat kekeliruan input.</p>
            </div>
        </div>
    </div>

    <form action="{{ route('kader.pemeriksaan.update', $pemeriksaan->id) }}" method="POST" id="formPemeriksaan">
        @csrf @method('PUT')
        
        {{-- ==========================================================
             BLOK 1: IDENTIFIKASI TERKUNCI
             ========================================================== --}}
        <div class="nexus-card animate-fade-in delay-100">
            <div class="nexus-card-header bg-slate-50 border-b border-slate-100">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl border border-amber-100 shadow-sm"><i class="fas fa-lock"></i></div>
                <div>
                    <h5 class="font-black text-slate-800 text-[14px] uppercase tracking-widest font-poppins">Identitas Terkunci</h5>
                    <p class="text-[12px] font-medium text-slate-500 mt-0.5">Kategori dan Nama Pasien bersifat permanen pada mode edit.</p>
                </div>
            </div>
            
            <div class="nexus-card-body grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="form-label text-slate-400">Nama Pasien / Warga</label>
                    <input type="text" value="{{ $pemeriksaan->nama_pasien }}" disabled class="form-input text-slate-500">
                    <input type="hidden" name="pasien_id" value="{{ $pemeriksaan->pasien_id }}">
                    {{-- Simpan Gender untuk Logic KEK --}}
                    <input type="hidden" id="pasien_jk" value="{{ $pemeriksaan->kunjungan->pasien->jenis_kelamin ?? '' }}">
                </div>
                <div>
                    <label class="form-label text-slate-400">Kategori Sasaran</label>
                    @php
                        $katDisplay = match($pemeriksaan->kategori_pasien) {
                            'balita' => 'Bayi & Balita (0-5 Tahun)',
                            'remaja' => 'Remaja',
                            'lansia' => 'Lansia',
                            default => strtoupper(str_replace('_', ' ', $pemeriksaan->kategori_pasien))
                        };
                    @endphp
                    <input type="text" value="{{ $katDisplay }}" disabled class="form-input text-slate-500">
                    <input type="hidden" id="kategori_pasien" name="kategori_pasien" value="{{ $pemeriksaan->kategori_pasien }}">
                </div>
                <div class="md:col-span-2 border-t border-slate-100 pt-6 mt-2">
                    <label class="form-label">Tanggal Pemeriksaan <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_periksa" value="{{ $pemeriksaan->tanggal_periksa->format('Y-m-d') }}" required max="{{ date('Y-m-d') }}" class="form-input w-full md:w-1/2">
                </div>
            </div>
        </div>

        {{-- ==========================================================
             BLOK 2: UKUR DASAR (SEMUA KATEGORI)
             ========================================================== --}}
        <div class="nexus-card animate-fade-in delay-200">
            <div class="nexus-card-header">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl border border-emerald-100 shadow-sm"><i class="fas fa-weight"></i></div>
                <div>
                    <h5 class="font-black text-slate-800 text-[14px] uppercase tracking-widest font-poppins">Ukur Antropometri Dasar</h5>
                    <p class="text-[12px] font-medium text-slate-500 mt-0.5">Pengukuran wajib. Sistem otomatis mengkalkulasi ulang IMT pasien.</p>
                </div>
            </div>
            
            <div class="nexus-card-body grid grid-cols-2 md:grid-cols-4 gap-6 items-start">
                <div>
                    <label class="form-label">Berat Badan <span class="text-rose-500">*</span></label>
                    <div class="input-wrapper">
                        <input type="number" step="0.1" name="berat_badan" id="berat_badan" value="{{ $pemeriksaan->berat_badan }}" required class="form-input text-lg font-black text-indigo-700" placeholder="0.0">
                        <span class="unit">kg</span>
                    </div>
                </div>
                <div>
                    <label class="form-label">Tinggi/Panjang <span class="text-rose-500">*</span></label>
                    <div class="input-wrapper">
                        <input type="number" step="0.1" name="tinggi_badan" id="tinggi_badan" value="{{ $pemeriksaan->tinggi_badan }}" required class="form-input text-lg font-black text-indigo-700" placeholder="0.0">
                        <span class="unit">cm</span>
                    </div>
                </div>
                <div>
                    <label class="form-label">Suhu Tubuh</label>
                    <div class="input-wrapper">
                        <input type="number" step="0.1" name="suhu_tubuh" value="{{ $pemeriksaan->suhu_tubuh }}" class="form-input text-lg font-black text-rose-500" placeholder="36.5">
                        <span class="unit">°C</span>
                    </div>
                </div>

                {{-- IMT Widget (Sleek Dark Mode Panel - Identik dgn Create) --}}
                <div class="col-span-2 md:col-span-1 bg-slate-900 rounded-[14px] p-4 flex flex-col justify-center items-center h-full shadow-lg relative overflow-hidden mt-1 md:mt-0">
                    <div class="absolute -right-3 -top-3 text-white/5 text-6xl transform rotate-12"><i class="fas fa-calculator"></i></div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 relative z-10">Nilai IMT</p>
                    <div class="flex items-center gap-2 relative z-10">
                        <span id="imt-val" class="text-2xl font-black text-white font-poppins leading-none">{{ $pemeriksaan->imt ?? '0.0' }}</span>
                    </div>
                    <span id="imt-kat" class="mt-2 px-3 py-1 rounded-md border border-slate-700 bg-slate-800 text-slate-400 text-[9px] font-bold uppercase tracking-widest relative z-10">-</span>
                </div>
            </div>
        </div>

        {{-- ==========================================================
             BLOK 3: FORM DINAMIS (KATEGORI SPESIFIK)
             ========================================================== --}}
        @php
            // Logic Smart Theme Sama Dengan Create JS
            $kat = $pemeriksaan->kategori_pasien;
            $isBaby = ($kat == 'balita' && $pemeriksaan->kunjungan && $pemeriksaan->kunjungan->pasien && $pemeriksaan->kunjungan->pasien->usia_bulan < 12);
            
            $themeMap = [
                'balita' => ['color' => 'sky', 'icon' => 'fa-child', 'title' => 'Pemeriksaan Balita', 'desc' => 'Pengukuran spesifik tumbuh kembang balita (1-5 Tahun).'],
                'remaja' => ['color' => 'indigo', 'icon' => 'fa-user-graduate', 'title' => 'Pemeriksaan Remaja', 'desc' => 'Skrining kesehatan fisik berkala remaja.'],
                'lansia' => ['color' => 'emerald', 'icon' => 'fa-wheelchair', 'title' => 'Cek Medis Lansia', 'desc' => 'Pengecekan indikator penyakit tidak menular (PTM).']
            ];

            if ($isBaby) {
                $themeMap['balita']['icon'] = 'fa-baby';
                $themeMap['balita']['title'] = 'Pemeriksaan Bayi';
                $themeMap['balita']['desc'] = 'Pengukuran spesifik pertumbuhan awal (0-11 Bulan).';
            }

            $theme = $themeMap[$kat] ?? ['color' => 'slate', 'icon' => 'fa-stethoscope', 'title' => 'Pemeriksaan Khusus', 'desc' => '-'];
            $c = $theme['color'];
        @endphp

        <div class="nexus-card border-2 border-{{$c}}-100 shadow-sm shadow-{{$c}}-500/5">
            <div class="nexus-card-header bg-{{$c}}-50/80 border-b border-{{$c}}-100">
                <div class="w-12 h-12 rounded-2xl bg-{{$c}}-100 text-{{$c}}-600 flex items-center justify-center text-xl border border-{{$c}}-200 shadow-sm"><i class="fas {{ $theme['icon'] }}"></i></div>
                <div>
                    <h5 class="font-black text-{{$c}}-900 text-[14px] uppercase tracking-widest font-poppins">{{ $theme['title'] }}</h5>
                    <p class="text-[12px] font-medium text-{{$c}}-700/70 mt-0.5">{{ $theme['desc'] }}</p>
                </div>
            </div>
            <div class="nexus-card-body">
                
                {{-- KONTEN BALITA --}}
                @if($kat == 'balita')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 md:gap-8">
                    <div>
                        <label class="form-label text-sky-800">Lingkar Kepala</label>
                        <div class="input-wrapper">
                            <input type="number" step="0.1" name="lingkar_kepala" value="{{ $pemeriksaan->lingkar_kepala }}" class="form-input focus:border-sky-500 focus:ring-sky-100">
                            <span class="unit">cm</span>
                        </div>
                    </div>
                    <div>
                        <label class="form-label text-sky-800">Lingkar Lengan Atas (LiLA)</label>
                        <div class="input-wrapper">
                            <input type="number" step="0.1" name="lingkar_lengan" value="{{ $pemeriksaan->lingkar_lengan }}" class="form-input focus:border-sky-500 focus:ring-sky-100">
                            <span class="unit">cm</span>
                        </div>
                    </div>
                </div>
                @endif

                

                {{-- KONTEN REMAJA --}}
                @if($kat == 'remaja')
                <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
                    <div class="col-span-1">
                        <label class="form-label text-indigo-800">Tensi Darah</label>
                        <input type="text" name="tekanan_darah" value="{{ $pemeriksaan->tekanan_darah }}" class="form-input font-mono focus:border-indigo-500" placeholder="110/80">
                    </div>
                    <div class="col-span-1">
                        <label class="form-label text-indigo-800">Hemoglobin</label>
                        <div class="input-wrapper">
                            <input type="number" step="0.1" name="hemoglobin" value="{{ $pemeriksaan->hemoglobin }}" class="form-input focus:border-indigo-500">
                            <span class="unit">g/dL</span>
                        </div>
                    </div>
                    <div class="col-span-1">
                        <label class="form-label text-indigo-800">Gula Darah</label>
                        <div class="input-wrapper">
                            <input type="number" step="0.1" name="gula_darah" value="{{ $pemeriksaan->gula_darah }}" class="form-input focus:border-indigo-500">
                            <span class="unit">mg/dL</span>
                        </div>
                    </div>
                    <div class="col-span-1">
                        <label class="form-label text-indigo-800">L. Perut</label>
                        <div class="input-wrapper">
                            <input type="number" step="0.1" name="lingkar_perut" value="{{ $pemeriksaan->lingkar_perut }}" class="form-input focus:border-indigo-500">
                            <span class="unit">cm</span>
                        </div>
                    </div>
                    <div class="col-span-2 md:col-span-1 bg-rose-50 p-4 rounded-xl border border-rose-100 mt-2">
                        <label class="form-label text-rose-600">LiLA <span class="font-medium text-rose-400 lowercase">(Putri)</span></label>
                        <div class="input-wrapper">
                            <input type="number" step="0.1" id="lila_remaja" name="lingkar_lengan" value="{{ $pemeriksaan->lingkar_lengan }}" class="form-input border-rose-200 focus:border-rose-400 bg-white">
                            <span class="unit text-rose-400">cm</span>
                        </div>
                    </div>
                </div>
                @endif

                {{-- KONTEN LANSIA --}}
                @if($kat == 'lansia')
                <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
                    <div class="col-span-1">
                        <label class="form-label text-emerald-800">Tensi Darah</label>
                        <input type="text" name="tekanan_darah" value="{{ $pemeriksaan->tekanan_darah }}" class="form-input font-mono focus:border-emerald-500" placeholder="130/90">
                    </div>
                    <div class="col-span-1">
                        <label class="form-label text-emerald-800">Gula Darah</label>
                        <div class="input-wrapper">
                            <input type="number" step="0.1" name="gula_darah" value="{{ $pemeriksaan->gula_darah }}" class="form-input focus:border-emerald-500">
                            <span class="unit">mg/dL</span>
                        </div>
                    </div>
                    <div class="col-span-1">
                        <label class="form-label text-emerald-800">Kolesterol</label>
                        <div class="input-wrapper">
                            <input type="number" name="kolesterol" value="{{ $pemeriksaan->kolesterol }}" class="form-input focus:border-emerald-500">
                            <span class="unit">mg/dL</span>
                        </div>
                    </div>
                    <div class="col-span-1">
                        <label class="form-label text-emerald-800">Asam Urat</label>
                        <div class="input-wrapper">
                            <input type="number" step="0.1" name="asam_urat" value="{{ $pemeriksaan->asam_urat }}" class="form-input focus:border-emerald-500">
                            <span class="unit">mg/dL</span>
                        </div>
                    </div>
                    <div class="col-span-2 md:col-span-1">
                        <label class="form-label text-emerald-800">Lingkar Perut</label>
                        <div class="input-wrapper">
                            <input type="number" step="0.1" name="lingkar_perut" value="{{ $pemeriksaan->lingkar_perut }}" class="form-input focus:border-emerald-500">
                            <span class="unit">cm</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- WARNING KEK --}}
        <div id="warn_kek" class="mb-8 bg-gradient-to-r from-rose-50 to-white border-2 border-rose-400 rounded-[20px] p-6 hidden items-center gap-5 shadow-lg shadow-rose-500/10 transform transition-all duration-500">
            <div class="w-14 h-14 rounded-full bg-rose-100 flex items-center justify-center text-rose-500 text-2xl shrink-0 animate-pulse"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <p class="text-[12px] font-black text-rose-800 uppercase tracking-widest mb-1">Peringatan Medis: Risiko KEK</p>
                <p class="text-[13px] font-medium text-rose-600 leading-relaxed">Nilai Lingkar Lengan Atas (LiLA) pasien < 23.5 cm. Pasien berisiko Kurang Energi Kronis. Data ini akan disorot untuk perhatian Bidan.</p>
            </div>
        </div>

        {{-- ==========================================================
             BLOK 4: CATATAN (Opsional)
             ========================================================== --}}
        <div class="nexus-card animate-fade-in delay-300">
            <div class="nexus-card-header">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl border border-amber-100 shadow-sm"><i class="fas fa-comment-medical"></i></div>
                <div>
                    <h5 class="font-black text-slate-800 text-[14px] uppercase tracking-widest font-poppins">Catatan Lapangan</h5>
                    <p class="text-[12px] font-medium text-slate-500 mt-0.5">Keluhan subyektif dari pasien dan catatan khusus (Opsional).</p>
                </div>
            </div>
            <div class="nexus-card-body grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="form-label">Keluhan Utama</label>
                    <textarea name="keluhan" rows="3" class="form-textarea resize-none" placeholder="Tuliskan keluhan yang dirasakan pasien...">{{ old('keluhan', $pemeriksaan->keluhan) }}</textarea>
                </div>
                <div>
                    <label class="form-label">Catatan Kader</label>
                    <textarea name="catatan_kader" rows="3" class="form-textarea resize-none" placeholder="Pesan untuk Bidan...">{{ old('catatan_kader', $pemeriksaan->catatan_kader) }}</textarea>
                </div>
            </div>
        </div>

        {{-- ==========================================================
             ACTION BAR (Tombol Simpan)
             ========================================================== --}}
        <div class="flex flex-col-reverse sm:flex-row justify-end items-center gap-4 mb-10 pt-2">
            <a href="{{ route('kader.pemeriksaan.index') }}" class="w-full sm:w-auto px-8 py-4 rounded-xl font-black text-slate-500 bg-white border-2 border-slate-200 hover:bg-slate-50 hover:text-slate-800 transition-colors uppercase text-[12px] tracking-widest text-center shadow-sm">
                Batalkan
            </a>
            <button type="submit" id="btnSubmit" class="w-full sm:w-auto px-10 py-4 rounded-xl font-black text-white bg-slate-900 hover:bg-amber-600 transition-all shadow-[0_10px_25px_rgba(0,0,0,0.2)] hover:shadow-[0_15px_35px_rgba(245,158,11,0.3)] uppercase text-[12px] tracking-widest flex items-center justify-center gap-2 hover:-translate-y-1">
                <i class="fas fa-check-circle text-sm"></i> Simpan Koreksi Medis
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // 1. ENGINE KALKULATOR IMT CERDAS & KONSISTEN (Kecuali Balita)
    const bbInput = document.getElementById('berat_badan'), tbInput = document.getElementById('tinggi_badan');
    
    function hitungIMT() {
        const bb = parseFloat(bbInput.value), tb = parseFloat(tbInput.value) / 100;
        const imtVal = document.getElementById('imt-val');
        const imtKatEl = document.getElementById('imt-kat');
        
        if(!imtVal || !imtKatEl) return;

        if(bb > 0 && tb > 0) {
            const imt = (bb / (tb * tb)).toFixed(2);
            let label = 'Normal', color = 'bg-emerald-500/20 text-emerald-300 border-emerald-400/50';
            
            if(imt < 18.5) { label = 'Kurus'; color = 'bg-amber-500/20 text-amber-300 border-amber-400/50'; }
            else if(imt >= 25 && imt < 27) { label = 'Gemuk'; color = 'bg-rose-500/20 text-rose-300 border-rose-400/50'; }
            else if(imt >= 27) { label = 'Obesitas'; color = 'bg-rose-600 text-white border-rose-500'; }
            
            imtVal.textContent = imt;
            imtKatEl.textContent = label;
            imtKatEl.className = `mt-1 px-3 py-1 rounded text-[9px] font-black uppercase tracking-widest border transition-colors ${color}`;
        } else {
            imtVal.textContent = '0.0';
            imtKatEl.textContent = '-';
            imtKatEl.className = 'mt-1 px-3 py-1 rounded text-[9px] font-black uppercase tracking-widest border bg-slate-800 text-slate-400 border-slate-700';
        }
    }
    
    if(bbInput && tbInput) {
        bbInput.addEventListener('input', hitungIMT); tbInput.addEventListener('input', hitungIMT); 
        hitungIMT(); // Init on load
    }

    // 2. DETEKSI DINI RISIKO KEK SAAT EDIT
    function cekLila() {
        const remaja = document.getElementById('lila_remaja');
        const val = parseFloat(|| (remaja ? remaja.value : null));
        const kat = document.getElementById('kategori_pasien').value; 
        const jk = document.getElementById('pasien_jk').value;
        const warn = document.getElementById('warn_kek');

        if (val > 0 && val < 23.5 || (kat === 'remaja' && jk === 'P'))) {
            warn.classList.remove('hidden'); warn.classList.add('flex');
        } else {
            warn.classList.add('hidden'); warn.classList.remove('flex');
        }
    }
    document.getElementById('lila_remaja')?.addEventListener('input', cekLila);
    cekLila(); // Init KEK check on load

    // 3. UX SUBMIT DENGAN AJAX MODAL (ANTI-BUG ABU-ABU)
    document.getElementById('formPemeriksaan').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Menyimpan Koreksi...',
            html: '<p class="text-slate-500 text-sm mt-1">Sistem sedang memvalidasi dan merekam data medis ke server...</p>',
            allowOutsideClick: false, showConfirmButton: false, backdrop: true,
            customClass: { popup: 'nexus-modal' },
            willOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData(this);
        const btn = document.getElementById('btnSubmit');
        btn.classList.add('opacity-50', 'cursor-wait');
        btn.disabled = true;

        try {
            const response = await fetch(this.action, {
                method: 'POST', // Native form is POST, Laravel interprets @_method=PUT
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const result = await response.json();

            if (response.ok && result.status === 'success') {
                // Notifikasi toast tanpa layar abu-abu
                const Toast = Swal.mixin({
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 1500, backdrop: false,
                    customClass: { popup: 'bg-white border border-slate-200 shadow-lg rounded-xl px-4 py-3 text-slate-800 font-medium text-sm' }
                });
                Toast.fire({ icon: 'success', title: result.message }).then(() => {
                    window.location.href = result.redirect;
                });
            } else {
                Swal.fire({ 
                    icon: 'error', title: 'Gagal Menyimpan', text: result.message, 
                    confirmButtonText: 'Tutup', backdrop: true, customClass: { popup: 'nexus-modal', confirmButton: 'btn-swal-primary' } 
                });
                btn.classList.remove('opacity-50', 'cursor-wait');
                btn.disabled = false;
            }
        } catch (error) {
            Swal.fire({ 
                icon: 'error', title: 'Koneksi Gagal', text: 'Terjadi gangguan jaringan saat menghubungi server.', 
                confirmButtonText: 'Tutup', backdrop: true, customClass: { popup: 'nexus-modal', confirmButton: 'btn-swal-primary' } 
            });
            btn.classList.remove('opacity-50', 'cursor-wait');
            btn.disabled = false;
        }
    });
</script>
@endpush
@endsection