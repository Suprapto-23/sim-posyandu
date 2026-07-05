@extends('layouts.kader')

@section('title', 'Riwayat Presensi')
@section('page-name', 'Riwayat Presensi')
@section('page-title', 'Riwayat Presensi')

@php
    use Carbon\Carbon;
    Carbon::setLocale('id');

    $kategoriAktif = $kategori ?? '';
    $bulanAktif = $bulan ?? now('Asia/Jakarta')->month;
    $tahunAktif = $tahun ?? now('Asia/Jakarta')->year;
    $searchQuery = $search ?? '';

    $totalSesi = $riwayats->count();
    $totalSemuaPeserta = $riwayats->sum('total_peserta');
    $totalSemuaHadir = $riwayats->sum('total_hadir');
    $persentaseTotal = $totalSemuaPeserta > 0 ? round(($totalSemuaHadir / $totalSemuaPeserta) * 100) : 0;

    $bulanOptions = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    $tahunMulai = 2024;
    $tahunSekarang = now('Asia/Jakarta')->year + 1;
    $tahunOptions = range($tahunMulai, $tahunSekarang);

    $getTheme = function($kat) {
        $kat = strtolower($kat);
        return match($kat) {
            'balita' => ['icon' => 'fa-child-reaching', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'badge' => 'bg-emerald-100 text-emerald-700', 'border_accent' => 'border-l-emerald-500'],
            'remaja' => ['icon' => 'fa-user-graduate', 'color' => 'text-sky-600', 'bg' => 'bg-sky-50', 'border' => 'border-sky-200', 'badge' => 'bg-sky-100 text-sky-700', 'border_accent' => 'border-l-sky-500'],
            'lansia' => ['icon' => 'fa-person-cane', 'color' => 'text-amber-600', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'badge' => 'bg-amber-100 text-amber-700', 'border_accent' => 'border-l-amber-500'],
            default => ['icon' => 'fa-users', 'color' => 'text-slate-600', 'bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'badge' => 'bg-slate-100 text-slate-700', 'border_accent' => 'border-l-slate-400'],
        };
    };
@endphp

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
    body { 
        background-color: #f8fafc; 
        background-image: radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
                          radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.97); }

    .pc-row {
        transition: all 0.25s ease;
    }
    .pc-row:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px -5px rgba(15, 23, 42, 0.08);
    }

    /* Modal Animation */
    .nexus-modal {
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .nexus-modal.is-open {
        opacity: 1;
        visibility: visible;
    }
    
    .animate-pop-in {
        animation: popIn .4s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }
    @keyframes popIn {
        from { opacity: 0; transform: scale(.96) translateY(12px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    /* Scrollbar elegan untuk dropdown kustom */
    .custom-scroll::-webkit-scrollbar { width: 5px; }
    .custom-scroll::-webkit-scrollbar-track { background: transparent; }
    .custom-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }

    /* Transisi Halus AJAX */
    #live-region, #stats-container {
        transition: opacity 0.3s ease;
    }
    .is-loading {
        opacity: 0.4;
        pointer-events: none;
    }
</style>
@endpush

@section('content')
<div class="max-w-[1280px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6 space-y-6 relative">

    {{-- SYSTEM ALERT --}}
    @if(session('success') || session('error'))
        <div class="bg-white rounded-[1.5rem] p-4 shadow-sm border-l-4 flex items-center gap-4 {{ session('success') ? 'border-l-emerald-500' : 'border-l-rose-500' }}">
            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ session('success') ? 'bg-emerald-50 text-emerald-500' : 'bg-rose-50 text-rose-500' }}">
                <i class="fa-solid {{ session('success') ? 'fa-circle-check' : 'fa-triangle-exclamation' }} text-lg"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-sm">{{ session('success') ? 'Berhasil' : 'Peringatan' }}</h3>
                <p class="font-medium text-slate-500 text-xs mt-0.5">{{ session('success') ?? session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- 1. HERO WIDGET --}}
    <section class="bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-600 rounded-[2.5rem] p-8 md:p-10 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(16,185,129,.35)] border border-white/20 flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/15 blur-[60px] rounded-full pointer-events-none"></div>
        
        <div class="relative z-10 max-w-xl">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/20 border border-white/20 backdrop-blur-sm text-[10px] font-black uppercase tracking-widest text-white/90 mb-4 shadow-sm">
                <i class="fa-solid fa-clock-rotate-left opacity-80"></i> Archive & History
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight mb-3 leading-tight">Riwayat Presensi</h1>
            <p class="text-teal-50 text-sm font-medium leading-relaxed opacity-90">
                Tinjau kembali seluruh log kehadiran yang telah tersimpan. Gunakan filter di bawah untuk mencari arsip berdasarkan periode dan sasaran.
            </p>
        </div>

        {{-- Widget Statistik --}}
        <div id="stats-container" class="relative z-10 flex flex-col sm:flex-row gap-4 w-full md:w-auto shrink-0">
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-[1.5rem] p-5 flex items-center gap-4 w-full md:w-48 shadow-inner">
                <div class="w-12 h-12 rounded-full bg-white/20 text-white flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-white/70 uppercase tracking-widest">Total Sesi</p>
                    <p class="text-2xl font-black text-white leading-none mt-0.5">{{ number_format($totalSesi) }}</p>
                </div>
            </div>
            
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-[1.5rem] p-5 flex items-center gap-4 w-full md:w-48 shadow-inner">
                <div class="w-12 h-12 rounded-full bg-white/20 text-white flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-white/70 uppercase tracking-widest">Kehadiran</p>
                    <p class="text-2xl font-black text-white leading-none mt-0.5">{{ $persentaseTotal }}<span class="text-base opacity-70">%</span></p>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. FILTER WIDGET --}}
    <form id="filterForm" method="GET" action="{{ route('kader.absensi.riwayat') }}" class="bg-white border border-slate-200 rounded-[2rem] p-5 shadow-sm flex flex-col xl:flex-row gap-5 items-center z-20 relative">
        
        {{-- Tabs Kategori --}}
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 p-1.5 bg-slate-50 border border-slate-100 rounded-2xl sm:rounded-full w-full xl:w-auto shrink-0">
            <label class="cursor-pointer relative w-full sm:w-auto">
                <input type="radio" name="kategori" value="" class="peer sr-only" @checked($kategoriAktif === '')>
                <div class="w-full px-5 py-2.5 rounded-xl sm:rounded-full text-xs font-bold text-slate-500 text-center hover:text-slate-700 peer-checked:bg-white peer-checked:text-slate-800 peer-checked:shadow-sm peer-checked:border peer-checked:border-slate-200 transition-all duration-200">
                    <i class="fa-solid fa-layer-group sm:mr-1 opacity-50"></i> <span class="hidden sm:inline">Semua</span>
                </div>
            </label>
            <label class="cursor-pointer relative w-full sm:w-auto">
                <input type="radio" name="kategori" value="balita" class="peer sr-only" @checked($kategoriAktif === 'balita')>
                <div class="w-full px-5 py-2.5 rounded-xl sm:rounded-full text-xs font-bold text-slate-500 text-center hover:text-slate-700 peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:shadow-md transition-all duration-200">
                    <i class="fa-solid fa-child-reaching sm:mr-1 opacity-50"></i> Balita
                </div>
            </label>
            <label class="cursor-pointer relative w-full sm:w-auto">
                <input type="radio" name="kategori" value="remaja" class="peer sr-only" @checked($kategoriAktif === 'remaja')>
                <div class="w-full px-5 py-2.5 rounded-xl sm:rounded-full text-xs font-bold text-slate-500 text-center hover:text-slate-700 peer-checked:bg-sky-500 peer-checked:text-white peer-checked:shadow-md transition-all duration-200">
                    <i class="fa-solid fa-user-graduate sm:mr-1 opacity-50"></i> Remaja
                </div>
            </label>
            <label class="cursor-pointer relative w-full sm:w-auto">
                <input type="radio" name="kategori" value="lansia" class="peer sr-only" @checked($kategoriAktif === 'lansia')>
                <div class="w-full px-5 py-2.5 rounded-xl sm:rounded-full text-xs font-bold text-slate-500 text-center hover:text-slate-700 peer-checked:bg-amber-100 peer-checked:text-amber-700 peer-checked:shadow-md transition-all duration-200">
    <i class="fa-solid fa-person-cane sm:mr-1 opacity-50"></i> Lansia
</div>
            </label>
        </div>

        {{-- Pencarian & Dropdown Kustom --}}
        <div class="flex flex-col sm:flex-row gap-3 w-full xl:flex-1 border-t xl:border-t-0 xl:border-l border-slate-100 pt-5 xl:pt-0 xl:pl-5 relative z-30">
            
            <div class="w-full sm:flex-1 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="search" name="search" id="searchInput" value="{{ $searchQuery }}" autocomplete="off" placeholder="Cari kode / nama..." class="w-full btn-pill border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm font-semibold text-slate-800 outline-none transition focus:bg-white focus:border-teal-400 focus:ring-4 focus:ring-teal-500/10">
            </div>

            <div class="flex gap-2 w-full sm:w-auto">
                
                {{-- Dropdown Bulan (Alpine.js) --}}
                <div x-data="{ 
                        open: false, 
                        selected: '{{ $bulanAktif }}', 
                        label: '{{ $bulanOptions[$bulanAktif] ?? "Bulan" }}',
                        options: {{ json_encode($bulanOptions) }}
                    }" 
                    class="relative w-full sm:w-40">
                    <input type="hidden" name="bulan" :value="selected" x-ref="bulanInput">
                    <button type="button" @click="open = !open" @click.outside="open = false" class="w-full btn-pill border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 flex justify-between items-center outline-none hover:bg-white hover:border-teal-400 transition-colors shadow-inner">
                        <span x-text="label" class="truncate"></span>
                        <i class="fa-solid fa-chevron-down text-slate-400 text-[10px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    
                    <div x-show="open" x-transition.opacity.duration.200ms class="absolute left-0 mt-2 w-full bg-white border border-slate-100 rounded-2xl shadow-xl z-50 overflow-hidden py-2" style="display: none;">
                        <div class="max-h-60 overflow-y-auto custom-scroll">
                            <template x-for="(name, num) in options" :key="num">
                                <div @click="selected = num; label = name; open = false; $nextTick(() => { $refs.bulanInput.dispatchEvent(new Event('change', { bubbles: true })) })" 
                                     class="px-4 py-2.5 text-xs font-bold cursor-pointer transition-colors"
                                     :class="selected == num ? 'bg-teal-50 text-teal-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                    <span x-text="name"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Dropdown Tahun (Alpine.js) --}}
                <div x-data="{ 
                        open: false, 
                        selected: '{{ $tahunAktif }}', 
                        options: {{ json_encode($tahunOptions) }}
                    }" 
                    class="relative w-full sm:w-28">
                    <input type="hidden" name="tahun" :value="selected" x-ref="tahunInput">
                    <button type="button" @click="open = !open" @click.outside="open = false" class="w-full btn-pill border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 flex justify-between items-center outline-none hover:bg-white hover:border-teal-400 transition-colors shadow-inner">
                        <span x-text="selected"></span>
                        <i class="fa-solid fa-chevron-down text-slate-400 text-[10px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    
                    <div x-show="open" x-transition.opacity.duration.200ms class="absolute left-0 mt-2 w-full bg-white border border-slate-100 rounded-2xl shadow-xl z-50 overflow-hidden py-2" style="display: none;">
                        <div class="max-h-60 overflow-y-auto custom-scroll">
                            <template x-for="thn in options" :key="thn">
                                <div @click="selected = thn; open = false; $nextTick(() => { $refs.tahunInput.dispatchEvent(new Event('change', { bubbles: true })) })" 
                                     class="px-4 py-2.5 text-xs font-bold cursor-pointer transition-colors"
                                     :class="selected == thn ? 'bg-teal-50 text-teal-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                                    <span x-text="thn"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </form>

    {{-- 3. DAFTAR RIWAYAT (WADAH AJAX) --}}
    <div id="live-region" class="w-full">
        <div class="space-y-4">
            <div class="flex items-center justify-between px-2">
                <p class="text-[10px] sm:text-xs font-black text-slate-500 uppercase tracking-widest">
                    Menampilkan <span class="text-slate-800">{{ number_format($totalSesi) }}</span> sesi presensi
                </p>
                <a href="{{ route('kader.absensi.index', ['kategori' => $kategoriAktif ?: 'balita']) }}" class="btn-pill bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 px-4 py-2 text-[10px] sm:text-xs font-bold transition-all shadow-sm">
                    <i class="fa-solid fa-plus mr-1"></i> Sesi Baru
                </a>
            </div>

            @forelse($riwayats as $item)
                @php
                    $theme = $getTheme($item->kategori);
                    $persentase = $item->total_peserta > 0 ? round(($item->total_hadir / $item->total_peserta) * 100) : 0;
                @endphp
                
                <article class="pc-row bg-white border border-slate-200 rounded-[1.5rem] p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-5 border-l-4 {{ $theme['border_accent'] }} shadow-sm">
                    
                    {{-- Info Utama --}}
                    <div class="flex items-start sm:items-center gap-4 w-full lg:w-auto min-w-0">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl {{ $theme['bg'] }} {{ $theme['border'] }} border flex items-center justify-center text-lg sm:text-xl {{ $theme['color'] }} shrink-0">
                            <i class="fa-solid {{ $theme['icon'] }}"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                <h3 class="text-base sm:text-lg font-black text-slate-800 tracking-tight truncate" title="{{ $item->kode_absensi }}">{{ $item->kode_absensi }}</h3>
                                <span class="px-2.5 py-0.5 rounded border text-[9px] sm:text-[10px] font-black uppercase tracking-wider {{ $theme['badge'] }} {{ $theme['border'] }}">
                                    {{ $item->kategori }}
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 text-[11px] sm:text-xs font-semibold text-slate-500">
                                <span title="Tanggal"><i class="fa-solid fa-calendar-day text-slate-400 mr-1"></i> {{ \Carbon\Carbon::parse($item->tanggal_posyandu)->translatedFormat('d M Y') }}</span>
                                <span class="w-1 h-1 rounded-full bg-slate-300 hidden sm:block"></span>
                                <span title="Pertemuan"><i class="fa-solid fa-hashtag text-slate-400 mr-1"></i> Ke-{{ $item->nomor_pertemuan }}</span>
                                <span class="w-1 h-1 rounded-full bg-slate-300 hidden sm:block"></span>
                                <span title="Pencatat" class="truncate max-w-[120px] sm:max-w-none"><i class="fa-solid fa-user-pen text-slate-400 mr-1"></i> {{ $item->pencatat->name ?? 'Sistem' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Stats & Aksi --}}
                    <div class="flex flex-col sm:flex-row items-center justify-between lg:justify-end gap-5 w-full lg:w-auto shrink-0 border-t lg:border-t-0 border-slate-100 pt-4 lg:pt-0">
                        
                        <div class="grid grid-cols-3 gap-3 w-full sm:w-auto text-center bg-slate-50 border border-slate-100 rounded-xl p-2.5 shrink-0">
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Sasaran</p>
                                <p class="text-sm font-black text-slate-700">{{ $item->total_peserta }}</p>
                            </div>
                            <div class="border-x border-slate-200">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Hadir</p>
                                <p class="text-sm font-black text-emerald-600">{{ $item->total_hadir }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">%</p>
                                <p class="text-sm font-black text-slate-800">{{ $persentase }}</p>
                            </div>
                        </div>

                        {{-- Action Buttons (Proporsi teks utuh & anti-gepeng) --}}
                        <div class="flex items-center gap-2 w-full sm:w-auto justify-end sm:border-l border-slate-200 sm:pl-5 shrink-0">
                            <a href="{{ route('kader.absensi.show', $item->id) }}" class="flex-1 sm:flex-none h-11 px-4 flex items-center justify-center btn-pill border border-slate-200 bg-white text-slate-600 hover:text-teal-600 hover:border-teal-200 hover:bg-teal-50 text-xs sm:text-sm font-bold gap-2 transition-all shadow-sm shrink-0" title="Lihat Detail">
                                <i class="fa-solid fa-eye"></i> <span>Detail</span>
                            </a>
                            
                            <form action="{{ route('kader.absensi.destroy', $item->id) }}" method="POST" data-delete-form class="m-0 p-0 flex-1 sm:flex-none shrink-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full h-11 px-4 flex items-center justify-center btn-pill border border-rose-200 bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all text-xs sm:text-sm font-bold gap-2 shadow-sm shrink-0" title="Hapus Permanen">
                                    <i class="fa-solid fa-trash-can"></i> <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="p-10 sm:p-14 text-center border-2 border-dashed border-slate-200 rounded-[2.5rem] bg-white">
                    <div class="mx-auto flex h-16 w-16 sm:h-20 sm:w-20 items-center justify-center rounded-3xl bg-slate-50 text-slate-300 mb-5 shadow-inner border border-slate-100">
                        <i class="fa-solid fa-folder-open text-2xl sm:text-3xl"></i>
                    </div>
                    <h3 class="font-black text-slate-800 text-lg sm:text-xl tracking-tight">Tidak Ada Presensi</h3>
                    <p class="text-sm font-medium text-slate-500 mt-2 max-w-md mx-auto">
                        Data absensi kosong pada periode yang Anda pilih. Pastikan filter sudah benar atau mulai sesi absensi baru.
                    </p>
                    <a href="{{ route('kader.absensi.index') }}" class="inline-flex mt-8 btn-pill bg-teal-600 text-white px-6 py-3 text-sm font-bold shadow-md hover:bg-teal-700 gap-2 items-center">
                        <i class="fa-solid fa-plus"></i> Input Sesi Baru
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- MODAL HAPUS DATA --}}
    <div id="pcDeleteModal" class="nexus-modal fixed inset-0 z-[9999] flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" id="nexusAlertBackdrop"></div>
        <div class="bg-white w-full max-w-sm p-6 sm:p-8 relative z-10 scale-95 transform transition-all duration-300 rounded-[2.5rem] shadow-2xl border border-white/50">
            <div class="w-16 h-16 rounded-full bg-rose-50 border border-rose-100 flex items-center justify-center mx-auto mb-5 text-rose-500 shadow-inner">
                <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
            </div>
            <h3 class="text-xl font-black text-slate-800 text-center mb-2 tracking-tight">Hapus Presensi?</h3>
            <p class="text-sm font-medium text-slate-500 text-center mb-8 leading-relaxed">
                Tindakan ini akan menghapus riwayat sesi beserta seluruh data kehadiran sasaran di dalamnya secara permanen.
            </p>
            <div class="flex gap-3">
                <button type="button" id="pcCancelDelete" class="w-full flex-1 rounded-full border border-slate-200 bg-white text-slate-600 px-4 py-3 text-sm font-bold shadow-sm hover:bg-slate-50 transition-all cursor-pointer">
                    Batal
                </button>
                <button type="button" id="pcConfirmDelete" class="w-full flex-1 rounded-full bg-rose-500 text-white px-4 py-3 text-sm font-bold shadow-md hover:bg-rose-600 transition-all flex items-center justify-center gap-2 cursor-pointer border border-rose-600/50">
                    <i class="fa-solid fa-trash-can"></i> Hapus
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    /* ========================================================
       1. AJAX LIVE FILTER & SEARCH LOGIC
       ======================================================== */
    const form = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchInput');
    const liveRegion = document.getElementById('live-region');
    const statsContainer = document.getElementById('stats-container');
    
    let debounceTimer;
    let fetchController;

    async function fetchLiveResults(url) {
        if (fetchController) fetchController.abort();
        fetchController = new AbortController();

        liveRegion.classList.add('is-loading');
        statsContainer.classList.add('is-loading');

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                signal: fetchController.signal
            });

            if (!response.ok) throw new Error('Jaringan bermasalah');

            const html = await response.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            
            const newRegion = doc.getElementById('live-region');
            const newStats = doc.getElementById('stats-container');

            if (newRegion) liveRegion.innerHTML = newRegion.innerHTML;
            if (newStats) statsContainer.innerHTML = newStats.innerHTML;

            window.history.pushState({}, '', url);

        } catch (error) {
            if (error.name !== 'AbortError') console.error('Gagal mengambil data:', error);
        } finally {
            liveRegion.classList.remove('is-loading');
            statsContainer.classList.remove('is-loading');
        }
    }

    function triggerFilter() {
        const url = new URL(form.action);
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        
        for (const [key, value] of params.entries()) {
            if (value.trim() === '') params.delete(key);
        }
        
        url.search = params.toString();
        fetchLiveResults(url);
    }

    // Mendengarkan semua perubahan di dalam form (Radio dan Custom Dropdown)
    form.addEventListener('change', function() {
        triggerFilter();
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        triggerFilter();
    });

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(triggerFilter, 300);
    });

    window.addEventListener('popstate', function() {
        window.location.reload(); 
    });


    /* ========================================================
       2. MODAL HAPUS DATA LOGIC 
       ======================================================== */
    let targetDeleteForm = null;
    const modal = document.querySelector('#pcDeleteModal');
    const cancelBtn = document.querySelector('#pcCancelDelete');
    const confirmBtn = document.querySelector('#pcConfirmDelete');
    const backdrop = document.querySelector('#nexusAlertBackdrop');

    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    function openModal(deleteForm) {
        targetDeleteForm = deleteForm;
        if (!modal) return;
        modal.classList.add('is-open');
        modal.querySelector('.bg-white').classList.remove('scale-95');
        modal.querySelector('.bg-white').classList.add('scale-100');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        targetDeleteForm = null;
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.querySelector('.bg-white').classList.remove('scale-100');
        modal.querySelector('.bg-white').classList.add('scale-95');
        document.body.style.overflow = '';
        
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            confirmBtn.innerHTML = '<i class="fa-solid fa-trash-can"></i> Hapus';
        }
    }

    document.addEventListener('submit', function (event) {
        const deleteFormTarget = event.target.closest('[data-delete-form]');
        if (!deleteFormTarget) return;
        event.preventDefault(); 
        openModal(deleteFormTarget);        
    }, true);

    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (backdrop) backdrop.addEventListener('click', closeModal);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!targetDeleteForm) return closeModal();
            confirmBtn.disabled = true;
            confirmBtn.classList.add('opacity-70', 'cursor-not-allowed');
            confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
            HTMLFormElement.prototype.submit.call(targetDeleteForm);
        });
    }
});
</script>
@endpush