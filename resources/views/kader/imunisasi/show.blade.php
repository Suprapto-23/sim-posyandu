@extends('layouts.kader')

@section('title', 'Detail Log Imunisasi')
@section('page-name', 'Detail Log Imunisasi')
@section('page-title', 'Detail Log Imunisasi')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    // Data Utama Imunisasi[cite: 12]
    $kategoriTheme = $imunisasi->kategori_theme;
    $badgeTheme = $imunisasi->badge_theme;
    $nama = $imunisasi->nama_penerima;
    $nik = $imunisasi->nik_penerima;
    $initial = Str::upper(Str::substr(trim($nama), 0, 1)) ?: 'P';

    $catLabel = strtolower($kategoriTheme['label'] ?? 'balita');
    
    // Tema Gradien Berdasarkan Kategori
    $heroTheme = match ($catLabel) {
        'remaja' => [
            'gradient' => 'from-teal-500 via-cyan-500 to-blue-500',
            'shadow' => 'shadow-[0_20px_40px_-12px_rgba(6,182,212,.35)]',
            'text' => 'text-teal-500',
            'badge' => 'bg-teal-100 text-teal-700',
            'light' => 'bg-teal-50 border-teal-100',
        ],
        'lansia' => [
            'gradient' => 'from-sky-500 via-blue-500 to-indigo-500',
            'shadow' => 'shadow-[0_20px_40px_-12px_rgba(14,165,233,.35)]',
            'text' => 'text-sky-500',
            'badge' => 'bg-sky-100 text-sky-700',
            'light' => 'bg-sky-50 border-sky-100',
        ],
        default => [
            'gradient' => 'from-emerald-500 via-teal-500 to-teal-600',
            'shadow' => 'shadow-[0_20px_40px_-12px_rgba(16,185,129,.35)]',
            'text' => 'text-emerald-500',
            'badge' => 'bg-emerald-100 text-emerald-700',
            'light' => 'bg-emerald-50 border-emerald-100',
        ],
    };

    // Metrik Detail[cite: 12]
    $detailMetrics = [
        ['label' => 'Tanggal Imunisasi', 'value' => $imunisasi->tanggal_lengkap_label],
        ['label' => 'Waktu Pencatatan', 'value' => $imunisasi->jam_label],
        ['label' => 'Dosis Ke-', 'value' => $imunisasi->dosis_label],
        ['label' => 'No. Batch', 'value' => $imunisasi->batch_label],
        ['label' => 'Tanggal Kedaluwarsa', 'value' => $imunisasi->expiry_label],
        ['label' => 'Instansi Penyelenggara', 'value' => $imunisasi->penyelenggara_label],
    ];
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
        animation: popIn .48s cubic-bezier(.16, 1, .3, 1) forwards;
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

    .pc-glass {
        background: rgba(255, 255, 255, .86);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .05);
        border: 1px solid rgba(255, 255, 255, 0.9);
    }

    .data-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.875rem 0;
        border-bottom: 1px dashed rgba(203, 213, 225, 0.6);
    }
    .data-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .data-label {
        font-size: 0.65rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #94a3b8;
    }
    .data-value {
        font-size: 0.875rem;
        font-weight: 900;
        color: #1e293b;
        text-align: right;
    }

    /* Modal Styling Nexus Premium */
    .pc-modal-backdrop {
        position: fixed !important;
        inset: 0 !important;
        z-index: 2147483647 !important;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 1rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .pc-modal-backdrop.is-open { display: flex !important; opacity: 1; }
    
    .pc-modal-card {
        width: 100%;
        max-width: 400px;
        background: white;
        border-radius: 2rem;
        padding: 2.5rem 2rem;
        transform: scale(0.95) translateY(10px);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    .pc-modal-backdrop.is-open .pc-modal-card {
        transform: scale(1) translateY(0);
        opacity: 1;
    }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto animate-pop-in pb-12 px-4 sm:px-6 lg:px-8 mt-6">

    {{-- 1. HERO SECTION (Identitas Penerima)[cite: 12] --}}
    <section class="bg-gradient-to-br {{ $heroTheme['gradient'] }} rounded-[2.5rem] p-8 md:p-12 mb-8 relative overflow-hidden {{ $heroTheme['shadow'] }} border border-white/20">
        <div class="hero-grid absolute inset-0 opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/15 blur-[80px] rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-40 h-40 bg-white/10 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start gap-6 text-center md:text-left">
            
            {{-- Avatar --}}
            <div class="w-28 h-28 shrink-0 rounded-[2rem] bg-white border-4 border-white/50 {{ $heroTheme['text'] }} flex items-center justify-center font-black text-5xl shadow-xl">
                {{ $initial }}
            </div>

            {{-- Info Utama --}}
            <div class="flex-1">
                <div class="inline-flex items-center justify-center md:justify-start gap-2 mb-3">
                    <span class="bg-white/20 backdrop-blur-md border border-white/30 text-white font-bold text-[10px] uppercase tracking-widest px-3 py-1 rounded-full shadow-sm flex items-center gap-1.5">
                        <i class="fas {{ $kategoriTheme['icon'] }}"></i> {{ $kategoriTheme['label'] }}
                    </span>
                    <span class="bg-white/20 backdrop-blur-md border border-white/30 text-white text-[10px] font-bold px-3 py-1 rounded-full flex items-center gap-1.5 shadow-sm uppercase tracking-widest">
                        <i class="fas fa-syringe"></i> Data Arsip
                    </span>
                </div>

                <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-2">
                    {{ $nama }}
                </h1>
                
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-x-4 gap-y-2 text-white/90 text-sm font-semibold">
                    <span class="flex items-center gap-1"><i class="fa-solid fa-id-card opacity-70"></i> NIK: {{ $nik }}</span>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-col items-center md:items-end gap-3 shrink-0 w-full md:w-auto mt-4 md:mt-0">
                <span class="bg-slate-900/40 backdrop-blur-md text-white text-[10px] uppercase tracking-widest px-4 py-2 rounded-2xl shadow-lg border border-white/20 flex items-center gap-2 mb-2">
                    <i class="fas fa-lock text-sm"></i> Mode Baca Saja
                </span>

                <div class="flex gap-2 w-full justify-center md:justify-end">
                    <a href="{{ route('kader.imunisasi.index') }}" class="w-full md:w-auto bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 text-white text-[11px] uppercase tracking-widest font-bold px-6 py-3 rounded-xl transition-all shadow-sm text-center">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
                    </a>
                </div>
            </div>

        </div>
    </section>

    {{-- System Alerts --}}
    @if(session('success') || session('error'))
        <div class="mb-8">
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
            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 rounded-3xl p-5 flex items-center gap-4 shadow-sm relative overflow-hidden">
                    <div class="w-12 h-12 rounded-2xl bg-white text-rose-500 flex items-center justify-center text-xl shrink-0 shadow-sm border border-rose-100">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-rose-800">Peringatan!</h4>
                        <p class="text-xs font-semibold text-rose-600 mt-0.5">{{ session('error') }}</p>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- 2. GRID CONTENT[cite: 12] --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        
        {{-- KOLOM KIRI: Informasi Vaksin & Detail Imunisasi --}}
        <div class="lg:col-span-7 flex flex-col gap-6">
            
            {{-- Card Vaksin Utama --}}
            <div class="pc-glass rounded-[2rem] p-6 sm:p-8 flex flex-col">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-2xl {{ $badgeTheme['soft'] ?? 'bg-indigo-50 border-indigo-100' }} {{ $badgeTheme['text'] ?? 'text-indigo-600' }} flex items-center justify-center text-xl shrink-0 shadow-inner">
                        <i class="fas {{ $badgeTheme['icon'] ?? 'fa-syringe' }}"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Informasi Vaksin</h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Vaksin yang diberikan</p>
                    </div>
                </div>

                <div class="bg-white/50 rounded-2xl p-5 border border-slate-100 flex-1 flex flex-col justify-center text-center sm:text-left">
                    <p class="text-[11px] font-black uppercase tracking-[.16em] text-slate-500 mb-1">Nama Vaksin</p>
                    <h3 class="text-2xl font-black text-slate-900 mb-2">{{ $imunisasi->vaksin_label }}</h3>
                    <p class="text-sm font-bold text-slate-500">{{ $imunisasi->jenis_label }}</p>
                </div>
            </div>

            {{-- Card Detail Pelaksanaan --}}
            <div class="pc-glass rounded-[2rem] p-6 sm:p-8 flex flex-col flex-1">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-500 flex items-center justify-center text-xl shrink-0 shadow-inner border border-slate-100">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Detail Pelaksanaan</h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Waktu & Tempat</p>
                    </div>
                </div>

                <div class="bg-white/50 rounded-2xl p-4 border border-slate-100 flex-1 flex flex-col justify-center">
                    @foreach($detailMetrics as $metric)
                        <div class="data-row">
                            <span class="data-label">{{ $metric['label'] }}</span>
                            <span class="data-value">{{ $metric['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: Otoritas Medis & Catatan --}}
        <div class="lg:col-span-5 flex flex-col gap-6">
            
            {{-- Card Otoritas Bidan (Highlight Card) --}}
            <div class="rounded-[2rem] p-6 sm:p-8 border {{ $heroTheme['light'] }} shadow-sm relative overflow-hidden flex-none">
                {{-- Dekorasi Latar --}}
                <i class="fas fa-user-nurse absolute -right-6 -top-6 text-9xl opacity-5"></i>
                
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-white {{ $heroTheme['text'] }} flex items-center justify-center text-lg shadow-sm border {{ $heroTheme['light'] }}">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <div>
                            <h4 class="text-[11px] font-black {{ $heroTheme['text'] }} uppercase tracking-widest">Otoritas Medis</h4>
                            <p class="text-sm font-black text-slate-800 leading-tight">Penanggung Jawab</p>
                        </div>
                    </div>

                    <div class="bg-white/70 rounded-2xl p-5 border border-white/50 space-y-4">
                        <div class="flex justify-between items-center border-b border-white pb-3">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama Petugas</span>
                            <span class="text-xs font-black text-slate-800">{{ $imunisasi->nama_petugas }}</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-white pb-3">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sumber Data</span>
                            <span class="text-xs font-black text-slate-800">Akun Bidan</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status Data</span>
                            <span class="text-[10px] font-bold text-slate-500 bg-white px-2 py-1 rounded-md border border-slate-100 shadow-sm"><i class="fa-solid fa-lock mr-1"></i> Terkunci (Read-Only)</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Catatan & Keterangan --}}
            <div class="pc-glass rounded-[2rem] p-6 sm:p-8 flex flex-col flex-1">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl shrink-0 shadow-inner border border-amber-100">
                        <i class="fas fa-comment-medical"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Catatan Medis</h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Keterangan Tambahan</p>
                    </div>
                </div>

                <div class="flex-1 flex flex-col gap-4">
                    <div class="flex-1 flex flex-col">
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Catatan Pelaksanaan Imunisasi</span>
                        <div class="bg-white/50 rounded-2xl p-4 border border-slate-100 text-xs font-semibold text-slate-700 leading-relaxed flex-1">
                            {{ $imunisasi->catatan_label ?: '-' }}
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 text-[11px] font-semibold text-slate-500 leading-relaxed text-center">
                        <i class="fa-solid fa-circle-info mr-1"></i> Kader hanya dapat melihat data pemantauan imunisasi. Edit atau hapus data harus melalui wewenang akun Bidan.
                    </div>
                </div>
            </div>
            
        </div>
    </div>

</div>
@endsection