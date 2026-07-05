@extends('layouts.user')

@section('title', 'Detail Jadwal')
@section('page-title', 'Detail Agenda Posyandu')

@section('content')
<div class="max-w-[1000px] mx-auto py-6 px-4 sm:px-6 mb-20">

    {{-- 1. HERO SECTION (Gradien Biru Terang ke Hijau) --}}
    <!-- Menggunakan from-sky-500 agar warna biru di sisi kiri lebih tegas dan tidak menyatu dengan background -->
    <div class="bg-gradient-to-r from-sky-500 to-emerald-500 rounded-[2.5rem] p-10 md:p-14 text-center relative shadow-[0_15px_30px_-10px_rgba(16,185,129,0.3)] mb-8 overflow-hidden">
        
        {{-- Efek Dekorasi Latar Belakang --}}
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-20 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col items-center">
            
            {{-- Kotak Tanggal --}}
            <div class="w-24 h-24 bg-white rounded-[1.5rem] flex flex-col items-center justify-center mb-6 text-slate-800 shadow-xl border-4 border-white/20">
                <span class="text-[11px] font-black uppercase tracking-widest mb-1 text-slate-400">{{ $card['bulan'] }}</span>
                <span class="text-4xl font-black leading-none text-slate-800">{{ $card['hari'] }}</span>
            </div>

            {{-- Judul Kegiatan --}}
            <h1 class="text-3xl md:text-4xl font-black text-white leading-tight uppercase tracking-tight mb-6 drop-shadow-md">
                {{ $card['judul'] }}
            </h1>

            {{-- Badge Info Singkat --}}
            <div class="flex flex-wrap justify-center gap-3 mb-8">
                <span class="px-5 py-2 bg-white/20 text-white rounded-full text-[10px] font-black uppercase tracking-widest backdrop-blur-md border border-white/30 flex items-center gap-2 shadow-sm">
                    <i class="fas fa-users"></i> {{ $card['target_label'] }}
                </span>
                <span class="px-5 py-2 bg-white/20 text-white rounded-full text-[10px] font-black uppercase tracking-widest backdrop-blur-md border border-white/30 flex items-center gap-2 shadow-sm">
                    <i class="far fa-calendar-check"></i> {{ $card['kategori'] }}
                </span>
                <span class="px-5 py-2 {{ $card['is_past'] ? 'bg-rose-500/80 border-rose-400' : 'bg-emerald-500/80 border-emerald-400' }} text-white rounded-full text-[10px] font-black uppercase tracking-widest backdrop-blur-md border flex items-center gap-2 shadow-sm">
                    <i class="fas {{ $card['is_past'] ? 'fa-history' : 'fa-check-circle' }}"></i> {{ $card['status_label'] }}
                </span>
            </div>

            {{-- Tombol Kembali --}}
            <div class="flex justify-center">
                <a href="{{ route('user.jadwal.index') }}" class="px-8 py-3 bg-white/20 hover:bg-white/30 text-white rounded-full text-xs font-black uppercase tracking-widest transition-all border border-white/30 backdrop-blur-md flex items-center gap-3 hover:-translate-y-1 shadow-sm">
                    <i class="fas fa-arrow-left"></i> KEMBALI
                </a>
            </div>

        </div>
    </div>

    {{-- 2. KARTU DETAIL (Split 2 Kolom) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        {{-- Kartu Kiri: Informasi Pelaksanaan --}}
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-200 hover:border-sky-200 transition-colors">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-10 h-10 rounded-full bg-sky-50 text-sky-500 flex items-center justify-center border border-sky-100">
                    <i class="fas fa-info-circle text-lg"></i>
                </div>
                <h2 class="text-xs font-black text-slate-800 uppercase tracking-widest">Informasi Pelaksanaan</h2>
            </div>
            
            <div class="space-y-6">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Tanggal Lengkap</p>
                    <p class="text-sm font-bold text-slate-700">{{ $card['tanggal'] }}</p>
                </div>
                <div class="w-full h-px bg-slate-100"></div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Waktu Operasional</p>
                    <p class="text-sm font-bold text-slate-700">{{ $card['waktu'] }}</p>
                </div>
                <div class="w-full h-px bg-slate-100"></div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Lokasi Posyandu</p>
                    <p class="text-sm font-bold text-slate-700 break-words">{{ $card['lokasi'] }}</p>
                </div>
            </div>
        </div>

        {{-- Kartu Kanan: Catatan / Deskripsi --}}
        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-200 hover:border-emerald-200 transition-colors">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center border border-emerald-100">
                    <i class="fas fa-clipboard-list text-lg"></i>
                </div>
                <h2 class="text-xs font-black text-slate-800 uppercase tracking-widest">Catatan / Deskripsi</h2>
            </div>
            
            <div class="prose prose-sm prose-slate">
                <p class="text-sm font-medium text-slate-600 leading-relaxed whitespace-pre-wrap">{{ $card['deskripsi'] ?: 'Tidak ada catatan tambahan untuk jadwal ini.' }}</p>
            </div>
        </div>

    </div>

</div>
@endsection