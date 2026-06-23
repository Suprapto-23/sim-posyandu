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
            'balita' => ['icon' => 'fa-child-reaching', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'badge' => 'bg-emerald-100 text-emerald-700'],
            'remaja' => ['icon' => 'fa-user-graduate', 'color' => 'text-violet-600', 'bg' => 'bg-violet-50', 'border' => 'border-violet-200', 'badge' => 'bg-violet-100 text-violet-700'],
            'lansia' => ['icon' => 'fa-person-cane', 'color' => 'text-sky-600', 'bg' => 'bg-sky-50', 'border' => 'border-sky-200', 'badge' => 'bg-sky-100 text-sky-700'],
            default => ['icon' => 'fa-users', 'color' => 'text-slate-600', 'bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'badge' => 'bg-slate-100 text-slate-700'],
        };
    };
@endphp

@push('styles')
<style>
    body { 
        background-color: #f1f5f9; 
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(150, 100%, 94%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }
    
    .widget-card {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: 2rem; 
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }
    .btn-pill:active {
        transform: scale(0.95);
    }

    .pc-row {
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.8);
        background: rgba(255, 255, 255, 0.6);
        border-radius: 1.5rem;
    }
    .pc-row:hover {
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.04);
        transform: translateY(-2px);
    }

    /* Input Native Clear Button Fix */
    input[type="search"]::-webkit-search-cancel-button {
        -webkit-appearance: none;
        height: 14px;
        width: 14px;
        background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%2394a3b8"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>') no-repeat 50% 50%;
        cursor: pointer;
        opacity: 0.7;
    }

    /* Modal Animation */
    .nexus-modal {
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    .nexus-modal.is-open {
        opacity: 1;
        visibility: visible;
    }
    .animate-fade-in {
        animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6 relative pb-32 animate-fade-in">

    {{-- SYSTEM ALERT --}}
    @if(session('success') || session('error'))
        <div class="rounded-[2rem] p-4 sm:p-6 shadow-sm border-2 flex items-center gap-4 {{ session('success') ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }}">
            <div class="bg-white rounded-full w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center shrink-0 shadow-inner">
                <i class="fa-solid {{ session('success') ? 'fa-circle-check text-emerald-500' : 'fa-triangle-exclamation text-rose-500' }} text-lg sm:text-xl"></i>
            </div>
            <div>
                <h3 class="font-black text-base sm:text-lg">{{ session('success') ? 'Berhasil' : 'Peringatan' }}</h3>
                <p class="font-medium text-xs sm:text-sm mt-0.5 sm:mt-1 opacity-80">{{ session('success') ?? session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- 1. HEADER / HERO WIDGET --}}
    <div class="widget-card p-5 sm:p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div class="max-w-xl w-full">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-[10px] font-black uppercase tracking-widest text-slate-500 mb-3">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                Archive & History
            </div>
            <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-800 tracking-tight mb-2">Riwayat Presensi.</h1>
            <p class="text-slate-500 text-xs sm:text-sm font-medium leading-relaxed">
                Tinjau kembali seluruh log presensi yang telah tersimpan. Data ditampilkan berdasarkan filter periode dan kategori.
            </p>
        </div>

        {{-- Grid 2 Kolom untuk Mobile agar tidak keluar layar --}}
        <div class="grid grid-cols-2 sm:flex gap-3 w-full md:w-auto">
            <div class="widget-card !rounded-2xl sm:!rounded-3xl !bg-white/90 p-3 sm:p-4 sm:px-6 border-slate-100 flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center text-lg sm:text-xl shadow-inner shrink-0">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <p class="text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Sesi</p>
                    <p class="text-xl sm:text-2xl font-black text-slate-800">{{ number_format($totalSesi) }}</p>
                </div>
            </div>
            
            <div class="widget-card !rounded-2xl sm:!rounded-3xl !bg-white/90 p-3 sm:p-4 sm:px-6 border-slate-100 flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg sm:text-xl shadow-inner shrink-0">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <div>
                    <p class="text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest">Kehadiran</p>
                    <p class="text-xl sm:text-2xl font-black text-slate-800">{{ $persentaseTotal }}<span class="text-sm sm:text-lg text-slate-400">%</span></p>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. FILTER WIDGET --}}
    <form id="filterForm" method="GET" action="{{ route('kader.absensi.riwayat') }}" class="flex flex-col xl:flex-row gap-4 items-center z-20 relative">
        
        {{-- Tabs Kategori: Menggunakan Grid 2x2 di HP agar kotak presisi --}}
        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 p-1.5 bg-white/60 backdrop-blur-md border border-white/80 rounded-[1.5rem] sm:rounded-full w-full xl:w-auto shadow-[0_8px_20px_-4px_rgba(0,0,0,0.05)]">
            <label class="cursor-pointer relative w-full sm:w-auto">
                <input type="radio" name="kategori" value="" class="peer sr-only" @checked($kategoriAktif === '')>
                <div class="w-full px-2 sm:px-5 py-2.5 rounded-xl sm:rounded-full text-xs sm:text-sm font-bold text-slate-500 text-center peer-checked:bg-white peer-checked:text-slate-800 peer-checked:shadow-sm transition-all duration-300">
                    <i class="fa-solid fa-layer-group sm:mr-1 opacity-50 block sm:inline mb-1 sm:mb-0"></i> Semua
                </div>
            </label>
            <label class="cursor-pointer relative w-full sm:w-auto">
                <input type="radio" name="kategori" value="balita" class="peer sr-only" @checked($kategoriAktif === 'balita')>
                <div class="w-full px-2 sm:px-5 py-2.5 rounded-xl sm:rounded-full text-xs sm:text-sm font-bold text-slate-500 text-center peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:shadow-sm transition-all duration-300">
                    <i class="fa-solid fa-child-reaching sm:mr-1 opacity-50 block sm:inline mb-1 sm:mb-0"></i> Balita
                </div>
            </label>
            <label class="cursor-pointer relative w-full sm:w-auto">
                <input type="radio" name="kategori" value="remaja" class="peer sr-only" @checked($kategoriAktif === 'remaja')>
                <div class="w-full px-2 sm:px-5 py-2.5 rounded-xl sm:rounded-full text-xs sm:text-sm font-bold text-slate-500 text-center peer-checked:bg-violet-500 peer-checked:text-white peer-checked:shadow-sm transition-all duration-300">
                    <i class="fa-solid fa-user-graduate sm:mr-1 opacity-50 block sm:inline mb-1 sm:mb-0"></i> Remaja
                </div>
            </label>
            <label class="cursor-pointer relative w-full sm:w-auto">
                <input type="radio" name="kategori" value="lansia" class="peer sr-only" @checked($kategoriAktif === 'lansia')>
                <div class="w-full px-2 sm:px-5 py-2.5 rounded-xl sm:rounded-full text-xs sm:text-sm font-bold text-slate-500 text-center peer-checked:bg-sky-500 peer-checked:text-white peer-checked:shadow-sm transition-all duration-300">
                    <i class="fa-solid fa-person-cane sm:mr-1 opacity-50 block sm:inline mb-1 sm:mb-0"></i> Lansia
                </div>
            </label>
        </div>

        {{-- Search & Periode --}}
        <div class="widget-card p-2 flex flex-col sm:flex-row gap-2 w-full xl:flex-1">
            <div class="w-full sm:flex-1 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="search" name="search" value="{{ $searchQuery }}" placeholder="Cari kode/nama..." class="w-full btn-pill border border-slate-200 bg-white/80 py-3 pl-10 pr-4 text-sm font-semibold text-slate-800 outline-none transition focus:bg-white focus:border-teal-400">
            </div>

            <div class="flex gap-2 w-full sm:w-auto">
                <select name="bulan" class="w-full sm:w-36 btn-pill border border-slate-200 bg-white/80 px-3 py-3 text-sm font-bold text-slate-700 outline-none cursor-pointer">
                    @foreach($bulanOptions as $num => $name)
                        <option value="{{ $num }}" @selected($bulanAktif == $num)>{{ $name }}</option>
                    @endforeach
                </select>

                <select name="tahun" class="w-full sm:w-28 btn-pill border border-slate-200 bg-white/80 px-3 py-3 text-sm font-bold text-slate-700 outline-none cursor-pointer">
                    @foreach($tahunOptions as $thn)
                        <option value="{{ $thn }}" @selected($tahunAktif == $thn)>{{ $thn }}</option>
                    @endforeach
                </select>
                
                <button type="submit" class="w-12 shrink-0 h-11 flex items-center justify-center btn-pill bg-slate-100 text-slate-500 hover:bg-teal-50 hover:text-teal-600 transition border border-slate-200" title="Refresh/Filter">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
            </div>
        </div>
    </form>

    {{-- 3. DAFTAR RIWAYAT --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between px-2">
            <p class="text-[10px] sm:text-[11px] font-black text-slate-400 uppercase tracking-widest">Menampilkan {{ number_format($totalSesi) }} sesi presensi</p>
            <a href="{{ route('kader.absensi.index', ['kategori' => $kategoriAktif ?: 'balita']) }}" class="text-[10px] sm:text-xs font-bold text-teal-600 hover:text-teal-800"><i class="fa-solid fa-plus mr-1"></i> INPUT BARU</a>
        </div>

        @forelse($riwayats as $item)
            @php
                $theme = $getTheme($item->kategori);
                $persentase = $item->total_peserta > 0 ? round(($item->total_hadir / $item->total_peserta) * 100) : 0;
            @endphp
            
            <article class="pc-row p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                {{-- Info Utama --}}
                <div class="flex items-start sm:items-center gap-3 sm:gap-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full {{ $theme['bg'] }} {{ $theme['border'] }} border flex items-center justify-center text-base sm:text-lg {{ $theme['color'] }} shrink-0 shadow-inner">
                        <i class="fa-solid {{ $theme['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h3 class="text-sm sm:text-base font-black text-slate-800 tracking-tight">{{ $item->kode_absensi }}</h3>
                            <span class="px-2 py-0.5 rounded-md text-[8px] sm:text-[9px] font-black uppercase tracking-wider {{ $theme['badge'] }}">
                                {{ $item->kategori }}
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[10px] sm:text-[11px] font-bold text-slate-500">
                            <span title="Tanggal"><i class="fa-solid fa-calendar-day opacity-70 mr-1"></i> {{ \Carbon\Carbon::parse($item->tanggal_posyandu)->translatedFormat('d M Y') }}</span>
                            <span title="Pertemuan"><i class="fa-solid fa-hashtag opacity-70 mr-1"></i> Ke-{{ $item->nomor_pertemuan }}</span>
                            <span title="Pencatat" class="hidden sm:inline"><i class="fa-solid fa-user-pen opacity-70 mr-1"></i> {{ $item->kader->name ?? 'Sistem' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Stats & Aksi (Dibuat responsif bertumpuk di mobile) --}}
                <div class="flex flex-col sm:flex-row items-center justify-between lg:justify-end gap-4 w-full lg:w-auto pt-3 mt-1 lg:pt-0 lg:mt-0 border-t lg:border-t-0 border-slate-100">
                    
                    <div class="grid grid-cols-3 gap-2 w-full sm:w-auto text-center bg-slate-50/50 sm:bg-transparent p-2 sm:p-0 rounded-xl">
                        <div>
                            <p class="text-[8px] sm:text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Sasaran</p>
                            <p class="text-xs sm:text-sm font-black text-slate-700">{{ $item->total_peserta }}</p>
                        </div>
                        <div class="border-x border-slate-200">
                            <p class="text-[8px] sm:text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Hadir</p>
                            <p class="text-xs sm:text-sm font-black text-emerald-600">{{ $item->total_hadir }}</p>
                        </div>
                        <div>
                            <p class="text-[8px] sm:text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">%</p>
                            <p class="text-xs sm:text-sm font-black text-slate-800">{{ $persentase }}%</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto border-t sm:border-t-0 sm:border-l border-slate-200 pt-3 sm:pt-0 sm:pl-4 justify-center sm:justify-end">
                        <a href="{{ route('kader.absensi.show', $item->id) }}" class="flex-1 sm:flex-none h-9 px-4 sm:px-0 sm:w-9 flex items-center justify-center btn-pill border border-slate-200 bg-white text-slate-500 hover:text-teal-600 hover:border-teal-200 hover:bg-teal-50 text-xs sm:text-sm font-bold gap-2" title="Lihat Detail">
                            <i class="fa-solid fa-eye"></i> <span class="sm:hidden">Detail</span>
                        </a>
                        
                        <form action="{{ route('kader.absensi.destroy', $item->id) }}" method="POST" data-delete-form class="m-0 p-0 flex-1 sm:flex-none">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full sm:w-9 h-9 flex items-center justify-center btn-pill border border-rose-200 bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition text-xs sm:text-sm font-bold gap-2" title="Hapus Permanen">
                                <i class="fa-solid fa-trash"></i> <span class="sm:hidden">Hapus</span>
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="p-8 sm:p-12 text-center border-2 border-dashed border-slate-200 rounded-[2rem] bg-white/50">
                <div class="mx-auto flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-4 shadow-inner">
                    <i class="fa-solid fa-folder-open text-xl sm:text-2xl"></i>
                </div>
                <h3 class="font-black text-slate-800 text-base sm:text-lg">Belum Ada Presensi</h3>
                <p class="text-xs sm:text-sm font-medium text-slate-500 mt-1">Tidak ada data absensi yang ditemukan untuk filter periode ini.</p>
                <a href="{{ route('kader.absensi.index') }}" class="inline-flex mt-6 btn-pill bg-teal-500 text-white px-5 sm:px-6 py-2.5 text-xs sm:text-sm font-bold shadow-md hover:bg-teal-600">
                    Input Absensi Baru
                </a>
            </div>
        @endforelse
    </div>

    {{-- MODAL HAPUS DATA PREMIUM --}}
    <div id="pcDeleteModal" class="nexus-modal fixed inset-0 z-[9999] flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" id="nexusAlertBackdrop"></div>
        <div class="bg-white/90 backdrop-blur-xl border border-white/80 w-full max-w-sm p-6 relative z-10 scale-95 transform transition-all duration-300 rounded-[2rem] shadow-2xl">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-rose-50 border border-rose-100 flex items-center justify-center mx-auto mb-4 text-rose-500 shadow-inner">
                <i class="fa-solid fa-triangle-exclamation text-xl sm:text-2xl"></i>
            </div>
            <h3 class="text-lg sm:text-xl font-black text-slate-800 text-center mb-2">Hapus Presensi?</h3>
            <p class="text-xs sm:text-sm font-medium text-slate-500 text-center mb-6 leading-relaxed">
                Apakah Anda yakin ingin menghapus riwayat absensi ini? Seluruh detail kehadiran di dalamnya juga akan ikut terhapus permanen.
            </p>
            <div class="flex gap-3">
                <button type="button" id="pcCancelDelete" class="w-full flex-1 rounded-full border border-slate-200 bg-white text-slate-700 px-4 py-2.5 sm:py-3 text-xs sm:text-sm font-bold shadow-sm hover:bg-slate-50 transition-all cursor-pointer">
                    Batal
                </button>
                <button type="button" id="pcConfirmDelete" class="w-full flex-1 rounded-full bg-gradient-to-r from-rose-500 to-rose-600 text-white px-4 py-2.5 sm:py-3 text-xs sm:text-sm font-bold shadow-md hover:from-rose-600 hover:to-rose-700 transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-trash"></i> Hapus
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

    // 1. Auto-Submit Filter (Agar tab & dropdown langsung memuat data)
    document.addEventListener('change', function(e) {
        if (e.target.matches('select[name="bulan"], select[name="tahun"], input[name="kategori"]')) {
            document.getElementById('filterForm').submit();
        }
    });

    // 2. Logic Modal Hapus Premium
    let targetDeleteForm = null;
    const modal = document.querySelector('#pcDeleteModal');

    // Pindahkan modal ke luar agar z-index aman (termasuk menutupi sidebar)
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    const cancelBtn = document.querySelector('#pcCancelDelete');
    const confirmBtn = document.querySelector('#pcConfirmDelete');
    const backdrop = document.querySelector('#nexusAlertBackdrop');

    function lockBody() { document.body.style.overflow = 'hidden'; }
    function unlockBody() { document.body.style.overflow = ''; }

    function openModal(form) {
        targetDeleteForm = form;
        if (!modal) return;
        
        modal.classList.add('is-open');
        modal.querySelector('.bg-white\\/90').classList.remove('scale-95');
        modal.querySelector('.bg-white\\/90').classList.add('scale-100');
        lockBody();
    }

    function closeModal() {
        targetDeleteForm = null;
        if (!modal) return;

        modal.classList.remove('is-open');
        modal.querySelector('.bg-white\\/90').classList.remove('scale-100');
        modal.querySelector('.bg-white\\/90').classList.add('scale-95');
        unlockBody();
    }

    // Tangkap klik form tombol Hapus
    document.addEventListener('submit', function (event) {
        const form = event.target.closest('[data-delete-form]');
        if (!form) return;

        event.preventDefault(); // Cegah submit bawaan
        openModal(form);        // Buka modal custom
    }, true);

    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (backdrop) backdrop.addEventListener('click', closeModal);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeModal();
    });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!targetDeleteForm) {
                closeModal();
                return;
            }

            // Animasi Loading
            confirmBtn.disabled = true;
            confirmBtn.classList.add('opacity-70', 'cursor-not-allowed');
            confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghapus...';

            // Kirim form sesungguhnya ke database
            HTMLFormElement.prototype.submit.call(targetDeleteForm);
        });
    }
});
</script>
@endpush
