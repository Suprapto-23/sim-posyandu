@extends('layouts.admin')

@section('title', 'Edit Data Bidan')
@section('page-name', 'Edit Bidan')
@section('page-title', 'Edit Akun Bidan')

@php
    $profile = $bidan->profile;
    $name = $profile?->full_name ?? $bidan->name ?? 'Bidan';
    $nik = $bidan->nik ?? $profile?->nik ?? '';
    $birthDate = old('tanggal_lahir', $profile?->tanggal_lahir ? \Carbon\Carbon::parse($profile->tanggal_lahir)->format('Y-m-d') : '');
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
    }

    .input-soft {
        width: 100%;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px 14px;
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        outline: none;
        transition: all .22s ease;
    }

    .input-soft:focus {
        background: #ffffff;
        border-color: #14b8a6;
        box-shadow: 0 0 0 4px rgba(20,184,166,.12);
    }

    .input-readonly {
        background: #f1f5f9;
        color: #64748b;
        cursor: not-allowed;
    }
</style>

<div class="max-w-4xl mx-auto animate-pop-in pb-12">

    <section class="bg-gradient-to-br from-sky-500 via-cyan-500 to-teal-500 rounded-[2.5rem] p-8 md:p-10 mb-8 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(14,165,233,.35)] border border-white/20 text-center">
        <div class="hero-grid absolute inset-0 opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[70px] rounded-full pointer-events-none"></div>

        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 text-white/85 text-[10px] font-black uppercase tracking-widest mb-4">
                <a href="{{ route('admin.bidans.index') }}" class="hover:text-white transition-colors">Daftar Bidan</a>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span class="text-white">Edit Data</span>
            </div>

            <h1 class="text-3xl md:text-4xl font-black text-white font-poppins tracking-tight">
                Edit Data Bidan
            </h1>

            <p class="text-sky-50 text-sm font-medium max-w-xl mx-auto mt-3 leading-relaxed">
                Perbarui profil bidan, kontak, alamat, dan status akses login.
            </p>
        </div>
    </section>

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 mb-6 text-sm font-bold text-rose-600 flex justify-center text-center gap-3 shadow-sm">
            <i class="fas fa-exclamation-circle text-lg"></i>
            Mohon periksa kembali isian form.
        </div>
    @endif

    <form action="{{ route('admin.bidans.update', $bidan->id) }}" method="POST" id="bidanForm">
        @csrf
        @method('PUT')

        <input type="hidden" name="nik" value="{{ old('nik', $nik) }}">
        <input type="hidden" name="email" value="{{ old('email', $bidan->email) }}">

        <section class="soft-card bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden mb-8">
            <div class="bg-slate-50/70 px-8 py-6 border-b border-slate-100 flex items-center justify-center">
                <h5 class="font-black text-slate-700 text-sm uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-id-badge text-teal-500"></i>
                    Perbarui Data Bidan
                </h5>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            NIK
                        </label>
                        <input type="text" value="{{ $nik ?: '-' }}" readonly class="input-soft input-readonly">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Email Login
                        </label>
                        <input type="email" value="{{ $bidan->email }}" readonly class="input-soft input-readonly">
                    </div>

                    <div class="md:col-span-2 space-y-2 border-t border-slate-100 pt-6">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $name) }}" required class="input-soft">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Jenis Kelamin <span class="text-rose-500">*</span>
                        </label>
                        <select name="jenis_kelamin" required class="input-soft cursor-pointer">
                            <option value="">Pilih jenis kelamin</option>
                            <option value="L" {{ old('jenis_kelamin', $profile?->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-Laki</option>
                            <option value="P" {{ old('jenis_kelamin', $profile?->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Nomor Telepon / WhatsApp
                        </label>
                        <input type="text" name="telepon" id="telepon" value="{{ old('telepon', $profile?->telepon) }}" maxlength="20" class="input-soft" placeholder="08xxxxxxxxxx">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Tempat Lahir
                        </label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $profile?->tempat_lahir) }}" class="input-soft">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Tanggal Lahir
                        </label>
                        <input type="date" name="tanggal_lahir" value="{{ $birthDate }}" class="input-soft">
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Alamat Lengkap
                        </label>
                        <input type="text" name="alamat" value="{{ old('alamat', $profile?->alamat) }}" class="input-soft" placeholder="Dusun, RT/RW, Desa">
                    </div>

                    <div class="md:col-span-2 space-y-2 border-t border-slate-100 pt-6">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Status Akses Login <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" required class="input-soft cursor-pointer">
                            <option value="active" {{ old('status', $bidan->status) === 'active' ? 'selected' : '' }}>Aktif, bidan dapat login</option>
                            <option value="inactive" {{ old('status', $bidan->status) === 'inactive' ? 'selected' : '' }}>Nonaktif, akses login dibatasi</option>
                        </select>
                    </div>

                </div>
            </div>
        </section>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('admin.bidans.index') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-700 transition-all shadow-sm text-sm text-center">
                <i class="fas fa-times mr-1"></i>
                Batal
            </a>

            <button type="submit" id="btnSubmit" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold text-white bg-teal-500 hover:bg-teal-600 hover:-translate-y-0.5 transition-all shadow-[0_4px_15px_rgba(20,184,166,.30)] text-sm flex items-center justify-center gap-2">
                <i class="fas fa-save"></i>
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
    const teleponInput = document.getElementById('telepon');
    const form = document.getElementById('bidanForm');
    const btnSubmit = document.getElementById('btnSubmit');

    teleponInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    form.addEventListener('submit', function() {
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        btnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
    });
</script>
@endsection