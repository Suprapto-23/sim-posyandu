@extends('layouts.admin')

@section('title', 'Detail Warga')
@section('page-name', 'Detail Profil Warga')
@section('page-title', 'Detail Akun Warga')

@php
    use Illuminate\Support\Str;

    $profile = $user->profile;

    $name = $profile?->full_name ?? $user->name ?? 'Warga';
    $nik = $user->nik ?? $profile?->nik ?? '-';
    $phone = $profile?->telepon ?? '-';
    $address = $profile?->alamat ?? '-';
    $initial = Str::upper(Str::substr(trim($name), 0, 1)) ?: 'W';

    $gender = match ($profile?->jenis_kelamin) {
        'L' => 'Laki-Laki',
        'P' => 'Perempuan',
        default => '-',
    };

    $birthDate = $profile?->tanggal_lahir
        ? \Carbon\Carbon::parse($profile->tanggal_lahir)
        : null;
@endphp

@section('content')
<style>
    .animate-pop-in {
        animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards;
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

    .soft-card {
        background: rgba(255,255,255,.92);
        backdrop-filter: blur(18px);
        transition: all .25s ease;
    }

    .soft-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 40px -30px rgba(15,23,42,.30);
    }

    .info-row {
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
    }

    @media (min-width: 640px) {
        .info-row {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }
    }
</style>

<div class="max-w-5xl mx-auto animate-pop-in pb-12">

    <section class="bg-gradient-to-br from-blue-600 via-sky-500 to-emerald-400 rounded-[2.5rem] p-8 md:p-12 mb-8 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(59,130,246,.35)] border border-white/20 text-center">
        <div class="hero-grid absolute inset-0 opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/15 blur-[80px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col items-center">
            <div class="w-28 h-28 rounded-[2rem] bg-white border-4 border-white/50 text-blue-500 flex items-center justify-center font-black text-5xl shadow-xl mb-5">
                {{ $initial }}
            </div>

            <h1 class="text-3xl md:text-4xl font-black text-white font-poppins tracking-tight">
                {{ $name }}
            </h1>

            <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                <span class="bg-white/20 backdrop-blur-md border border-white/30 text-white font-bold text-[11px] uppercase tracking-widest px-4 py-1.5 rounded-full shadow-sm">
                    <i class="fas fa-users mr-1"></i>
                    Warga Posyandu
                </span>

                <span class="bg-white/20 backdrop-blur-md border border-white/30 text-white font-mono text-[11px] font-bold px-4 py-1.5 rounded-full flex items-center gap-2 shadow-sm">
                    <i class="fas fa-id-card"></i>
                    NIK: {{ $nik }}
                </span>

                @if($user->status === 'active')
                    <span class="bg-emerald-400/90 backdrop-blur-md text-white font-bold text-[11px] uppercase tracking-widest px-4 py-1.5 rounded-full shadow-sm border border-emerald-300/50">
                        <i class="fas fa-check-circle mr-1"></i>
                        Aktif
                    </span>
                @else
                    <span class="bg-rose-500/90 backdrop-blur-md text-white font-bold text-[11px] uppercase tracking-widest px-4 py-1.5 rounded-full shadow-sm border border-rose-400/50">
                        <i class="fas fa-ban mr-1"></i>
                        Nonaktif
                    </span>
                @endif
            </div>

            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('admin.users.index') }}" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 text-white text-[11px] uppercase tracking-widest font-bold px-6 py-3 rounded-xl transition-all">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Kembali
                </a>

                <a href="{{ route('admin.users.edit', $user->id) }}" class="bg-white hover:bg-slate-50 text-blue-600 text-[11px] uppercase tracking-widest font-bold px-6 py-3 rounded-xl transition-all shadow-lg hover:-translate-y-0.5">
                    <i class="fas fa-edit mr-1"></i>
                    Edit Profil
                </a>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

        <div class="soft-card bg-white rounded-[2.5rem] border border-slate-100 shadow-sm p-8">
            <div class="flex items-center gap-4 mb-6 border-b border-slate-50 pb-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-user"></i>
                </div>

                <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest font-poppins">
                    Biodata Pribadi
                </h4>
            </div>

            <div class="space-y-4">
                <div class="info-row">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Nama Lengkap</span>
                    <span class="text-[13px] font-black text-slate-700">{{ $name }}</span>
                </div>

                <div class="info-row">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Jenis Kelamin</span>
                    <span class="text-[13px] font-black text-slate-700">{{ $gender }}</span>
                </div>

                <div class="info-row">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Tempat Lahir</span>
                    <span class="text-[13px] font-black text-slate-700">{{ $profile?->tempat_lahir ?? '-' }}</span>
                </div>

                <div class="info-row">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Tanggal Lahir</span>
                    <span class="text-[13px] font-black text-slate-700">
                        {{ $birthDate ? $birthDate->translatedFormat('d F Y') : '-' }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Usia</span>
                    <span class="text-[13px] font-black text-blue-600 bg-blue-50 px-2 py-0.5 rounded">
                        {{ $birthDate ? $birthDate->age . ' Tahun' : '-' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="soft-card bg-white rounded-[2.5rem] border border-slate-100 shadow-sm p-8">
            <div class="flex items-center gap-4 mb-6 border-b border-slate-50 pb-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-id-card"></i>
                </div>

                <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest font-poppins">
                    Akses & Kontak
                </h4>
            </div>

            <div class="space-y-4">
                <div class="info-row">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">NIK Login</span>
                    <span class="text-[13px] font-black text-slate-700 font-mono">{{ $nik }}</span>
                </div>

                <div class="info-row">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Telepon / WhatsApp</span>
                    <span class="text-[13px] font-black text-slate-700">{{ $phone }}</span>
                </div>

                <div class="flex flex-col border-b border-slate-50 pb-3 gap-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">
                        Alamat Lengkap
                    </span>
                    <span class="text-[13px] font-black text-slate-700 leading-relaxed">
                        {{ $address }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Status Akun</span>
                    <span class="text-[13px] font-black {{ $user->status === 'active' ? 'text-emerald-600' : 'text-rose-500' }}">
                        {{ $user->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Akun Dibuat</span>
                    <span class="text-[13px] font-black text-slate-500">
                        {{ $user->created_at ? $user->created_at->translatedFormat('d M Y, H:i') : '-' }}
                    </span>
                </div>
            </div>
        </div>

    </section>

    <section class="bg-blue-50 border border-blue-100 rounded-[2rem] p-6 shadow-sm relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-32 h-32 bg-blue-200/50 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-white text-blue-600 flex items-center justify-center text-xl shrink-0 shadow-sm">
                <i class="fas fa-info-circle"></i>
            </div>

            <div>
                <h5 class="text-sm font-black text-blue-800 mb-1.5 tracking-wide">
                    Fungsi Akun Warga
                </h5>

                <p class="text-[12px] text-blue-700/80 font-medium leading-relaxed">
                    Akun warga digunakan untuk akses login dan monitoring data kesehatan pada sisi warga.
                    Data sasaran seperti Balita, Remaja, dan Lansia tetap dikelola melalui modul data sasaran oleh Kader.
                </p>
            </div>
        </div>
    </section>

</div>
@endsection