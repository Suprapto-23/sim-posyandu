@extends('layouts.admin')
@section('title', 'Tambah User Warga')
@section('page-name', 'Tambah Warga')

@section('content')
<style>
    .animate-pop-in { animation: popIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    @keyframes popIn { 0% { opacity: 0; transform: scale(0.95) translateY(10px); } 100% { opacity: 1; transform: scale(1) translateY(0); } }
</style>

<div class="max-w-4xl mx-auto animate-pop-in">

    {{-- Hero Section --}}
    <div class="bg-gradient-to-br from-blue-600 to-sky-400 rounded-[2.5rem] p-8 md:p-10 mb-8 relative overflow-hidden shadow-lg border border-white/20 flex flex-col items-center justify-center text-center group">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 right-0 w-48 h-48 bg-white/10 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-full flex flex-col items-center">
            <div class="inline-flex items-center gap-2 text-white/80 text-[10px] font-black uppercase tracking-widest mb-3">
                <a href="{{ route('admin.users.index') }}" class="hover:text-white transition-colors smooth-route">Daftar Warga</a>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span class="text-white">Tambah Baru</span>
            </div>
            
            <h2 class="text-3xl font-black text-white font-poppins tracking-tight flex items-center justify-center gap-3 text-shadow-sm">
                <i class="fas fa-user-plus"></i> Pendaftaran Warga
            </h2>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 mb-6 text-sm font-bold text-rose-600 flex justify-center text-center gap-3 shadow-sm">
        <i class="fas fa-exclamation-circle text-lg"></i> Mohon periksa kembali isian form Anda.
    </div>
    @endif

    {{-- Info Alert Premium (Ocean Blue) --}}
<div class="bg-blue-50 border border-blue-100 rounded-[2rem] p-6 mb-8 flex items-start gap-4 shadow-sm relative overflow-hidden">
    <div class="absolute -right-10 -top-10 w-32 h-32 bg-blue-200/50 rounded-full blur-2xl pointer-events-none"></div>
    <div class="w-12 h-12 rounded-2xl bg-white text-blue-600 flex items-center justify-center text-xl shrink-0 shadow-sm">
        <i class="fas fa-id-card"></i>
    </div>
    <div class="relative z-10">
        <h5 class="text-sm font-black text-blue-800 mb-1.5 tracking-wide">Autentikasi Berbasis NIK</h5>
        <ul class="text-[12px] text-blue-700/80 font-medium space-y-1 list-disc list-inside">
            <li>Warga akan login menggunakan <span class="font-bold text-blue-600">Nomor Induk Kependudukan (NIK)</span> sebagai ID Pengguna.</li>
            <li>Sistem <span class="text-blue-600 font-bold">tidak memerlukan email</span> untuk pendaftaran warga.</li>
            <li>Password akan digenerate otomatis dan wajib diberikan kepada warga setelah pendaftaran selesai.</li>
        </ul>
    </div>
</div>

    <form action="{{ route('admin.users.store') }}" method="POST" id="wargaForm">
        @csrf
        <input type="hidden" name="status" value="active">
        
        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden mb-8">
            <div class="bg-slate-50/50 px-8 py-6 border-b border-slate-50 flex items-center justify-center">
                <h5 class="font-black text-slate-700 text-sm uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-id-card text-blue-500"></i> Informasi Data Diri
                </h5>
            </div>
            
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-1 md:col-span-2 space-y-2 text-center md:text-left">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Nomor Induk Kependudukan (NIK) <span class="text-rose-500">*</span></label>
                        <input type="text" name="nik" id="nik" value="{{ old('nik') }}" maxlength="16" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium" placeholder="16 Digit Angka NIK">
                    </div>

                    <div class="space-y-2 text-center md:text-left">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium" placeholder="Nama Sesuai KTP">
                    </div>

                    <div class="space-y-2 text-center md:text-left">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Jenis Kelamin <span class="text-rose-500">*</span></label>
                        <select name="jenis_kelamin" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all cursor-pointer">
                            <option value="">-- Pilih --</option>
                            <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-Laki</option>
                            <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div class="space-y-2 text-center md:text-left">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Tempat Lahir <span class="text-rose-500">*</span></label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium" placeholder="Kota Kelahiran">
                    </div>

                    <div class="space-y-2 text-center md:text-left">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Tanggal Lahir <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                    </div>

                    <div class="space-y-2 text-center md:text-left">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Nomor Telepon/WA <span class="text-rose-500">*</span></label>
                        <input type="text" name="telepon" id="telepon" value="{{ old('telepon') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium" placeholder="08xx...">
                    </div>

                    <div class="col-span-1 md:col-span-2 space-y-2 text-center md:text-left">
                        <label class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Alamat Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="alamat" value="{{ old('alamat') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-800 focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all placeholder:text-slate-300 placeholder:font-medium" placeholder="Jalan, RT/RW, Desa">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pb-10">
            <a href="{{ route('admin.users.index') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-700 transition-all shadow-sm text-sm text-center smooth-route">
                <i class="fas fa-times mr-1"></i> Batal
            </a>
            <button type="submit" id="btnSubmit" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 hover:-translate-y-0.5 transition-all shadow-[0_4px_15px_rgba(37,99,235,0.3)] text-sm flex items-center justify-center gap-2">
                <i class="fas fa-save"></i> Simpan Data Warga
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('nik').addEventListener('input', function(e) { this.value = this.value.replace(/[^0-9]/g, ''); });
    document.getElementById('telepon').addEventListener('input', function(e) { this.value = this.value.replace(/[^0-9]/g, ''); });
    
    document.getElementById('wargaForm').addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmit');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        btn.classList.add('opacity-75', 'cursor-not-allowed');
    });
</script>
@endsection