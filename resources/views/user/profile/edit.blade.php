@extends('layouts.user')

@section('title', 'Pengaturan Profil')

@push('styles')
<style>
    .animate-slide-up { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes slideUpFade { 
        from { opacity: 0; transform: translateY(30px); } 
        to { opacity: 1; transform: translateY(0); } 
    }
</style>
@endpush

@section('content')
@php $userAuth = auth()->user(); @endphp
<div class="max-w-6xl mx-auto pb-32 px-4 md:px-8 font-poppins mobile-padding-bottom">
    
    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8 md:mb-10 animate-slide-up">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-500 text-[10px] font-black uppercase tracking-[0.15em] mb-4 border border-slate-200 shadow-sm">
                <i class="fas fa-user-shield"></i> Pengaturan & Keamanan
            </div>
            <h1 class="text-2xl md:text-4xl font-black text-slate-800 tracking-tight leading-tight mb-2">Data Profil Anda ⚙️</h1>
            <p class="text-[13px] md:text-sm font-medium text-slate-500 leading-relaxed">
                Kelola informasi pribadi, NIK, dan keamanan akun Anda. Pastikan data selaras dengan KTP untuk sinkronisasi rekam medis otomatis.
            </p>
        </div>
    </div>

    {{-- 2. ALERTS --}}
    @if(empty($userAuth->nik) && empty($userAuth->profile->nik))
        <div class="mb-8 bg-gradient-to-r from-rose-50 to-orange-50 border border-rose-200 rounded-[2rem] p-6 md:p-8 flex gap-5 items-start shadow-sm relative overflow-hidden animate-slide-up" style="animation-delay: 0.1s;">
            <div class="absolute -right-10 -top-10 w-48 h-48 bg-rose-200 rounded-full blur-3xl pointer-events-none opacity-50"></div>
            <div class="w-14 h-14 bg-white text-rose-500 rounded-[1.2rem] flex items-center justify-center shrink-0 shadow-sm border border-rose-100 z-10">
                <i class="fas fa-lock text-2xl animate-pulse"></i>
            </div>
            <div class="z-10 flex-1">
                <h3 class="text-lg font-black text-rose-800 tracking-tight">Akses Rekam Medis Terkunci!</h3>
                <p class="text-[13px] font-medium text-rose-700 mt-1.5 leading-relaxed max-w-3xl">Anda belum memasukkan Nomor Induk Kependudukan (NIK). Silakan lengkapi formulir di bawah ini agar sistem dapat menyinkronkan data KMS Balita, Lansia, dan riwayat kesehatan keluarga Anda dari Posyandu.</p>
            </div>
        </div>
    @endif

    @if(session('status') === 'profile-updated' || session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)" class="mb-8 bg-emerald-50 border border-emerald-200 rounded-[1.5rem] p-5 flex gap-4 items-center shadow-sm animate-slide-up" style="animation-delay: 0.1s;">
            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-emerald-500 shadow-sm shrink-0">
                <i class="fas fa-check"></i>
            </div>
            <p class="text-[14px] font-black text-emerald-800">{{ session('success') ?? 'Data profil berhasil diperbarui dan disinkronkan!' }}</p>
        </div>
    @endif

    {{-- 3. MAIN GRID FORM --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8">
        
        {{-- KOLOM KIRI (Profil & Identitas) - 8 Kolom --}}
        <div class="lg:col-span-8 space-y-6 md:space-y-8 animate-slide-up" style="animation-delay: 0.2s;">
            
            <div class="bg-white rounded-[2rem] md:rounded-[2.5rem] border border-slate-100 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.03)] overflow-hidden">
                {{-- Header Form --}}
                <div class="px-6 md:px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-[1.2rem] bg-gradient-to-tr from-teal-400 to-emerald-500 text-white flex items-center justify-center shadow-lg shadow-teal-500/30">
                        <i class="fas fa-id-card text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-800 tracking-tight">Informasi Identitas</h3>
                        <p class="text-[11px] font-medium text-slate-400 mt-0.5">Biodata utama dan kunci sinkronisasi Posyandu</p>
                    </div>
                </div>

                {{-- Body Form --}}
                <div class="p-6 md:p-8">
                  <form method="post" action="{{ route('user.profile.update') }}" class="space-y-6 md:space-y-8">
                        @csrf
                        @method('patch')

                        {{-- NIK (Highlight Khusus) --}}
                        <div class="bg-teal-50/40 border border-teal-100 rounded-[1.8rem] p-5 md:p-6 relative overflow-hidden group hover:border-teal-300 transition-colors">
                            <div class="absolute right-0 top-0 bottom-0 w-1.5 bg-teal-500 group-hover:w-2 transition-all"></div>
                            <label for="nik" class="block text-[10px] font-black text-teal-700 uppercase tracking-[0.15em] mb-2 flex items-center gap-2">
                                <i class="fas fa-fingerprint text-teal-500"></i> Nomor Induk Kependudukan (NIK) <span class="text-rose-500">*</span>
                            </label>
                            <input id="nik" name="nik" type="text" 
                                class="w-full px-5 py-3.5 bg-white border border-teal-200 rounded-[1.2rem] text-[14px] font-black text-slate-800 focus:outline-none focus:ring-4 focus:ring-teal-50 focus:border-teal-400 transition-all placeholder:text-slate-300 shadow-sm" 
                                placeholder="Masukkan 16 Digit NIK KTP Anda" 
                                value="{{ old('nik', $userAuth->nik ?? ($userAuth->profile->nik ?? '')) }}" required maxlength="16">
                            <p class="text-[10px] font-bold text-teal-600/70 mt-2"><i class="fas fa-info-circle mr-1"></i> Digunakan otomatis untuk menarik data kesehatan anak dan orang tua.</p>
                            @error('nik') <p class="text-[11px] text-rose-500 mt-2 font-bold px-2"><i class="fas fa-exclamation-triangle mr-1"></i> {{ $message }}</p> @enderror
                        </div>

                        {{-- Grid Input Standard --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
                            
                            {{-- Nama Lengkap (Mengisi 2 Kolom karena email dihapus) --}}
                            <div class="md:col-span-2">
                                <label for="name" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Nama Sesuai KTP</label>
                                <input id="name" name="name" type="text" 
                                    class="w-full px-5 py-3.5 bg-slate-50/50 border border-slate-200 rounded-[1.2rem] text-[13px] font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-4 focus:ring-teal-50 focus:border-teal-400 transition-all" 
                                    value="{{ old('name', $userAuth->profile->full_name ?? $userAuth->name) }}" required>
                                @error('name') <p class="text-[11px] text-rose-500 mt-1 font-bold">{{ $message }}</p> @enderror
                            </div>

                            {{-- Tempat Lahir --}}
                            <div>
                                <label for="tempat_lahir" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Tempat Lahir</label>
                                <input id="tempat_lahir" name="tempat_lahir" type="text" 
                                    class="w-full px-5 py-3.5 bg-slate-50/50 border border-slate-200 rounded-[1.2rem] text-[13px] font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-4 focus:ring-teal-50 focus:border-teal-400 transition-all" 
                                    value="{{ old('tempat_lahir', $userAuth->profile->tempat_lahir ?? '') }}">
                            </div>

                            {{-- Tanggal Lahir --}}
                            <div>
                                <label for="tanggal_lahir" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Tanggal Lahir</label>
                                <input id="tanggal_lahir" name="tanggal_lahir" type="date" 
                                    class="w-full px-5 py-3.5 bg-slate-50/50 border border-slate-200 rounded-[1.2rem] text-[13px] font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-4 focus:ring-teal-50 focus:border-teal-400 transition-all cursor-text" 
                                    value="{{ old('tanggal_lahir', $userAuth->profile->tanggal_lahir ?? '') }}">
                            </div>

                            {{-- Jenis Kelamin --}}
                            <div>
                                <label for="jenis_kelamin" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Jenis Kelamin</label>
                                <div class="relative">
                                    <select id="jenis_kelamin" name="jenis_kelamin" class="w-full px-5 py-3.5 bg-slate-50/50 border border-slate-200 rounded-[1.2rem] text-[13px] font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-4 focus:ring-teal-50 focus:border-teal-400 transition-all appearance-none cursor-pointer">
                                        <option value="L" {{ old('jenis_kelamin', $userAuth->profile->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="P" {{ old('jenis_kelamin', $userAuth->profile->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-400">
                                        <i class="fas fa-chevron-down text-[10px]"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- Telepon --}}
                            <div>
                                <label for="telepon" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">No. WhatsApp Aktif</label>
                                <input id="telepon" name="telepon" type="text" 
                                    class="w-full px-5 py-3.5 bg-slate-50/50 border border-slate-200 rounded-[1.2rem] text-[13px] font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-4 focus:ring-teal-50 focus:border-teal-400 transition-all" 
                                    value="{{ old('telepon', $userAuth->profile->telepon ?? '') }}" placeholder="Contoh: 08123456789">
                            </div>
                        </div>

                        {{-- Alamat Lengkap --}}
                        <div>
                            <label for="alamat" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Alamat Domisili Lengkap</label>
                            <textarea id="alamat" name="alamat" rows="3" 
                                class="w-full px-5 py-4 bg-slate-50/50 border border-slate-200 rounded-[1.2rem] text-[13px] font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-4 focus:ring-teal-50 focus:border-teal-400 transition-all resize-none">{{ old('alamat', $userAuth->profile->alamat ?? '') }}</textarea>
                        </div>

                        {{-- Action Button --}}
                        <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <p class="text-[10px] font-medium text-slate-400 text-center sm:text-left"><i class="fas fa-shield-alt text-teal-500 mr-1"></i> Data Anda dienkripsi secara aman.</p>
                            <button type="submit" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-white text-[11px] font-black uppercase tracking-[0.15em] rounded-[1.2rem] transition-all duration-300 shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 active:scale-95 flex items-center justify-center gap-2">
                                <i class="fas fa-save text-[13px]"></i> Simpan Profil
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN (Keamanan & Bantuan) - 4 Kolom --}}
        <div class="lg:col-span-4 space-y-6 md:space-y-8 animate-slide-up" style="animation-delay: 0.3s;">
            
            {{-- Form Keamanan Sandi --}}
            <div class="bg-white rounded-[2rem] md:rounded-[2.5rem] border border-slate-100 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.03)] overflow-hidden">
                <div class="px-6 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-[1.2rem] bg-gradient-to-tr from-indigo-500 to-sky-400 text-white flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <i class="fas fa-key text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-800 tracking-tight">Ubah Sandi</h3>
                        <p class="text-[11px] font-medium text-slate-400 mt-0.5">Keamanan akun login</p>
                    </div>
                </div>

                <div class="p-6">
                    <form method="post" action="{{ route('user.password.update') }}" class="space-y-5">
                        @csrf
                        @method('put')

                        <div>
                            <label for="current_password" class="block text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1.5 px-1">Sandi Saat Ini</label>
                            <input id="current_password" name="current_password" type="password" 
                                class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl text-[13px] font-medium focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-50 focus:border-indigo-400 transition-all">
                            @error('current_password', 'updatePassword') <p class="text-[10px] text-rose-500 mt-1.5 font-bold px-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1.5 px-1">Sandi Baru</label>
                            <input id="password" name="password" type="password" 
                                class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl text-[13px] font-medium focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-50 focus:border-indigo-400 transition-all">
                            @error('password', 'updatePassword') <p class="text-[10px] text-rose-500 mt-1.5 font-bold px-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1.5 px-1">Konfirmasi Sandi Baru</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" 
                                class="w-full px-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl text-[13px] font-medium focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-50 focus:border-indigo-400 transition-all">
                        </div>

                        <div class="pt-5 mt-2 border-t border-slate-100">
                            <button type="submit" class="w-full py-3.5 bg-slate-800 text-white text-[10px] font-black uppercase tracking-[0.15em] rounded-[1rem] hover:bg-slate-900 transition-colors shadow-lg shadow-slate-200 active:scale-95">
                                Perbarui Sandi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Widget Bantuan Tanpa Chat Konseling --}}
            <div class="bg-gradient-to-br from-sky-500 to-indigo-600 rounded-[2rem] md:rounded-[2.5rem] p-8 text-white shadow-[0_15px_40px_-10px_rgba(99,102,241,0.4)] relative overflow-hidden group">
                {{-- Decorative Shapes --}}
                <div class="absolute -right-6 -top-6 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                <i class="fas fa-bullhorn absolute -right-6 -bottom-6 text-[8rem] text-white opacity-[0.07] group-hover:rotate-12 transition-transform duration-500"></i>
                
                <div class="relative z-10">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-[1rem] flex items-center justify-center text-xl border border-white/30 mb-5">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h3 class="text-xl font-black tracking-tight mb-2 font-poppins">Pusat Informasi</h3>
                    <p class="text-[12px] font-medium text-sky-50 leading-relaxed">
                        Jika NIK sudah benar namun rekam medis tidak muncul, atau ada data yang tidak sesuai, silakan lapor secara langsung kepada Kader atau Bidan saat kunjungan Posyandu berikutnya.
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Memastikan Alpine.js dimuat untuk penutupan alert --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
@endsection