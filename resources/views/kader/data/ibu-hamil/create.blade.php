@extends('layouts.kader')

@section('title', 'Tambah Data Ibu Hamil')
@section('page-name', 'Registrasi Kehamilan')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    /* ANIMASI MASUK */
    .animate-slide-up { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes slideUpFade { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    /* FORM INPUT CRM NEXUS */
    .form-label { display: block; font-size: 0.70rem; font-weight: 900; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.6rem; }
    .form-input {
        width: 100%; background-color: #f8fafc; border: 2px solid #f1f5f9; color: #1e293b;
        font-size: 0.875rem; border-radius: 16px; padding: 1rem 1.25rem; outline: none;
        transition: all 0.3s ease; font-weight: 500;
        box-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.01);
    }
    .form-input:focus {
        background-color: #ffffff; border-color: #ec4899;
        box-shadow: 0 4px 20px -3px rgba(236, 72, 153, 0.15); transform: translateY(-2px);
    }
    .form-input::placeholder { color: #94a3b8; font-weight: 500; }
    .form-error { border-color: #f43f5e !important; background-color: #fff1f2 !important; box-shadow: 0 4px 15px -3px rgba(244, 63, 94, 0.15) !important; }
    
    /* KARTU KACA (GLASSMORPHISM) */
    .glass-panel { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.8); }

    /* SWEETALERT CUSTOM KAPSUL NEXUS */
    div:where(.swal2-container) { z-index: 10000 !important; backdrop-filter: blur(8px) !important; background: rgba(15, 23, 42, 0.4) !important; }
    .swal2-popup.swal2-toast { border-radius: 16px !important; padding: 16px 24px !important; background: rgba(255, 255, 255, 0.98) !important; border: 1px solid #e2e8f0 !important; box-shadow: 0 10px 40px -10px rgba(0,0,0,0.15) !important; }
    .swal2-toast .swal2-title { font-family: 'Poppins', sans-serif !important; font-size: 14px !important; color: #1e293b !important; }
    .swal2-toast .swal2-html-container { font-family: sans-serif !important; font-size: 12px !important; color: #64748b !important; margin-top: 4px !important; text-align: left !important; }
    .swal2-popup:not(.swal2-toast) { border-radius: 32px !important; padding: 2.5rem 2rem !important; background: rgba(255, 255, 255, 0.98) !important; border: 1px solid rgba(255,255,255,0.5) !important; width: 28em !important; box-shadow: 0 20px 60px -15px rgba(0,0,0,0.1) !important; }
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
        <div class="absolute inset-0 border-4 border-pink-100 rounded-full"></div>
        <div class="absolute inset-0 border-4 border-pink-500 rounded-full border-t-transparent animate-spin"></div>
        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg"><i class="fas fa-female text-pink-500 text-2xl animate-pulse"></i></div>
    </div>
    <p class="text-pink-900 font-black tracking-widest text-[11px] animate-pulse uppercase">MENYIAPKAN FORMULIR...</p>
</div>

<div class="max-w-6xl mx-auto animate-slide-up relative z-10 pb-12">
    
    {{-- AURA BACKGROUND --}}
    <div class="absolute top-0 right-0 w-96 h-96 bg-pink-400/10 rounded-full blur-[80px] pointer-events-none z-0"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-rose-400/10 rounded-full blur-[80px] pointer-events-none z-0"></div>

    {{-- TOMBOL KEMBALI --}}
    <div class="mb-6 flex items-center gap-3 relative z-10">
        <a href="{{ route('kader.data.ibu-hamil.index') }}" class="w-12 h-12 rounded-[16px] bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-pink-50 hover:border-pink-300 hover:text-pink-600 transition-all shadow-sm group">
            <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
        </a>
    </div>

    {{-- HEADER FORM --}}
    <div class="text-center mb-10 relative z-10">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-[24px] bg-gradient-to-br from-pink-100 to-rose-100 text-pink-600 mb-5 shadow-sm border border-pink-200 transform rotate-3 hover:rotate-0 transition-transform">
            <i class="fas fa-female text-4xl"></i>
        </div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight font-poppins">Pendaftaran Ibu Hamil</h1>
        <p class="text-slate-500 mt-2 font-medium text-[13px] max-w-lg mx-auto">Sistem akan mengalkulasi secara presisi <b>Hari Perkiraan Lahir (HPL)</b> dan <b>Indeks Massa Tubuh (IMT)</b> dari data yang Anda masukkan.</p>
    </div>

    {{-- FORM UTAMA --}}
    <form action="{{ route('kader.data.ibu-hamil.store') }}" method="POST" id="formCreateIbu">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- KOLOM 1: IDENTITAS (7 KOLOM) --}}
            <div class="lg:col-span-7 glass-panel rounded-[32px] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.04)] p-8 md:p-10 relative overflow-hidden flex flex-col">
                <div class="absolute top-0 right-0 w-24 h-24 bg-pink-500/10 rounded-bl-full pointer-events-none"></div>
                
                <div class="flex items-center gap-4 mb-8 border-b border-slate-100 pb-5">
                    <span class="w-10 h-10 rounded-[14px] bg-pink-500 text-white flex items-center justify-center font-black shadow-md">1</span>
                    <h3 class="text-xl font-black text-slate-800 font-poppins">Identitas Lengkap</h3>
                </div>
                
                <div class="space-y-6 flex-1">
                    <div>
                        <label class="form-label">NIK Ibu (Akses Warga) <span class="text-rose-500">*</span></label>
                        <input type="number" name="nik" value="{{ old('nik') }}" placeholder="16 Digit NIK KTP" class="form-input @error('nik') form-error @enderror">
                        @error('nik') <p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">Nama Lengkap Ibu <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required placeholder="Contoh: Siti Aisyah" class="form-input @error('nama_lengkap') form-error @enderror">
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
                            <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required class="form-input cursor-pointer @error('tanggal_lahir') form-error @enderror">
                            @error('tanggal_lahir') <p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="form-label">Nama Suami</label>
                            <input type="text" name="nama_suami" value="{{ old('nama_suami') }}" placeholder="Nama Lengkap Suami" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">No. HP Keluarga (Opsional)</label>
                            <input type="tel" name="telepon_ortu" value="{{ old('telepon_ortu') }}" placeholder="Contoh: 0812xxxx" class="form-input">
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Alamat Domisili <span class="text-rose-500">*</span></label>
                        <textarea name="alamat" rows="2" required placeholder="Alamat lengkap RT/RW..." class="form-input resize-none @error('alamat') form-error @enderror">{{ old('alamat') }}</textarea>
                        @error('alamat') <p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- KOLOM 2: KANDUNGAN & FISIK (5 KOLOM) --}}
            <div class="lg:col-span-5 flex flex-col gap-8">
                
                {{-- KARTU KANDUNGAN --}}
                <div class="bg-rose-50/80 rounded-[32px] border border-rose-200/80 shadow-[0_10px_40px_-10px_rgba(236,72,153,0.03)] p-8 md:p-10 relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-32 h-32 bg-rose-500/10 rounded-bl-full pointer-events-none blur-xl"></div>
                    
                    <div class="flex items-center gap-4 mb-8 border-b border-rose-200 pb-5 relative z-10">
                        <span class="w-10 h-10 rounded-[14px] bg-rose-500 text-white flex items-center justify-center font-black shadow-md">2</span>
                        <h3 class="text-xl font-black text-rose-900 font-poppins">Kandungan</h3>
                    </div>

                    <div class="space-y-6 relative z-10">
                        <div class="bg-white p-5 rounded-[20px] border border-rose-100 shadow-sm">
                            <label class="form-label text-rose-600"><i class="fas fa-calendar-minus mr-1"></i> HPHT (Haid Terakhir) <span class="text-rose-500">*</span></label>
                            <input type="date" name="hpht" id="hpht" value="{{ old('hpht') }}" required class="form-input bg-slate-50 border-slate-200 focus:bg-white focus:border-rose-400 mb-4 cursor-pointer">
                            @error('hpht') <p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                            
                            <label class="form-label text-rose-600"><i class="fas fa-baby mr-1"></i> HPL (Perkiraan Lahir)</label>
                            <input type="date" name="hpl" id="hpl" value="{{ old('hpl') }}" class="form-input bg-rose-50 border-rose-200 text-rose-800 font-black focus:bg-white focus:border-rose-400">
                            <p class="text-[10px] font-black text-rose-400 mt-2 uppercase tracking-widest">*Sistem menghitung otomatis dari HPHT (+280 Hari)</p>
                            @error('hpl') <p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="form-label text-rose-800">Golongan Darah</label>
                            <select name="golongan_darah" class="form-input bg-white border-rose-100 focus:ring-4 focus:ring-rose-50 cursor-pointer">
                                <option value="">-- Pilih Golongan Darah --</option>
                                @foreach(['A','B','AB','O','A+','A-','B+','B-','AB+','AB-','O+','O-'] as $gol)
                                    <option value="{{ $gol }}" {{ old('golongan_darah') == $gol ? 'selected' : '' }}>{{ $gol }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="form-label text-rose-800">Riwayat Penyakit (Opsional)</label>
                            <input type="text" name="riwayat_penyakit" value="{{ old('riwayat_penyakit') }}" placeholder="Contoh: Asma, Diabetes, Hipertensi" class="form-input">
                        </div>
                    </div>
                </div>

                {{-- KARTU FISIK DASAR --}}
                <div class="bg-slate-900 rounded-[32px] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] p-8 md:p-10 relative overflow-hidden flex-1 flex flex-col justify-center">
                    <div class="absolute right-0 bottom-0 w-32 h-32 bg-indigo-500/20 rounded-tl-full pointer-events-none blur-2xl"></div>

                    <div class="flex items-center gap-4 mb-6 border-b border-slate-700/80 pb-5 relative z-10">
                        <span class="w-10 h-10 rounded-[14px] bg-slate-700 text-white flex items-center justify-center font-black shadow-md border border-slate-600">3</span>
                        <h3 class="text-xl font-black text-white font-poppins">Fisik Dasar</h3>
                    </div>

                    <div class="grid grid-cols-2 gap-5 mb-5 relative z-10">
                        <div>
                            <label class="form-label text-slate-400">Berat Badan (kg)</label>
                            <input type="number" step="0.1" name="berat_badan" id="berat_badan" value="{{ old('berat_badan') }}" placeholder="0.0" class="form-input bg-slate-800 border-slate-700 text-white placeholder:text-slate-600 focus:bg-slate-700 focus:border-pink-500">
                        </div>
                        <div>
                            <label class="form-label text-slate-400">Tinggi Badan (cm)</label>
                            <input type="number" step="0.1" name="tinggi_badan" id="tinggi_badan" value="{{ old('tinggi_badan') }}" placeholder="0.0" class="form-input bg-slate-800 border-slate-700 text-white placeholder:text-slate-600 focus:bg-slate-700 focus:border-pink-500">
                        </div>
                    </div>

                    {{-- Status Kehamilan --}}
                    <div class="mb-5 relative z-10">
                        <label class="form-label text-slate-400">Status Kehamilan</label>
                        <select name="status" class="form-input bg-slate-800 border-slate-700 text-white focus:bg-slate-700 focus:border-pink-500 cursor-pointer">
                            <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif (Masih Hamil)</option>
                            <option value="selesai" {{ old('status') == 'selesai' ? 'selected' : '' }}>Selesai (Sudah Melahirkan)</option>
                        </select>
                        @error('status') <p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    {{-- Widget IMT Real-time --}}
                    <div id="imt-result" class="hidden animate-slide-up bg-slate-800 border border-slate-700 p-5 rounded-[20px] flex items-center justify-between relative z-10 shadow-inner">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Skor IMT</p>
                            <p class="text-2xl font-black text-white font-poppins tracking-tight" id="imt-val">0.00</p>
                        </div>
                        <div id="imt-kat" class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest text-white shadow-sm">
                            -
                        </div>
                    </div>
                </div>

            </div>
            
        </div>
        
        {{-- ACTION BUTTONS --}}
        <div class="mt-8 bg-white border border-slate-200 p-6 md:p-8 rounded-[32px] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.05)] flex flex-col sm:flex-row items-center justify-between gap-6 relative z-30">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-[16px] bg-pink-50 text-pink-500 flex items-center justify-center text-2xl shrink-0"><i class="fas fa-check-circle"></i></div>
                <div class="hidden sm:block">
                    <h4 class="text-[14px] font-black text-slate-800 font-poppins mb-0.5">Konfirmasi Registrasi</h4>
                    <p class="text-[12px] font-medium text-slate-500 leading-relaxed">Data NIK Ibu akan digunakan sebagai kunci integrasi portal Warga.</p>
                </div>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto shrink-0">
                <a href="{{ route('kader.data.ibu-hamil.index') }}" class="flex-1 sm:flex-none px-8 py-4 bg-slate-100 border border-slate-200 text-slate-600 font-extrabold text-[12px] rounded-full hover:bg-slate-200 transition-colors text-center uppercase tracking-widest">
                    Batalkan
                </a>
                <button type="submit" id="btnSubmit" class="flex-1 sm:flex-none px-10 py-4 bg-gradient-to-r from-pink-500 to-rose-600 text-white font-black text-[12px] rounded-full hover:from-pink-600 hover:to-rose-700 shadow-[0_8px_20px_rgba(236,72,153,0.3)] hover:-translate-y-1 transition-all flex items-center justify-center gap-2 uppercase tracking-widest">
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
        
        // 1. ELEMEN DOM
        const form = document.getElementById('formCreateIbu');
        const btnSubmit = document.getElementById('btnSubmit');
        const loader = document.getElementById('smoothLoader');
        const tanggalLahirInput = document.getElementById('tanggal_lahir');
        const hphtInput = document.getElementById('hpht');
        const hplInput = document.getElementById('hpl');
        const beratInput = document.getElementById('berat_badan');
        const tinggiInput = document.getElementById('tinggi_badan');
        const imtResult = document.getElementById('imt-result');
        const imtVal = document.getElementById('imt-val');
        const imtKat = document.getElementById('imt-kat');
        
        // 2. HIDE LOADER SAAT PAGE LOAD
        function hideLoader() {
            if (loader) {
                loader.classList.add('hidden');
            }
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fas fa-save text-lg"></i> Simpan Data';
                btnSubmit.classList.remove('opacity-75', 'cursor-wait');
            }
        }
        
        function showLoader() {
            if (loader) {
                loader.classList.remove('hidden');
            }
        }
        
        // 3. SET MAX DATE UNTUK TANGGAL LAHIR & HPHT
        const today = new Date();
        const todayStr = today.toISOString().split('T')[0];
        if (tanggalLahirInput) tanggalLahirInput.max = todayStr;
        if (hphtInput) hphtInput.max = todayStr;
        
        // 4. LIMITASI INPUT NIK (16 digit)
        document.querySelectorAll('input[type="number"]').forEach(input => {
            if (input.name === 'nik') {
                input.addEventListener('input', function() {
                    if (this.value.length > 16) this.value = this.value.slice(0, 16);
                });
            }
        });
        
        // 5. AUTO-CALC HPL (Naegle Rule: HPHT + 280 hari)
        if (hphtInput && hplInput) {
            hphtInput.addEventListener('change', function() {
                const hphtValue = this.value;
                if (hphtValue) {
                    const hphtDate = new Date(hphtValue);
                    if (!isNaN(hphtDate.getTime())) {
                        const hplDate = new Date(hphtDate);
                        hplDate.setDate(hplDate.getDate() + 280);
                        const year = hplDate.getFullYear();
                        const month = String(hplDate.getMonth() + 1).padStart(2, '0');
                        const day = String(hplDate.getDate()).padStart(2, '0');
                        hplInput.value = `${year}-${month}-${day}`;
                    }
                } else {
                    hplInput.value = '';
                }
            });
            
            // Trigger jika ada old value
            if (hphtInput.value) {
                hphtInput.dispatchEvent(new Event('change'));
            }
        }
        
        // 6. AUTO-CALC IMT
        function hitungIMT() {
            if (!beratInput || !tinggiInput || !imtResult || !imtVal || !imtKat) return;
            
            const bb = parseFloat(beratInput.value);
            const tb = parseFloat(tinggiInput.value);
            
            if (!bb || !tb || tb < 50) {
                imtResult.classList.add('hidden');
                return;
            }
            
            const imt = (bb / Math.pow(tb / 100, 2)).toFixed(2);
            let kat = '';
            let clr = '';
            const imtNum = parseFloat(imt);
            
            if (imtNum < 18.5) {
                kat = 'Kurus';
                clr = 'bg-amber-500 shadow-[0_0_15px_rgba(245,158,11,0.5)]';
            } else if (imtNum < 25) {
                kat = 'Normal';
                clr = 'bg-emerald-500 shadow-[0_0_15px_rgba(16,185,129,0.5)]';
            } else if (imtNum < 30) {
                kat = 'Overweight';
                clr = 'bg-orange-500 shadow-[0_0_15px_rgba(249,115,22,0.5)]';
            } else {
                kat = 'Obesitas';
                clr = 'bg-rose-500 shadow-[0_0_15px_rgba(244,63,94,0.5)]';
            }
            
            imtVal.textContent = imt;
            imtKat.textContent = kat;
            imtKat.className = `px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest text-white border border-white/20 ${clr}`;
            imtResult.classList.remove('hidden');
        }
        
        if (beratInput && tinggiInput) {
            beratInput.addEventListener('input', hitungIMT);
            tinggiInput.addEventListener('input', hitungIMT);
            
            // Trigger jika ada old value
            if (beratInput.value && tinggiInput.value) {
                hitungIMT();
            }
        }
        
        // 7. FORM SUBMIT HANDLER - TIDAK MEMBLOCK REDIRECT
        if (form && btnSubmit) {
            form.addEventListener('submit', function(e) {
                // JANGAN panggil e.preventDefault() - biarkan form submit normal!
                
                // Ubah tampilan tombol
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fas fa-circle-notch fa-spin text-lg"></i> Memproses...';
                btnSubmit.classList.add('opacity-75', 'cursor-wait');
                
                // Tampilkan loader
                showLoader();
                
                // Biarkan form submit secara normal - TIDAK ada return false
                // Form akan mengirim data ke server dan redirect sesuai response
            });
        }
        
        // 8. TAMPILKAN ERROR DARI BACKEND MENGGUNAKAN SWEETALERT
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
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="fas fa-save text-lg"></i> Simpan Data';
                    btnSubmit.classList.remove('opacity-75', 'cursor-wait');
                }
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
                    confirmButton: 'bg-pink-500 hover:bg-pink-600 text-white px-6 py-3 rounded-full font-bold text-[12px] uppercase tracking-wider transition-all mt-2'
                }
            }).then(() => {
                hideLoader();
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="fas fa-save text-lg"></i> Simpan Data';
                    btnSubmit.classList.remove('opacity-75', 'cursor-wait');
                }
            });
        @endif
        
        // 9. HIDE LOADER SETELAH PAGE FULLY LOADED
        window.addEventListener('load', hideLoader);
        document.addEventListener('DOMContentLoaded', hideLoader);
        window.addEventListener('pageshow', hideLoader);
        
    })();
</script>
@endpush
@endsection