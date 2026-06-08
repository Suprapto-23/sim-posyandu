@extends('layouts.admin')

@section('title', 'Edit User Warga')
@section('page-name', 'Edit Warga')
@section('page-title', 'Edit Akun Warga')

@php
    $profile = $user->profile;
    $name = $profile?->full_name ?? $user->name ?? 'Warga';
    $nik = $user->nik ?? $profile?->nik ?? '';
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

    .soft-card {
        background: rgba(255,255,255,.92);
        backdrop-filter: blur(18px);
        transition: all .25s ease;
    }

    .input-soft {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px 14px;
        width: 100%;
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        outline: none;
        transition: all .22s ease;
    }

    .input-soft:focus {
        background: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59,130,246,.10);
    }
</style>

<div class="max-w-4xl mx-auto animate-pop-in pb-12">

    <section class="bg-gradient-to-br from-blue-600 via-sky-500 to-emerald-400 rounded-[2.5rem] p-8 md:p-10 mb-8 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(59,130,246,.35)] border border-white/20 text-center">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[70px] rounded-full pointer-events-none"></div>

        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 text-white/85 text-[10px] font-black uppercase tracking-widest mb-4">
                <a href="{{ route('admin.users.index') }}" class="hover:text-white transition-colors">Daftar Warga</a>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span class="text-white">Edit Data</span>
            </div>

            <h1 class="text-3xl md:text-4xl font-black text-white font-poppins tracking-tight">
                Edit Data Warga
            </h1>

            <p class="text-blue-50 text-sm font-medium max-w-xl mx-auto mt-3 leading-relaxed">
                Perbarui identitas warga, nomor telepon, alamat, dan status akses login.
            </p>
        </div>
    </section>

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 mb-6 text-sm font-bold text-rose-600 flex justify-center text-center gap-3 shadow-sm">
            <i class="fas fa-exclamation-circle text-lg"></i>
            Mohon periksa kembali isian form.
        </div>
    @endif

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" id="wargaForm">
        @csrf
        @method('PUT')

        <input type="hidden" name="email" id="email" value="{{ old('email', $user->email ?? ($nik ? $nik . '@posyandu.user' : '')) }}">

        <section class="soft-card bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden mb-8">
            <div class="bg-slate-50/70 px-8 py-6 border-b border-slate-100 flex items-center justify-center">
                <h5 class="font-black text-slate-700 text-sm uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-id-card-alt text-blue-500"></i>
                    Perbarui Data Warga
                </h5>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Nomor Induk Kependudukan (NIK) <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="nik"
                            id="nik"
                            value="{{ old('nik', $nik) }}"
                            maxlength="16"
                            required
                            class="input-soft"
                        >
                        <p class="text-[10px] font-medium text-slate-400">
                            Jika NIK diubah, ID login warga akan ikut diperbarui.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $name) }}"
                            required
                            class="input-soft"
                        >
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
                            Tempat Lahir
                        </label>
                        <input
                            type="text"
                            name="tempat_lahir"
                            value="{{ old('tempat_lahir', $profile?->tempat_lahir) }}"
                            class="input-soft"
                            placeholder="Contoh: Pekalongan"
                        >
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Tanggal Lahir
                        </label>
                        <input
                            type="date"
                            name="tanggal_lahir"
                            value="{{ $birthDate }}"
                            class="input-soft"
                        >
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Nomor Telepon / WhatsApp
                        </label>
                        <input
                            type="text"
                            name="telepon"
                            id="telepon"
                            value="{{ old('telepon', $profile?->telepon) }}"
                            maxlength="20"
                            class="input-soft"
                            placeholder="08xxxxxxxxxx"
                        >
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Alamat Lengkap
                        </label>
                        <input
                            type="text"
                            name="alamat"
                            value="{{ old('alamat', $profile?->alamat) }}"
                            class="input-soft"
                            placeholder="Dusun, RT/RW, Desa"
                        >
                    </div>

                    <div class="md:col-span-2 space-y-2 border-t border-slate-100 pt-6">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Status Akses Login <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" required class="input-soft cursor-pointer">
                            <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Aktif, warga dapat login</option>
                            <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Nonaktif, akses login dibatasi</option>
                        </select>
                    </div>

                </div>
            </div>
        </section>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('admin.users.index') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-700 transition-all shadow-sm text-sm text-center">
                <i class="fas fa-times mr-1"></i>
                Batal
            </a>

            <button type="submit" id="btnSubmit" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 hover:-translate-y-0.5 transition-all shadow-[0_4px_15px_rgba(37,99,235,.30)] text-sm flex items-center justify-center gap-2">
                <i class="fas fa-save"></i>
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
    const nikInput = document.getElementById('nik');
    const teleponInput = document.getElementById('telepon');
    const emailInput = document.getElementById('email');
    const form = document.getElementById('wargaForm');
    const btnSubmit = document.getElementById('btnSubmit');

    function onlyNumber(input) {
        input.value = input.value.replace(/[^0-9]/g, '');
    }

    function syncSystemEmail() {
        const nik = nikInput.value.trim();
        emailInput.value = nik ? nik + '@posyandu.user' : '';
    }

    nikInput.addEventListener('input', function() {
        onlyNumber(this);
        syncSystemEmail();
    });

    teleponInput.addEventListener('input', function() {
        onlyNumber(this);
    });

    form.addEventListener('submit', function() {
        syncSystemEmail();

        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        btnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
    });

    syncSystemEmail();
</script>
@endsection