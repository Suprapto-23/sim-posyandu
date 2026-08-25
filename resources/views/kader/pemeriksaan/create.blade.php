@extends('layouts.kader')

@section('title', 'Input Pengukuran Fisik')
@section('page-name', 'Input Pengukuran Fisik')
@section('page-title', 'Input Pengukuran Fisik')

@php
    use Carbon\Carbon;

    $kategori_awal = $kategori_awal ?? request('kategori', 'balita');
    $pasien_id_awal = $pasien_id_awal ?? request('pasien_id');

    if (! in_array($kategori_awal, ['balita', 'remaja', 'lansia'], true)) {
        $kategori_awal = 'balita';
    }

    $kategoriMenus = [
        'balita' => [
            'label' => 'Balita',
            'desc' => 'Tumbuh kembang anak',
            'icon' => 'fa-child-reaching',
            'color' => 'emerald',
            'bg' => 'bg-gradient-to-br from-emerald-500 to-teal-500',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'desc' => 'Skrining kesehatan',
            'icon' => 'fa-user-graduate',
            'color' => 'teal',
            'bg' => 'bg-gradient-to-br from-teal-500 to-cyan-500',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'desc' => 'Pemantauan lansia',
            'icon' => 'fa-person-cane',
            'color' => 'sky',
            'bg' => 'bg-gradient-to-br from-sky-500 to-blue-500',
        ],
    ];

    $today = now('Asia/Jakarta')->toDateString();
    $oldKategori = old('kategori_pasien', $kategori_awal);
    $oldPasienId = old('pasien_id', $pasien_id_awal);
@endphp

@push('styles')
<style>
    html { scroll-behavior: smooth; }
    body {
        background-color: #f4f7f6;
        background-image: radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
                          radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }

    /* Menghilangkan panah spinner pada input number */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type="number"] { -moz-appearance: textfield; }

    .widget-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        transform: translateZ(0);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .widget-card:hover {
        transform: translateY(-2px) translateZ(0);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
    }

    .btn-pill { border-radius: 9999px; transition: all 0.2s ease; cursor: pointer; }
    .btn-pill:active { transform: scale(0.97); }

    .animate-pop-in {
        animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }

    @keyframes popIn {
        from { opacity: 0; transform: scale(.98) translateY(10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .hero-grid {
        background-image: radial-gradient(rgba(255,255,255,.45) 1px, transparent 1px);
        background-size: 24px 24px;
    }

    .input-soft {
        width: 100%;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 1.2rem;
        padding: 12px 16px;
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        outline: none;
        transition: all .25s ease;
    }
    .input-soft-right-icon {
        padding-right: 3.25rem !important;
    }

    .input-soft:focus {
        background: #ffffff;
        border-color: #10b981; 
        box-shadow: 0 0 0 4px rgba(16, 185, 129, .15);
    }
    
    .input-soft:disabled {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
        border-color: #e2e8f0;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #64748b;
    }

    .req-star { 
        color: #f43f5e; 
        margin-left: 2px;
    }

    .nexus-dropdown {
        scrollbar-width: thin;
        scrollbar-color: rgba(16, 185, 129, 0.3) transparent;
    }
    .nexus-dropdown::-webkit-scrollbar { width: 6px; }
    .nexus-dropdown::-webkit-scrollbar-thumb { background-color: rgba(16, 185, 129, 0.3); border-radius: 999px; }

    /* Modal Styling */
    .pc-modal-backdrop {
        position: fixed !important;
        inset: 0 !important;
        z-index: 9999999 !important;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, 0.45) !important;
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        padding: 1rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .pc-modal-backdrop.is-open { display: flex !important; opacity: 1; }
    
    .pc-modal-card {
        width: 100%;
        max-width: 440px;
        background: white;
        border-radius: 2.5rem;
        padding: 2.5rem 2rem;
        transform: scale(0.9) translateY(20px);
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        position: relative;
        overflow: hidden;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }
    .pc-modal-backdrop.is-open .pc-modal-card {
        transform: scale(1) translateY(0);
        opacity: 1;
    }
</style>
@endpush

@section('content')
<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1000px] mx-auto space-y-6 animate-pop-in">

    {{-- Hero Banner --}}
    <section class="relative overflow-hidden rounded-[3rem] bg-gradient-to-r from-emerald-500 via-teal-500 to-green-500 p-8 sm:p-10 shadow-2xl shadow-emerald-500/20 flex flex-col justify-center items-center text-center gap-4 border-[6px] border-white/40" style="transform: translateZ(0);">
        <div class="hero-grid absolute inset-0 opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-full flex flex-col items-center gap-3">
            <div class="inline-flex items-center gap-2">
                <span class="btn-pill bg-white/20 border border-white/30 text-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest backdrop-blur-md shadow-inner flex items-center gap-2">
                    <a href="{{ route('kader.pemeriksaan.index') }}" class="hover:text-white transition-colors">Daftar Pengukuran</a>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span>Input Baru</span>
                </span>
            </div>

            <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight">
                Catat Pengukuran Fisik
            </h1>

            <p class="text-white/90 text-sm font-medium max-w-xl mx-auto leading-relaxed">
                Pilih sasaran, lengkapi hasil pengukuran fisik dasar[cite: 12], dan simpan data untuk antrean validasi klinis oleh Bidan[cite: 12].
            </p>
        </div>
    </section>

    {{-- System Alerts --}}
    @if(session('success') || session('error') || $errors->any())
        <div class="space-y-4">
            @if(session('success'))
                <div class="widget-card bg-emerald-50 border-emerald-200 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white text-emerald-500 flex items-center justify-center text-xl shrink-0 shadow-sm border border-emerald-100">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-emerald-800">Berhasil!</h4>
                        <p class="text-xs font-semibold text-emerald-600 mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error') || $errors->any())
                <div class="widget-card bg-rose-50 border-rose-200 p-5 flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white text-rose-500 flex items-center justify-center text-xl shrink-0 shadow-sm border border-rose-100">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-rose-800">Gagal Menyimpan</h4>
                        <p class="text-xs font-semibold text-rose-600 mt-0.5 mb-2">{{ session('error') ?? 'Mohon periksa kembali isian form Anda.' }}</p>
                        @if($errors->any())
                            <ul class="text-[11px] font-bold text-rose-500/80 list-disc list-inside space-y-0.5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    <form id="measurementForm" method="POST" action="{{ route('kader.pemeriksaan.store') }}" data-selected-pasien="{{ $oldPasienId }}" data-api-url="{{ route('kader.pemeriksaan.api') }}" class="space-y-6">
        @csrf

        {{-- CARD 1: Data Sasaran & Kategori --}}
        <section class="widget-card overflow-hidden">
            <div class="bg-slate-50/70 px-8 py-5 border-b border-slate-100 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-base shadow-sm border border-emerald-100">
                    <i class="fas fa-users"></i>
                </div>
                <h5 class="font-black text-slate-800 text-sm uppercase tracking-wider">Identitas Sasaran</h5>
            </div>

            <div class="p-6 md:p-8 space-y-6">
                <div>
                    <label class="form-label mb-3">Pilih Kategori Sasaran <span class="req-star">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach($kategoriMenus as $key => $item)
                            <label class="cursor-pointer relative block">
                                <input type="radio" name="kategori_pasien" value="{{ $key }}" class="peer sr-only" data-category-radio @checked($oldKategori === $key)>
                                <div class="h-full rounded-2xl border-2 border-slate-100 bg-slate-50/50 p-4 transition-all hover:bg-slate-50 peer-checked:border-emerald-400 peer-checked:bg-white peer-checked:shadow-md flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white {{ $item['bg'] }} shadow-inner shrink-0 transition-transform peer-checked:scale-110">
                                        <i class="fa-solid {{ $item['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-700 peer-checked:text-emerald-700 transition-colors">{{ $item['label'] }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 mt-0.5 leading-tight">{{ $item['desc'] }}</p>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2 md:col-span-2">
                        <label class="form-label">Nama Sasaran (Live Search) <span class="req-star">*</span></label>
                        <div class="relative" id="liveSearchWrapper">
                            <input type="hidden" name="pasien_id" id="pasienIdHidden" value="{{ $oldPasienId }}">
                            <div class="relative">
                                <input type="text" id="pasienSearchInput" class="input-soft input-soft-right-icon cursor-text" placeholder="Ketik nama atau NIK sasaran..." autocomplete="off">
                                {{-- Gunakan satu elemen ikon tunggal yang dikontrol via JS agar tidak pernah tumpang tindih --}}
                                <i class="fas fa-search absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-all" id="inputIcon"></i>
                            </div>
                            
                            <div id="pasienDropdown" class="absolute z-50 w-full mt-2 bg-white border border-slate-100 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] max-h-60 overflow-y-auto hidden nexus-dropdown">
                                <ul id="pasienList" class="p-2 space-y-1"></ul>
                            </div>
                        </div>
                        <p id="pasienHelp" class="text-[10px] font-bold text-slate-400 mt-1 pl-1">Ketik untuk mencari sasaran berdasarkan kategori yang dipilih[cite: 12].</p>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="form-label">Tanggal Pengukuran <span class="req-star">*</span></label>
                        <input type="date" name="tanggal_periksa" value="{{ old('tanggal_periksa', $today) }}" max="{{ $today }}" class="input-soft cursor-pointer">
                    </div>
                </div>
            </div>
        </section>

        {{-- CARD 2: Pengukuran Fisik Dasar --}}
        <section class="widget-card overflow-hidden">
            <div class="bg-slate-50/70 px-8 py-5 border-b border-slate-100 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-500 flex items-center justify-center text-base shadow-sm border border-teal-100">
                    <i class="fas fa-stethoscope"></i>
                </div>
                <h5 class="font-black text-slate-800 text-sm uppercase tracking-wider">Pengukuran Fisik Antropometri</h5>
            </div>

            <div class="p-6 md:p-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="form-label">Berat Badan <span class="req-star">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.1" min="0.1" max="300" name="berat_badan" value="{{ old('berat_badan') }}" placeholder="Cth: 12.5" class="input-soft pr-12" data-imt-source>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 pointer-events-none">Kg</span>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="form-label">Tinggi / Panjang <span class="req-star">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.1" min="10" max="250" name="tinggi_badan" value="{{ old('tinggi_badan') }}" placeholder="Cth: 85.0" class="input-soft pr-12" data-imt-source>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 pointer-events-none">Cm</span>
                        </div>
                    </div>

                    <div class="space-y-2 hidden" data-field-group="remaja lansia">
                        <label class="form-label text-teal-600">IMT Estimasi</label>
                        <div class="relative">
                            <div id="imtDisplay" class="input-soft bg-teal-50/50 border-teal-100 text-teal-700 flex items-center min-h-[46px]">
                                -
                            </div>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-teal-600/50 pointer-events-none">kg/m²</span>
                        </div>
                    </div>

                    <div class="space-y-2 hidden" data-field-group="balita">
                        <label class="form-label">Lingkar Kepala <span class="req-star">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.1" min="10" max="100" name="lingkar_kepala" value="{{ old('lingkar_kepala') }}" placeholder="Wajib Balita" class="input-soft pr-12">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 pointer-events-none">Cm</span>
                        </div>
                    </div>

                    <div class="space-y-2 hidden" data-field-group="balita remaja">
                        <label class="form-label">Lingkar Lengan (LiLA) <span class="req-star">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.1" min="5" max="100" name="lingkar_lengan" value="{{ old('lingkar_lengan') }}" placeholder="LiLA" class="input-soft pr-12">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 pointer-events-none">Cm</span>
                        </div>
                    </div>

                    <div class="space-y-2 hidden" data-field-group="remaja lansia">
                        <label class="form-label">Lingkar Perut <span class="req-star">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.1" min="20" max="200" name="lingkar_perut" value="{{ old('lingkar_perut') }}" placeholder="Wajib" class="input-soft pr-12">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 pointer-events-none">Cm</span>
                        </div>
                    </div>
                    
                    <div class="space-y-2 hidden" data-field-group="remaja lansia">
                        <label class="form-label">Tekanan Darah <span class="req-star">*</span></label>
                        <input type="text" name="tekanan_darah" value="{{ old('tekanan_darah') }}" placeholder="Cth: 120/80" class="input-soft">
                    </div>

                    <div class="space-y-2 hidden sm:col-span-2 md:col-span-1" data-field-group="lansia">
                        <label class="form-label">Kemandirian <span class="req-star">*</span></label>
                        <select name="tingkat_kemandirian" class="input-soft cursor-pointer">
                            <option value="">Pilih Status</option>
                            <option value="mandiri" @selected(old('tingkat_kemandirian') === 'mandiri')>Mandiri</option>
                            <option value="bantuan_sebagian" @selected(old('tingkat_kemandirian') === 'bantuan_sebagian')>Bantuan Sebagian</option>
                            <option value="bantuan_penuh" @selected(old('tingkat_kemandirian') === 'bantuan_penuh')>Bantuan Penuh</option>
                        </select>
                    </div>
                </div>
            </div>
        </section>

        {{-- CARD 3: Skrining Lanjutan & Anamnesis --}}
        <section class="widget-card overflow-hidden">
            <div class="bg-slate-50/70 px-8 py-5 border-b border-slate-100 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-cyan-50 text-cyan-500 flex items-center justify-center text-base shadow-sm border border-cyan-100">
                    <i class="fas fa-vial-circle-check"></i>
                </div>
                <h5 class="font-black text-slate-800 text-sm uppercase tracking-wider">Skrining Lanjutan & Catatan Tambahan</h5>
            </div>

            <div class="p-6 md:p-8 space-y-6">
                <div class="hidden pb-6 border-b border-slate-100" data-field-group="remaja lansia">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-1.5"><i class="fa-solid fa-notes-medical"></i> Pemeriksaan Penunjang PTM</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="space-y-2">
                            <label class="form-label">Gula Darah</label>
                            <div class="relative">
                                <input type="number" step="0.1" min="10" max="1000" name="gula_darah" value="{{ old('gula_darah') }}" placeholder="Opsional" class="input-soft pr-14">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 pointer-events-none">mg/dL</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="form-label">Kolesterol</label>
                            <div class="relative">
                                <input type="number" min="10" max="1000" name="kolesterol" value="{{ old('kolesterol') }}" placeholder="Opsional" class="input-soft pr-14">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 pointer-events-none">mg/dL</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="form-label">Asam Urat</label>
                            <div class="relative">
                                <input type="number" step="0.1" min="1" max="30" name="asam_urat" value="{{ old('asam_urat') }}" placeholder="Opsional" class="input-soft pr-14">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 pointer-events-none">mg/dL</span>
                            </div>
                        </div>
                        <div class="space-y-2 hidden" data-field-group="remaja">
                            <label class="form-label">Hemoglobin (Hb)</label>
                            <div class="relative">
                                <input type="number" step="0.1" min="1" max="30" name="hemoglobin" value="{{ old('hemoglobin') }}" placeholder="Opsional" class="input-soft pr-12">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 pointer-events-none">g/dL</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-1.5"><i class="fa-solid fa-clipboard-question"></i> Anamnesis & Catatan</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="form-label">Riwayat Keluhan Saat Datang</label>
                            <textarea name="keluhan" rows="3" placeholder="Contoh: Batuk pilek sejak 2 hari yang lalu, pusing, atau tidak ada keluhan..." class="input-soft resize-none">{{ old('keluhan') }}</textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="form-label">Catatan Tambahan Kader</label>
                            <textarea name="catatan_kader" rows="3" placeholder="Catat temuan spesifik untuk di-review oleh Bidan nantinya..." class="input-soft resize-none">{{ old('catatan_kader') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- BUTTONS --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
            <a href="{{ route('kader.pemeriksaan.index') }}" class="btn-pill w-full sm:w-auto px-8 py-3.5 font-bold text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-700 transition-all shadow-sm text-sm text-center">
                <i class="fas fa-times mr-1"></i> Batal
            </a>

            <button type="button" id="openSubmitModalBtn" class="btn-pill w-full sm:w-auto px-8 py-3.5 font-bold text-white bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 transition-all shadow-[0_4px_15px_rgba(16,185,129,.30)] hover:-translate-y-0.5 text-sm flex items-center justify-center gap-2">
                <i class="fas fa-save"></i> Simpan Pengukuran
            </button>
        </div>
    </form>
</div>

{{-- MODAL KONFIRMASI SIMPAN --}}
<div id="pcSubmitModal" class="pc-modal-backdrop">
    <div class="pc-modal-card text-center">
        <div class="absolute -top-16 -left-16 w-32 h-32 bg-emerald-400/20 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-16 -right-16 w-32 h-32 bg-teal-400/20 rounded-full blur-2xl"></div>
        
        <div class="relative z-10">
            <div class="w-20 h-20 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center mx-auto mb-5 text-emerald-500 shadow-inner">
                <i class="fa-solid fa-cloud-arrow-up text-3xl"></i>
            </div>
            <h3 class="text-2xl font-black text-slate-800 mb-2">Simpan Data?</h3>
            <p class="text-sm font-medium text-slate-500 mb-8 leading-relaxed px-4">
                Data pengukuran akan disimpan dan diteruskan ke antrean review Bidan untuk validasi klinis[cite: 12].
            </p>
            <div class="flex gap-3">
                <button type="button" id="pcCancelSubmit" class="btn-pill w-full flex-1 border border-slate-200 bg-white text-slate-700 px-4 py-3.5 text-sm font-bold shadow-sm hover:bg-slate-50 transition-all">
                    Kembali
                </button>
                <button type="button" id="pcConfirmSubmit" class="btn-pill w-full flex-1 bg-gradient-to-r from-teal-500 to-emerald-500 text-white px-4 py-3.5 text-sm font-bold shadow-md hover:from-teal-600 hover:to-emerald-600 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-check"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- CUSTOM ALERT MODAL --}}
<div id="pcAlertModal" class="pc-modal-backdrop">
    <div class="pc-modal-card text-center">
        <div class="absolute -top-16 -left-16 w-32 h-32 bg-rose-400/20 rounded-full blur-2xl"></div>
        
        <div class="relative z-10">
            <div class="w-20 h-20 rounded-full bg-rose-50 border border-rose-100 flex items-center justify-center mx-auto mb-5 text-rose-500 shadow-inner">
                <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
            </div>
            <h3 class="text-xl font-black text-slate-800 mb-2">Pemberitahuan</h3>
            <p id="pcAlertMessage" class="text-sm font-medium text-slate-500 mb-8 leading-relaxed px-4">
                Pesan peringatan sistem.
            </p>
            <button type="button" id="pcCloseAlert" class="btn-pill w-full bg-gradient-to-r from-rose-500 to-pink-500 text-white px-4 py-3.5 text-sm font-bold shadow-md hover:from-rose-600 hover:to-pink-600 transition-all">
                Saya Mengerti
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const submitModal = document.getElementById('pcSubmitModal');
    const alertModal = document.getElementById('pcAlertModal');
    if (submitModal) document.body.appendChild(submitModal);
    if (alertModal) document.body.appendChild(alertModal);

    const form = document.querySelector('#measurementForm');
    const categoryRadios = Array.from(document.querySelectorAll('[data-category-radio]'));
    
    const searchInput = document.getElementById('pasienSearchInput');
    const hiddenInput = document.getElementById('pasienIdHidden');
    const dropdown = document.getElementById('pasienDropdown');
    const list = document.getElementById('pasienList');
    const inputIcon = document.getElementById('inputIcon'); // Menggunakan elemen ikon tunggal
    const pasienHelp = document.getElementById('pasienHelp');
    
    const summaryImt = document.querySelector('#imtDisplay');
    const submitBtn = document.querySelector('#openSubmitModalBtn');
    
    const cancelSubmit = document.querySelector('#pcCancelSubmit');
    const confirmSubmit = document.querySelector('#pcConfirmSubmit');

    const alertMessage = document.getElementById('pcAlertMessage');
    const closeAlertBtn = document.getElementById('pcCloseAlert');

    let allPatients = []; 

    function showCustomAlert(message) {
        if (!alertModal) return;
        alertMessage.textContent = message;
        alertModal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeCustomAlert() {
        if (!alertModal) return;
        alertModal.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    if (closeAlertBtn) closeAlertBtn.addEventListener('click', closeCustomAlert);

    function getSelectedCategory() {
        const selected = document.querySelector('[data-category-radio]:checked');
        return selected ? selected.value : 'balita';
    }

    function updateVisibleFields() {
        const selected = getSelectedCategory();

        document.querySelectorAll('[data-field-group]').forEach(function (group) {
            const values = String(group.dataset.fieldGroup || '').split(' ');
            const isVisible = values.includes(selected);

            if (isVisible) {
                group.classList.remove('hidden');
                group.querySelectorAll('input, select, textarea').forEach(el => el.disabled = false);
            } else {
                group.classList.add('hidden');
                group.querySelectorAll('input, select, textarea').forEach(el => el.disabled = true);
            }
        });
    }

    function loadPasien() {
        if (!form) return;

        const category = getSelectedCategory();
        const apiUrl = form.dataset.apiUrl;
        const oldSelected = hiddenInput.value;

        allPatients = [];
        searchInput.value = '';
        hiddenInput.value = '';
        searchInput.disabled = true;
        
        // Ubah ikon tunggal menjadi spinner loading
        inputIcon.className = 'fas fa-spinner fa-spin absolute right-4 top-1/2 -translate-y-1/2 text-teal-500 pointer-events-none transition-all';
        
        searchInput.placeholder = 'Memuat data sasaran...';

        fetch(apiUrl + '?kategori=' + encodeURIComponent(category), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => { if (!res.ok) throw new Error(); return res.json(); })
        .then(payload => {
            allPatients = Array.isArray(payload.data) ? payload.data : [];
            
            searchInput.disabled = false;
            searchInput.placeholder = 'Ketik nama atau NIK sasaran...';
            
            // Kembalikan ikon tunggal menjadi ikon search normal
            inputIcon.className = 'fas fa-search absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-all';
            
            if(allPatients.length > 0) {
                pasienHelp.textContent = `${allPatients.length} data tersedia.`;
                
                if(oldSelected) {
                    const found = allPatients.find(p => String(p.id) === String(oldSelected));
                    if(found) {
                        hiddenInput.value = found.id;
                        searchInput.value = found.nama;
                    }
                }
            } else {
                pasienHelp.textContent = 'Belum ada data sasaran terdaftar di kategori ini.';
                searchInput.disabled = true;
                searchInput.placeholder = 'Data kosong';
            }
        })
        .catch(() => {
            searchInput.placeholder = 'Gagal memuat data';
            inputIcon.className = 'fas fa-search absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-all';
            pasienHelp.textContent = 'Gagal terhubung ke server API.';
        });
    }

    function renderDropdown(data) {
        list.innerHTML = '';
        if(data.length === 0) {
            list.innerHTML = '<li class="p-3 text-sm font-bold text-slate-400 text-center">Pencarian tidak ditemukan</li>';
            return;
        }

        data.forEach(item => {
            const li = document.createElement('li');
            li.className = 'px-4 py-3 hover:bg-emerald-50 cursor-pointer rounded-xl transition-colors flex flex-col group';
            li.innerHTML = `
                <span class="text-sm font-black text-slate-700 group-hover:text-emerald-700 transition-colors">${item.nama}</span>
                <span class="text-[10px] font-bold text-slate-400 mt-0.5">NIK: ${item.nik || '-'}</span>
            `;
            
            li.addEventListener('click', () => {
                hiddenInput.value = item.id;
                searchInput.value = item.nama;
                dropdown.classList.add('hidden');
            });
            
            list.appendChild(li);
        });
    }

    searchInput.addEventListener('input', (e) => {
        const keyword = e.target.value.toLowerCase();
        
        const filtered = allPatients.filter(p => 
            p.nama.toLowerCase().includes(keyword) || 
            (p.nik && String(p.nik).includes(keyword))
        );
        
        renderDropdown(filtered);
        dropdown.classList.remove('hidden');
        
        if(keyword === '') {
            hiddenInput.value = '';
        }
    });

    searchInput.addEventListener('focus', () => {
        if(allPatients.length > 0) {
            renderDropdown(allPatients); 
            dropdown.classList.remove('hidden');
        }
    });

    document.addEventListener('click', (e) => {
        if(!document.getElementById('liveSearchWrapper').contains(e.target)) {
            dropdown.classList.add('hidden');
            if(!hiddenInput.value) {
                searchInput.value = '';
            } else {
                const found = allPatients.find(p => String(p.id) === String(hiddenInput.value));
                if(found) searchInput.value = found.nama;
            }
        }
    });

    function calculateImt() {
        if (!summaryImt) return;
        const category = getSelectedCategory();
        const bb = parseFloat(document.querySelector('[name="berat_badan"]')?.value || 0);
        const tb = parseFloat(document.querySelector('[name="tinggi_badan"]')?.value || 0);

        if (category === 'balita') {
            summaryImt.textContent = '-'; 
            return;
        }

        if (!bb || !tb) {
            summaryImt.textContent = '-';
            return;
        }

        const meter = tb / 100;
        const imt = bb / (meter * meter);
        summaryImt.textContent = isFinite(imt) ? imt.toFixed(2) : '-';
    }

    categoryRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            updateVisibleFields();
            calculateImt();
            form.dataset.selectedPasien = ''; 
            loadPasien();
        });
    });

    document.querySelectorAll('[data-imt-source]').forEach(input => input.addEventListener('input', calculateImt));

    function openModal() {
        if(!hiddenInput.value) {
            showCustomAlert('Pilih sasaran dari daftar dropdown pencarian terlebih dahulu.');
            searchInput.focus();
            return;
        }

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        if (!submitModal) { form.submit(); return; }
        submitModal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        if (!submitModal) return;
        submitModal.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal();
        });
    }

    if (cancelSubmit) cancelSubmit.addEventListener('click', closeModal);
    
    if (submitModal) {
        submitModal.addEventListener('click', function(e) {
            if(e.target === submitModal) closeModal();
        });
    }
    if (alertModal) {
        alertModal.addEventListener('click', function(e) {
            if(e.target === alertModal) closeCustomAlert();
        });
    }

    document.addEventListener('keydown', e => { 
        if (e.key === 'Escape') {
            closeModal();
            closeCustomAlert();
        }
    });

    if (confirmSubmit) {
        confirmSubmit.addEventListener('click', function () {
            confirmSubmit.disabled = true;
            confirmSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
            if (submitBtn) submitBtn.disabled = true;
            form.submit();
        });
    }

    updateVisibleFields();
    loadPasien();
    calculateImt();
});
</script>
@endpush