@extends('layouts.kader')

@section('title', 'Kader Identity Center')
@section('page-name', 'Pusat Identitas & Akreditasi')

@php
    use Carbon\Carbon;
    Carbon::setLocale('id');

    $tanggalLahir = optional($user->profile)->tanggal_lahir 
        ? Carbon::parse($user->profile->tanggal_lahir)->translatedFormat('d F Y') 
        : '-';

    $jenisKelamin = match($user->profile->jenis_kelamin ?? null) {
        'L' => 'Laki-Laki',
        'P' => 'Perempuan',
        default => '-',
    };
@endphp

@push('styles')
<style>
    /* NEXUS ANIMATION SYSTEM */
    .fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    
    /* GLASS CARD PREMIUM (Emerald Shadow) */
    .nexus-glass { 
        background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px); 
        border: 1px solid rgba(255, 255, 255, 0.8); 
        box-shadow: 0 10px 40px -10px rgba(16, 185, 129, 0.05); 
        border-radius: 32px;
        transition: all 0.4s ease;
    }
    
    /* READ-ONLY DISPLAY BOX */
    .display-nexus {
        background-color: rgba(248, 250, 252, 0.8);
        border: 1px solid #f1f5f9;
        border-radius: 18px;
        padding: 14px 20px;
        display: flex;
        align-items: flex-start;
        font-size: 13px;
        color: #1e293b;
        transition: all 0.3s ease;
    }
    .display-nexus:hover {
        background-color: #ffffff;
        border-color: #d1fae5; /* Emerald 100 */
        box-shadow: 0 8px 25px -5px rgba(16, 185, 129, 0.08); /* Emerald Shadow */
        transform: translateY(-2px);
    }
    .display-icon {
        color: #10b981; /* Emerald 500 */
        font-size: 16px;
        width: 24px;
        text-align: center;
        margin-right: 14px;
        margin-top: 2px;
    }
</style>
@endpush

@section('content')
<div class="max-w-[1250px] mx-auto relative pb-16 fade-in-up mt-6">

    {{-- Latar Belakang Dekoratif (Emerald) --}}
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-gradient-to-br from-emerald-50/50 to-transparent rounded-full blur-3xl pointer-events-none z-0"></div>

    {{-- 1. HEADER BANNER --}}
    <div class="flex items-center gap-5 mb-10 relative z-10">
        <div class="w-16 h-16 rounded-[24px] bg-emerald-500 text-white flex items-center justify-center text-3xl shadow-[0_10px_25px_rgba(16,185,129,0.35)] transform -rotate-3">
            <i class="fas fa-id-badge"></i>
        </div>
        <div>
            <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight font-poppins">Identity Center</h1>
            <p class="text-slate-500 font-medium text-sm mt-1">Otorisasi & kredensial petugas medis Posyandu Bantarkulon.</p>
        </div>
    </div>

    {{-- 2. GRID UTAMA (KIRI DAN KANAN AKAN SEJAJAR TINGGINYA) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 relative z-10 items-stretch">
        
        {{-- KIRI: VIRTUAL ID CARD --}}
        <div class="lg:col-span-4 nexus-glass relative overflow-hidden flex flex-col h-full">
            
            {{-- Banner Atas (Emerald & Teal Gradient) --}}
            <div class="absolute top-0 left-0 w-full h-36 bg-gradient-to-br from-emerald-600 via-teal-500 to-teal-600 z-0"></div>
            <div class="absolute top-4 right-4 text-white/20 text-3xl z-0"><i class="fas fa-qrcode"></i></div>
            
            {{-- Bagian Atas: Avatar & Nama --}}
            <div class="relative z-10 p-8 flex flex-col items-center flex-1">
                <div class="w-36 h-36 mx-auto rounded-[28px] bg-white p-2.5 shadow-2xl mb-6 mt-6 transform rotate-2 hover:rotate-0 transition-transform duration-500 border border-emerald-100 shrink-0">
                    <div class="w-full h-full rounded-[20px] bg-slate-50 flex items-center justify-center text-6xl font-black text-emerald-400 border border-slate-100">
                        {{ strtoupper(substr($user->profile->full_name ?? $user->name ?? 'K', 0, 1)) }}
                    </div>
                </div>

                <h3 class="text-2xl font-black text-slate-800 font-poppins leading-tight mb-2 text-center">{{ $user->profile->full_name ?? $user->name }}</h3>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-50 text-emerald-600 rounded-full border border-emerald-200 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[10px] font-black uppercase tracking-[0.1em]">Kader Aktif</span>
                </div>
            </div>

            {{-- Bagian Bawah: Info Kontak --}}
            <div class="relative z-10 p-8 pt-0 mt-auto w-full">
                <div class="space-y-4">
                    <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-100 flex items-center gap-4 hover:bg-white hover:border-emerald-100 hover:shadow-md transition-all">
                        <div class="w-12 h-12 rounded-[14px] bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-sm shrink-0 border border-emerald-100"><i class="fas fa-envelope text-[15px]"></i></div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Email Terdaftar</p>
                            <p class="text-[13px] font-bold text-slate-700 truncate mt-0.5">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-100 flex items-center gap-4 hover:bg-white hover:border-teal-100 hover:shadow-md transition-all">
                        <div class="w-12 h-12 rounded-[14px] bg-teal-50 text-teal-600 flex items-center justify-center shadow-sm shrink-0 border border-teal-100"><i class="fas fa-id-badge text-[15px]"></i></div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Nomor Petugas</p>
                            <p class="text-[13px] font-bold text-slate-700 truncate mt-0.5">#{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KANAN: BIODATA LENGKAP (READ-ONLY) --}}
        <div class="lg:col-span-8 nexus-glass overflow-hidden flex flex-col h-full">
            <div class="px-8 py-6 border-b border-slate-100 bg-white/50 flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl border border-emerald-100 shadow-sm"><i class="fas fa-address-card"></i></div>
                <div>
                    <h3 class="text-xl font-black text-slate-800 font-poppins leading-none">Biodata Petugas</h3>
                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mt-1.5">Informasi Profil Mendalam</p>
                </div>
            </div>

            <div class="flex flex-col flex-1">
                <div class="p-8 space-y-8 flex-1">
                    {{-- Row 1: Nama & NIK --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 pl-1">Nama Lengkap</label>
                            <div class="display-nexus">
                                <i class="fas fa-user display-icon"></i>
                                <span class="font-bold">{{ $user->profile->full_name ?? $user->name ?? '-' }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 pl-1">Nomor Induk Kependudukan (NIK)</label>
                            <div class="display-nexus">
                                <i class="fas fa-id-card display-icon"></i>
                                <span class="font-bold">{{ $user->profile->nik ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Row 2: Kontak --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 pl-1">Email Terdaftar</label>
                            <div class="display-nexus">
                                <i class="fas fa-envelope display-icon"></i>
                                <span class="font-bold">{{ $user->email ?? '-' }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 pl-1">Nomor Seluler / WhatsApp</label>
                            <div class="display-nexus">
                                <i class="fas fa-phone-alt display-icon"></i>
                                <span class="font-bold">{{ $user->profile->telepon ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="h-px w-full border-t border-dashed border-slate-200"></div>

                    {{-- Row 3: TTL & JK --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 pl-1">Tempat Lahir</label>
                            <div class="display-nexus">
                                <i class="fas fa-map-marker-alt display-icon"></i>
                                <span class="font-bold">{{ $user->profile->tempat_lahir ?? '-' }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 pl-1">Tanggal Lahir</label>
                            <div class="display-nexus">
                                <i class="fas fa-calendar-alt display-icon"></i>
                                <span class="font-bold">{{ $tanggalLahir }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 pl-1">Jenis Kelamin</label>
                            <div class="display-nexus">
                                <i class="fas fa-venus-mars display-icon"></i>
                                <span class="font-bold">{{ $jenisKelamin }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Row 4: Alamat --}}
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2.5 pl-1">Domisili Lengkap</label>
                        <div class="display-nexus min-h-[80px]">
                            <i class="fas fa-home display-icon"></i>
                            <span class="font-bold leading-relaxed">{{ $user->profile->alamat ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- FOOTER KANAN --}}
                <div class="px-8 py-5 bg-slate-50/80 border-t border-slate-100 rounded-b-[32px] flex items-center justify-center">
                    <div class="flex items-center gap-3 text-slate-500">
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center border border-slate-200 shadow-sm shrink-0">
                            <i class="fas fa-shield-check text-[11px] text-emerald-500"></i>
                        </div>
                        <p class="text-[11px] font-bold italic">Data identitas dikelola dan dilindungi oleh sistem terpusat Posyandu.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection