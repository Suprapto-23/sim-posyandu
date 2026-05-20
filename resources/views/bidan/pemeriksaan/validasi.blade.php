@extends('layouts.bidan')

@section('title', 'Validasi Rekam Medis - Meja 5')

@section('content')
@php
    $kategori = strtolower($pemeriksaan->kategori_pasien);
@endphp

<div class="bg-[#f8fafc] min-h-screen pb-12 font-poppins w-full animate-fade-in">
    
    {{-- BARIS HEADER --}}
    <div class="pt-6 pb-4 px-4 md:px-8 max-w-7xl mx-auto flex justify-between items-center shrink-0">
        <a href="{{ route('bidan.pemeriksaan.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-teal-600 transition-colors group">
            <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center shadow-sm group-hover:bg-slate-50 transition-all">
                <i class="fas fa-arrow-left text-xs"></i>
            </div>
            Kembali ke Ruang Tunggu
        </a>
        <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
            <span class="text-[10px] font-black tracking-widest uppercase text-slate-500">Tinjauan Klinis Meja 5</span>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 md:px-8">
        {{-- GRID UTAMA: Kiri (Data Pengukuran & AI), Kanan (Form Isian Bidan) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8 items-stretch w-full">
            
            {{-- KOLOM KIRI: DATA DARI KADER & REKOMENDASI OTOMATIS --}}
            <div class="lg:col-span-5 h-full flex flex-col gap-6">
                
                {{-- 1. Kartu Identitas Pasien --}}
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4"><i class="fas fa-id-card mr-2 text-teal-500"></i>Profil Pasien</h3>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl font-black border border-teal-100">
                            {{ strtoupper(substr($pasien->nama_lengkap, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-base font-black text-slate-800 leading-tight truncate">{{ $pasien->nama_lengkap }}</h2>
                            <p class="text-xs text-slate-400 mt-1 font-bold uppercase tracking-wider">NIK: {{ $pasien->nik ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- 2. Hasil Pengukuran Fisik (Dinamis Sesuai Kategori) --}}
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4"><i class="fas fa-weight-scale mr-2 text-sky-500"></i>Hasil Ukur Meja 1-4</h3>
                    
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="border-b border-slate-100 pb-2">
                            <p class="text-slate-400 font-medium">Berat Badan</p>
                            <p class="text-sm font-black text-slate-800 mt-0.5">{{ $pemeriksaan->berat_badan ?? '-' }} kg</p>
                        </div>
                        <div class="border-b border-slate-100 pb-2">
                            <p class="text-slate-400 font-medium">Tinggi / Panjang Badan</p>
                            <p class="text-sm font-black text-slate-800 mt-0.5">{{ $pemeriksaan->tinggi_badan ?? '-' }} cm</p>
                        </div>
                        
                        {{-- Field Tambahan Unik Tiap Demografi --}}
                        @if($kategori === 'balita')
                            <div>
                                <p class="text-slate-400 font-medium">Lingkar Kepala</p>
                                <p class="text-sm font-black text-slate-800 mt-0.5">{{ $pemeriksaan->lingkar_kepala ?? '-' }} cm</p>
                            </div>
                            <div>
                                <p class="text-slate-400 font-medium">Lingkar Lengan (LILA)</p>
                                <p class="text-sm font-black text-slate-800 mt-0.5">{{ $pemeriksaan->lila ?? '-' }} cm</p>
                            </div>
                        @elseif($kategori === 'remaja')
                            <div>
                                <p class="text-slate-400 font-medium">Tekanan Darah</p>
                                <p class="text-sm font-black text-slate-800 mt-0.5">{{ $pemeriksaan->tekanan_darah ?? '-' }} mmHg</p>
                            </div>
                            <div>
                                <p class="text-slate-400 font-medium">Kadar Hemoglobin (HB)</p>
                                <p class="text-sm font-black text-slate-800 mt-0.5">{{ $pemeriksaan->hb ?? '-' }} g/dL</p>
                            </div>
                        
                        @elseif($kategori === 'lansia')
                            <div class="border-b border-slate-100 pb-2">
                                <p class="text-slate-400 font-medium">Tekanan Darah</p>
                                <p class="text-sm font-black text-slate-800 mt-0.5">{{ $pemeriksaan->tekanan_darah ?? '-' }} mmHg</p>
                            </div>
                            <div class="border-b border-slate-100 pb-2">
                                <p class="text-slate-400 font-medium">Gula Darah Sewaktu</p>
                                <p class="text-sm font-black text-slate-800 mt-0.5">{{ $pemeriksaan->gula_darah ?? '-' }} mg/dL</p>
                            </div>
                            <div>
                                <p class="text-slate-400 font-medium">Asam Urat</p>
                                <p class="text-sm font-black text-slate-800 mt-0.5">{{ $pemeriksaan->asam_urat ?? '-' }} mg/dL</p>
                            </div>
                            <div>
                                <p class="text-slate-400 font-medium">Kolesterol</p>
                                <p class="text-sm font-black text-slate-800 mt-0.5">{{ $pemeriksaan->kolesterol ?? '-' }} mg/dL</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- 3. KOTAK REKOMENDASI OTOMATIS (DECISION SUPPORT SYSTEM) --}}
                @if($analisis)
                    <div class="bg-slate-900 text-white rounded-3xl p-6 shadow-sm flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-teal-400"><i class="fas fa-wand-magic-sparkles mr-1.5"></i>Analisis Saran Gizi</h4>
                                <span class="text-[9px] bg-teal-500/10 border border-teal-500/20 text-teal-400 font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Otomatis</span>
                            </div>

                            {{-- Render Hasil Analisis Berdasarkan Kategori --}}
                            @if($kategori === 'balita')
                                <div class="flex flex-col gap-2.5 text-xs border-b border-white/10 pb-4">
                                    <p>• Indikator BB/U: <span class="text-teal-300 font-bold">{{ $analisis['bbu']['status'] }}</span> ({{ $analisis['bbu']['detail'] }})</p>
                                    <p>• Indikator TB/U (Stunting): <span class="text-teal-300 font-bold">{{ $analisis['tbu']['status'] }}</span> ({{ $analisis['tbu']['detail'] }})</p>
                                    <p>• Indikator BB/TB (Wasting): <span class="text-teal-300 font-bold">{{ $analisis['bbtb']['status'] }}</span> ({{ $analisis['bbtb']['detail'] }})</p>
                                </div>
                                <div class="mt-4">
                                    <p class="text-[11px] text-slate-400 uppercase font-bold">Rekomendasi Status:</p>
                                    <h5 class="text-base font-black text-white mt-0.5">{{ $analisis['kesimpulan']['status'] }}</h5>
                                    <p class="text-xs text-slate-300 mt-1 leading-relaxed">"{{ $analisis['kesimpulan']['pesan'] }}"</p>
                                </div>
                            @elseif($kategori === 'remaja')
                                <div>
                                    <p class="text-[11px] text-slate-400 uppercase font-bold">Rekomendasi Status Gizi (IMT):</p>
                                    <h5 class="text-base font-black text-white mt-0.5 uppercase tracking-wide">{{ $analisis['kategori'] }}</h5>
                                    <p class="text-xs text-slate-300 mt-1.5 leading-relaxed">"{{ $analisis['pesan'] }}"</p>
                                    <p class="text-xs text-teal-300 mt-3 italic"><i class="fas fa-lightbulb text-amber-400 mr-1"></i> Saran: {{ $analisis['rekomendasi'] }}</p>
                                </div>
                            
                            @elseif($kategori === 'lansia')
                                <div>
                                    <p class="text-[11px] text-slate-400 uppercase font-bold">Rekomendasi Skrining PTM:</p>
                                    <h5 class="text-base font-black text-white mt-0.5">{{ $analisis['kesimpulan']['status'] }}</h5>
                                    <p class="text-xs text-slate-300 mt-1 leading-relaxed">"{{ $analisis['kesimpulan']['pesan'] }}"</p>
                                    <div class="mt-3 bg-white/5 border border-white/10 p-3 rounded-xl text-xs">
                                        <p class="text-[10px] font-bold text-teal-400 uppercase tracking-wider">Tindakan Rujukan Bidan:</p>
                                        <p class="font-bold text-white mt-0.5">{{ $analisis['kesimpulan']['tindakan'] }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <p class="text-[10px] text-slate-500 mt-6 pt-2 border-t border-white/5 font-mono">Gunakan kesimpulan di atas untuk mengisi formulir medis secara efisien.</p>
                    </div>
                @endif
            </div>

            {{-- KOLOM KANAN: FORMULIR DIAGNOSA RESMI BIDAN --}}
            <div class="lg:col-span-7 h-full flex flex-col">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 md:p-8 h-full flex flex-col justify-between">
                    <div>
                        <div class="border-b border-slate-100 pb-4 mb-6">
                            <h3 class="text-base font-black text-slate-800"><i class="fas fa-file-signature text-teal-500 mr-2"></i>Form Validasi & Pengesahan</h3>
                            <p class="text-xs text-slate-500 mt-1">Data yang disahkan di bawah ini akan langsung masuk ke dalam grafik perkembangan pada akun warga.</p>
                        </div>

                        <form action="{{ route('bidan.pemeriksaan.simpan-validasi', $pemeriksaan->id) }}" method="POST" id="mainFormValidasi">
                            @csrf
                            @method('PUT')

                            {{-- 1. INPUT STATUS GIZI / STATUS UTAMA --}}
                            <div class="mb-5">
                                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Status Ringkas Pasien</label>
                                <input type="text" name="status_gizi" 
                                    value="{{ old('status_gizi', $analisis['kesimpulan']['status'] ?? $analisis['kategori'] ?? $pemeriksaan->status_gizi) }}" 
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs font-bold text-slate-700 focus:outline-none focus:border-teal-500 focus:bg-white transition-all" 
                                    placeholder="Contoh: Gizi Normal / Risiko Stunting / Hipertensi">
                            </div>

                            {{-- 2. INPUT KESIMPULAN DIAGNOSA MEDIS (WAJIB) --}}
                            <div class="mb-5">
                                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Kesimpulan / Diagnosa Klinis <span class="text-rose-500">*</span></label>
                                <textarea name="diagnosa" rows="3" required
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs text-slate-700 focus:outline-none focus:border-teal-500 focus:bg-white transition-all @error('diagnosa') border-rose-400 @enderror"
                                    placeholder="Tuliskan diagnosa pemeriksaan medis resmi dari Bidan...">{{ old('diagnosa', $analisis['kesimpulan']['pesan'] ?? $analisis['pesan'] ?? '') }}</textarea>
                                @error('diagnosa')
                                    <p class="text-xs text-rose-500 font-bold mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- 3. INPUT PEMBERIAN VITAMIN / RESEP OBAT --}}
                            <div class="mb-5">
                                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Tindakan Medis / Resep Obat & Vitamin</label>
                                <input type="text" name="tindakan" 
                                    value="{{ old('tindakan', $analisis['kesimpulan']['tindakan'] ?? '') }}" 
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs text-slate-700 focus:outline-none focus:border-teal-500 focus:bg-white transition-all" 
                                    placeholder="Contoh: Pemberian PMT Tambahan & Vitamin A / Rujuk Puskesmas">
                            </div>

                            {{-- 4. INPUT CATATAN EDUKASI REKOMENDASI WARGA --}}
                            <div class="mb-6">
                                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Catatan Edukasi & Saran Mandiri</label>
                                <textarea name="catatan_edukasi" rows="3" 
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs text-slate-700 focus:outline-none focus:border-teal-500 focus:bg-white transition-all"
                                    placeholder="Contoh: Evaluasi pemberian ASI eksklusif, atau kurangi asupan garam dan karbohidrat tinggi...">{{ old('catatan_edukasi', $analisis['kesimpulan']['rekomendasi'] ?? $analisis['rekomendasi'] ?? '') }}</textarea>
                            </div>
                        </form>
                    </div>

                    {{-- TOMBOL SUBMIT PERMANEN --}}
                    <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row gap-3">
                        <button type="submit" form="mainFormValidasi" 
                                class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-4 bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-sm hover:shadow-md transition-all outline-none">
                            <i class="fas fa-clipboard-check text-sm"></i> Sahkan & Terbitkan Rekam Medis
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .animate-fade-in { animation: fadeIn 0.4s ease forwards; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush