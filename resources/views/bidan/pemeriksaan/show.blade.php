@extends('layouts.bidan')

@section('title', 'Detail Pemeriksaan Klinis')
@section('page-name', 'Detail Pemeriksaan')
@section('page-title', 'Detail Pemeriksaan Klinis')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    $kategori = strtolower(data_get($ringkasan ?? [], 'kategori', 'balita'));
    if (! in_array($kategori, ['balita', 'remaja', 'lansia'], true)) {
        $kategori = 'balita';
    }

    $parameter = collect($parameter ?? []);

    $getValue = function ($item, array $keys, mixed $default = '-') {
        if (!$item) return $default;
        foreach ($keys as $key) {
            $value = data_get($item, $key);
            if ($value !== null && $value !== '') return $value;
        }
        return $default;
    };

    $formatDate = fn($date) => $date ? Carbon::parse($date)->translatedFormat('d F Y') : '-';
    $formatDateTime = fn($date) => $date ? Carbon::parse($date)->translatedFormat('d M Y, H:i') . ' WIB' : '-';
    $formatParameterValue = fn($value) => ($value === null || $value === '' || $value === '-') ? 'Belum diisi' : $value;

    // Data Pasien
    $pasienNama = data_get($ringkasan, 'nama_pasien') ?? $getValue($pasien, ['nama_lengkap', 'nama', 'nama_balita', 'nama_remaja', 'nama_lansia'], 'Tanpa Nama');
    $pasienNik = data_get($ringkasan, 'nik_pasien') ?? $getValue($pasien, ['nik', 'nik_anak'], '-');
    $tanggalLahir = $getValue($pasien, ['tanggal_lahir', 'tgl_lahir', 'lahir'], null);
    
    $usia = '-';
    if ($tanggalLahir) {
        $usia = Carbon::parse($tanggalLahir)->diff(now())->format('%y Thn %m Bln');
    }

    $initial = Str::upper(Str::substr(trim($pasienNama), 0, 1)) ?: 'P';
    $tanggalKunjungan = data_get($ringkasan, 'tanggal_kunjungan') ?? $formatDate(data_get($pemeriksaan, 'kunjungan.tanggal_kunjungan') ?? data_get($pemeriksaan, 'created_at'));

    // Status Verifikasi
    $statusRaw = strtolower((string) data_get($pemeriksaan, 'status_verifikasi', 'pending'));
    $isVerified = in_array($statusRaw, ['verified', 'tervalidasi', 'approved'], true);
    $statusType = $isVerified ? 'verified' : 'pending';

    $statusMeta = match ($statusType) {
        'verified' => [
            'label' => 'Tervalidasi',
            'desc' => 'Telah disahkan Bidan',
            'icon' => 'fa-check-circle',
            'badge' => 'bg-emerald-500 text-white shadow-emerald-500/30',
            'text' => 'text-emerald-600',
            'bg_light' => 'bg-emerald-50 border-emerald-200',
        ],
        default => [
            'label' => 'Menunggu Validasi',
            'desc' => 'Perlu tinjauan medis',
            'icon' => 'fa-clock-rotate-left',
            'badge' => 'bg-amber-500 text-white shadow-amber-500/30',
            'text' => 'text-amber-600',
            'bg_light' => 'bg-amber-50 border-amber-200',
        ],
    };

    // Tema Kategori Pasien
    $kategoriMeta = match ($kategori) {
        'remaja' => [
            'label' => 'Remaja',
            'icon' => 'fa-user-graduate',
            'gradient' => 'from-teal-500 via-cyan-500 to-blue-500',
            'shadow' => 'shadow-[0_20px_40px_-12px_rgba(6,182,212,.35)]',
            'text' => 'text-teal-500',
            'badge' => 'bg-teal-100 text-teal-700',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'icon' => 'fa-person-cane',
            'gradient' => 'from-sky-500 via-blue-500 to-indigo-500',
            'shadow' => 'shadow-[0_20px_40px_-12px_rgba(14,165,233,.35)]',
            'text' => 'text-sky-500',
            'badge' => 'bg-sky-100 text-sky-700',
        ],
        default => [
            'label' => 'Balita',
            'icon' => 'fa-baby',
            'gradient' => 'from-emerald-500 via-teal-500 to-teal-600',
            'shadow' => 'shadow-[0_20px_40px_-12px_rgba(16,185,129,.35)]',
            'text' => 'text-emerald-500',
            'badge' => 'bg-emerald-100 text-emerald-700',
        ],
    };

    $backTab = $isVerified ? 'verified' : 'pending';

    // Data Tambahan Klinis & Sistem
    $statusRingkasLabel = $kategori === 'balita' ? 'Status Gizi' : 'Status Ringkas';
    $statusRingkas = $getValue($pemeriksaan, ['status_gizi'], '-');
    $kesimpulan = $getValue($pemeriksaan, ['kesimpulan_pemeriksaan', 'diagnosa', 'catatan_bidan'], '-');
    $tindakan = $getValue($pemeriksaan, ['tindakan', 'tindakan_lanjut', 'layanan'], '-');
    $edukasi = $getValue($pemeriksaan, ['catatan_edukasi', 'edukasi', 'edukasi_kesehatan'], '-');
    
    $keluhan = $pemeriksaan->keluhan ?? '-';
    $catatanKader = $pemeriksaan->catatan_kader ?? '-';

    $petugasInput = data_get($ringkasan, 'petugas_input') ?? data_get($pemeriksaan, 'pemeriksa.name') ?? data_get($pemeriksaan, 'kunjungan.petugas.name') ?? '-';
    $bidanValidasi = data_get($ringkasan, 'bidan_validasi') ?? data_get($pemeriksaan, 'verifikator.name') ?? data_get($pemeriksaan, 'verifikatorLegacy.name') ?? '-';
    $waktuInput = $formatDateTime(data_get($pemeriksaan, 'created_at'));
    $waktuUpdate = $formatDateTime(data_get($pemeriksaan, 'updated_at'));
    $tanggalValidasi = $pemeriksaan->verified_at ?? $pemeriksaan->tanggal_validasi ?? $pemeriksaan->updated_at ?? null;
@endphp

@push('styles')
<style>
    /* SCROLLING OPTIMIZATION */
    html { scroll-behavior: smooth; }
    body { background-color: #f4f7f6; } 

    /* WIDGET CARD DENGAN HARDWARE ACCELERATION */
    .widget-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        transform: translateZ(0); 
        will-change: transform, box-shadow;
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
</style>
@endpush

@section('content')
<div class="relative min-h-screen overflow-hidden px-4 py-5 sm:px-6 lg:px-8">

    <div class="relative z-10 mx-auto max-w-[1200px] animate-pop-in pb-20 mt-4">

        {{-- 1. FLOATING HERO SECTION --}}
        <section class="bg-gradient-to-br {{ $kategoriMeta['gradient'] }} rounded-[2.5rem] p-8 md:p-10 mb-8 relative overflow-hidden {{ $kategoriMeta['shadow'] }} border border-white/20">
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/15 blur-[60px] rounded-full pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start gap-6 text-center md:text-left">
                
                {{-- Avatar --}}
                <div class="w-24 h-24 shrink-0 rounded-[1.8rem] bg-white border-4 border-white/50 {{ $kategoriMeta['text'] }} flex items-center justify-center font-black text-4xl shadow-xl">
                    {{ $initial }}
                </div>

                {{-- Info --}}
                <div class="flex-1">
                    <div class="inline-flex items-center justify-center md:justify-start gap-2 mb-2">
                        <span class="bg-white/20 border border-white/30 text-white font-bold text-[10px] uppercase tracking-widest px-3 py-1 rounded-full flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid {{ $kategoriMeta['icon'] }}"></i> {{ $kategoriMeta['label'] }}
                        </span>
                        <span class="bg-white/20 border border-white/30 text-white text-[10px] font-bold px-3 py-1 rounded-full flex items-center gap-1.5 uppercase tracking-widest shadow-sm">
                            <i class="fa-solid fa-calendar-day"></i> Diukur: {{ $tanggalKunjungan }}
                        </span>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-2">
                        {{ $pasienNama }}
                    </h1>
                    
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-x-4 gap-y-2 text-white/90 text-sm font-semibold">
                        <span class="flex items-center gap-1"><i class="fa-solid fa-id-card opacity-70"></i> NIK: {{ $pasienNik }}</span>
                        <span class="flex items-center gap-1"><i class="fa-solid fa-cake-candles opacity-70"></i> Usia: {{ $usia }}</span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col items-center md:items-end gap-3 shrink-0 w-full md:w-auto mt-4 md:mt-0">
                    <span class="{{ $statusMeta['badge'] }} text-[10px] font-black uppercase tracking-widest px-4 py-2 rounded-2xl shadow-lg border border-white/20 flex items-center gap-2 mb-1">
                        <i class="fa-solid {{ $statusMeta['icon'] }} text-sm"></i> {{ $statusMeta['label'] }}
                    </span>

                    <div class="flex flex-wrap justify-center md:justify-end gap-2 w-full">
                        <a href="{{ route('bidan.pemeriksaan.index', ['tab' => $backTab]) }}" class="bg-white/20 hover:bg-white/30 border border-white/30 text-white text-[11px] uppercase tracking-widest font-bold px-5 py-2.5 rounded-xl transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-arrow-left"></i> Antrean
                        </a>
                        @unless($isVerified)
                            <a href="{{ route('bidan.pemeriksaan.validasi', $pemeriksaan->id) }}" class="bg-white/90 hover:bg-white text-teal-600 text-[11px] uppercase tracking-widest font-black px-5 py-2.5 rounded-xl transition-all shadow-sm hover:-translate-y-0.5 flex items-center gap-2">
                                <i class="fa-solid fa-clipboard-check"></i> Validasi
                            </a>
                        @endunless
                    </div>
                </div>

            </div>
        </section>

        {{-- 2. GRID KONTEN UTAMA (Seimbang 7 vs 5) --}}
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start mb-6">
            
            {{-- KOLOM KIRI: Data Kader (Col 7) --}}
            <div class="xl:col-span-7 flex flex-col gap-6">
                
                {{-- Card Pengukuran Fisik --}}
                <div class="widget-card p-6 sm:p-8">
                    <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $kategoriMeta['badge'] }} flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid fa-clipboard-list"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Hasil Pengukuran</h4>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Inputan Awal Kader</p>
                            </div>
                        </div>
                        <span class="bg-slate-50 text-slate-500 border border-slate-200 text-[9px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-user-nurse"></i> {{ $petugasInput }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @forelse($parameter as $item)
                            @php 
                                $val = $formatParameterValue(data_get($item, 'value', '-')); 
                                $isEmpty = ($val === 'Belum diisi' || $val === '-');
                            @endphp
                            <div class="bg-slate-50/50 border border-slate-100 rounded-2xl p-4 flex flex-col justify-center">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1 truncate">{{ data_get($item, 'label', '-') }}</p>
                                <p class="text-base font-black {{ $isEmpty ? 'text-slate-300' : 'text-slate-800' }}">{{ $val }}</p>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-6 border border-dashed border-slate-200 rounded-2xl">
                                <p class="text-xs font-bold text-slate-400">Belum ada parameter tercatat.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Card Anamnesis / Keluhan Kader --}}
                <div class="widget-card p-6 sm:p-8">
                    <div class="flex items-center gap-3 mb-5 border-b border-slate-100 pb-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 border border-amber-100 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid fa-clipboard-question"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Anamnesis</h4>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Keluhan & Catatan Kader</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">Riwayat Keluhan</p>
                            <p class="text-xs font-bold text-slate-700 leading-relaxed">{{ $keluhan }}</p>
                        </div>
                        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-col">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">Catatan Kader</p>
                            <p class="text-xs font-bold text-slate-700 leading-relaxed">{{ $catatanKader }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: Validasi Bidan (Col 5) --}}
            <div class="xl:col-span-5 flex flex-col gap-6">
                
                {{-- Card Validasi Bidan (Highlight Card) --}}
                <div class="widget-card p-6 sm:p-8 border {{ $statusMeta['bg_light'] }} relative overflow-hidden flex-1">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                        <div class="w-10 h-10 rounded-full bg-white {{ $statusMeta['text'] }} border border-slate-100 flex items-center justify-center text-lg shadow-sm">
                            <i class="fa-solid fa-file-signature"></i>
                        </div>
                        <div>
                            <h4 class="text-[11px] font-black {{ $statusMeta['text'] }} uppercase tracking-widest">Validasi Bidan</h4>
                            <p class="text-sm font-black text-slate-800 leading-tight">{{ $statusMeta['label'] }}</p>
                        </div>
                    </div>

                    @if($isVerified)
                        <div class="space-y-3 relative z-10">
                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ $statusRingkasLabel }}</p>
                                <p class="text-sm font-black text-slate-800">{{ $statusRingkas }}</p>
                            </div>
                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Diagnosa / Kesimpulan</p>
                                <p class="text-sm font-bold text-slate-700 leading-relaxed">{{ $kesimpulan }}</p>
                            </div>
                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Tindakan / Layanan</p>
                                <p class="text-sm font-bold text-slate-700 leading-relaxed">{{ $tindakan }}</p>
                            </div>
                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Edukasi Kesehatan</p>
                                <p class="text-sm font-bold text-slate-700 leading-relaxed">{{ $edukasi }}</p>
                            </div>
                            
                            {{-- Box Divalidasi Oleh --}}
                            <div class="bg-teal-600 rounded-2xl p-5 shadow-md flex justify-between items-center mt-4">
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-widest text-teal-100 mb-0.5">Divalidasi Oleh</p>
                                    <p class="text-sm font-black text-white">{{ $bidanValidasi }}</p>
                                </div>
                                @if($tanggalValidasi)
                                    <div class="text-right">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-teal-200 mb-0.5">Tanggal</p>
                                        <p class="text-xs font-bold text-white">{{ Carbon::parse($tanggalValidasi)->translatedFormat('d M Y') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-2xl p-6 text-center border border-amber-100 mt-4 relative z-10">
                            <i class="fa-solid fa-clock-rotate-left text-4xl text-amber-400 mb-4"></i>
                            <p class="text-sm font-bold text-slate-700 px-2 leading-relaxed mb-5">Pemeriksaan ini belum ditinjau. Segera berikan keputusan medis untuk mengarsipkan data.</p>
                            <a href="{{ route('bidan.pemeriksaan.validasi', $pemeriksaan->id) }}" class="btn-pill inline-block bg-teal-500 hover:bg-teal-600 hover:-translate-y-0.5 text-white px-6 py-3 text-[10px] font-black uppercase tracking-widest shadow-md transition-all">Validasi Sekarang</a>
                        </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- 3. FULL WIDTH CARD: Jejak Rekam Sistem (Mengunci Tata Letak) --}}
        <div class="widget-card p-6 sm:p-8 w-full">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                
                {{-- Header Jejak Rekam --}}
                <div class="flex items-center gap-4 border-b lg:border-b-0 lg:border-r border-slate-100 pb-4 lg:pb-0 lg:pr-6 shrink-0">
                    <div class="w-12 h-12 rounded-full bg-slate-50 text-slate-500 border border-slate-200 flex items-center justify-center text-xl shadow-inner">
                        <i class="fa-solid fa-server"></i>
                    </div>
                    <div>
                        <h4 class="text-[11px] font-black text-slate-600 uppercase tracking-widest">Jejak Rekam Sistem</h4>
                        <p class="text-sm font-black text-slate-800 leading-tight">Log Aktivitas Data</p>
                    </div>
                </div>

                {{-- Detail Waktu Menyamping (Horizontal) --}}
                <div class="flex flex-col sm:flex-row flex-1 gap-4 lg:pl-2">
                    <div class="flex-1 flex justify-between items-center bg-slate-50/80 rounded-2xl px-5 py-4 border border-slate-100">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-cloud-arrow-up text-slate-300"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Waktu Input</span>
                        </div>
                        <span class="text-[11px] font-bold text-slate-700">{{ $waktuInput }}</span>
                    </div>
                    <div class="flex-1 flex justify-between items-center bg-slate-50/80 rounded-2xl px-5 py-4 border border-slate-100">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-slate-300"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Pembaruan Terakhir</span>
                        </div>
                        <span class="text-[11px] font-bold text-slate-700">{{ $waktuUpdate }}</span>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>
@endsection