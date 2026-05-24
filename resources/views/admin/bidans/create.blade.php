@extends('layouts.admin')
@section('title', 'Tambah Bidan Baru')
@section('page-name', 'Registrasi Bidan')

@section('content')
<style>
    .animate-pop-in { animation: popIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    @keyframes popIn { 0% { opacity: 0; transform: scale(0.95) translateY(10px); } 100% { opacity: 1; transform: scale(1) translateY(0); } }
</style>

<div class="max-w-4xl mx-auto animate-pop-in">

    {{-- Hero Section --}}
    <div class="bg-gradient-to-br from-sky-500 to-teal-500 rounded-[2.5rem] p-8 md:p-10 mb-8 relative overflow-hidden shadow-lg border border-white/20 flex flex-col items-center justify-center text-center group">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 right-0 w-48 h-48 bg-white/10 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-full flex flex-col items-center">
            <div class="inline-flex items-center gap-2 text-white/80 text-[10px] font-black uppercase tracking-widest mb-3">
                <a href="{{ route('admin.bidans.index') }}" class="hover:text-white transition-colors smooth-route">Daftar Bidan</a>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span class="text-white">Tambah Baru</span>
            </div>
            
            <h2 class="text-3xl font-black text-white font-poppins tracking-tight flex items-center justify-center gap-3 text-shadow-sm">
                <i class="fas fa-user-plus"></i> Registrasi Bidan
            </h2>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 mb-6 text-sm font-bold text-rose-600 flex justify-center text-center gap-3 shadow-sm">
        <i class="fas fa-exclamation-circle text-lg"></i> Mohon periksa kembali isian form Anda.
    </div>
    @endif

    {{-- Info Alert Premium (Soft Sky) --}}
    <div class="bg-sky-50 border border-sky-100 rounded-[2rem] p-6 mb-8 flex items-start gap-4 shadow-sm relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-32 h-32 bg-sky-200/50 rounded-full blur-2xl pointer-events-none"></div>
        <div class="w-12 h-12 rounded-2xl bg-white text-sky-500 flex items-center justify-center text-xl shrink-0 shadow-sm">
            <i class="fas fa-info-circle"></i>
        </div>
        <div class="relative z-10">
            <h5 class="text-sm font-black text-sky-800 mb-1.5 tracking-wide">Sistem Pembuatan Akun</h5>
            <ul class="text-[12px] text-sky-700/80 font-medium space-y-1 list-disc list-inside">
                <li>Password akan <span class="text-sky-600 font-bold">digenerate otomatis</span> oleh sistem PosyanduCare.</li>
                <li>Password akan muncul di layar (brankas) setelah Anda berhasil menyimpan data ini.</li>
                <li>Detail profil lainnya (STR/SIP) dapat dilengkapi secara mandiri oleh Bidan yang bersangkutan.</li>
            </ul>
        </div>
    </div>

    <form action="{{ route('admin.bidans.store') }}" method="POST" id="bidanForm">
        @csrf
        <input type="hidden" name="status" value="{{ old('status', 'active') }}">
        
        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden mb-8">
            <div class="bg-slate-50/50 px-8 py-6 border-b border-slate-50 flex items-center justify-center">
                <h5 class="font-black text-slate-700 text-sm uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-id-badge text-sky-500"></i> Informasi Data Diri Bidan
                </h5>
            </div>
            
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2 text-center md:text-left">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Nomor Induk Kependudukan (NIK) <span class="text-rose-500">*</span></label>
                        <input type="text" name="nik" id="nik" value="{{ old('nik') }}" maxlength="16" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:bg-white focus:border-sky-400 focus:ring-4 focus:ring-sky-500/10 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium" placeholder="16 Digit Angka NIK">
                    </div>

                    <div class="space-y-2 text-center md:text-left">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:bg-white focus:border-sky-400 focus:ring-4 focus:ring-sky-500/10 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium" placeholder="Nama Lengkap dengan Gelar">
                    </div>

                    <div class="col-span-1 md:col-span-2 space-y-2 text-center md:text-left">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Email Sistem <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:bg-white focus:border-sky-400 focus:ring-4 focus:ring-sky-500/10 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium" placeholder="email@contoh.com">
                    </div>

                    <div class="space-y-2 text-center md:text-left">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Jenis Kelamin <span class="text-rose-500">*</span></label>
                        <select name="jenis_kelamin" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:bg-white focus:border-sky-400 focus:ring-4 focus:ring-sky-500/10 outline-none transition-all cursor-pointer">
                            <option value="">-- Pilih --</option>
                            <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-Laki</option>
                            <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div class="space-y-2 text-center md:text-left">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Nomor Telepon/WA</label>
                        <input type="text" name="telepon" id="telepon" value="{{ old('telepon') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:bg-white focus:border-sky-400 focus:ring-4 focus:ring-sky-500/10 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium" placeholder="08xx...">
                    </div>

                    <div class="space-y-2 text-center md:text-left">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:bg-white focus:border-sky-400 focus:ring-4 focus:ring-sky-500/10 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium" placeholder="Kota Kelahiran">
                    </div>

                    <div class="space-y-2 text-center md:text-left">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Tanggal Lahir <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:bg-white focus:border-sky-400 focus:ring-4 focus:ring-sky-500/10 outline-none transition-all">
                    </div>

                    <div class="col-span-1 md:col-span-2 space-y-2 text-center md:text-left">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Alamat Lengkap</label>
                        <input type="text" name="alamat" value="{{ old('alamat') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:bg-white focus:border-sky-400 focus:ring-4 focus:ring-sky-500/10 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium" placeholder="Jalan, RT/RW, Desa">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pb-10">
            <a href="{{ route('admin.bidans.index') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-700 transition-all shadow-sm text-sm text-center smooth-route">
                <i class="fas fa-times mr-1"></i> Batal
            </a>
            <button type="submit" id="btnSubmit" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold text-white bg-sky-500 hover:bg-sky-600 hover:-translate-y-0.5 transition-all shadow-[0_4px_15px_rgba(14,165,233,0.3)] text-sm flex items-center justify-center gap-2">
                <i class="fas fa-save"></i> Buat Akun Bidan
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('nik').addEventListener('input', function(e) { this.value = this.value.replace(/[^0-9]/g, ''); });
    document.getElementById('telepon').addEventListener('input', function(e) { this.value = this.value.replace(/[^0-9]/g, ''); });
    
    document.getElementById('bidanForm').addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmit');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        btn.classList.add('opacity-75', 'cursor-not-allowed');
    });
</script>
@endsection