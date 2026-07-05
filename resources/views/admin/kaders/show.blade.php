@extends('layouts.admin')
@section('title', 'Detail Profil Kader')
@section('page-name', 'Profil Kader')

@section('content')
<style>
    .animate-pop-in { animation: popIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    @keyframes popIn { 0% { opacity: 0; transform: scale(0.95) translateY(10px); } 100% { opacity: 1; transform: scale(1) translateY(0); } }
</style>

<div class="max-w-5xl mx-auto animate-pop-in">

    {{-- Hero Section Clean --}}
    <div class="bg-gradient-to-br from-sky-500 to-indigo-500 rounded-[2.5rem] p-8 md:p-12 mb-8 relative overflow-hidden shadow-lg border border-white/20 flex flex-col items-center justify-center text-center group">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
        
        <div class="relative z-10 w-full flex flex-col items-center">
            {{-- Avatar Besar Premium (TIDAK KOSONG) --}}
            <div class="w-28 h-28 rounded-[2rem] bg-white border-4 border-white/50 text-indigo-500 flex items-center justify-center font-black text-5xl shadow-xl mb-5 group-hover:scale-105 transition-transform duration-500">
                {{ strtoupper(substr($kader->profile->full_name ?? $kader->name, 0, 1)) }}
            </div>
            
            <h2 class="text-3xl md:text-4xl font-black text-white font-poppins tracking-tight text-shadow-sm">
                {{ $kader->profile->full_name ?? $kader->name }}
            </h2>
            
            {{-- Badge Jabatan, Status & NIK --}}
            <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                <span class="bg-white/20 backdrop-blur-md border border-white/30 text-white font-bold text-[11px] uppercase tracking-widest px-4 py-1.5 rounded-full shadow-sm">
                    <i class="fas fa-id-badge mr-1"></i> {{ $kader->kader?->jabatan ?? 'Kader Biasa' }}
                </span>
                <span class="bg-white/20 backdrop-blur-md border border-white/30 text-white font-mono text-[11px] font-bold px-4 py-1.5 rounded-full flex items-center gap-2 shadow-sm">
                    <i class="fas fa-id-card"></i> NIK: {{ $kader->nik ?? $kader->profile?->nik ?? '-' }}
                </span>
                @if($kader->status === 'active')
                    <span class="bg-emerald-400/90 backdrop-blur-md text-white font-bold text-[11px] uppercase tracking-widest px-4 py-1.5 rounded-full shadow-sm border border-emerald-300/50"><i class="fas fa-check-circle mr-1"></i> Aktif</span>
                @else
                    <span class="bg-rose-500/90 backdrop-blur-md text-white font-bold text-[11px] uppercase tracking-widest px-4 py-1.5 rounded-full shadow-sm border border-rose-400/50"><i class="fas fa-ban mr-1"></i> Nonaktif</span>
                @endif
            </div>

            {{-- Tombol Aksi --}}
            <div class="mt-8 flex gap-3">
                <a href="{{ route('admin.kaders.index') }}" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 text-white text-[11px] uppercase tracking-widest font-bold px-6 py-3 rounded-xl transition-all smooth-route"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
                <a href="{{ route('admin.kaders.edit', $kader->id) }}" class="bg-white hover:bg-slate-50 text-indigo-600 text-[11px] uppercase tracking-widest font-bold px-6 py-3 rounded-xl transition-all shadow-lg hover:-translate-y-0.5 smooth-route"><i class="fas fa-edit mr-1"></i> Edit Profil</a>
            </div>
        </div>
    </div>

    {{-- Grid 2 Kolom untuk Biodata & Sistem --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
        
        {{-- Card 1: Biodata Pribadi --}}
        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm p-8 hover:shadow-md hover:border-slate-200 transition-all">
            <div class="flex items-center gap-4 mb-6 border-b border-slate-50 pb-4">
                <div class="w-12 h-12 rounded-[1rem] bg-indigo-50 text-indigo-500 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-user"></i>
                </div>
                <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest font-poppins">Biodata Diri</h4>
            </div>
            
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-slate-50 pb-3 gap-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Jenis Kelamin</span>
                    <span class="text-[13px] font-black text-slate-700">{{ ($kader->profile?->jenis_kelamin == 'L') ? 'Laki-Laki' : (($kader->profile?->jenis_kelamin == 'P') ? 'Perempuan' : '-') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-slate-50 pb-3 gap-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Tempat Lahir</span>
                    <span class="text-[13px] font-black text-slate-700">{{ $kader->profile?->tempat_lahir ?? '-' }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-slate-50 pb-3 gap-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Tanggal Lahir</span>
                    <span class="text-[13px] font-black text-slate-700">{{ $kader->profile?->tanggal_lahir ? \Carbon\Carbon::parse($kader->profile->tanggal_lahir)->translatedFormat('d F Y') : '-' }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-slate-50 pb-3 gap-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Usia</span>
                    <span class="text-[13px] font-black text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ $kader->profile?->tanggal_lahir ? \Carbon\Carbon::parse($kader->profile->tanggal_lahir)->age . ' Tahun' : '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Informasi Kontak & Sistem --}}
        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm p-8 hover:shadow-md hover:border-slate-200 transition-all">
            <div class="flex items-center gap-4 mb-6 border-b border-slate-50 pb-4">
                <div class="w-12 h-12 rounded-[1rem] bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-desktop"></i>
                </div>
                <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest font-poppins">Akses & Kontak</h4>
            </div>
            
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-slate-50 pb-3 gap-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Email (Login)</span>
                    <span class="text-[13px] font-black text-slate-700 break-all">{{ $kader->email }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between border-b border-slate-50 pb-3 gap-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Telepon / WhatsApp</span>
                    <span class="text-[13px] font-black text-slate-700">{{ $kader->profile?->telepon ?? '-' }}</span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">Alamat Lengkap</span>
                    <span class="text-[13px] font-black text-slate-700 leading-relaxed">{{ $kader->profile?->alamat ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection