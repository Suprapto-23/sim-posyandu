@extends('layouts.bidan')

@section('title', 'Buat Jadwal Baru')
@section('page-name', 'Tambah Agenda Posyandu')

@push('styles')
<style>
    /* ANIMASI MASUK HALUS */
    .fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    /* NEXUS INPUT SYSTEM */
    .med-input { 
        width: 100%; background: #ffffff; border: 2px solid #f1f5f9; border-radius: 16px; 
        padding: 16px 20px 16px 52px; color: #0f172a; font-weight: 600; font-size: 14px; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); outline: none; appearance: none;
        box-shadow: 0 2px 6px rgba(15,23,42,0.02);
    }
    .med-input:focus { border-color: #0ea5e9; box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15), 0 2px 6px rgba(15,23,42,0.02); }
    .med-input::placeholder { color: #94a3b8; font-weight: 500; }
    
    /* ERROR STATE HANDLING */
    .med-input.is-invalid { border-color: #f43f5e; background-color: #fff1f2; }
    .med-input.is-invalid:focus { box-shadow: 0 0 0 4px rgba(244, 63, 94, 0.15); }
    .error-msg { font-size: 10.5px; font-weight: 700; color: #e11d48; margin-top: 6px; display: flex; align-items: center; gap: 4px; text-transform: uppercase; letter-spacing: 0.05em; }
    
    .med-label { display: block; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 10px; margin-left: 4px; font-family: 'Poppins', sans-serif;}
    .input-wrapper { position: relative; width: 100%; }
    .input-icon { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 16px; transition: all 0.3s ease; z-index: 10; pointer-events: none; }
    .med-input:focus + .input-icon { color: #0ea5e9; }
    .is-invalid + .input-icon { color: #f43f5e; }

    /* PANEL BROADCAST MEWAH */
    .broadcast-panel { background: linear-gradient(145deg, #0284c7 0%, #0369a1 100%); position: relative; overflow: hidden; }
    .broadcast-panel::before { content: ''; position: absolute; top: -100px; right: -50px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%); border-radius: 50%; }
    .broadcast-panel::after { content: ''; position: absolute; bottom: -50px; left: -50px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(56,189,248,0.2) 0%, rgba(56,189,248,0) 70%); border-radius: 50%; }

    /* CUSTOM SELECT DROPDOWN */
    .select-custom { padding-right: 44px !important; cursor: pointer; }
    .select-arrow { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; font-size: 12px; }
</style>
@endpush

@section('content')

{{-- =================================================================
     LOADER SISTEM NEXUS
     ================================================================= --}}
<div id="smoothLoader" class="fixed inset-0 bg-slate-50/90 backdrop-blur-sm z-[9999] flex flex-col items-center justify-center transition-all duration-300 opacity-0 pointer-events-none">
    <div class="relative w-20 h-20 flex items-center justify-center mb-6">
        <div class="absolute inset-0 border-4 border-cyan-100 rounded-full"></div>
        <div class="absolute inset-0 border-4 border-cyan-600 rounded-full border-t-transparent animate-spin"></div>
        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg">
            <i class="fas fa-satellite-dish text-cyan-600 text-xl animate-pulse"></i>
        </div>
    </div>
    <div class="bg-white px-6 py-2.5 rounded-full shadow-lg border border-slate-100 flex items-center gap-3">
        <div class="w-2.5 h-2.5 rounded-full bg-cyan-500 animate-ping"></div>
        <p class="text-[11px] font-black text-cyan-700 uppercase tracking-[0.2em] font-poppins" id="loaderText">MENYIAPKAN BROADCAST...</p>
    </div>
</div>

<div class="max-w-[1150px] mx-auto fade-in-up pb-20">

    {{-- NAVIGASI HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 px-2">
        <div class="flex items-center gap-5">
            <div class="w-14 h-14 rounded-[20px] bg-white border border-slate-200 text-cyan-600 flex items-center justify-center text-2xl shadow-sm"><i class="fas fa-calendar-plus"></i></div>
            <div>
                <h1 class="text-[24px] md:text-[26px] font-black text-slate-800 tracking-tight font-poppins leading-none mb-1">Terbitkan Agenda</h1>
                <p class="text-[13px] font-semibold text-slate-500">Formulir penjadwalan & sistem notifikasi otomatis KIA.</p>
            </div>
        </div>
        <a href="{{ route('bidan.jadwal.index') }}" class="inline-flex items-center gap-2.5 px-6 py-3.5 bg-white border border-slate-200 text-slate-600 font-bold text-[11px] uppercase tracking-widest rounded-xl hover:bg-slate-50 hover:text-cyan-600 transition-all shadow-sm">
            <i class="fas fa-arrow-left text-slate-400"></i> Kembali ke Daftar
        </a>
    </div>

    {{-- KONTANER UTAMA --}}
    <div class="bg-white rounded-[36px] border border-slate-100 shadow-[0_20px_60px_-15px_rgba(0,0,0,0.05)] overflow-hidden flex flex-col lg:flex-row">
        
        {{-- PANEL KIRI (INFORMASI BROADCAST) --}}
        <div class="lg:w-[380px] broadcast-panel p-10 md:p-12 flex flex-col text-white shrink-0">
            <div class="w-16 h-16 bg-white/20 backdrop-blur-md rounded-[20px] border border-white/30 flex items-center justify-center text-3xl mb-8 shadow-xl relative z-10"><i class="fas fa-broadcast-tower"></i></div>
            <h2 class="text-[26px] font-black font-poppins tracking-tight mb-4 leading-tight relative z-10">Distribusi<br>Notifikasi Pintar</h2>
            <p class="text-sky-100 text-[13.5px] leading-relaxed font-medium mb-8 relative z-10">Agenda yang Anda simpan di sini akan secara otomatis dikirimkan sebagai peringatan (*Push Notification*) ke *smartphone* warga desa yang menjadi target sasaran pelayanan Anda.</p>
            
            <div class="mt-auto relative z-10 bg-black/10 rounded-2xl p-5 border border-white/10">
                <div class="flex items-center gap-3 mb-2">
                    <i class="fas fa-shield-alt text-sky-300"></i>
                    <span class="text-[11px] font-bold uppercase tracking-widest text-sky-100">Tips Penjadwalan</span>
                </div>
                <p class="text-[12px] text-sky-50 opacity-90 leading-relaxed">Hindari tumpang tindih lokasi dan jam. Pastikan Anda memilih <b class="text-white">Target Sasaran</b> yang presisi agar warga tidak menerima notifikasi yang salah.</p>
            </div>
        </div>

        {{-- PANEL KANAN (FORM INPUT) --}}
        <div class="flex-1 p-8 md:p-12 lg:p-14">
            <form id="formJadwal" action="{{ route('bidan.jadwal.store') }}" method="POST">
                @csrf
                <div class="space-y-8">
                    
                    {{-- JUDUL AGENDA --}}
                    <div>
                        <label class="med-label">Nama/Judul Kegiatan <span class="text-rose-500">*</span></label>
                        <div class="input-wrapper">
                            <input type="text" name="judul" value="{{ old('judul') }}" required class="med-input @error('judul') is-invalid @enderror" placeholder="Contoh: Posyandu Balita & Imunisasi Desa">
                            <i class="fas fa-heading input-icon"></i>
                        </div>
                        @error('judul') <span class="error-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span> @enderror
                    </div>

                    {{-- WAKTU PELAKSANAAN --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 bg-slate-50/50 p-6 rounded-[24px] border border-slate-100">
                        <div>
                            <label class="med-label">Tanggal Pelaksanaan <span class="text-rose-500">*</span></label>
                            <div class="input-wrapper">
                                {{-- Atribut min mencegah pemilihan tanggal di masa lalu --}}
                                <input type="date" name="tanggal" value="{{ old('tanggal') }}" min="{{ date('Y-m-d') }}" required class="med-input @error('tanggal') is-invalid @enderror">
                                <i class="fas fa-calendar-day input-icon"></i>
                            </div>
                            @error('tanggal') <span class="error-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span> @enderror
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="med-label">Jam Mulai <span class="text-rose-500">*</span></label>
                                <div class="input-wrapper">
                                    <input type="time" name="waktu_mulai" value="{{ old('waktu_mulai', '08:00') }}" required class="med-input @error('waktu_mulai') is-invalid @enderror" style="padding-left: 48px;">
                                    <i class="far fa-clock input-icon"></i>
                                </div>
                                @error('waktu_mulai') <span class="error-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="med-label">Jam Selesai <span class="text-rose-500">*</span></label>
                                <div class="input-wrapper">
                                    <input type="time" name="waktu_selesai" value="{{ old('waktu_selesai', '12:00') }}" required class="med-input @error('waktu_selesai') is-invalid @enderror" style="padding-left: 48px;">
                                    <i class="far fa-clock input-icon"></i>
                                </div>
                                @error('waktu_selesai') <span class="error-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- LOKASI --}}
                    <div>
                        <label class="med-label">Titik Lokasi / Gedung <span class="text-rose-500">*</span></label>
                        <div class="input-wrapper">
                            <input type="text" name="lokasi" value="{{ old('lokasi', 'Posyandu Induk Desa') }}" required class="med-input @error('lokasi') is-invalid @enderror">
                            <i class="fas fa-map-marked-alt input-icon"></i>
                        </div>
                        @error('lokasi') <span class="error-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span> @enderror
                    </div>

                    {{-- KLASIFIKASI LAYANAN --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="med-label">Kategori Layanan <span class="text-rose-500">*</span></label>
                            <div class="input-wrapper">
                                <select name="kategori" required class="med-input select-custom @error('kategori') is-invalid @enderror">
                                    <option value="" disabled {{ old('kategori') ? '' : 'selected' }}>-- Pilih Kategori --</option>
                                    <option value="posyandu" {{ old('kategori') == 'posyandu' ? 'selected' : '' }}>Posyandu Rutin (Antropometri)</option>
                                    <option value="imunisasi" {{ old('kategori') == 'imunisasi' ? 'selected' : '' }}>Vaksinasi & Imunisasi Dasar</option>
                                    <option value="pemeriksaan" {{ old('kategori') == 'pemeriksaan' ? 'selected' : '' }}>Pemeriksaan Khusus (ANC, PTM)</option>
                                    <option value="konseling" {{ old('kategori') == 'konseling' ? 'selected' : '' }}>Penyuluhan & Edukasi Warga</option>
                                    <option value="lainnya" {{ old('kategori') == 'lainnya' ? 'selected' : '' }}>Kegiatan Lainnya</option>
                                </select>
                                <i class="fas fa-tags input-icon"></i>
                                <i class="fas fa-chevron-down select-arrow"></i>
                            </div>
                            @error('kategori') <span class="error-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="med-label text-cyan-600">Target Sasaran Warga <span class="text-rose-500">*</span></label>
                            <div class="input-wrapper">
                                <select name="target_peserta" required class="med-input select-custom border-cyan-200 bg-cyan-50/20 focus:border-cyan-500 @error('target_peserta') is-invalid @enderror">
                                    <option value="semua" {{ old('target_peserta') == 'semua' ? 'selected' : '' }}>Semua Warga Terdaftar (Umum)</option>
                                    <option value="balita" {{ old('target_peserta') == 'balita' ? 'selected' : '' }}>Khusus Ibu & Balita / Bayi</option>
                                    <option value="ibu_hamil" {{ old('target_peserta') == 'ibu_hamil' ? 'selected' : '' }}>Khusus Ibu Hamil (Bumil)</option>
                                    <option value="remaja" {{ old('target_peserta') == 'remaja' ? 'selected' : '' }}>Khusus Remaja (Posyandu Remaja)</option>
                                    <option value="lansia" {{ old('target_peserta') == 'lansia' ? 'selected' : '' }}>Khusus Lansia (Geriatri / PTM)</option>
                                </select>
                                <i class="fas fa-bullseye input-icon text-cyan-500"></i>
                                <i class="fas fa-chevron-down select-arrow text-cyan-400"></i>
                            </div>
                            @error('target_peserta') <span class="error-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- DESKRIPSI OPSIONAL --}}
                    <div>
                        <label class="med-label">Pesan Tambahan / Persyaratan (Opsional)</label>
                        <div class="input-wrapper">
                            <textarea name="deskripsi" rows="3" class="med-input resize-none @error('deskripsi') is-invalid @enderror" style="padding-top: 16px;" placeholder="Misal: Wajib membawa Buku KIA/KMS, fotokopi KK, dsb.">{{ old('deskripsi') }}</textarea>
                            <i class="fas fa-comment-medical input-icon" style="top: 26px;"></i>
                        </div>
                        @error('deskripsi') <span class="error-msg"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span> @enderror
                    </div>

                    {{-- TOMBOL SUBMIT --}}
                    <div class="pt-8 border-t border-slate-100 flex justify-end">
                        <button type="submit" id="btnSubmit" class="w-full sm:w-auto px-10 py-4 md:py-5 bg-gradient-to-r from-cyan-600 to-blue-700 text-white font-black text-[13px] uppercase tracking-widest rounded-2xl hover:shadow-[0_20px_40px_rgba(6,182,212,0.3)] hover:-translate-y-1 transition-all duration-300">
                            <i class="fas fa-satellite-dish text-lg mr-2"></i> Simpan & Kirim Notifikasi
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('formJadwal').addEventListener('submit', function(e) {
        const btn = document.getElementById('btnSubmit');
        const loader = document.getElementById('smoothLoader');
        
        // Cek manual jika jam selesai lebih kecil dari jam mulai (UX Improvement)
        const jamMulai = document.querySelector('input[name="waktu_mulai"]').value;
        const jamSelesai = document.querySelector('input[name="waktu_selesai"]').value;
        
        if(jamMulai && jamSelesai && jamSelesai <= jamMulai) {
            e.preventDefault();
            alert("Kesalahan: Jam Selesai harus lebih lambat dari Jam Mulai.");
            return;
        }

        // Tampilkan State Loading
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin text-lg mr-2"></i> MENGIRIM BROADCAST...';
        btn.classList.add('opacity-80', 'cursor-not-allowed');
        
        if(loader) {
            loader.style.display = 'flex';
            void loader.offsetWidth; 
            loader.classList.remove('opacity-0', 'pointer-events-none');
            loader.classList.add('opacity-100');
        }
    });

    // Sembunyikan loader jika pengguna menekan "Back" di browser
    window.addEventListener('pageshow', (event) => {
        const loader = document.getElementById('smoothLoader');
        if(loader) {
            loader.classList.remove('opacity-100');
            loader.classList.add('opacity-0');
            setTimeout(() => {
                loader.classList.add('pointer-events-none');
                loader.style.display = 'none';
            }, 300);
        }
        
        // Reset tombol submit
        const btn = document.getElementById('btnSubmit');
        if(btn) {
            btn.innerHTML = '<i class="fas fa-satellite-dish text-lg mr-2"></i> Simpan & Kirim Notifikasi';
            btn.classList.remove('opacity-80', 'cursor-not-allowed');
        }
    });
</script>
@endpush