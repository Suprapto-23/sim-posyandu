@extends('layouts.admin')

@section('title', 'Tambah Bidan Baru')
@section('page-name', 'Registrasi Bidan')
@section('page-title', 'Tambah Akun Bidan')

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
        border-color: #0ea5e9;
        box-shadow: 0 0 0 4px rgba(14,165,233,.12);
    }
</style>

<div class="max-w-4xl mx-auto animate-pop-in pb-12">

    {{-- Hero --}}
    <section class="bg-gradient-to-br from-sky-500 via-cyan-500 to-teal-500 rounded-[2.5rem] p-8 md:p-10 mb-8 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(14,165,233,.35)] border border-white/20 text-center">
        <div class="hero-grid absolute inset-0 opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[70px] rounded-full pointer-events-none"></div>

        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 text-white/85 text-[10px] font-black uppercase tracking-widest mb-4">
                <a href="{{ route('admin.bidans.index') }}" class="hover:text-white transition-colors">Daftar Bidan</a>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span class="text-white">Tambah Baru</span>
            </div>

            <h1 class="text-3xl md:text-4xl font-black text-white font-poppins tracking-tight">
                Registrasi Bidan
            </h1>

            <p class="text-sky-50 text-sm font-medium max-w-xl mx-auto mt-3 leading-relaxed">
                Buat akun bidan untuk akses pemeriksaan klinis, jadwal posyandu, imunisasi, dan rekam medis.
            </p>
        </div>
    </section>

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 mb-6 text-sm font-bold text-rose-600 flex justify-center text-center gap-3 shadow-sm">
            <i class="fas fa-exclamation-circle text-lg"></i>
            Mohon periksa kembali isian form.
        </div>
    @endif

    {{-- Info --}}
    <section class="bg-sky-50 border border-sky-100 rounded-[2rem] p-6 mb-8 flex items-start gap-4 shadow-sm relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-32 h-32 bg-sky-200/50 rounded-full blur-2xl pointer-events-none"></div>

        <div class="w-12 h-12 rounded-2xl bg-white text-sky-500 flex items-center justify-center text-xl shrink-0 shadow-sm">
            <i class="fas fa-info-circle"></i>
        </div>

        <div class="relative z-10">
            <h5 class="text-sm font-black text-sky-800 mb-1.5 tracking-wide">
                Ketentuan Akun Bidan
            </h5>

            <ul class="text-[12px] text-sky-700/80 font-medium space-y-1 list-disc list-inside">
                <li>Email digunakan sebagai username login bidan.</li>
                <li>Password awal dibuat otomatis oleh sistem setelah data disimpan.</li>
                <li>NIK digunakan sebagai identitas profil bidan di sistem.</li>
            </ul>
        </div>
    </section>

    <form action="{{ route('admin.bidans.store') }}" method="POST" id="bidanForm">
        @csrf
        <input type="hidden" name="status" value="active">

        <section class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden mb-8">
            <div class="bg-slate-50/70 px-8 py-6 border-b border-slate-100 flex items-center justify-center">
                <h5 class="font-black text-slate-700 text-sm uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-id-badge text-sky-500"></i>
                    Informasi Data Bidan
                </h5>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            NIK <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nik" id="nik" value="{{ old('nik') }}" maxlength="16" required class="input-soft" placeholder="16 digit NIK">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="input-soft" placeholder="Nama lengkap dengan gelar">
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Email Login <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="input-soft" placeholder="contoh@email.com">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Jenis Kelamin <span class="text-rose-500">*</span>
                        </label>
                        <select name="jenis_kelamin" required class="input-soft cursor-pointer">
                            <option value="">Pilih jenis kelamin</option>
                            <option value="L" {{ old('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-Laki</option>
                            <option value="P" {{ old('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Nomor Telepon / WhatsApp
                        </label>
                        <input type="text" name="telepon" id="telepon" value="{{ old('telepon') }}" maxlength="20" class="input-soft" placeholder="08xxxxxxxxxx">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Tempat Lahir
                        </label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" class="input-soft" placeholder="Contoh: Pekalongan">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Tanggal Lahir
                        </label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" class="input-soft">
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Alamat Lengkap
                        </label>
                        <input type="text" name="alamat" value="{{ old('alamat') }}" class="input-soft" placeholder="Dusun, RT/RW, Desa">
                    </div>

                </div>
            </div>
        </section>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('admin.bidans.index') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-700 transition-all shadow-sm text-sm text-center">
                <i class="fas fa-times mr-1"></i>
                Batal
            </a>

            <button type="submit" id="btnSubmit" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold text-white bg-sky-500 hover:bg-sky-600 hover:-translate-y-0.5 transition-all shadow-[0_4px_15px_rgba(14,165,233,.30)] text-sm flex items-center justify-center gap-2">
                <i class="fas fa-save"></i>
                Buat Akun Bidan
            </button>
        </div>
    </form>
</div>

<script>
    const nikInput = document.getElementById('nik');
    const teleponInput = document.getElementById('telepon');
    const form = document.getElementById('bidanForm');
    const btnSubmit = document.getElementById('btnSubmit');

    function onlyNumber(input) {
        input.value = input.value.replace(/[^0-9]/g, '');
    }

    nikInput.addEventListener('input', function() {
        onlyNumber(this);
    });

    teleponInput.addEventListener('input', function() {
        onlyNumber(this);
    });

    form.addEventListener('submit', function() {
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        btnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
    });
</script>
@endsection