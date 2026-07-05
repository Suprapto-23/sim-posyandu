@extends('layouts.user')

@section('title', 'Detail Pesan Bidan')
@section('page_title', 'Detail Pesan Bidan')

@php
    // Menyiapkan warna tema (tone) berdasarkan data dari database. 
    // Jika di tabel database Anda tidak ada kolom 'tone', maka otomatis menggunakan 'emerald'.
    $toneMap = [
        'emerald' => 'text-emerald-600 bg-emerald-50 border-emerald-200',
        'rose'    => 'text-rose-600 bg-rose-50 border-rose-200',
        'sky'     => 'text-sky-600 bg-sky-50 border-sky-200',
        'amber'   => 'text-amber-600 bg-amber-50 border-amber-200',
        'violet'  => 'text-violet-600 bg-violet-50 border-violet-200',
        'slate'   => 'text-slate-600 bg-slate-50 border-slate-200',
    ];
    
    // Ambil tone dari database (atau fallback ke emerald)
    $toneKey = $notifikasi->tone ?? 'emerald';
    $toneClass = $toneMap[$toneKey] ?? $toneMap['emerald'];

    // Menyiapkan Ikon
    $icon = $notifikasi->icon ?? 'fa-envelope-open-text';
    
    // Kategori/Label
    $kategori = $notifikasi->label ?? 'Informasi';
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
        animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }

    @keyframes popIn {
        from { opacity: 0; transform: scale(.96) translateY(12px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .btn-pill {
        border-radius: 12px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.97); }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto animate-pop-in pb-20 px-4 sm:px-6 mt-6 space-y-6">

    {{-- 1. TOMBOL KEMBALI --}}
    <div class="flex items-center justify-between mb-2">
        <a href="{{ route('user.notifikasi.index') }}" class="btn-pill inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 shadow-sm text-[11px] font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50 hover:text-slate-700">
            <i class="fas fa-arrow-left"></i> Kembali ke Kotak Masuk
        </a>
    </div>

    {{-- 2. KARTU DETAIL PESAN --}}
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-[0_10px_30px_-15px_rgba(0,0,0,0.1)] overflow-hidden">
        
        {{-- Header Pesan --}}
        <div class="bg-slate-50/70 border-b border-slate-100 p-8 sm:p-10 text-center relative overflow-hidden">
            {{-- Efek cahaya/blur di background --}}
            <div class="absolute -right-10 -top-10 w-32 h-32 bg-emerald-100/60 blur-3xl rounded-full"></div>
            <div class="absolute -left-10 -bottom-10 w-32 h-32 bg-teal-100/50 blur-3xl rounded-full"></div>
            
            {{-- Ikon Bulat Besar --}}
            <div class="inline-flex items-center justify-center h-20 w-20 rounded-[1.5rem] mb-5 border shadow-sm {{ $toneClass }} text-3xl relative z-10">
                <i class="fas {{ $icon }}"></i>
            </div>
            
            {{-- Lencana Kategori & Tanggal --}}
            <div class="relative z-10 flex flex-wrap justify-center gap-2 mb-4">
                <span class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest border border-slate-200 bg-white text-slate-500 shadow-sm">
                    Kategori: {{ $kategori }}
                </span>
                <span class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest border border-slate-200 bg-white text-slate-500 shadow-sm">
                    <i class="fas fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($notifikasi->created_at)->translatedFormat('d F Y, H:i') }} WIB
                </span>
            </div>

            {{-- Judul Pesan --}}
            <h1 class="text-2xl sm:text-3xl font-black text-slate-800 font-poppins tracking-tight relative z-10 max-w-2xl mx-auto leading-snug">
                {{ $notifikasi->judul }}
            </h1>
        </div>

        {{-- Isi Pesan Utama --}}
        <div class="p-8 sm:p-12">
            <div class="prose prose-sm sm:prose-base prose-slate max-w-none text-slate-700 font-medium leading-relaxed">
                {{-- Menampilkan teks, mengubah Enter menjadi <br> agar rapi --}}
                {!! nl2br(e($notifikasi->pesan)) !!}
            </div>
        </div>

        {{-- Footer Detail Pengirim & Status --}}
        <div class="bg-slate-50/50 border-t border-slate-100 p-6 sm:px-12 flex flex-col sm:flex-row items-center justify-between gap-4">
            
            {{-- Info Pengirim --}}
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 border border-emerald-200">
                    <i class="fas fa-user-nurse"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Pengirim</p>
                    <p class="text-sm font-bold text-slate-700">Bidan Posyandu</p>
                </div>
            </div>

            {{-- Status Baca --}}
            <span class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 text-[10px] font-black uppercase tracking-widest shadow-sm">
                <i class="fas fa-check-double text-emerald-500"></i> Telah Dibaca
            </span>

        </div>
    </div>
</div>
@endsection