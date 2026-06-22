@extends('layouts.kader')

@section('title', 'Edit Pengukuran Fisik')
@section('page-name', 'Edit Pengukuran Fisik')
@section('page-title', 'Edit Pengukuran Fisik')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    $pasien = optional($pemeriksaan->kunjungan)->pasien;

    $kategori = strtolower((string) ($pemeriksaan->kategori_pasien ?? 'balita'));

    if (! in_array($kategori, ['balita', 'remaja', 'lansia'], true)) {
        $kategori = 'balita';
    }

    $kategoriMeta = match ($kategori) {
        'remaja' => [
            'label' => 'Remaja',
            'desc' => 'Skrining kesehatan',
            'icon' => 'fa-user-graduate',
            'bg' => 'bg-gradient-to-br from-teal-500 to-cyan-500',
            'color' => 'teal'
        ],
        'lansia' => [
            'label' => 'Lansia',
            'desc' => 'Pemantauan lansia',
            'icon' => 'fa-person-cane',
            'bg' => 'bg-gradient-to-br from-sky-500 to-blue-500',
            'color' => 'sky'
        ],
        default => [
            'label' => 'Balita',
            'desc' => 'Tumbuh kembang anak',
            'icon' => 'fa-child-reaching',
            'bg' => 'bg-gradient-to-br from-emerald-500 to-teal-500',
            'color' => 'emerald'
        ],
    };

    $namaPasien = $pasien->nama_lengkap ?? $pasien->nama ?? $pemeriksaan->nama_pasien ?? 'Tanpa Nama';
    $nikPasien = $pasien->nik ?? $pemeriksaan->nik_pasien ?? '-';
    $tanggalValue = old('tanggal_periksa', optional($pemeriksaan->tanggal_periksa)->format('Y-m-d') ?: Carbon::parse($pemeriksaan->tanggal_periksa ?? now())->format('Y-m-d'));
    $today = now('Asia/Jakarta')->toDateString();

    $status = strtolower((string) ($pemeriksaan->status_verifikasi ?? 'pending'));
    $isRevisi = in_array($status, ['ditolak', 'revisi', 'perlu_revisi', 'needs_revision', 'rejected', 'dikembalikan'], true);

    $catatanBidan = $pemeriksaan->catatan_validasi ?? $pemeriksaan->catatan_bidan ?? $pemeriksaan->catatan_review ?? null;
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

    .hero-grid {
        background-image: radial-gradient(rgba(255,255,255,.45) 1px, transparent 1px);
        background-size: 24px 24px;
    }

    .input-soft {
        width: 100%;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px 14px;
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        outline: none;
        transition: all .22s ease;
    }

    .input-soft:focus {
        background: #ffffff;
        border-color: #10b981; 
        box-shadow: 0 0 0 4px rgba(16, 185, 129, .12);
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
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #64748b;
    }

    .req-star { 
        color: #f43f5e; 
        margin-left: 2px;
    }

    /* Modal Styling */
    .pc-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, .5);
        backdrop-filter: blur(10px);
        padding: 1rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .pc-modal-backdrop.is-open { display: flex; opacity: 1; }
    
    .pc-modal-card {
        width: 100%;
        max-width: 420px;
        background: white;
        border-radius: 2rem;
        padding: 2.5rem 2rem;
        transform: scale(0.95) translateY(10px);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        position: relative;
        overflow: hidden;
    }
    .pc-modal-backdrop.is-open .pc-modal-card {
        transform: scale(1) translateY(0);
        opacity: 1;
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto animate-pop-in pb-12 px-4 sm:px-6 lg:px-8 mt-6">

    {{-- Hero Section --}}
    <section class="bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-600 rounded-[2.5rem] p-8 md:p-10 mb-8 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(16,185,129,.35)] border border-white/20 text-center">
        <div class="hero-grid absolute inset-0 opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[70px] rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-56 h-56 bg-white/10 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 text-white/90 text-[10px] font-black uppercase tracking-widest mb-4">
                <a href="{{ route('kader.pemeriksaan.index') }}" class="hover:text-white transition-colors">Daftar Pengukuran</a>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span class="text-white">Edit Pengukuran</span>
            </div>

            <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight">
                Edit Data Pengukuran
            </h1>

            <p class="text-teal-50 text-sm font-medium max-w-xl mx-auto mt-3 leading-relaxed">
                Perbaiki kesalahan pada data pengukuran sebelum dikunci oleh Bidan secara permanen.
            </p>
            
            <div class="flex flex-wrap justify-center gap-3 mt-5">
                <a href="{{ route('kader.pemeriksaan.index') }}" class="px-6 py-2.5 rounded-xl bg-white/20 hover:bg-white/30 backdrop-blur-md border border-white/30 text-white text-[11px] font-bold uppercase tracking-widest transition-all shadow-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <a href="{{ route('kader.pemeriksaan.show', $pemeriksaan->id) }}" class="px-6 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-teal-600 text-[11px] font-bold uppercase tracking-widest transition-all shadow-lg hover:-translate-y-0.5">
                    <i class="fas fa-eye mr-1"></i> Detail
                </a>
            </div>
        </div>
    </section>

    {{-- System Alerts (Nexus Premium Style) --}}
    @if(session('success') || session('error') || $errors->any() || ($isRevisi && $catatanBidan))
        <div class="mb-8 space-y-4">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 rounded-3xl p-5 flex items-center gap-4 shadow-sm relative overflow-hidden">
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
                <div class="bg-rose-50 border border-rose-200 rounded-3xl p-5 flex items-start gap-4 shadow-sm relative overflow-hidden">
                    <div class="w-12 h-12 rounded-2xl bg-white text-rose-500 flex items-center justify-center text-xl shrink-0 shadow-sm border border-rose-100">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-rose-800">Gagal Memperbarui</h4>
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

            {{-- Note Khusus Revisi --}}
            @if($isRevisi && $catatanBidan)
                <div class="bg-amber-50 border border-amber-200 rounded-3xl p-5 flex items-start gap-4 shadow-sm relative overflow-hidden">
                    <div class="w-12 h-12 rounded-2xl bg-white text-amber-500 flex items-center justify-center text-xl shrink-0 shadow-sm border border-amber-100">
                        <i class="fas fa-comment-medical"></i>
                    </div>
                    <div>
                        <h4 class="text-[11px] font-black uppercase tracking-widest text-amber-700">Catatan Revisi dari Bidan</h4>
                        <p class="text-xs font-medium text-amber-800 leading-relaxed italic mt-1 bg-white/50 p-2 rounded-lg border border-amber-100">"{{ $catatanBidan }}"</p>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <form id="measurementEditForm" method="POST" action="{{ route('kader.pemeriksaan.update', $pemeriksaan->id) }}">
        @csrf
        @method('PUT')

        {{-- CARD 1: Identitas Sasaran (Locked) --}}
        <section class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden mb-6">
            <div class="bg-slate-50/70 px-8 py-5 border-b border-slate-100 flex items-center justify-center">
                <h5 class="font-black text-slate-700 text-sm uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-id-card text-teal-500"></i>
                    Identitas Terkunci
                </h5>
            </div>

            <div class="p-6 md:p-8">
                <div class="flex items-center gap-4 p-4 rounded-2xl border-2 border-slate-100 bg-slate-50/50 mb-6">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white {{ $kategoriMeta['bg'] }} shadow-inner shrink-0">
                        <i class="fa-solid {{ $kategoriMeta['icon'] }} text-xl"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Kategori: {{ $kategoriMeta['label'] }}</p>
                        <p class="text-base font-black text-slate-800 leading-tight line-clamp-1">{{ $namaPasien }}</p>
                        <p class="text-[11px] font-semibold text-slate-500 mt-1">NIK: {{ $nikPasien }}</p>
                    </div>
                </div>

                <div>
                    <label class="form-label">Tanggal Pengukuran <span class="req-star">*</span></label>
                    <input type="date" name="tanggal_periksa" value="{{ $tanggalValue }}" max="{{ $today }}" class="input-soft cursor-pointer">
                </div>
            </div>
        </section>

        {{-- CARD 2: Pengukuran Fisik Dasar --}}
        <section class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden mb-6">
            <div class="bg-slate-50/70 px-8 py-5 border-b border-slate-100 flex items-center justify-center">
                <h5 class="font-black text-slate-700 text-sm uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-stethoscope text-teal-500"></i>
                    Pengukuran Fisik Antropometri
                </h5>
            </div>

            <div class="p-6 md:p-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    
                    {{-- All Categories --}}
                    <div class="space-y-2">
                        <label class="form-label">Berat Badan <span class="req-star">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.1" min="0.1" max="300" name="berat_badan" value="{{ old('berat_badan', $pemeriksaan->berat_badan) }}" placeholder="Cth: 12.5" class="input-soft pr-12" data-imt-source>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 pointer-events-none">Kg</span>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="form-label">Tinggi / Panjang <span class="req-star">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.1" min="10" max="250" name="tinggi_badan" value="{{ old('tinggi_badan', $pemeriksaan->tinggi_badan) }}" placeholder="Cth: 85.0" class="input-soft pr-12" data-imt-source>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 pointer-events-none">Cm</span>
                        </div>
                    </div>

                    {{-- IMT Khusus Remaja/Lansia --}}
                    <div class="space-y-2 {{ $kategori === 'balita' ? 'hidden' : '' }}">
                        <label class="form-label text-teal-600">IMT Estimasi</label>
                        <div class="relative">
                            <div id="imtDisplay" class="input-soft bg-teal-50/50 border-teal-100 text-teal-700 flex items-center min-h-[44px]">
                                -
                            </div>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-teal-600/50 pointer-events-none">kg/m²</span>
                        </div>
                    </div>

                    {{-- Balita --}}
                    <div class="space-y-2 {{ $kategori !== 'balita' ? 'hidden' : '' }}">
                        <label class="form-label">Lingkar Kepala <span class="req-star">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.1" min="10" max="100" name="lingkar_kepala" value="{{ old('lingkar_kepala', $pemeriksaan->lingkar_kepala) }}" placeholder="Wajib Balita" class="input-soft pr-12" @disabled($kategori !== 'balita')>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 pointer-events-none">Cm</span>
                        </div>
                    </div>

                    {{-- Balita & Remaja --}}
                    <div class="space-y-2 {{ !in_array($kategori, ['balita', 'remaja']) ? 'hidden' : '' }}">
                        <label class="form-label">Lingkar Lengan (LiLA) <span class="req-star">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.1" min="5" max="100" name="lingkar_lengan" value="{{ old('lingkar_lengan', $pemeriksaan->lingkar_lengan) }}" placeholder="LiLA" class="input-soft pr-12" @disabled(!in_array($kategori, ['balita', 'remaja']))>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 pointer-events-none">Cm</span>
                        </div>
                    </div>

                    {{-- Remaja & Lansia --}}
                    <div class="space-y-2 {{ !in_array($kategori, ['remaja', 'lansia']) ? 'hidden' : '' }}">
                        <label class="form-label">Lingkar Perut <span class="req-star">*</span></label>
                        <div class="relative">
                            <input type="number" step="0.1" min="20" max="200" name="lingkar_perut" value="{{ old('lingkar_perut', $pemeriksaan->lingkar_perut) }}" placeholder="Wajib" class="input-soft pr-12" @disabled(!in_array($kategori, ['remaja', 'lansia']))>
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 pointer-events-none">Cm</span>
                        </div>
                    </div>
                    
                    <div class="space-y-2 {{ !in_array($kategori, ['remaja', 'lansia']) ? 'hidden' : '' }}">
                        <label class="form-label">Tekanan Darah <span class="req-star">*</span></label>
                        <input type="text" name="tekanan_darah" value="{{ old('tekanan_darah', $pemeriksaan->tekanan_darah) }}" placeholder="Cth: 120/80" class="input-soft" @disabled(!in_array($kategori, ['remaja', 'lansia']))>
                    </div>

                    {{-- Lansia --}}
                    <div class="space-y-2 {{ $kategori !== 'lansia' ? 'hidden' : '' }} sm:col-span-2 md:col-span-1">
                        <label class="form-label">Kemandirian <span class="req-star">*</span></label>
                        <select name="tingkat_kemandirian" class="input-soft cursor-pointer" @disabled($kategori !== 'lansia')>
                            <option value="">Pilih Status</option>
                            <option value="mandiri" @selected(old('tingkat_kemandirian', $pemeriksaan->tingkat_kemandirian) === 'mandiri')>Mandiri</option>
                            <option value="bantuan_sebagian" @selected(old('tingkat_kemandirian', $pemeriksaan->tingkat_kemandirian) === 'bantuan_sebagian')>Bantuan Sebagian</option>
                            <option value="bantuan_penuh" @selected(old('tingkat_kemandirian', $pemeriksaan->tingkat_kemandirian) === 'bantuan_penuh')>Bantuan Penuh</option>
                        </select>
                    </div>

                </div>
            </div>
        </section>

        {{-- CARD 3: Skrining Lanjutan & Anamnesis --}}
        <section class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden mb-8">
            <div class="bg-slate-50/70 px-8 py-5 border-b border-slate-100 flex items-center justify-center">
                <h5 class="font-black text-slate-700 text-sm uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-vial-circle-check text-teal-500"></i>
                    Skrining Lanjutan & Catatan Tambahan
                </h5>
            </div>

            <div class="p-6 md:p-8">
                {{-- Pemeriksaan Penunjang PTM (Remaja & Lansia Only) --}}
                <div class="{{ !in_array($kategori, ['remaja', 'lansia']) ? 'hidden' : '' }} mb-6 pb-6 border-b border-slate-100">
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-1"><i class="fa-solid fa-notes-medical"></i> Pemeriksaan Penunjang PTM</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="space-y-2">
                            <label class="form-label">Gula Darah</label>
                            <div class="relative">
                                <input type="number" step="0.1" min="10" max="1000" name="gula_darah" value="{{ old('gula_darah', $pemeriksaan->gula_darah) }}" placeholder="Opsional" class="input-soft pr-14" @disabled(!in_array($kategori, ['remaja', 'lansia']))>
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 pointer-events-none">mg/dL</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="form-label">Kolesterol</label>
                            <div class="relative">
                                <input type="number" min="10" max="1000" name="kolesterol" value="{{ old('kolesterol', $pemeriksaan->kolesterol) }}" placeholder="Opsional" class="input-soft pr-14" @disabled(!in_array($kategori, ['remaja', 'lansia']))>
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 pointer-events-none">mg/dL</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="form-label">Asam Urat</label>
                            <div class="relative">
                                <input type="number" step="0.1" min="1" max="30" name="asam_urat" value="{{ old('asam_urat', $pemeriksaan->asam_urat) }}" placeholder="Opsional" class="input-soft pr-14" @disabled(!in_array($kategori, ['remaja', 'lansia']))>
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 pointer-events-none">mg/dL</span>
                            </div>
                        </div>
                        <div class="space-y-2 {{ $kategori !== 'remaja' ? 'hidden' : '' }}">
                            <label class="form-label">Hemoglobin (Hb)</label>
                            <div class="relative">
                                <input type="number" step="0.1" min="1" max="30" name="hemoglobin" value="{{ old('hemoglobin', $pemeriksaan->hemoglobin) }}" placeholder="Opsional" class="input-soft pr-12" @disabled($kategori !== 'remaja')>
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-400 pointer-events-none">g/dL</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Riwayat Keluhan & Anamnesis (All Categories) --}}
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-4 flex items-center gap-1"><i class="fa-solid fa-clipboard-question"></i> Anamnesis & Catatan</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="form-label">Riwayat Keluhan Saat Datang</label>
                            <textarea name="keluhan" rows="3" placeholder="Contoh: Batuk pilek sejak 2 hari yang lalu, pusing..." class="input-soft resize-none">{{ old('keluhan', $pemeriksaan->keluhan) }}</textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="form-label">Catatan Tambahan Kader</label>
                            <textarea name="catatan_kader" rows="3" placeholder="Catat temuan spesifik untuk di-review oleh Bidan nantinya..." class="input-soft resize-none">{{ old('catatan_kader', $pemeriksaan->catatan_kader) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- BUTTONS --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('kader.pemeriksaan.index') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-700 transition-all shadow-sm text-sm text-center">
                <i class="fas fa-times mr-1"></i>
                Batal
            </a>

            <button type="button" id="openSubmitModalBtn" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold text-white bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 transition-all shadow-[0_4px_15px_rgba(16,185,129,.30)] hover:-translate-y-0.5 text-sm flex items-center justify-center gap-2">
                <i class="fas fa-save"></i>
                Simpan Perubahan
            </button>
        </div>
    </form>

    {{-- NEXUS MODAL KONFIRMASI --}}
    <div id="pcSubmitModal" class="pc-modal-backdrop">
        <div class="pc-modal-card text-center">
            <div class="absolute -top-16 -left-16 w-32 h-32 bg-emerald-400/20 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-16 -right-16 w-32 h-32 bg-teal-400/20 rounded-full blur-2xl"></div>
            
            <div class="relative z-10">
                <div class="w-20 h-20 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center mx-auto mb-5 text-emerald-500 shadow-inner">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-800 mb-2">Simpan Perubahan?</h3>
                <p class="text-sm font-medium text-slate-500 mb-8 leading-relaxed px-4">
                    Data akan diperbarui dan dikirim kembali ke antrean review Bidan jika sebelumnya berstatus revisi.
                </p>
                <div class="flex gap-3">
                    <button type="button" id="pcCancelSubmit" class="w-full flex-1 rounded-xl border border-slate-200 bg-white text-slate-700 px-4 py-3.5 text-sm font-bold shadow-sm hover:bg-slate-50 transition-all">
                        Kembali
                    </button>
                    <button type="button" id="pcConfirmSubmit" class="w-full flex-1 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-500 text-white px-4 py-3.5 text-sm font-bold shadow-md hover:from-teal-600 hover:to-emerald-600 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const form = document.querySelector('#measurementEditForm');
    const summaryImt = document.querySelector('#imtDisplay');
    const category = @json($kategori);
    
    const submitBtn = document.querySelector('#openSubmitModalBtn');
    const modal = document.querySelector('#pcSubmitModal');
    const cancelSubmit = document.querySelector('#pcCancelSubmit');
    const confirmSubmit = document.querySelector('#pcConfirmSubmit');

    function calculateImt() {
        if (!summaryImt) return;
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

    // Modal Submit
    function openModal() {
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        if (!modal) { form.submit(); return; }
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-imt-source]').forEach(input => input.addEventListener('input', calculateImt));

    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal();
        });
    }

    if (cancelSubmit) cancelSubmit.addEventListener('click', closeModal);
    if (modal) {
        modal.addEventListener('click', function(e) {
            if(e.target === modal) closeModal();
        });
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    if (confirmSubmit) {
        confirmSubmit.addEventListener('click', function () {
            confirmSubmit.disabled = true;
            confirmSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
            if (submitBtn) submitBtn.disabled = true;
            form.submit();
        });
    }

    // Init state on page load
    calculateImt();
});
</script>
@endpush