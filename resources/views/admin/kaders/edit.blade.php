@extends('layouts.admin')
@section('title', 'Edit Data Kader')
@section('page-name', 'Edit Kader')

@php
    $profile = $kader->profile;
    $kaderData = $kader->kader;

    $nama = old('full_name', $profile?->full_name ?? $kader->name);
    $nik = old('nik', $kader->nik ?? $profile?->nik);
    $tanggalLahir = old('tanggal_lahir', $profile?->tanggal_lahir ? \Carbon\Carbon::parse($profile->tanggal_lahir)->format('Y-m-d') : null);
    $tanggalBergabung = old('tanggal_bergabung', $kaderData?->tanggal_bergabung ? \Carbon\Carbon::parse($kaderData->tanggal_bergabung)->format('Y-m-d') : date('Y-m-d'));
@endphp

@section('content')
<style>
    .animate-pop-in { animation: popIn .4s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popIn { from { opacity: 0; transform: scale(.95) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    .input-nexus {
        width: 100%;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px 16px;
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        outline: none;
        transition: all .22s ease;
    }
    .input-nexus:focus {
        background: #fff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59,130,246,.10);
    }
</style>

<div class="max-w-4xl mx-auto animate-pop-in pb-10">

    <div class="bg-gradient-to-br from-blue-600 via-sky-500 to-emerald-400 rounded-[2.5rem] p-8 md:p-10 mb-8 relative overflow-hidden shadow-lg border border-white/20 text-center">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 right-0 w-48 h-48 bg-white/10 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 text-white/80 text-[10px] font-black uppercase tracking-widest mb-3">
                <a href="{{ route('admin.kaders.index') }}" class="hover:text-white transition-colors">Daftar Kader</a>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span class="text-white">Edit Data</span>
            </div>

            <h2 class="text-3xl font-black text-white font-poppins tracking-tight">
                <i class="fas fa-user-edit mr-2"></i> Edit Data Kader
            </h2>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 mb-6 text-sm font-bold text-rose-600 flex justify-center text-center gap-3 shadow-sm">
            <i class="fas fa-exclamation-circle text-lg"></i>
            Mohon periksa kembali isian form.
        </div>
    @endif

    <form action="{{ route('admin.kaders.update', $kader->id) }}" method="POST" id="kaderForm">
        @csrf
        @method('PUT')

        <input type="hidden" name="jabatan" value="Kader">
        <input type="hidden" name="name" id="nameMirror" value="{{ old('name', $nama) }}">

        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden mb-8">
            <div class="bg-slate-50/50 px-8 py-6 border-b border-slate-50 flex items-center justify-center">
                <h5 class="font-black text-slate-700 text-sm uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-id-badge text-blue-500"></i>
                    Perbarui Data Kader
                </h5>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">NIK <span class="text-rose-500">*</span></label>
                        <input type="text" name="nik" id="nik" value="{{ $nik }}" maxlength="16" required class="input-nexus">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="full_name" id="fullName" value="{{ $nama }}" required class="input-nexus">
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Email Login <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $kader->email) }}" required class="input-nexus">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Jenis Kelamin <span class="text-rose-500">*</span></label>
                        <select name="jenis_kelamin" required class="input-nexus cursor-pointer">
                            <option value="">Pilih jenis kelamin</option>
                            <option value="L" {{ old('jenis_kelamin', $profile?->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-Laki</option>
                            <option value="P" {{ old('jenis_kelamin', $profile?->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Jabatan</label>
                        <input type="text" value="Kader" readonly class="input-nexus bg-slate-100 text-slate-500 cursor-not-allowed">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $profile?->tempat_lahir) }}" class="input-nexus">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="{{ $tanggalLahir }}" class="input-nexus">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Nomor Telepon / WA</label>
                        <input type="text" name="telepon" id="telepon" value="{{ old('telepon', $profile?->telepon) }}" maxlength="20" class="input-nexus">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Tanggal Bergabung</label>
                        <input type="date" name="tanggal_bergabung" value="{{ $tanggalBergabung }}" class="input-nexus">
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Alamat Lengkap</label>
                        <input type="text" name="alamat" value="{{ old('alamat', $profile?->alamat) }}" class="input-nexus">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Status Login</label>
                        <select name="status" required class="input-nexus cursor-pointer">
                            <option value="active" {{ old('status', $kader->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status', $kader->status) == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Status Kader</label>
                        <select name="status_kader" required class="input-nexus cursor-pointer">
                            <option value="aktif" {{ old('status_kader', $kaderData?->status_kader ?? 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ old('status_kader', $kaderData?->status_kader) == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('admin.kaders.index') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-700 transition-all shadow-sm text-sm text-center">
                <i class="fas fa-times mr-1"></i> Batal
            </a>

            <button type="submit" id="btnSubmit" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 hover:-translate-y-0.5 transition-all shadow-[0_4px_15px_rgba(37,99,235,.3)] text-sm flex items-center justify-center gap-2">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
    const nikInput = document.getElementById('nik');
    const teleponInput = document.getElementById('telepon');
    const fullNameInput = document.getElementById('fullName');
    const nameMirror = document.getElementById('nameMirror');
    const form = document.getElementById('kaderForm');
    const btn = document.getElementById('btnSubmit');

    nikInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    teleponInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    fullNameInput.addEventListener('input', function() {
        nameMirror.value = this.value;
    });

    form.addEventListener('submit', function() {
        nameMirror.value = fullNameInput.value;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        btn.classList.add('opacity-75', 'cursor-not-allowed');
    });
</script>
@endsection