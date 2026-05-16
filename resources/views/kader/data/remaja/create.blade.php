@extends('layouts.kader')

@section('title', 'Tambah Data Remaja')
@section('page-name', 'Pendaftaran Remaja')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    /* ANIMASI MASUK */
    .animate-slide-up { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes slideUpFade { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    /* FORM INPUT */
    .form-label { display: block; font-size: 0.70rem; font-weight: 900; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.6rem; }
    .form-input {
        width: 100%; background-color: #f8fafc; border: 2px solid #e2e8f0; color: #1e293b;
        font-size: 0.875rem; border-radius: 16px; padding: 1rem 1.25rem; outline: none;
        transition: all 0.3s ease; font-weight: 500;
        box-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.01);
    }
    .form-input:focus {
        background-color: #ffffff; border-color: #4f46e5;
        box-shadow: 0 4px 20px -3px rgba(79, 70, 229, 0.15); transform: translateY(-2px);
    }
    .form-input::placeholder { color: #94a3b8; font-weight: 500; }
    .form-error { border-color: #f43f5e !important; background-color: #fff1f2 !important; box-shadow: 0 4px 15px -3px rgba(244, 63, 94, 0.15) !important; }
    
    /* KARTU KACA */
    .glass-panel { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.8); }

    /* SWEETALERT */
    div:where(.swal2-container) { z-index: 10000 !important; backdrop-filter: blur(8px) !important; background: rgba(15, 23, 42, 0.4) !important; }
    .swal2-popup { border-radius: 32px !important; padding: 2.5rem 2rem !important; background: rgba(255, 255, 255, 0.98) !important; backdrop-filter: blur(16px) !important; box-shadow: 0 20px 60px -15px rgba(0,0,0,0.1) !important; border: 1px solid rgba(255,255,255,0.5) !important; }
    .swal2-popup .swal2-title { font-family: 'Poppins', sans-serif !important; font-weight: 900 !important; font-size: 1.5rem !important; color: #1e293b !important; }
    
    /* LOADER OVERLAY */
    #smoothLoader {
        position: fixed;
        inset: 0;
        background: rgba(248, 250, 252, 0.95);
        backdrop-filter: blur(12px);
        z-index: 9999;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    #smoothLoader.hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }
</style>
@endpush

@section('content')
{{-- PRELOADER --}}
<div id="smoothLoader" class="hidden">
    <div class="relative w-20 h-20 flex items-center justify-center mb-4">
        <div class="absolute inset-0 border-4 border-indigo-100 rounded-full"></div>
        <div class="absolute inset-0 border-4 border-indigo-600 rounded-full border-t-transparent animate-spin"></div>
        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg"><i class="fas fa-user-graduate text-indigo-600 text-2xl animate-pulse"></i></div>
    </div>
    <p class="text-indigo-900 font-black tracking-widest text-[11px] animate-pulse uppercase">MENYIMPAN DATA...</p>
</div>

<div class="max-w-6xl mx-auto animate-slide-up relative z-10 pb-12">
    
    {{-- AURA BACKGROUND --}}
    <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-400/10 rounded-full blur-[80px] pointer-events-none z-0"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-blue-400/10 rounded-full blur-[80px] pointer-events-none z-0"></div>

    {{-- TOMBOL KEMBALI --}}
    <div class="mb-6 flex items-center gap-3 relative z-10">
        <a href="{{ route('kader.data.remaja.index') }}" class="w-12 h-12 rounded-[16px] bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-600 transition-all shadow-sm group">
            <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
        </a>
    </div>

    {{-- HEADER FORM --}}
    <div class="text-center mb-10 relative z-10">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-[20px] bg-gradient-to-br from-indigo-100 to-blue-100 text-indigo-600 mb-5 shadow-sm border border-indigo-200 transform rotate-3 hover:rotate-0 transition-transform">
            <i class="fas fa-user-graduate text-4xl"></i>
        </div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight font-poppins">Registrasi Remaja</h1>
        <p class="text-slate-500 mt-2 font-medium text-[13px] max-w-lg mx-auto">Input data master peserta Posyandu Remaja secara presisi. NIK akan digunakan sebagai kunci integrasi akun Warga.</p>
    </div>

    {{-- FORM UTAMA --}}
    <form action="{{ route('kader.data.remaja.store') }}" method="POST" id="formRemaja" class="relative z-10">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- KOLOM 1: IDENTITAS --}}
            <div class="lg:col-span-7 glass-panel rounded-[32px] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.04)] p-8 md:p-10 relative overflow-hidden flex flex-col">
                <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-500/10 rounded-bl-full pointer-events-none"></div>
                
                <div class="flex items-center gap-4 mb-8 border-b border-slate-100 pb-5">
                    <span class="w-10 h-10 rounded-[14px] bg-indigo-600 text-white flex items-center justify-center font-black shadow-md">1</span>
                    <h3 class="text-xl font-black text-slate-800 font-poppins">Profil Remaja</h3>
                </div>
                
                <div class="space-y-6 flex-1">
                    <div>
                        <label class="form-label">NIK Remaja (Akses Warga) <span class="text-rose-500">*</span></label>
                        <input type="number" name="nik" value="{{ old('nik') }}" required placeholder="16 Digit NIK KTP" class="form-input @error('nik') form-error @enderror">
                        @error('nik') <p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required placeholder="Sesuai Kartu Identitas" class="form-input @error('nama_lengkap') form-error @enderror">
                        @error('nama_lengkap') <p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="form-label">Tempat Lahir <span class="text-rose-500">*</span></label>
                            <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" required placeholder="Kota Kelahiran" class="form-input @error('tempat_lahir') form-error @enderror">
                            @error('tempat_lahir') <p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Tanggal Lahir <span class="text-rose-500">*</span></label>
                            <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required class="form-input cursor-pointer">
                            <div id="age-helper" class="mt-2"></div>
                            @error('tanggal_lahir') <p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Jenis Kelamin <span class="text-rose-500">*</span></label>
                        <select name="jenis_kelamin" required class="form-input cursor-pointer">
                            <option value="">-- Pilih Gender --</option>
                            <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('jenis_kelamin') <p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">Alamat Domisili <span class="text-rose-500">*</span></label>
                        <textarea name="alamat" rows="2" required placeholder="Alamat lengkap RT/RW..." class="form-input resize-none @error('alamat') form-error @enderror">{{ old('alamat') }}</textarea>
                        @error('alamat') <p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- KOLOM 2: AKADEMIK & WALI --}}
            <div class="lg:col-span-5 flex flex-col gap-8">
                
                {{-- KARTU AKADEMIK --}}
                <div class="bg-indigo-50/80 rounded-[32px] border border-indigo-100 shadow-[0_10px_40px_-10px_rgba(79,70,229,0.03)] p-8 md:p-10 relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-32 h-32 bg-indigo-500/10 rounded-bl-full pointer-events-none blur-xl"></div>
                    
                    <div class="flex items-center gap-4 mb-8 border-b border-indigo-200 pb-5 relative z-10">
                        <span class="w-10 h-10 rounded-[14px] bg-indigo-600 text-white flex items-center justify-center font-black shadow-md">2</span>
                        <h3 class="text-xl font-black text-indigo-900 font-poppins">Info Akademik</h3>
                    </div>

                    <div class="space-y-6 relative z-10">
                        <div>
                            <label class="form-label text-indigo-600"><i class="fas fa-school mr-1"></i> Nama Sekolah</label>
                            <input type="text" name="sekolah" value="{{ old('sekolah') }}" placeholder="SMP / SMA N 1..." class="form-input bg-white focus:border-indigo-400">
                        </div>
                        
                        <div>
                            <label class="form-label text-indigo-600"><i class="fas fa-chalkboard mr-1"></i> Tingkat Kelas</label>
                            <input type="text" name="kelas" value="{{ old('kelas') }}" placeholder="Misal: VIII-A" class="form-input bg-white focus:border-indigo-400">
                        </div>
                    </div>
                </div>

                {{-- KARTU ORANG TUA --}}
                <div class="bg-white rounded-[32px] border border-slate-200 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.03)] p-8 md:p-10 relative overflow-hidden flex-1 flex flex-col">
                    <div class="flex items-center gap-4 mb-8 border-b border-slate-100 pb-5">
                        <span class="w-10 h-10 rounded-[14px] bg-slate-400 text-white flex items-center justify-center font-black shadow-md">3</span>
                        <h3 class="text-xl font-black text-slate-800 font-poppins">Data Orang Tua</h3>
                    </div>

                    <div class="space-y-6 flex-1">
                        <div>
                            <label class="form-label">Nama Lengkap Wali <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama_ortu" value="{{ old('nama_ortu') }}" required placeholder="Nama Ibu atau Ayah" class="form-input @error('nama_ortu') form-error @enderror">
                            @error('nama_ortu') <p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">No. HP Keluarga (WhatsApp)</label>
                            <input type="tel" name="telepon_ortu" value="{{ old('telepon_ortu') }}" placeholder="Contoh: 0812xxxx" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Golongan Darah</label>
                            <select name="golongan_darah" class="form-input cursor-pointer">
                                <option value="">-- Belum Tahu --</option>
                                @foreach(['A','B','AB','O'] as $gol)
                                    <option value="{{ $gol }}" {{ old('golongan_darah') == $gol ? 'selected' : '' }}>{{ $gol }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        {{-- ACTION BUTTONS --}}
        <div class="mt-8 bg-white border border-slate-200 p-6 md:p-8 rounded-[32px] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.05)] flex flex-col sm:flex-row items-center justify-between gap-6 relative z-30">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-[16px] bg-indigo-50 text-indigo-500 flex items-center justify-center text-2xl shrink-0"><i class="fas fa-check-circle"></i></div>
                <div class="hidden sm:block">
                    <h4 class="text-[14px] font-black text-slate-800 font-poppins mb-0.5">Konfirmasi Registrasi</h4>
                    <p class="text-[12px] font-medium text-slate-500 leading-relaxed">Pastikan NIK akurat agar sistem dapat menarik data akun warga secara otomatis.</p>
                </div>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto shrink-0">
                <a href="{{ route('kader.data.remaja.index') }}" class="flex-1 sm:flex-none px-8 py-4 bg-slate-100 border border-slate-200 text-slate-600 font-extrabold text-[12px] rounded-full hover:bg-slate-200 transition-colors text-center uppercase tracking-widest">
                    Batal
                </a>
                <button type="submit" id="btnSubmit" class="flex-1 sm:flex-none px-10 py-4 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-black text-[12px] rounded-full shadow-[0_8px_20px_rgba(79,70,229,0.3)] hover:-translate-y-1 transition-all flex items-center justify-center gap-2 uppercase tracking-widest">
                    <i class="fas fa-save text-lg"></i> Simpan Data
                </button>
            </div>
        </div>
        
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (function() {
        'use strict';
        
        // ELEMEN DOM
        const form = document.getElementById('formRemaja');
        const btnSubmit = document.getElementById('btnSubmit');
        const loader = document.getElementById('smoothLoader');
        const tanggalLahirInput = document.getElementById('tanggal_lahir');
        const today = new Date();
        const todayStr = today.toISOString().split('T')[0];
        
        // SET MAX DATE
        if (tanggalLahirInput) tanggalLahirInput.max = todayStr;
        
        // LIMITASI INPUT NIK (16 digit)
        document.querySelectorAll('input[type="number"]').forEach(input => {
            if (input.name === 'nik') {
                input.addEventListener('input', function() {
                    if (this.value.length > 16) this.value = this.value.slice(0, 16);
                });
            }
        });
        
        // HITUNG USIA
        function calculateAge() {
            const dobInput = tanggalLahirInput ? tanggalLahirInput.value : '';
            const helper = document.getElementById('age-helper');
            if (!helper) return;
            
            if (!dobInput) {
                helper.innerHTML = '';
                return;
            }

            const dob = new Date(dobInput);
            if (isNaN(dob.getTime())) return;
            
            let months = (today.getFullYear() - dob.getFullYear()) * 12;
            months -= dob.getMonth();
            months += today.getMonth();
            if (today.getDate() < dob.getDate()) months--;

            const y = Math.floor(months / 12);
            const m = months % 12;
            const text = m > 0 ? `${y} Tahun ${m} Bulan` : `${y} Tahun`;

            let alertClass = 'bg-indigo-50 border-indigo-100 text-indigo-700';
            let alertIcon = 'fa-info-circle text-indigo-500';
            let alertMsg = '';
            
            if (y < 10 || y > 19) {
                alertClass = 'bg-amber-50 border-amber-200 text-amber-700';
                alertIcon = 'fa-exclamation-triangle text-amber-500';
                alertMsg = '<span class="text-[9px] bg-amber-500 text-white px-2 py-0.5 rounded ml-2 shadow-sm uppercase tracking-widest">Di Luar Range Remaja</span>';
            }

            helper.innerHTML = `<div class="inline-flex items-center ${alertClass} border px-4 py-2 rounded-xl text-xs font-bold shadow-sm"><i class="fas ${alertIcon} mr-2 text-lg"></i> Usia tercatat: ${text} ${alertMsg}</div>`;
        }
        
        if (tanggalLahirInput && tanggalLahirInput.value) calculateAge();
        if (tanggalLahirInput) tanggalLahirInput.addEventListener('change', calculateAge);
        
        // HIDE LOADER
        function hideLoader() {
            if (loader) loader.classList.add('hidden');
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fas fa-save text-lg"></i> Simpan Data';
                btnSubmit.classList.remove('opacity-75', 'cursor-wait');
            }
        }
        
        function showLoader() {
            if (loader) loader.classList.remove('hidden');
        }
        
        // FORM SUBMIT - TIDAK MEMBLOCK REDIRECT
        if (form && btnSubmit) {
            form.addEventListener('submit', function(e) {
                // JANGAN panggil e.preventDefault() - biarkan form submit normal!
                
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fas fa-circle-notch fa-spin text-lg"></i> Memproses...';
                btnSubmit.classList.add('opacity-75', 'cursor-wait');
                showLoader();
                
                // Biarkan form submit secara normal
            });
        }
        
        // TAMPILKAN ERROR DARI BACKEND
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan Data',
                html: '<div class="text-slate-600 text-sm text-left mt-2">{!! addslashes(session('error')) !!}</div>',
                confirmButtonText: 'Kembali',
                buttonsStyling: false,
                customClass: {
                    popup: 'rounded-[28px] p-6 bg-white shadow-xl border border-slate-100',
                    confirmButton: 'bg-rose-500 hover:bg-rose-600 text-white px-6 py-3 rounded-full font-bold text-[12px] uppercase tracking-wider transition-all mt-2'
                }
            }).then(() => {
                hideLoader();
            });
        @endif
        
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                html: '<div class="text-slate-600 text-sm mt-2">{!! addslashes(session('success')) !!}</div>',
                timer: 2000,
                showConfirmButton: false,
                customClass: {
                    popup: 'rounded-[28px] p-6 bg-white shadow-xl border border-slate-100'
                }
            });
        @endif
        
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: '<div class="text-left text-sm text-slate-600 mt-2"><ul class="list-disc pl-4">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>',
                confirmButtonText: 'Perbaiki',
                buttonsStyling: false,
                customClass: {
                    popup: 'rounded-[28px] p-6 bg-white shadow-xl border border-slate-100',
                    confirmButton: 'bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-full font-bold text-[12px] uppercase tracking-wider transition-all mt-2'
                }
            }).then(() => {
                hideLoader();
            });
        @endif
        
        // HIDE LOADER SETELAH PAGE LOAD
        window.addEventListener('load', hideLoader);
        document.addEventListener('DOMContentLoaded', hideLoader);
        window.addEventListener('pageshow', hideLoader);
        
    })();
</script>
@endpush
@endsection