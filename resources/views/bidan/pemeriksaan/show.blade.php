@extends('layouts.bidan')

@section('title', 'Detail Arsip Pemeriksaan Klinis')

@section('content')
@php
    $kategori = strtolower($pemeriksaan->kategori_pasien);
@endphp

<div class="bg-[#f8fafc] min-h-screen pb-12 font-poppins w-full animate-fade-in">
    
    {{-- BARIS HEADER --}}
    <div class="pt-6 pb-4 px-4 md:px-8 max-w-7xl mx-auto flex justify-between items-center shrink-0">
        <a href="{{ route('bidan.pemeriksaan.index', ['tab' => 'verified']) }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-400 hover:text-slate-700 transition-colors group">
            <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center shadow-sm group-hover:bg-slate-100 transition-all">
                <i class="fas fa-arrow-left text-xs"></i>
            </div>
            Kembali ke Ruang Tunggu
        </a>
        <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="text-[10px] font-black tracking-widest uppercase text-slate-500">Dokumen Arsip Terverifikasi</span>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 md:px-8">
        {{-- GRID UTAMA --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8 items-stretch w-full">
            
            {{-- KOLOM KIRI: PROFIL PASIEN & HASIL UKUR FISIK --}}
            <div class="lg:col-span-5 h-full flex flex-col gap-6">
                
                {{-- Kartu Identitas Pasien --}}
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4"><i class="fas fa-id-card mr-2 text-slate-400"></i>Biodata Pasien</h3>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center text-xl font-black">
                            {{ strtoupper(substr($pasien->nama_lengkap, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-base font-black text-slate-800 leading-tight truncate">{{ $pasien->nama_lengkap }}</h2>
                            <p class="text-xs text-slate-400 mt-1 font-bold tracking-wider uppercase">NIK: {{ $pasien->nik ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-5 pt-4 border-t border-slate-100 text-xs">
                        <div>
                            <p class="text-slate-400 font-medium">Kategori Pelayanan</p>
                            <p class="font-black text-slate-700 uppercase mt-0.5">{{ $pemeriksaan->kategori_pasien }}</p>
                        </div>
                        <div>
                            <p class="text-slate-400 font-medium">Tanggal Periksa</p>
                            <p class="font-bold text-slate-700 mt-0.5">{{ \Carbon\Carbon::parse($pemeriksaan->tanggal_periksa ?? $pemeriksaan->tanggal_kunjungan)->translatedFormat('d M Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Hasil Pengukuran Fisik (Dari Kader Meja 1-4) --}}
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 flex-1">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4"><i class="fas fa-chart-bar mr-2 text-sky-500"></i>Parameter Fisik Pengukuran</h3>
                    
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="border-b border-slate-100 pb-2">
                            <p class="text-slate-400 font-medium">Berat Badan</p>
                            <p class="text-sm font-black text-slate-800 mt-0.5">{{ $pemeriksaan->berat_badan ?? '-' }} kg</p>
                        </div>
                        <div class="border-b border-slate-100 pb-2">
                            <p class="text-slate-400 font-medium">Tinggi / Panjang Badan</p>
                            <p class="text-sm font-black text-slate-800 mt-0.5">{{ $pemeriksaan->tinggi_badan ?? '-' }} cm</p>
                        </div>

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
                        @elseif(str_contains($kategori, 'hamil') || $kategori === 'ibu_hamil')
                            <div class="border-b border-slate-100 pb-2">
                                <p class="text-slate-400 font-medium">Tinggi Fundus (TFU)</p>
                                <p class="text-sm font-black text-slate-800 mt-0.5">{{ $pemeriksaan->tinggi_fundus ?? '-' }} cm</p>
                            </div>
                            <div class="border-b border-slate-100 pb-2">
                                <p class="text-slate-400 font-medium">Detak Jantung Janin (DJJ)</p>
                                <p class="text-sm font-black text-slate-800 mt-0.5">{{ $pemeriksaan->djj ?? '-' }} bpm</p>
                            </div>
                            <div>
                                <p class="text-slate-400 font-medium">LILA Ibu</p>
                                <p class="text-sm font-black text-slate-800 mt-0.5">{{ $pemeriksaan->lila ?? '-' }} cm</p>
                            </div>
                            <div>
                                <p class="text-slate-400 font-medium">Tekanan Darah</p>
                                <p class="text-sm font-black text-slate-800 mt-0.5">{{ $pemeriksaan->tekanan_darah ?? '-' }} mmHg</p>
                            </div>
                        @elseif($kategori === 'lansia')
                            <div class="border-b border-slate-100 pb-2">
                                <p class="text-slate-400 font-medium">Tekanan Darah</p>
                                <p class="text-sm font-black text-slate-800 mt-0.5">{{ $pemeriksaan->tekanan_darah ?? '-' }} mmHg</p>
                            </div>
                            <div class="border-b border-slate-100 pb-2">
                                <p class="text-slate-400 font-medium">Gula Darah</p>
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
            </div>

            {{-- KOLOM KANAN: RESUME KEPUTUSAN MEDIS RESMI (READ ONLY) --}}
            <div class="lg:col-span-7 h-full flex flex-col">
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 md:p-8 h-full flex flex-col justify-between">
                    
                    <div class="flex flex-col gap-6">
                        <div class="border-b border-slate-100 pb-4">
                            <h3 class="text-base font-black text-slate-800"><i class="fas fa-file-invoice-dollar text-emerald-500 mr-2"></i>Resume Hasil Pemeriksaan Resmi</h3>
                            <p class="text-xs text-slate-500 mt-1">Salinan dokumen medis resmi yang telah disahkan dan dikunci oleh Bidan Puskesmas.</p>
                        </div>

                        {{-- DISPLAY STATUS / KATEGORI GIZI --}}
                        <div class="bg-slate-50 p-4.5 rounded-2xl border border-slate-100">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Kesimpulan Status Kesehatan</p>
                            <p class="text-sm font-black text-slate-800 mt-1 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                {{ $pemeriksaan->status_gizi ?? 'Normal' }}
                            </p>
                        </div>

                        {{-- DISPLAY DIAGNOSA KLIINIS --}}
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5">Catatan Diagnosa Klinis</p>
                            <div class="bg-emerald-50/30 border border-emerald-100 rounded-2xl p-4">
                                <p class="text-xs font-bold text-slate-700 leading-relaxed italic">
                                    "{{ $pemeriksaan->diagnosa ?? 'Pemeriksaan rutin berjalan normal tanpa indikasi klinis lanjutan.' }}"
                                </p>
                            </div>
                        </div>

                        {{-- DISPLAY PEMBERIAN OBAT / TINDAKAN --}}
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5">Tindakan Medis & Terapi Vitamin</p>
                            <p class="text-xs font-black text-slate-800 bg-slate-50 border border-slate-100 px-4 py-3 rounded-xl">
                                <i class="fas fa-capsules text-teal-500 mr-2"></i> {{ $pemeriksaan->tindakan ?? '-' }}
                            </p>
                        </div>

                        {{-- DISPLAY CATATAN EDUKASI --}}
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5 font-sans">Saran Konseling / Edukasi Keluarga</p>
                            <p class="text-xs font-medium text-slate-600 bg-slate-50 border border-slate-100 px-4 py-3 rounded-xl leading-relaxed">
                                {{ $pemeriksaan->edukasi ?? 'Tidak ada catatan edukasi khusus.' }}
                            </p>
                        </div>
                    </div>

                    {{-- STEMPEL VERIFIKATOR DIGITAL (TANDA TANGAN RESMI) --}}
                    <div class="mt-8 pt-5 border-t border-slate-100 flex justify-end">
                        <div class="bg-emerald-50/50 border border-emerald-200/60 rounded-2xl p-4 flex items-center gap-3 w-full sm:w-auto">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-lg shadow-sm">
                                <i class="fas fa-signature"></i>
                            </div>
                            <div class="text-left text-xs">
                                <p class="text-[9px] font-black text-emerald-700 uppercase tracking-wider leading-none">Disahkan Digital Oleh:</p>
                                <p class="font-black text-slate-800 mt-1.5">{{ $pemeriksaan->verifikator->name ?? 'Bidan Puskesmas' }}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">NIP/SIP. {{ $pemeriksaan->verifikator->nip ?? '198902142015032001' }}</p>
                            </div>
                        </div>
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
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>
@endpush