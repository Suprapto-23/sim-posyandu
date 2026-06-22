@extends('layouts.bidan')

@section('title', 'Detail Imunisasi Balita')
@section('page-name', 'Imunisasi')
@section('page-title', 'Detail Imunisasi Balita')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    $programOptions = $programOptions ?? [];

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

    $balita = $balita ?? data_get($imunisasi, 'kunjungan.pasien') ?? data_get($imunisasi, 'balita');

    $namaBalita = $balita ? $getValue($balita, ['nama_lengkap', 'nama', 'nama_balita'], 'Balita tidak terdata') : $getValue($imunisasi, ['nama_balita', 'nama_pasien'], 'Balita tidak terdata');
    $nikBalita = $balita ? $getValue($balita, ['nik', 'nik_anak'], '-') : $getValue($imunisasi, ['nik', 'nik_balita', 'nik_anak'], '-');
    $waliBalita = $balita ? $getValue($balita, ['nama_ibu', 'nama_ayah', 'nama_wali'], '-') : $getValue($imunisasi, ['nama_ibu', 'nama_ayah', 'nama_wali'], '-');
    $alamatBalita = $balita ? $getValue($balita, ['alamat', 'alamat_lengkap', 'dusun'], '-') : $getValue($imunisasi, ['alamat', 'alamat_balita'], '-');

    $jenisKelamin = strtolower((string) ($balita ? $getValue($balita, ['jenis_kelamin', 'jk', 'gender'], '-') : '-'));
    $jenisKelamin = match ($jenisKelamin) {
        'l', 'laki-laki', 'male' => 'Laki-laki',
        'p', 'perempuan', 'female' => 'Perempuan',
        default => '-'
    };

    $tanggalLahirRaw = $balita ? $getValue($balita, ['tanggal_lahir', 'tgl_lahir', 'lahir'], null) : null;
    $tanggalLahir = $formatDate($tanggalLahirRaw);
    $usia = $tanggalLahirRaw ? Carbon::parse($tanggalLahirRaw)->diff(now())->format('%y Thn %m Bln') : '-';
    $initial = Str::upper(Str::substr(trim($namaBalita), 0, 1)) ?: 'B';

    $jenisImunisasi = $getValue($imunisasi, ['jenis_imunisasi', 'nama_imunisasi', 'jenis'], 'Imunisasi');
    $vaksin = $getValue($imunisasi, ['vaksin', 'nama_vaksin'], '-');
    $dosis = $getValue($imunisasi, ['dosis', 'dosis_ke'], '-');
    $batch = $getValue($imunisasi, ['batch_number', 'no_batch', 'nomor_batch'], '-');
    $tanggalImunisasi = $formatDate($getValue($imunisasi, ['tanggal_imunisasi', 'tanggal', 'created_at'], null));
    $catatan = $getValue($imunisasi, ['keterangan', 'catatan'], '-');
    $petugas = data_get($imunisasi, 'kunjungan.petugas.name') ?? data_get($imunisasi, 'kunjungan.petugas.nama') ?? '-';
    
    $tanggalInput = $formatDateTime(data_get($imunisasi, 'created_at'));
    $tanggalPerbarui = $formatDateTime(data_get($imunisasi, 'updated_at'));
@endphp

@push('styles')
<style>
    html { scroll-behavior: smooth; }
    body { background-color: #f4f7f6; } 

    .pc-page {
        background:
            radial-gradient(circle at 8% 5%, rgba(16, 185, 129, .14), transparent 28%),
            radial-gradient(circle at 95% 8%, rgba(14, 165, 233, .13), transparent 26%),
            radial-gradient(circle at 50% 96%, rgba(251, 191, 36, .10), transparent 30%),
            linear-gradient(135deg, #f3fff9 0%, #eef9ff 48%, #f8fafc 100%);
        background-attachment: fixed;
    }

    .pc-grid {
        background-image:
            linear-gradient(rgba(15, 23, 42, .035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, .035) 1px, transparent 1px);
        background-size: 30px 30px;
    }

    .widget-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .widget-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
    }

    .btn-pill { border-radius: 9999px; transition: all 0.2s ease; cursor: pointer; }
    .btn-pill:active { transform: scale(0.97); }

    .animate-pop-in { animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popIn { from { opacity: 0; transform: scale(.98) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
</style>
@endpush

@section('content')
<div class="pc-page relative min-h-screen overflow-hidden px-4 py-5 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute inset-0 pc-grid opacity-70"></div>

    <div class="relative z-10 mx-auto max-w-[1200px] animate-pop-in pb-20 mt-4">

        {{-- 1. FLOATING HERO SECTION --}}
        <section class="bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-600 rounded-[2.5rem] p-8 md:p-10 mb-8 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(16,185,129,.35)] border border-white/20">
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/15 blur-[60px] rounded-full pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start gap-6 text-center md:text-left">
                
                {{-- Avatar --}}
                <div class="w-24 h-24 shrink-0 rounded-[1.8rem] bg-white border-4 border-white/50 text-emerald-500 flex items-center justify-center font-black text-4xl shadow-xl">
                    {{ $initial }}
                </div>

                {{-- Info --}}
                <div class="flex-1">
                    <div class="inline-flex items-center justify-center md:justify-start gap-2 mb-2">
                        <span class="bg-white/20 border border-white/30 text-white font-bold text-[10px] uppercase tracking-widest px-3 py-1 rounded-full flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-baby"></i> Balita
                        </span>
                        <span class="bg-white/20 border border-white/30 text-white text-[10px] font-bold px-3 py-1 rounded-full flex items-center gap-1.5 uppercase tracking-widest shadow-sm">
                            <i class="fa-solid fa-calendar-day"></i> Layanan: {{ $tanggalImunisasi }}
                        </span>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-2">
                        {{ $namaBalita }}
                    </h1>
                    
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-x-4 gap-y-2 text-white/90 text-sm font-semibold">
                        <span class="flex items-center gap-1"><i class="fa-solid fa-id-card opacity-70"></i> NIK: {{ $nikBalita }}</span>
                        <span class="flex items-center gap-1"><i class="fa-solid fa-cake-candles opacity-70"></i> Usia: {{ $usia }}</span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col items-center md:items-end gap-3 shrink-0 w-full md:w-auto mt-4 md:mt-0">
                    <div class="flex flex-wrap justify-center md:justify-end gap-2 w-full">
                        <a href="{{ route('bidan.imunisasi.index') }}" class="bg-white/20 hover:bg-white/30 border border-white/30 text-white text-[11px] uppercase tracking-widest font-bold px-5 py-2.5 rounded-xl transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-arrow-left"></i> Arsip Imunisasi
                        </a>
                        <a href="{{ route('bidan.imunisasi.edit', $imunisasi->id) }}" class="bg-white/90 hover:bg-white text-emerald-600 text-[11px] uppercase tracking-widest font-black px-5 py-2.5 rounded-xl transition-all shadow-sm hover:-translate-y-0.5 flex items-center gap-2">
                            <i class="fa-solid fa-pen"></i> Edit Data
                        </a>
                    </div>
                </div>

            </div>
        </section>

        {{-- 2. GRID KONTEN UTAMA (Sangat Presisi menggunakan items-stretch) --}}
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-stretch mb-6">
            
            {{-- KOLOM KIRI: Identitas & Catatan (Col 7) --}}
            <div class="xl:col-span-7 flex flex-col gap-6 h-full">
                
                {{-- Card Identitas --}}
                <div class="widget-card p-6 sm:p-8 flex flex-col">
                    <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-500 border border-sky-100 flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid fa-user-circle"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Identitas Balita</h4>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Informasi Profil Pasien</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 flex-1">
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 flex flex-col justify-center">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Nama Lengkap</p>
                            <p class="text-sm font-bold text-slate-800">{{ $namaBalita }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 flex flex-col justify-center">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Jenis Kelamin</p>
                            <p class="text-sm font-bold text-slate-700">{{ $jenisKelamin }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 flex flex-col justify-center">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Ibu / Wali</p>
                            <p class="text-sm font-bold text-slate-700">{{ $waliBalita }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 flex flex-col justify-center">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Alamat Domisili</p>
                            <p class="text-sm font-medium text-slate-600 leading-relaxed truncate">{{ $alamatBalita }}</p>
                        </div>
                    </div>
                </div>

                {{-- Card Catatan KIPI (Memanjang mengisi ruang kosong / flex-1) --}}
                <div class="widget-card p-6 sm:p-8 flex flex-col flex-1">
                    <div class="flex items-center gap-3 mb-5 border-b border-slate-100 pb-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 border border-amber-100 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid fa-notes-medical"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Catatan Tambahan</h4>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Kondisi Klinis / KIPI</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col flex-1">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-2">Keterangan / Observasi Medis</p>
                        <p class="text-sm font-bold text-slate-700 leading-relaxed">{{ $catatan }}</p>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: Data Layanan Vaksin (Col 5) --}}
            <div class="xl:col-span-5 flex flex-col h-full">
                
                {{-- Card Layanan Vaksin (Highlight) --}}
                <div class="widget-card p-6 sm:p-8 border bg-emerald-50 border-emerald-200 relative overflow-hidden flex flex-col flex-1">
                    <i class="fa-solid fa-syringe absolute -right-6 -top-6 text-9xl opacity-5 pointer-events-none"></i>
                    
                    <div class="relative z-10 flex flex-col h-full">
                        <div class="flex items-center gap-3 mb-6 border-b border-white/50 pb-4 shrink-0">
                            <div class="w-10 h-10 rounded-full bg-white text-emerald-600 border border-emerald-100 flex items-center justify-center text-lg shadow-sm">
                                <i class="fa-solid fa-vial-circle-check"></i>
                            </div>
                            <div>
                                <h4 class="text-[11px] font-black text-emerald-600 uppercase tracking-widest">Detail Layanan</h4>
                                <p class="text-sm font-black text-slate-800 leading-tight">Data Imunisasi</p>
                            </div>
                        </div>

                        <div class="space-y-4 flex flex-col flex-1 relative z-10">
                            <div class="bg-white/90 rounded-2xl p-4 border border-white/50 shadow-sm flex flex-col justify-center">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1.5">Program / Jenis Imunisasi</p>
                                <p class="text-base font-black text-slate-800">{{ $jenisImunisasi }}</p>
                            </div>
                            <div class="bg-white/90 rounded-2xl p-4 border border-white/50 shadow-sm flex flex-col justify-center">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1.5">Nama Vaksin Diberikan</p>
                                <p class="text-base font-bold text-slate-700">{{ $vaksin }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-white/90 rounded-2xl p-4 border border-white/50 shadow-sm flex flex-col justify-center">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1.5">Dosis Ke</p>
                                    <p class="text-base font-bold text-slate-700">{{ $dosis }}</p>
                                </div>
                                <div class="bg-white/90 rounded-2xl p-4 border border-white/50 shadow-sm flex flex-col justify-center">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1.5">Nomor Batch</p>
                                    <p class="text-base font-bold text-slate-700 font-mono">{{ $batch }}</p>
                                </div>
                            </div>
                            
                            {{-- Box Petugas (Dipaksa selalu ke bawah dengan mt-auto) --}}
                            <div class="bg-emerald-600 rounded-2xl p-5 shadow-lg shadow-emerald-600/30 flex justify-between items-center mt-auto shrink-0">
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-widest text-emerald-100 mb-0.5">Petugas Pelaksana</p>
                                    <p class="text-base font-black text-white">{{ $petugas }}</p>
                                </div>
                                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-white"><i class="fa-solid fa-user-nurse"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- 3. FULL WIDTH CARD: Jejak Rekam Sistem (Menyamping) --}}
        <div class="widget-card p-6 sm:p-8 w-full border-t-4 border-t-slate-300">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                
                {{-- Header --}}
                <div class="flex items-center gap-4 border-b lg:border-b-0 lg:border-r border-slate-100 pb-4 lg:pb-0 lg:pr-8 shrink-0">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-500 border border-slate-200 flex items-center justify-center text-xl shadow-inner">
                        <i class="fa-solid fa-server"></i>
                    </div>
                    <div>
                        <h4 class="text-[11px] font-black text-slate-600 uppercase tracking-widest">Jejak Rekam Sistem</h4>
                        <p class="text-sm font-black text-slate-800 leading-tight">Log Aktivitas Data</p>
                    </div>
                </div>

                {{-- Detail Waktu --}}
                <div class="flex flex-col sm:flex-row flex-1 gap-4 lg:pl-2">
                    <div class="flex-1 flex justify-between items-center bg-white rounded-2xl px-6 py-5 border border-slate-200 shadow-sm">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-cloud-arrow-up text-slate-300 text-lg"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Waktu Input</span>
                        </div>
                        <span class="text-xs font-bold text-slate-800">{{ $tanggalInput }}</span>
                    </div>
                    
                    <div class="flex-1 flex justify-between items-center bg-white rounded-2xl px-6 py-5 border border-slate-200 shadow-sm">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-slate-300 text-lg"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Pembaruan Terakhir</span>
                        </div>
                        <span class="text-xs font-bold text-slate-800">{{ $tanggalPerbarui }}</span>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection