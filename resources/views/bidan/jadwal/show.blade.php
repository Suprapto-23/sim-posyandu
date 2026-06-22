@extends('layouts.bidan')

@section('title', 'Detail Jadwal Posyandu')
@section('page-name', 'Detail Jadwal')
@section('page-title', 'Detail Jadwal Posyandu')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    $kategoriOptions = $kategoriOptions ?? [
        'posyandu' => ['label' => 'Posyandu Rutin', 'desc' => 'Agenda pelayanan Posyandu umum, absensi, dan pengukuran dasar.', 'icon' => 'fa-house-medical'],
        'imunisasi' => ['label' => 'Imunisasi Balita', 'desc' => 'Agenda pelayanan imunisasi untuk sasaran Balita.', 'icon' => 'fa-syringe'],
        'pemeriksaan' => ['label' => 'Pemeriksaan Klinis', 'desc' => 'Agenda pemeriksaan lanjutan oleh Bidan.', 'icon' => 'fa-stethoscope'],
        'lainnya' => ['label' => 'Kegiatan Lainnya', 'desc' => 'Agenda tambahan Posyandu di luar layanan utama.', 'icon' => 'fa-calendar-plus'],
    ];

    $targetOptions = $targetOptions ?? [
        'semua'  => ['label' => 'Semua Sasaran', 'desc' => 'Balita, Remaja, Lansia, dan warga.', 'icon' => 'fa-users'],
        'balita' => ['label' => 'Balita', 'desc' => 'Khusus sasaran Balita.', 'icon' => 'fa-baby'],
        'remaja' => ['label' => 'Remaja', 'desc' => 'Khusus sasaran Remaja.', 'icon' => 'fa-user-graduate'],
        'lansia' => ['label' => 'Lansia', 'desc' => 'Khusus sasaran Lansia.', 'icon' => 'fa-person-cane'],
    ];

    $statusOptions = $statusOptions ?? [
        'aktif'      => ['label' => 'Aktif', 'desc' => 'Jadwal masih berlaku.', 'icon' => 'fa-circle-check'],
        'selesai'    => ['label' => 'Selesai', 'desc' => 'Jadwal sudah dilaksanakan.', 'icon' => 'fa-flag-checkered'],
        'dibatalkan' => ['label' => 'Dibatalkan', 'desc' => 'Jadwal dibatalkan atau ditunda.', 'icon' => 'fa-circle-xmark'],
    ];

    $judul = $jadwal->judul ?? 'Judul Jadwal Tidak Terdata';
    $kategori = strtolower((string) ($jadwal->kategori ?? 'posyandu'));
    $target = strtolower((string) ($jadwal->target_peserta ?? 'semua'));
    $status = strtolower((string) ($jadwal->status ?? 'aktif'));
    $lokasi = $jadwal->lokasi ?? '-';
    $deskripsi = trim((string) ($jadwal->deskripsi ?? ''));

    $kategoriMeta = $kategoriOptions[$kategori] ?? $kategoriOptions['posyandu'];
    $targetMeta = $targetOptions[$target] ?? $targetOptions['semua'];
    $statusMeta = $statusOptions[$status] ?? $statusOptions['aktif'];

    $formatTanggal = fn($date, $withDay = false) => $date ? Carbon::parse($date)->translatedFormat($withDay ? 'l, d F Y' : 'd F Y') : '-';
    $formatTanggalPendek = fn($date) => $date ? Carbon::parse($date)->translatedFormat('d M Y') : '-';
    $formatBulanPendek = fn($date) => $date ? Carbon::parse($date)->translatedFormat('M') : '-';
    $formatTanggalAngka = fn($date) => $date ? Carbon::parse($date)->format('d') : '-';
    $formatWaktu = fn($mulai, $selesai) => ($mulai && $selesai) ? Carbon::parse($mulai)->format('H:i') . ' - ' . Carbon::parse($selesai)->format('H:i') . ' WIB' : '-';
    $formatMetaDate = fn($date) => $date ? Carbon::parse($date)->translatedFormat('d M Y, H:i') : '-';

    $isToday = fn($date) => $date ? Carbon::parse($date)->isToday() : false;
    $isPastDate = fn($date) => $date ? Carbon::parse($date)->startOfDay()->lt(now()->startOfDay()) : false;

    $canModifyFallback = function ($jadwal) {
        if (($jadwal->status ?? 'aktif') !== 'aktif') return false;
        if (empty($jadwal->tanggal)) return false;
        try {
            $startDateTime = Carbon::parse(Carbon::parse($jadwal->tanggal)->format('Y-m-d') . ' ' . ($jadwal->waktu_mulai ?? '00:00:00'));
            return now()->lt($startDateTime);
        } catch (\Throwable $e) { return false; }
    };

    $canEdit = isset($canEdit) ? (bool) $canEdit : $canModifyFallback($jadwal);
    $canDelete = isset($canDelete) ? (bool) $canDelete : $canEdit;

    $tanggalLabel = $formatTanggal($jadwal->tanggal ?? null, true);
    $tanggalPendek = $formatTanggalPendek($jadwal->tanggal ?? null);
    $bulanPendek = $formatBulanPendek($jadwal->tanggal ?? null);
    $tanggalAngka = $formatTanggalAngka($jadwal->tanggal ?? null);
    $waktuLabel = $formatWaktu($jadwal->waktu_mulai ?? null, $jadwal->waktu_selesai ?? null);

    $today = $isToday($jadwal->tanggal ?? null);
    $past = $isPastDate($jadwal->tanggal ?? null);

    $statusTheme = function ($value) {
        return match (strtolower((string) $value)) {
            'aktif' => ['label' => 'Aktif', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'panel' => 'border-emerald-100 bg-emerald-50/70', 'iconBox' => 'bg-white text-emerald-600 border border-emerald-100', 'dot' => 'bg-emerald-500', 'icon' => 'fa-circle-check'],
            'selesai' => ['label' => 'Selesai', 'badge' => 'bg-slate-50 text-slate-600 border-slate-200', 'panel' => 'border-slate-100 bg-slate-50/80', 'iconBox' => 'bg-white text-slate-500 border border-slate-100', 'dot' => 'bg-slate-400', 'icon' => 'fa-flag-checkered'],
            'dibatalkan' => ['label' => 'Dibatalkan', 'badge' => 'bg-rose-50 text-rose-700 border-rose-200', 'panel' => 'border-rose-100 bg-rose-50/70', 'iconBox' => 'bg-white text-rose-600 border border-rose-100', 'dot' => 'bg-rose-500', 'icon' => 'fa-circle-xmark'],
            default => ['label' => ucfirst((string) $value), 'badge' => 'bg-slate-50 text-slate-600 border-slate-200', 'panel' => 'border-slate-100 bg-slate-50/80', 'iconBox' => 'bg-white text-slate-500 border border-slate-100', 'dot' => 'bg-slate-400', 'icon' => 'fa-circle-info'],
        };
    };

    $kategoriTheme = function ($value) use ($kategoriMeta) {
        $icon = $kategoriMeta['icon'];
        return match (strtolower((string) $value)) {
            'imunisasi' => ['badge' => 'bg-cyan-50 text-cyan-700 border-cyan-200', 'panel' => 'border-cyan-100 bg-cyan-50/70', 'iconBox' => 'bg-white text-cyan-600 border border-cyan-100', 'gradient' => 'from-cyan-500 to-sky-500', 'icon' => $icon],
            'pemeriksaan' => ['badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'panel' => 'border-emerald-100 bg-emerald-50/70', 'iconBox' => 'bg-white text-emerald-600 border border-emerald-100', 'gradient' => 'from-emerald-500 to-teal-500', 'icon' => $icon],
            'lainnya' => ['badge' => 'bg-amber-50 text-amber-700 border-amber-200', 'panel' => 'border-amber-100 bg-amber-50/70', 'iconBox' => 'bg-white text-amber-600 border border-amber-100', 'gradient' => 'from-amber-500 to-orange-500', 'icon' => $icon],
            default => ['badge' => 'bg-sky-50 text-sky-700 border-sky-200', 'panel' => 'border-sky-100 bg-sky-50/70', 'iconBox' => 'bg-white text-sky-600 border border-sky-100', 'gradient' => 'from-sky-500 to-blue-500', 'icon' => $icon],
        };
    };

    $targetTheme = function ($value) use ($targetMeta) {
        $icon = $targetMeta['icon'];
        return match (strtolower((string) $value)) {
            'balita' => ['badge' => 'bg-sky-50 text-sky-700 border-sky-200', 'panel' => 'border-sky-100 bg-sky-50/70', 'iconBox' => 'bg-white text-sky-600 border border-sky-100', 'icon' => $icon],
            'remaja' => ['badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'panel' => 'border-indigo-100 bg-indigo-50/70', 'iconBox' => 'bg-white text-indigo-600 border border-indigo-100', 'icon' => $icon],
            'lansia' => ['badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'panel' => 'border-emerald-100 bg-emerald-50/70', 'iconBox' => 'bg-white text-emerald-600 border border-emerald-100', 'icon' => $icon],
            default => ['badge' => 'bg-slate-50 text-slate-700 border-slate-200', 'panel' => 'border-slate-100 bg-slate-50/80', 'iconBox' => 'bg-white text-slate-600 border border-slate-100', 'icon' => $icon],
        };
    };

    $lockState = function () use ($canEdit, $status) {
        if ($canEdit) return ['label' => 'Bisa Diedit', 'desc' => 'Masih dapat diubah.', 'icon' => 'fa-pen', 'panel' => 'border-emerald-100 bg-emerald-50/70', 'iconBox' => 'bg-white text-emerald-600 border border-emerald-100'];
        if ($status === 'dibatalkan') return ['label' => 'Dibatalkan', 'desc' => 'Jadwal tidak berlaku.', 'icon' => 'fa-ban', 'panel' => 'border-rose-100 bg-rose-50/70', 'iconBox' => 'bg-white text-rose-600 border border-rose-100'];
        if ($status === 'selesai') return ['label' => 'Terkunci', 'desc' => 'Sudah jadi arsip.', 'icon' => 'fa-lock', 'panel' => 'border-slate-100 bg-slate-50/80', 'iconBox' => 'bg-white text-slate-500 border border-slate-100'];
        return ['label' => 'Terkunci', 'desc' => 'Waktu sudah lewat.', 'icon' => 'fa-lock', 'panel' => 'border-slate-100 bg-slate-50/80', 'iconBox' => 'bg-white text-slate-500 border border-slate-100'];
    };

    $statusData = $statusTheme($status);
    $kategoriData = $kategoriTheme($kategori);
    $targetData = $targetTheme($target);
    $lockData = $lockState();

    $summaryCards = [
        ['label' => 'Tanggal', 'value' => $tanggalPendek, 'icon' => 'fa-calendar-check', 'theme' => 'emerald'],
        ['label' => 'Waktu', 'value' => $waktuLabel, 'icon' => 'fa-clock', 'theme' => 'cyan'],
        ['label' => 'Status', 'value' => $statusData['label'], 'icon' => $statusData['icon'], 'theme' => 'slate'],
        ['label' => 'Akses', 'value' => $lockData['label'], 'icon' => $lockData['icon'], 'theme' => 'amber'],
    ];
@endphp

@push('styles')
<style>
    html { scroll-behavior: smooth; }
    body { background-color: #f4f7f6; } 

    .bg-mesh-fixed {
        position: fixed; inset: 0; z-index: -10;
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(150, 100%, 94%, 1) 0px, transparent 50%);
        pointer-events: none;
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

    /* Nexus Modal */
    .pc-modal-backdrop {
        position: fixed !important; inset: 0 !important; z-index: 9999 !important; display: none;
        align-items: center; justify-content: center; background: rgba(15, 23, 42, .6); backdrop-filter: blur(10px); padding: 1rem; opacity: 0; transition: opacity 0.3s ease;
    }
    .pc-modal-backdrop.is-open { display: flex !important; opacity: 1; }
    .pc-modal-card {
        width: 100%; max-width: 440px; background: white; border-radius: 2.5rem; padding: 2.5rem 2rem;
        transform: scale(0.9) translateY(20px); opacity: 0; transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); position: relative; overflow: hidden;
    }
    .pc-modal-backdrop.is-open .pc-modal-card { transform: scale(1) translateY(0); opacity: 1; }
</style>
@endpush

@section('content')
<div class="bg-mesh-fixed"></div>

<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6 animate-pop-in">

    {{-- 1. HERO BANNER --}}
    <section class="relative overflow-hidden rounded-[3rem] bg-gradient-to-r from-emerald-500 via-teal-500 to-green-500 p-8 sm:p-10 shadow-2xl shadow-emerald-500/20 flex flex-col md:flex-row justify-between items-center gap-8 border-[6px] border-white/40">
        <div class="absolute inset-0 bg-white/10 backdrop-blur-[2px]"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-full flex flex-col gap-4 text-center md:text-left">
            <div class="flex flex-col sm:flex-row items-center gap-3 justify-center md:justify-start">
                <a href="{{ route('bidan.jadwal.index') }}" class="btn-pill inline-flex items-center gap-2 border border-white/30 bg-white/20 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-white hover:bg-white/30 shadow-sm backdrop-blur-md transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Antrean Jadwal
                </a>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/30 bg-white/20 px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-white shadow-sm backdrop-blur-md">
                    <i class="fa-solid fa-calendar-check"></i> Detail Agenda
                </span>
            </div>
            
            <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight leading-tight">
                {{ $judul }}
            </h1>

            <div class="flex flex-wrap items-center justify-center md:justify-start gap-x-3 gap-y-2 mt-2">
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-black border {{ $statusData['badge'] }}">
                    <span class="h-2 w-2 rounded-full {{ $statusData['dot'] }}"></span> {{ $statusData['label'] }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-black border {{ $kategoriData['badge'] }}">
                    <i class="fa-solid {{ $kategoriData['icon'] }}"></i> {{ $kategoriMeta['label'] }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-black border {{ $targetData['badge'] }}">
                    <i class="fa-solid {{ $targetData['icon'] }}"></i> {{ $targetMeta['label'] }}
                </span>
                @if($today)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 text-emerald-700 px-3 py-1.5 text-[10px] font-black border border-emerald-200">
                        <i class="fa-solid fa-sparkles"></i> Hari Ini
                    </span>
                @elseif($past)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 text-amber-700 px-3 py-1.5 text-[10px] font-black border border-amber-200">
                        <i class="fa-solid fa-triangle-exclamation"></i> Terlewat
                    </span>
                @endif
            </div>
        </div>

        <div class="relative z-10 flex gap-3 flex-col sm:flex-row shrink-0">
            @if($canEdit)
                <a href="{{ route('bidan.jadwal.edit', $jadwal) }}" class="btn-pill bg-white text-emerald-600 hover:bg-emerald-50 px-6 py-3.5 text-[11px] font-black uppercase tracking-widest shadow-md flex items-center justify-center gap-2 transition-all hover:-translate-y-0.5">
                    <i class="fa-solid fa-pen text-sm"></i> Edit
                </a>
            @else
                <span class="btn-pill bg-white/20 text-white border border-white/30 px-6 py-3.5 text-[11px] font-black uppercase tracking-widest flex items-center justify-center gap-2 cursor-not-allowed">
                    <i class="fa-solid fa-lock text-sm"></i> Terkunci
                </span>
            @endif

            @if($canDelete)
                <button type="button" data-delete-trigger class="btn-pill bg-rose-500 text-white hover:bg-rose-600 px-6 py-3.5 text-[11px] font-black uppercase tracking-widest shadow-md flex items-center justify-center gap-2 transition-all hover:-translate-y-0.5">
                    <i class="fa-solid fa-trash text-sm"></i> Hapus
                </button>
            @endif
        </div>
    </section>

    {{-- 2. SUMMARY CARDS --}}
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach($summaryCards as $card)
            <div class="widget-card p-5 group flex items-center justify-between">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1 group-hover:text-{{ $card['theme'] }}-500 transition-colors">{{ $card['label'] }}</p>
                    <h2 class="text-xl font-black text-slate-800 leading-none">{{ $card['value'] }}</h2>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-{{ $card['theme'] }}-50 text-{{ $card['theme'] }}-500 border border-{{ $card['theme'] }}-100 flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition-transform">
                    <i class="fa-solid {{ $card['icon'] }}"></i>
                </div>
            </div>
        @endforeach
    </section>

    {{-- 3. GRID BENTO (MENGGUNAKAN items-start AGAR KOTAK TIDAK MELAR PAKSA) --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start mb-6">
        
        {{-- KOLOM KIRI (Col 8) --}}
        <div class="xl:col-span-8 flex flex-col gap-6">
            
            {{-- Detail Informasi Pelaksanaan --}}
            <section class="widget-card p-6 sm:p-8">
                <div class="flex items-center justify-between gap-4 mb-6 border-b border-slate-100 pb-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-0.5">Informasi Agenda</p>
                        <h2 class="text-base font-black tracking-tight text-slate-900">Detail Pelaksanaan</h2>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br {{ $kategoriData['gradient'] }} text-white shadow-md">
                        <i class="fa-solid {{ $kategoriData['icon'] }} text-xl"></i>
                    </div>
                </div>

                <div class="rounded-3xl border {{ $kategoriData['panel'] }} p-6">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
                        {{-- Ikon Kalender --}}
                        <div class="flex min-h-[120px] w-full sm:w-[140px] flex-col items-center justify-center rounded-[1.4rem] border border-white/70 bg-white/60 p-5 shrink-0 shadow-sm">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ $bulanPendek }}</p>
                            <p class="text-5xl font-black leading-none text-slate-800">{{ $tanggalAngka }}</p>
                            <p class="mt-2 text-[10px] font-black text-slate-500">{{ $tanggalPendek }}</p>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1.5">{{ $kategoriMeta['label'] }}</p>
                            <h3 class="text-2xl font-black tracking-tight text-slate-900 leading-tight mb-2">{{ $judul }}</h3>
                            <p class="text-sm font-bold text-slate-600 mb-4">{{ $tanggalLabel }}</p>

                            <div class="grid gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl border border-white/70 bg-white/70 p-3 shadow-sm flex flex-col justify-center">
                                    <span class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Waktu</span>
                                    <p class="text-[11px] font-bold text-slate-800">{{ $waktuLabel }}</p>
                                </div>
                                <div class="rounded-2xl border border-white/70 bg-white/70 p-3 shadow-sm flex flex-col justify-center">
                                    <span class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Lokasi</span>
                                    <p class="text-[11px] font-bold text-slate-800 truncate" title="{{ $lokasi }}">{{ $lokasi }}</p>
                                </div>
                                <div class="rounded-2xl border border-white/70 bg-white/70 p-3 shadow-sm flex flex-col justify-center">
                                    <span class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Sasaran</span>
                                    <p class="text-[11px] font-bold text-slate-800">{{ $targetMeta['label'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Deskripsi (Memiliki tinggi natural, tidak memaksa melar) --}}
            <section class="widget-card p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-5 border-b border-slate-100 pb-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 border border-amber-100 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-notes-medical"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Deskripsi / Catatan</h4>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Keterangan Tambahan Agenda</p>
                    </div>
                </div>

                @if($deskripsi !== '')
                    <div class="bg-slate-50 border border-slate-100 rounded-3xl p-5">
                        <p class="text-sm font-semibold leading-relaxed text-slate-700 whitespace-pre-line">{{ $deskripsi }}</p>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-6 bg-slate-50 border border-dashed border-slate-200 rounded-3xl">
                        <i class="fa-solid fa-comment-slash text-2xl text-slate-300 mb-2"></i>
                        <p class="text-[11px] font-bold text-slate-400 italic">Tidak ada catatan tambahan untuk agenda ini.</p>
                    </div>
                @endif
            </section>
        </div>

        {{-- KOLOM KANAN (Col 4): Sidebar Properties --}}
        <aside class="xl:col-span-4 flex flex-col gap-6">
            
            {{-- Distribusi (Kategori & Target) --}}
            <section class="widget-card p-6">
                <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-0.5">Distribusi</p>
                <h2 class="text-sm font-black tracking-tight text-slate-900 mb-5">Sasaran & Layanan</h2>

                <div class="space-y-4">
                    <div class="rounded-[1.4rem] border {{ $kategoriData['panel'] }} p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $kategoriData['iconBox'] }}">
                                <i class="fa-solid {{ $kategoriData['icon'] }} text-lg"></i>
                            </div>
                            <div>
                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block mb-0.5">Kategori</span>
                                <h3 class="text-sm font-black text-slate-900">{{ $kategoriMeta['label'] }}</h3>
                                <p class="mt-0.5 text-xs font-semibold leading-tight text-slate-600">{{ $kategoriMeta['desc'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.4rem] border {{ $targetData['panel'] }} p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $targetData['iconBox'] }}">
                                <i class="fa-solid {{ $targetData['icon'] }} text-lg"></i>
                            </div>
                            <div>
                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block mb-0.5">Target</span>
                                <h3 class="text-sm font-black text-slate-900">{{ $targetMeta['label'] }}</h3>
                                <p class="mt-0.5 text-xs font-semibold leading-tight text-slate-600">{{ $targetMeta['desc'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($kategori === 'imunisasi')
                    <div class="mt-4 rounded-[1.2rem] border border-amber-100 bg-amber-50/80 p-3.5 flex items-start gap-3">
                        <i class="fa-solid fa-circle-info text-amber-500 text-lg mt-0.5"></i>
                        <p class="text-[10px] font-bold leading-relaxed text-amber-700">Jadwal imunisasi otomatis ditujukan ke sasaran Balita.</p>
                    </div>
                @endif
            </section>

            {{-- Status & Akses --}}
            <section class="widget-card p-6">
                <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-0.5">Konfigurasi</p>
                <h2 class="text-sm font-black tracking-tight text-slate-900 mb-5">Status & Hak Akses</h2>

                <div class="space-y-4">
                    <div class="rounded-[1.4rem] border {{ $statusData['panel'] }} p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $statusData['iconBox'] }}">
                                <i class="fa-solid {{ $statusData['icon'] }} text-lg"></i>
                            </div>
                            <div>
                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block mb-0.5">Status Jadwal</span>
                                <h3 class="text-sm font-black text-slate-900">{{ $statusData['label'] }}</h3>
                                <p class="mt-0.5 text-xs font-semibold leading-tight text-slate-600">{{ $statusMeta['desc'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.4rem] border {{ $lockData['panel'] }} p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $lockData['iconBox'] }}">
                                <i class="fa-solid {{ $lockData['icon'] }} text-lg"></i>
                            </div>
                            <div>
                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block mb-0.5">Hak Edit</span>
                                <h3 class="text-sm font-black text-slate-900">{{ $lockData['label'] }}</h3>
                                <p class="mt-0.5 text-xs font-semibold leading-tight text-slate-600">{{ $lockData['desc'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </aside>
    </div>

    {{-- 4. FULL WIDTH CARD: Jejak Rekam Sistem (Sebagai Pijakan / Footer) --}}
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
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Waktu Dibuat</span>
                    </div>
                    <span class="text-xs font-bold text-slate-800">{{ $formatMetaDate($jadwal->created_at ?? null) }}</span>
                </div>
                
                <div class="flex-1 flex justify-between items-center bg-white rounded-2xl px-6 py-5 border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-slate-300 text-lg"></i>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Pembaruan Terakhir</span>
                    </div>
                    <span class="text-xs font-bold text-slate-800">{{ $formatMetaDate($jadwal->updated_at ?? null) }}</span>
                </div>
            </div>

        </div>
    </div>

</div>

{{-- MODAL HAPUS --}}
@if($canDelete)
<div id="pcJadwalDeleteModal" class="pc-modal-backdrop">
    <div class="pc-modal-card text-center">
        <div class="w-20 h-20 rounded-full bg-rose-50 border border-rose-100 flex items-center justify-center mx-auto mb-5 text-rose-500 shadow-inner">
            <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
        </div>
        <h3 class="text-2xl font-black text-slate-800 mb-2">Hapus Jadwal?</h3>
        <p class="text-sm font-medium text-slate-500 mb-6 leading-relaxed px-4">Jadwal <strong>{{ $judul }}</strong> akan dihapus permanen dan tidak akan tampil lagi di daftar agenda.</p>
        
        <form action="{{ route('bidan.jadwal.destroy', $jadwal) }}" method="POST" id="deleteForm">
            @csrf
            @method('DELETE')
            <div class="flex gap-3">
                <button type="button" id="pcJadwalDeleteCancel" class="btn-pill w-full flex-1 border border-slate-200 bg-white text-slate-700 px-4 py-3.5 text-sm font-bold shadow-sm hover:bg-slate-50 transition-all">Batal</button>
                <button type="submit" id="pcJadwalDeleteSubmit" class="btn-pill w-full flex-1 bg-gradient-to-r from-rose-500 to-orange-500 text-white px-4 py-3.5 text-sm font-bold shadow-md hover:from-rose-600 hover:to-orange-600 transition-all flex items-center justify-center gap-2"><i class="fa-solid fa-trash"></i> Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
(() => {
    // Delete Modal Logic
    const triggerBtn = document.querySelector('[data-delete-trigger]');
    const modal = document.getElementById('pcJadwalDeleteModal');
    const cancelBtn = document.getElementById('pcJadwalDeleteCancel');
    const submitBtn = document.getElementById('pcJadwalDeleteSubmit');
    const form = document.getElementById('deleteForm');

    function lockBody() {
        document.documentElement.classList.add('pc-modal-open');
        document.body.classList.add('pc-modal-open');
    }

    function unlockBody() {
        document.documentElement.classList.remove('pc-modal-open');
        document.body.classList.remove('pc-modal-open');
    }

    if (triggerBtn && modal) {
        triggerBtn.addEventListener('click', () => {
            lockBody();
            modal.classList.add('is-open');
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            modal.classList.remove('is-open');
            unlockBody();
        });
    }

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('is-open');
                unlockBody();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) {
                modal.classList.remove('is-open');
                unlockBody();
            }
        });
    }

    if (form && submitBtn) {
        form.addEventListener('submit', () => {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghapus...';
        });
    }
})();
</script>
@endpush