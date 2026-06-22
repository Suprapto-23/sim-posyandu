@extends('layouts.kader')

@section('title', 'Detail Pengukuran Fisik')
@section('page-name', 'Detail Pengukuran Fisik')
@section('page-title', 'Detail Pengukuran Fisik')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    // 1. Inisialisasi Data Pasien & Kunjungan
    $pasien = optional($pemeriksaan->kunjungan)->pasien;
    $kategori = strtolower((string) ($pemeriksaan->kategori_pasien ?? 'balita'));

    if (! in_array($kategori, ['balita', 'remaja', 'lansia'], true)) {
        $kategori = 'balita';
    }

    $namaPasien = $pasien->nama_lengkap ?? $pasien->nama ?? $pemeriksaan->nama_pasien ?? 'Tanpa Nama';
    $nikPasien = $pasien->nik ?? $pemeriksaan->nik_pasien ?? '-';
    $usia = '-';
    if(isset($pasien->tanggal_lahir)) {
        $usia = Carbon::parse($pasien->tanggal_lahir)->diff(now())->format('%y Thn %m Bln');
    }
    
    $initial = Str::upper(Str::substr(trim($namaPasien), 0, 1)) ?: 'P';

    $tanggal = $pemeriksaan->tanggal_periksa
        ? Carbon::parse($pemeriksaan->tanggal_periksa)->translatedFormat('d F Y')
        : '-';

    // 2. Status Verifikasi
    $statusRaw = strtolower((string) ($pemeriksaan->status_verifikasi ?? 'pending'));
    $statusType = 'pending';

    if (in_array($statusRaw, ['verified', 'terverifikasi', 'valid', 'approved', 'disetujui', 'selesai'], true)) {
        $statusType = 'verified';
    } elseif (in_array($statusRaw, ['ditolak', 'revisi', 'perlu_revisi', 'needs_revision', 'rejected', 'dikembalikan'], true)) {
        $statusType = 'revisi';
    }

    $statusMeta = match ($statusType) {
        'verified' => [
            'label' => 'Tervalidasi',
            'desc' => 'Sudah direview Bidan',
            'icon' => 'fa-check-circle',
            'badge' => 'bg-emerald-500 text-white shadow-emerald-500/30',
            'text' => 'text-emerald-600',
            'bg_light' => 'bg-emerald-50 border-emerald-200',
            'editable' => false,
        ],
        'revisi' => [
            'label' => 'Perlu Revisi',
            'desc' => 'Dikembalikan Bidan',
            'icon' => 'fa-rotate-left',
            'badge' => 'bg-rose-500 text-white shadow-rose-500/30',
            'text' => 'text-rose-600',
            'bg_light' => 'bg-rose-50 border-rose-200',
            'editable' => true,
        ],
        default => [
            'label' => 'Menunggu Review',
            'desc' => 'Belum direview',
            'icon' => 'fa-clock',
            'badge' => 'bg-amber-500 text-white shadow-amber-500/30',
            'text' => 'text-amber-600',
            'bg_light' => 'bg-amber-50 border-amber-200',
            'editable' => true,
        ],
    };

    // 3. Tema Kategori
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
            'icon' => 'fa-child-reaching',
            'gradient' => 'from-emerald-500 via-teal-500 to-teal-600',
            'shadow' => 'shadow-[0_20px_40px_-12px_rgba(16,185,129,.35)]',
            'text' => 'text-emerald-500',
            'badge' => 'bg-emerald-100 text-emerald-700',
        ],
    };

    // 4. Data Metrik Penunjang & Review
    $metric = function ($value, $unit = '') {
        return ($value === null || $value === '') ? '-' : trim($value . ' ' . $unit);
    };

    $catatanBidan = $pemeriksaan->catatan_validasi ?? $pemeriksaan->catatan_bidan ?? $pemeriksaan->catatan_review ?? null;
    $verifikator = optional($pemeriksaan->verifikator)->name ?? optional($pemeriksaan->verifikator)->nama ?? null;
    $tanggalValidasi = $pemeriksaan->verified_at ?? $pemeriksaan->tanggal_validasi ?? $pemeriksaan->reviewed_at ?? null;
@endphp

@push('styles')
<style>
    body {
        background-color: #f8fafc;
    }

    html.pc-modal-open,
    body.pc-modal-open {
        overflow: hidden !important;
    }

    .pc-page {
        background:
            radial-gradient(circle at 8% 5%, rgba(16, 185, 129, .14), transparent 28%),
            radial-gradient(circle at 95% 8%, rgba(14, 165, 233, .13), transparent 26%),
            radial-gradient(circle at 50% 96%, rgba(251, 191, 36, .10), transparent 30%),
            linear-gradient(135deg, #f3fff9 0%, #eef9ff 48%, #f8fafc 100%);
    }

    .pc-grid {
        background-image:
            linear-gradient(rgba(15, 23, 42, .035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, .035) 1px, transparent 1px);
        background-size: 30px 30px;
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
    }
    .pc-modal-backdrop.is-open { display: flex !important; }
    
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

    .animate-pop-in {
        animation: popIn .5s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }
    @keyframes popIn {
        from { opacity: 0; transform: scale(.97) translateY(15px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="pc-page relative min-h-screen overflow-hidden px-4 py-5 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute inset-0 pc-grid opacity-70"></div>

    <div class="relative z-10 mx-auto max-w-[1200px] animate-pop-in pb-20">

        {{-- 1. HERO SECTION (Identitas Pasien & Action Buttons) --}}
        <section class="bg-gradient-to-br {{ $kategoriMeta['gradient'] }} rounded-[2.5rem] p-8 md:p-12 mb-8 relative overflow-hidden {{ $kategoriMeta['shadow'] }} border border-white/20">
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/15 blur-[80px] rounded-full pointer-events-none"></div>
            <div class="absolute -bottom-16 -left-16 w-40 h-40 bg-white/10 blur-[60px] rounded-full pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start gap-6 text-center md:text-left">
                
                {{-- Avatar --}}
                <div class="w-28 h-28 shrink-0 rounded-[2rem] bg-white border-4 border-white/50 {{ $kategoriMeta['text'] }} flex items-center justify-center font-black text-5xl shadow-xl">
                    {{ $initial }}
                </div>

                {{-- Info Utama --}}
                <div class="flex-1">
                    <div class="inline-flex items-center gap-2 mb-3">
                        <span class="bg-white/20 backdrop-blur-md border border-white/30 text-white font-bold text-[10px] uppercase tracking-widest px-3 py-1 rounded-full shadow-sm flex items-center gap-1.5">
                            <i class="fas {{ $kategoriMeta['icon'] }}"></i> {{ $kategoriMeta['label'] }}
                        </span>
                        <span class="bg-white/20 backdrop-blur-md border border-white/30 text-white text-[10px] font-bold px-3 py-1 rounded-full flex items-center gap-1.5 shadow-sm uppercase tracking-widest">
                            <i class="fas fa-calendar-day"></i> {{ $tanggal }}
                        </span>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-2">
                        {{ $namaPasien }}
                    </h1>
                    
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-x-4 gap-y-2 text-white/90 text-sm font-semibold">
                        <span class="flex items-center gap-1"><i class="fa-solid fa-id-card opacity-70"></i> NIK: {{ $nikPasien }}</span>
                        <span class="flex items-center gap-1"><i class="fa-solid fa-cake-candles opacity-70"></i> {{ $usia }}</span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col items-center md:items-end gap-3 shrink-0 w-full md:w-auto mt-4 md:mt-0">
                    <span class="{{ $statusMeta['badge'] }} text-[10px] uppercase tracking-widest px-4 py-2 rounded-2xl shadow-lg border border-white/20 flex items-center gap-2 mb-2">
                        <i class="fas {{ $statusMeta['icon'] }} text-sm"></i> {{ $statusMeta['label'] }}
                    </span>

                    <div class="flex flex-wrap justify-center md:justify-end gap-2 w-full">
                        <a href="{{ route('kader.pemeriksaan.index') }}" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 text-white text-[11px] uppercase tracking-widest font-bold px-4 py-2.5 rounded-xl transition-all shadow-sm" title="Kembali">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        @if($statusMeta['editable'])
                            <a href="{{ route('kader.pemeriksaan.edit', $pemeriksaan->id) }}" class="bg-white/90 hover:bg-white text-slate-800 text-[11px] uppercase tracking-widest font-bold px-4 py-2.5 rounded-xl transition-all shadow-sm hover:-translate-y-0.5">
                                <i class="fas fa-pen mr-1"></i> Edit
                            </a>
                            <button type="button" id="openDeleteModal" class="bg-rose-500/90 hover:bg-rose-600/90 backdrop-blur-md text-white border border-rose-400/50 text-[11px] uppercase tracking-widest font-bold px-4 py-2.5 rounded-xl transition-all shadow-sm hover:-translate-y-0.5" title="Hapus Data">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    </div>
                </div>

            </div>
        </section>

        {{-- 2. GRID CONTENT --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            
            {{-- KOLOM KIRI: Data Klinis (Antropometri & Lab) --}}
            <div class="lg:col-span-7 flex flex-col gap-6">
                
                {{-- Card Antropometri --}}
                <div class="pc-glass rounded-[2rem] p-6 sm:p-8 flex flex-col {{ !in_array($kategori, ['remaja', 'lansia']) ? 'flex-1' : '' }}">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 rounded-2xl {{ $kategoriMeta['badge'] }} flex items-center justify-center text-xl shrink-0 shadow-inner">
                            <i class="fas fa-ruler-combined"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Antropometri & Fisik</h4>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Hasil Pengukuran Dasar</p>
                        </div>
                    </div>

                    <div class="bg-white/50 rounded-2xl p-4 border border-slate-100 flex-1 flex flex-col justify-center">
                        <div class="data-row">
                            <span class="data-label">Berat Badan</span>
                            <span class="data-value">{{ $metric($pemeriksaan->berat_badan, 'Kg') }}</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">Tinggi / Panjang</span>
                            <span class="data-value">{{ $metric($pemeriksaan->tinggi_badan, 'Cm') }}</span>
                        </div>

                        {{-- Tampilkan IMT Khusus Remaja & Lansia --}}
                        @if($kategori !== 'balita')
                            <div class="data-row">
                                <span class="data-label text-teal-600">IMT Estimasi</span>
                                <span class="data-value {{ $pemeriksaan->imt ? 'text-teal-700 bg-teal-100/50 px-3 py-1 rounded-full' : 'text-slate-700' }}">
                                    {{ $metric($pemeriksaan->imt, '') }}
                                </span>
                            </div>
                        @endif

                        {{-- Tampilkan LK Khusus Balita --}}
                        @if($kategori === 'balita')
                            <div class="data-row border-0 pb-0">
                                <span class="data-label">Lingkar Kepala</span>
                                <span class="data-value">{{ $metric($pemeriksaan->lingkar_kepala, 'Cm') }}</span>
                            </div>
                        @endif

                        {{-- Tampilkan LiLA Khusus Balita & Remaja --}}
                        @if(in_array($kategori, ['balita', 'remaja']))
                            <div class="data-row {{ $kategori === 'balita' ? 'border-0 pb-0' : '' }}">
                                <span class="data-label">Lingkar Lengan (LiLA)</span>
                                <span class="data-value">{{ $metric($pemeriksaan->lingkar_lengan, 'Cm') }}</span>
                            </div>
                        @endif

                        {{-- Tampilkan LP & Tensi Khusus Remaja & Lansia --}}
                        @if(in_array($kategori, ['remaja', 'lansia']))
                            <div class="data-row">
                                <span class="data-label">Lingkar Perut</span>
                                <span class="data-value">{{ $metric($pemeriksaan->lingkar_perut, 'Cm') }}</span>
                            </div>
                            <div class="data-row {{ $kategori === 'remaja' ? 'border-0 pb-0' : '' }}">
                                <span class="data-label text-rose-500">Tekanan Darah</span>
                                <span class="data-value text-rose-600 bg-rose-50 px-3 py-1 rounded-full">{{ $metric($pemeriksaan->tekanan_darah, '') }}</span>
                            </div>
                        @endif

                        {{-- Tampilkan Kemandirian Khusus Lansia --}}
                        @if($kategori === 'lansia')
                            <div class="data-row border-0 pb-0">
                                <span class="data-label">Status Kemandirian</span>
                                <span class="data-value uppercase text-sky-600 bg-sky-50 px-3 py-1 rounded-full text-[11px]">{{ $pemeriksaan->tingkat_kemandirian ? str_replace('_', ' ', $pemeriksaan->tingkat_kemandirian) : '-' }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Card Skrining Lab (Hanya untuk Remaja & Lansia) --}}
                @if(in_array($kategori, ['remaja', 'lansia']))
                    <div class="pc-glass rounded-[2rem] p-6 sm:p-8 flex flex-col flex-1">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl shrink-0 shadow-inner">
                                <i class="fas fa-vial-circle-check"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Penunjang PTM</h4>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Hasil Laboratorium</p>
                            </div>
                        </div>

                        <div class="bg-white/50 rounded-2xl p-4 border border-slate-100 flex-1 flex flex-col justify-center">
                            <div class="data-row">
                                <span class="data-label">Gula Darah</span>
                                <span class="data-value">{{ $metric($pemeriksaan->gula_darah, 'mg/dL') }}</span>
                            </div>
                            <div class="data-row">
                                <span class="data-label">Kolesterol</span>
                                <span class="data-value">{{ $metric($pemeriksaan->kolesterol, 'mg/dL') }}</span>
                            </div>
                            <div class="data-row {{ $kategori === 'lansia' ? 'border-0 pb-0' : '' }}">
                                <span class="data-label">Asam Urat</span>
                                <span class="data-value">{{ $metric($pemeriksaan->asam_urat, 'mg/dL') }}</span>
                            </div>
                            @if($kategori === 'remaja')
                                <div class="data-row border-0 pb-0">
                                    <span class="data-label">Hemoglobin (Hb)</span>
                                    <span class="data-value">{{ $metric($pemeriksaan->hemoglobin, 'g/dL') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- KOLOM KANAN: Validasi & Anamnesis --}}
            <div class="lg:col-span-5 flex flex-col gap-6">
                
                {{-- Card Validasi Bidan (Highlight Card) --}}
                <div class="rounded-[2rem] p-6 sm:p-8 border {{ $statusMeta['bg_light'] }} shadow-sm relative overflow-hidden flex-none">
                    {{-- Dekorasi Latar --}}
                    <i class="fas {{ $statusMeta['icon'] }} absolute -right-6 -top-6 text-9xl opacity-5"></i>
                    
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-white {{ $statusMeta['text'] }} flex items-center justify-center text-lg shadow-sm">
                                <i class="fas fa-user-nurse"></i>
                            </div>
                            <div>
                                <h4 class="text-[11px] font-black {{ $statusMeta['text'] }} uppercase tracking-widest">Validasi Bidan</h4>
                                <p class="text-sm font-black text-slate-800 leading-tight">{{ $statusMeta['label'] }}</p>
                            </div>
                        </div>

                        <div class="bg-white/60 rounded-2xl p-5 border border-white/50">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-xs font-black text-slate-800">{{ $verifikator ?: 'Belum Direview' }}</span>
                                @if($tanggalValidasi)
                                    <span class="text-[10px] font-bold text-slate-500 bg-white px-2 py-1 rounded-md">{{ Carbon::parse($tanggalValidasi)->translatedFormat('d M Y') }}</span>
                                @endif
                            </div>
                            
                            <p class="text-xs font-semibold text-slate-600 leading-relaxed italic relative z-10">
                                @if($catatanBidan)
                                    "{{ $catatanBidan }}"
                                @else
                                    <span class="opacity-60">Tidak ada catatan klinis atau feedback dari Bidan terkait hasil pengukuran ini.</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Card Keluhan & Catatan Kader --}}
                <div class="pc-glass rounded-[2rem] p-6 sm:p-8 flex flex-col flex-1">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl shrink-0 shadow-inner">
                            <i class="fas fa-clipboard-question"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Anamnesis</h4>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Keluhan & Catatan</p>
                        </div>
                    </div>

                    <div class="flex-1 flex flex-col gap-4">
                        <div class="flex-1 flex flex-col">
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Riwayat Keluhan Saat Datang</span>
                            <div class="bg-white/50 rounded-2xl p-4 border border-slate-100 text-xs font-semibold text-slate-700 leading-relaxed flex-1">
                                {{ $pemeriksaan->keluhan ?: '-' }}
                            </div>
                        </div>

                        <div class="flex-1 flex flex-col">
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Catatan Tambahan Kader</span>
                            <div class="bg-white/50 rounded-2xl p-4 border border-slate-100 text-xs font-semibold text-slate-700 leading-relaxed flex-1">
                                {{ $pemeriksaan->catatan_kader ?: '-' }}
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

        </div>
    </div>

    {{-- Delete Modal (Nexus Premium) --}}
    @if($statusMeta['editable'])
        <div id="pcDeleteModal" class="pc-modal-backdrop" aria-hidden="true">
            <div class="pc-modal-card p-6 text-center">
                <div class="w-16 h-16 rounded-full bg-rose-50 border border-rose-100 flex items-center justify-center mx-auto mb-4 text-rose-500 shadow-inner">
                    <i class="fas fa-triangle-exclamation text-2xl"></i>
                </div>
                
                <h3 class="text-xl font-black text-slate-800 mb-2">Hapus Data Pengukuran?</h3>
                <p class="text-sm font-medium text-slate-500 mb-8 leading-relaxed px-2">
                    Pengukuran fisik ini belum divalidasi dan dapat dihapus. Tindakan ini tidak dapat dibatalkan.
                </p>
                
                <div class="flex gap-3">
                    <button type="button" id="pcCancelDelete" class="w-full flex-1 rounded-xl border border-slate-200 bg-white text-slate-700 px-4 py-3.5 text-sm font-bold shadow-sm hover:bg-slate-50 transition-all">
                        Batal
                    </button>
                    <form action="{{ route('kader.pemeriksaan.destroy', $pemeriksaan->id) }}" method="POST" data-delete-form class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" id="pcConfirmDelete" class="w-full rounded-xl bg-gradient-to-r from-rose-500 to-rose-600 text-white px-4 py-3.5 text-sm font-bold shadow-md hover:from-rose-600 hover:to-rose-700 transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';
    
    // Modal Delete Logic
    const openBtn = document.getElementById('openDeleteModal');
    const modal = document.getElementById('pcDeleteModal');
    const cancelBtn = document.getElementById('pcCancelDelete');
    const confirmBtn = document.getElementById('pcConfirmDelete');

    function lockBody() {
        document.documentElement.classList.add('pc-modal-open');
        document.body.classList.add('pc-modal-open');
    }

    function unlockBody() {
        document.documentElement.classList.remove('pc-modal-open');
        document.body.classList.remove('pc-modal-open');
    }

    function openModal() {
        if (!modal) return;
        lockBody();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        unlockBody();
    }

    if (openBtn) openBtn.addEventListener('click', openModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });

    document.addEventListener('submit', function (event) {
        const form = event.target.closest('[data-delete-form]');
        if (form && confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';
        }
    }, true);
});
</script>
@endpush