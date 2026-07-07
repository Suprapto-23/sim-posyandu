@extends('layouts.user')

@section('title', 'Detail Pesan Bidan')
@section('page_title', 'Detail Pesan Bidan')

@php
    $toneMap = [
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200', 'btn' => 'bg-emerald-500 hover:bg-emerald-600 shadow-emerald-500/30'],
        'rose'    => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-rose-200', 'btn' => 'bg-rose-500 hover:bg-rose-600 shadow-rose-500/30'],
        'sky'     => ['bg' => 'bg-sky-50', 'text' => 'text-sky-600', 'border' => 'border-sky-200', 'btn' => 'bg-sky-500 hover:bg-sky-600 shadow-sky-500/30'],
        'amber'   => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-200', 'btn' => 'bg-amber-500 hover:bg-amber-600 shadow-amber-500/30'],
        'violet'  => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'border' => 'border-violet-200', 'btn' => 'bg-violet-500 hover:bg-violet-600 shadow-violet-500/30'],
        'slate'   => ['bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'border' => 'border-slate-200', 'btn' => 'bg-slate-500 hover:bg-slate-600 shadow-slate-500/30'],
    ];
    
    $toneKey = $notifikasi->tone ?? 'emerald';
    $tone = $toneMap[$toneKey] ?? $toneMap['emerald'];
    $icon = $notifikasi->icon ?? 'fa-envelope-open-text';
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
    .animate-pop-in { animation: popIn .4s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popIn {
        from { opacity: 0; transform: scale(.95) translateY(15px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto animate-pop-in pb-20 px-4 sm:px-6 mt-6 space-y-5">

    {{-- TOMBOL KEMBALI --}}
    <div>
        <a href="{{ route('user.notifikasi.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/80 backdrop-blur-sm border border-slate-200 shadow-sm rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-all active:scale-95">
            <i class="fas fa-arrow-left"></i> Kembali ke Kotak Masuk
        </a>
    </div>

    {{-- KARTU DETAIL PESAN --}}
    <div class="bg-white rounded-[2rem] border border-slate-100 shadow-[0_15px_35px_-15px_rgba(0,0,0,0.08)] overflow-hidden">
        
        {{-- Header Area (Lebih Modern dengan Pola Grid) --}}
        <div class="{{ $tone['bg'] }} border-b {{ $tone['border'] }} p-8 sm:p-12 relative overflow-hidden flex flex-col sm:flex-row items-center sm:items-start gap-6">
            {{-- Background Pattern --}}
            <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient({{ $tone['border'] }} 1px, transparent 1px); background-size: 20px 20px;"></div>
            
            {{-- Ikon Bulat Besar --}}
            <div class="shrink-0 flex items-center justify-center h-20 w-20 rounded-[1.2rem] bg-white border {{ $tone['border'] }} {{ $tone['text'] }} text-3xl shadow-sm relative z-10">
                <i class="fas {{ $icon }}"></i>
            </div>
            
            {{-- Judul & Meta Info --}}
            <div class="relative z-10 flex-1 text-center sm:text-left">
                <div class="flex flex-wrap justify-center sm:justify-start gap-2 mb-3">
                    <span class="px-2.5 py-1 rounded-[6px] text-[8px] font-black uppercase tracking-widest bg-white border {{ $tone['border'] }} {{ $tone['text'] }} shadow-sm">
                        {{ $kategori }}
                    </span>
                    <span class="px-2.5 py-1 rounded-[6px] text-[8px] font-black uppercase tracking-widest bg-white border border-slate-200 text-slate-500 shadow-sm">
                        <i class="fas fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($notifikasi->created_at)->translatedFormat('d M Y • H:i') }}
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight leading-tight">
                    {{ $notifikasi->judul }}
                </h1>
                <p class="text-[11px] font-bold text-slate-500 mt-2 uppercase tracking-wide">
                    Pengirim: <span class="text-slate-700">Bidan / Kader Posyandu</span>
                </p>
            </div>
        </div>

        {{-- Isi Pesan Utama --}}
        <div class="p-8 sm:p-12">
            <div class="prose prose-sm sm:prose-base prose-slate max-w-none text-slate-700 font-medium leading-relaxed bg-slate-50/50 p-6 rounded-2xl border border-slate-100">
                {!! nl2br(e($notifikasi->pesan)) !!}
            </div>

            {{-- UX INTEGRATION: Jembatan ke Fitur Agenda (Call to Action) --}}
            @if(strtolower($kategori) === 'jadwal' || str_contains(strtolower($notifikasi->judul), 'jadwal'))
            <div class="mt-8 p-6 bg-white border border-slate-200 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-5 shadow-sm">
                <div class="flex items-center gap-4 text-center sm:text-left">
                    <div class="h-12 w-12 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 text-lg shrink-0">
                        <i class="fa-regular fa-calendar-check"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-800">Cek Detail Agenda</h4>
                        <p class="text-[11px] font-semibold text-slate-500 mt-1">Lihat panduan lengkap, lokasi, dan sasaran di halaman Agenda Posyandu.</p>
                    </div>
                </div>
                {{-- Pastikan route 'user.jadwal.index' sesuai dengan nama route Anda --}}
                <a href="{{ route('user.jadwal.index') }}" class="shrink-0 w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-3 {{ $tone['btn'] }} text-white text-[11px] font-black uppercase tracking-widest rounded-[12px] shadow-md hover:-translate-y-0.5 transition-all">
                    Buka Agenda <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection