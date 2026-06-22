@extends('layouts.kader')

@section('title', 'Detail Agenda Posyandu')
@section('page-name', 'Detail Agenda')
@section('page-title', 'Detail Agenda Posyandu')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    // Data Utama
    $kategori = strtolower((string) ($jadwal->kategori ?? 'posyandu'));
    $status = strtolower((string) ($jadwal->status ?? 'terjadwal'));

    // Tema Kategori
    $kategoriMeta = match($kategori) {
        'balita' => ['label' => 'Balita', 'icon' => 'fa-child-reaching', 'color' => 'emerald', 'grad' => 'from-emerald-500 to-teal-600', 'text' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
        'remaja' => ['label' => 'Remaja', 'icon' => 'fa-user-graduate', 'color' => 'teal', 'grad' => 'from-teal-500 to-cyan-600', 'text' => 'text-teal-600', 'bg' => 'bg-teal-50'],
        'lansia' => ['label' => 'Lansia', 'icon' => 'fa-person-cane', 'color' => 'sky', 'grad' => 'from-sky-500 to-blue-600', 'text' => 'text-sky-600', 'bg' => 'bg-sky-50'],
        'imunisasi' => ['label' => 'Imunisasi', 'icon' => 'fa-syringe', 'color' => 'indigo', 'grad' => 'from-indigo-500 to-violet-600', 'text' => 'text-indigo-600', 'bg' => 'bg-indigo-50'],
        'pemeriksaan' => ['label' => 'Pemeriksaan', 'icon' => 'fa-stethoscope', 'color' => 'violet', 'grad' => 'from-violet-500 to-fuchsia-600', 'text' => 'text-violet-600', 'bg' => 'bg-violet-50'],
        default => ['label' => 'Posyandu Rutin', 'icon' => 'fa-calendar-check', 'color' => 'emerald', 'grad' => 'from-emerald-500 via-teal-400 to-emerald-400', 'text' => 'text-emerald-600', 'bg' => 'bg-emerald-50'],
    };

    // Tema Status
    $statusMeta = match($status) {
        'aktif' => ['label' => 'Sedang Aktif', 'dot' => 'bg-emerald-500', 'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'icon' => 'fa-satellite-dish animate-pulse'],
        'selesai' => ['label' => 'Selesai', 'dot' => 'bg-slate-400', 'badge' => 'bg-slate-100 text-slate-700 border-slate-200', 'icon' => 'fa-check-double'],
        'dibatalkan' => ['label' => 'Dibatalkan', 'dot' => 'bg-rose-500', 'badge' => 'bg-rose-100 text-rose-800 border-rose-200', 'icon' => 'fa-ban'],
        default => ['label' => 'Mendatang', 'dot' => 'bg-sky-500', 'badge' => 'bg-sky-100 text-sky-800 border-sky-200', 'icon' => 'fa-calendar-plus'],
    };
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
        padding: 1rem 0;
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
        color: #64748b;
    }
    .data-value {
        font-size: 0.875rem;
        font-weight: 900;
        color: #1e293b;
        text-align: right;
    }
</style>
@endpush

@section('content')
<div class="max-w-[1180px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6">

    {{-- 1. HERO SECTION (Identitas Agenda) --}}
    <section class="bg-gradient-to-br {{ $kategoriMeta['grad'] }} rounded-[2.5rem] p-8 md:p-12 mb-8 relative overflow-hidden shadow-2xl shadow-{{ $kategoriMeta['color'] }}-500/20 border border-white/20">
        <div class="hero-grid absolute inset-0 opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/15 blur-[80px] rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-40 h-40 bg-white/10 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start gap-6 text-center md:text-left">
            
            {{-- Icon Kategori --}}
            <div class="w-24 h-24 shrink-0 rounded-[2rem] bg-white border-4 border-white/50 {{ $kategoriMeta['text'] }} flex items-center justify-center text-4xl shadow-xl">
                <i class="fas {{ $kategoriMeta['icon'] }}"></i>
            </div>

            {{-- Info Utama --}}
            <div class="flex-1">
                <div class="inline-flex items-center justify-center md:justify-start gap-2 mb-3">
                    <span class="bg-white/20 backdrop-blur-md border border-white/30 text-white font-bold text-[10px] uppercase tracking-widest px-3 py-1 rounded-full shadow-sm flex items-center gap-1.5">
                        <i class="fas fa-tag"></i> {{ $jadwal->kategori_label ?? $kategoriMeta['label'] }}
                    </span>
                    <span class="bg-white/20 backdrop-blur-md border border-white/30 text-white text-[10px] font-bold px-3 py-1 rounded-full flex items-center gap-1.5 shadow-sm uppercase tracking-widest">
                        <i class="fas fa-users"></i> Target: {{ $jadwal->target_label ?? 'Semua Sasaran' }}
                    </span>
                </div>

                <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-2">
                    {{ $jadwal->judul }}
                </h1>
                
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-x-4 gap-y-2 text-white/90 text-sm font-semibold">
                    <span class="flex items-center gap-1"><i class="fa-solid fa-map-pin opacity-70"></i> {{ $jadwal->lokasi }}</span>
                </div>
            </div>

            {{-- Action Buttons & Status --}}
            <div class="flex flex-col items-center md:items-end gap-3 shrink-0 w-full md:w-auto mt-4 md:mt-0">
                <span class="{{ $statusMeta['badge'] }} text-[10px] uppercase tracking-widest px-4 py-2 rounded-full shadow-lg border flex items-center gap-2 mb-2 font-black">
                    <i class="fas {{ $statusMeta['icon'] }} text-sm"></i> {{ $statusMeta['label'] }}
                </span>

                <div class="flex gap-2 w-full justify-center md:justify-end">
                    <a href="{{ route('kader.jadwal.index') }}" class="w-full md:w-auto bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 text-white text-[11px] uppercase tracking-widest font-bold px-6 py-3 rounded-xl transition-all shadow-sm text-center">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
                    </a>
                </div>
            </div>

        </div>
    </section>

    {{-- 2. GRID CONTENT --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        
        {{-- KOLOM KIRI: Informasi Pelaksanaan & Deskripsi --}}
        <div class="lg:col-span-7 flex flex-col gap-6">
            
            {{-- Card Detail Pelaksanaan --}}
            <div class="pc-glass rounded-[2rem] p-6 sm:p-8 flex flex-col">
                <div class="flex items-center gap-4 mb-6 border-b border-slate-100 pb-5">
                    <div class="w-12 h-12 rounded-2xl {{ $kategoriMeta['bg'] }} {{ $kategoriMeta['text'] }} flex items-center justify-center text-xl shrink-0 shadow-inner">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Detail Pelaksanaan</h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Waktu & Tempat Acara</p>
                    </div>
                </div>

                <div class="bg-slate-50/50 rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-sm flex-1">
                    <div class="data-row pt-1">
                        <span class="data-label"><i class="fas fa-calendar-alt text-slate-400 mr-1"></i> Tanggal</span>
                        <span class="data-value">{{ $jadwal->tanggal_label }}</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label"><i class="fas fa-clock text-slate-400 mr-1"></i> Waktu</span>
                        <span class="data-value">{{ $jadwal->waktu_label }}</span>
                    </div>
                    <div class="data-row pb-1 border-b-0">
                        <span class="data-label"><i class="fas fa-map-marker-alt text-slate-400 mr-1"></i> Lokasi Posyandu</span>
                        <span class="data-value text-right">{{ $jadwal->lokasi }}</span>
                    </div>
                </div>
            </div>

            {{-- Card Deskripsi / Keterangan --}}
            <div class="pc-glass rounded-[2rem] p-6 sm:p-8 flex flex-col flex-1">
                <div class="flex items-center gap-4 mb-6 border-b border-slate-100 pb-5">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl shrink-0 shadow-inner border border-amber-100">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Keterangan Agenda</h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Informasi Tambahan</p>
                    </div>
                </div>

                <div class="bg-white/50 rounded-2xl p-5 border border-slate-100 flex-1 min-h-[6rem]">
                    <p class="text-sm font-semibold text-slate-700 leading-relaxed whitespace-pre-line">
                        {{ $jadwal->deskripsi ?: 'Tidak ada keterangan tambahan yang disertakan pada agenda ini.' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: Otoritas & Aturan --}}
        <div class="lg:col-span-5 flex flex-col gap-6">
            
            {{-- Card Otoritas Bidan --}}
            <div class="pc-glass rounded-[2rem] p-6 sm:p-8 flex flex-col">
                <div class="flex items-center gap-4 mb-6 border-b border-slate-100 pb-5">
                    <div class="w-12 h-12 rounded-2xl {{ $kategoriMeta['bg'] }} {{ $kategoriMeta['text'] }} flex items-center justify-center text-xl shrink-0 shadow-inner">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Otoritas Medis</h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Penanggung Jawab</p>
                    </div>
                </div>

                <div class="bg-slate-50/50 rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-sm flex-1">
                    <div class="data-row pt-1">
                        <span class="data-label">Pembuat Jadwal</span>
                        <span class="data-value">{{ $jadwal->nama_petugas ?? 'Bidan' }}</span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Sumber Data</span>
                        <span class="data-value">Akun Bidan</span>
                    </div>
                    <div class="data-row pb-1 border-b-0">
                        <span class="data-label">Dibuat Pada</span>
                        <span class="data-value font-semibold">{{ $jadwal->created_at ? $jadwal->created_at->translatedFormat('d M Y') : '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- Card Info Mode Baca Saja --}}
            <div class="pc-glass rounded-[2rem] p-6 sm:p-8 flex flex-col flex-1">
                <div class="flex items-center gap-4 mb-6 border-b border-slate-100 pb-5">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center text-xl shrink-0 shadow-inner border border-rose-100">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Mode Baca Saja</h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Wewenang Kader</p>
                    </div>
                </div>

                <div class="flex-1 flex flex-col justify-center">
                    <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 text-xs font-semibold text-slate-500 leading-relaxed text-center shadow-inner">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-slate-400 mx-auto mb-4 shadow-sm border border-slate-100">
                            <i class="fa-solid fa-circle-info text-lg"></i>
                        </div>
                        Sebagai Kader, Anda hanya dapat memantau dan mempersiapkan agenda ini. Pembuatan, pengubahan, atau pembatalan jadwal Posyandu adalah wewenang eksklusif dari akun Bidan.
                    </div>
                </div>
            </div>
            
        </div>
    </div>

</div>
@endsection