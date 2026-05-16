@extends('layouts.kader')

@section('title', 'Tambah Data Lansia')
@section('page-name', 'Registrasi Lansia')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    .animate-slide-up { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes slideUpFade { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    .form-label { display: block; font-size: 0.70rem; font-weight: 900; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.6rem; }
    .form-input {
        width: 100%; background-color: #f8fafc; border: 2px solid #f1f5f9; color: #1e293b;
        font-size: 0.875rem; border-radius: 16px; padding: 1rem 1.25rem; outline: none;
        transition: all 0.3s ease; font-weight: 500;
        box-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.01);
    }
    .form-input:focus {
        background-color: #ffffff; border-color: #10b981;
        box-shadow: 0 4px 20px -3px rgba(16, 185, 129, 0.15); transform: translateY(-2px);
    }
    .form-error { border-color: #f43f5e !important; background-color: #fff1f2 !important; }
    .glass-panel { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.8); }
    
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
<div id="smoothLoader" class="hidden">
    <div class="relative w-20 h-20 flex items-center justify-center mb-4">
        <div class="absolute inset-0 border-4 border-emerald-100 rounded-full"></div>
        <div class="absolute inset-0 border-4 border-emerald-500 rounded-full border-t-transparent animate-spin"></div>
        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg"><i class="fas fa-user-clock text-emerald-600 text-2xl animate-pulse"></i></div>
    </div>
    <p class="text-emerald-900 font-black tracking-widest text-[11px] animate-pulse uppercase">MENYIMPAN DATA...</p>
</div>

<div class="max-w-6xl mx-auto animate-slide-up relative z-10 pb-12">
    
    <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-400/10 rounded-full blur-[80px] pointer-events-none z-0"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-teal-400/10 rounded-full blur-[80px] pointer-events-none z-0"></div>

    <div class="mb-6 flex items-center gap-3 relative z-10">
        <a href="{{ route('kader.data.lansia.index') }}" class="w-12 h-12 rounded-[16px] bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-600 transition-all shadow-sm group">
            <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
        </a>
    </div>

    <div class="text-center mb-10 relative z-10">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-[20px] bg-gradient-to-br from-emerald-100 to-teal-100 text-emerald-600 mb-5 shadow-sm border border-emerald-200 transform rotate-3 hover:rotate-0 transition-transform">
            <i class="fas fa-user-clock text-4xl"></i>
        </div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight font-poppins">Pendaftaran Lansia</h1>
        <p class="text-slate-500 mt-2 font-medium text-[13px] max-w-lg mx-auto">Isi data identitas dan kesehatan dasar peserta Posyandu Lansia.</p>
    </div>

    <form action="{{ route('kader.data.lansia.store') }}" method="POST" id="formLansia" class="relative z-10">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-7 glass-panel rounded-[32px] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.04)] p-8 md:p-10 relative overflow-hidden flex flex-col">
                <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/10 rounded-bl-full pointer-events-none"></div>
                
                <div class="flex items-center gap-4 mb-8 border-b border-slate-100 pb-5">
                    <span class="w-10 h-10 rounded-[14px] bg-emerald-600 text-white flex items-center justify-center font-black shadow-md">1</span>
                    <h3 class="text-xl font-black text-slate-800 font-poppins">Profil Identitas</h3>
                </div>
                
                <div class="space-y-6 flex-1">
                    <div>
                        <label class="form-label">NIK Lansia <span class="text-rose-500">*</span></label>
                        <input type="number" name="nik" value="{{ old('nik') }}" placeholder="16 Digit NIK KTP" class="form-input @error('nik') form-error @enderror">
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

            <div class="lg:col-span-5 flex flex-col gap-8">
                
                <div class="bg-emerald-50/80 rounded-[32px] border border-emerald-100 p-8 md:p-10 relative overflow-hidden">
                    <div class="flex items-center gap-4 mb-8 border-b border-emerald-200 pb-5 relative z-10">
                        <span class="w-10 h-10 rounded-[14px] bg-emerald-600 text-white flex items-center justify-center font-black shadow-md">2</span>
                        <h3 class="text-xl font-black text-emerald-900 font-poppins">Data Kesehatan</h3>
                    </div>

                    <div class="space-y-6 relative z-10">
                        <div class="p-5 bg-white border border-emerald-100 rounded-[20px] shadow-sm">
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="form-label text-emerald-600">Berat Badan (kg)</label>
                                    <input type="number" step="0.1" name="berat_badan" id="berat_badan" value="{{ old('berat_badan') }}" placeholder="0.0" class="form-input bg-slate-50 focus:bg-white focus:border-emerald-400">
                                </div>
                                <div>
                                    <label class="form-label text-emerald-600">Tinggi Badan (cm)</label>
                                    <input type="number" step="0.1" name="tinggi_badan" id="tinggi_badan" value="{{ old('tinggi_badan') }}" placeholder="0.0" class="form-input bg-slate-50 focus:bg-white focus:border-emerald-400">
                                </div>
                            </div>
                            
                            <div id="imt-preview" class="hidden border rounded-xl p-4 flex items-center gap-4 transition-all bg-slate-100 border-slate-200">
                                <div class="flex-1">
                                    <p class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-0.5" id="imt-label">Status</p>
                                    <p class="text-2xl font-black font-poppins"><span id="imt-angka">0</span></p>
                                </div>
                                <div class="w-10 h-10 rounded-full bg-white/30 flex items-center justify-center text-lg shrink-0"><i class="fas fa-heartbeat"></i></div>
                            </div>
                        </div>

                        <div>
                            <label class="form-label text-emerald-800">Riwayat Penyakit Bawaan</label>
                            <input type="text" name="penyakit_bawaan" value="{{ old('penyakit_bawaan') }}" placeholder="Misal: Hipertensi, Diabetes..." class="form-input bg-white border-emerald-100">
                        </div>
                    </div>
                </div>

                {{-- HAPUS KARTU KONTAK KELUARGA karena 'telepon_keluarga' tidak ada di database --}}
                
            </div>
            
        </div>
        
        <div class="mt-8 bg-white border border-slate-200 p-6 md:p-8 rounded-[32px] shadow-[0_10px_40px_-10px_rgba(0,0,0,0.05)] flex flex-col sm:flex-row items-center justify-between gap-6 relative z-30">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-[16px] bg-emerald-50 text-emerald-500 flex items-center justify-center text-2xl shrink-0"><i class="fas fa-check-circle"></i></div>
                <div class="hidden sm:block">
                    <h4 class="text-[14px] font-black text-slate-800 font-poppins mb-0.5">Konfirmasi Registrasi</h4>
                    <p class="text-[12px] font-medium text-slate-500 leading-relaxed">Data akan dihubungkan otomatis ke akun warga via NIK.</p>
                </div>
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto shrink-0">
                <a href="{{ route('kader.data.lansia.index') }}" class="flex-1 sm:flex-none px-8 py-4 bg-slate-100 border border-slate-200 text-slate-600 font-extrabold text-[12px] rounded-full hover:bg-slate-200 transition-colors text-center uppercase tracking-widest">
                    Batal
                </a>
                <button type="submit" id="btnSubmit" class="flex-1 sm:flex-none px-10 py-4 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-black text-[12px] rounded-full shadow-[0_8px_20px_rgba(16,185,129,0.3)] hover:-translate-y-1 transition-all flex items-center justify-center gap-2 uppercase tracking-widest">
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
        const form = document.getElementById('formLansia');
        const btnSubmit = document.getElementById('btnSubmit');
        const loader = document.getElementById('smoothLoader');
        const tanggalLahirInput = document.getElementById('tanggal_lahir');
        const today = new Date();
        
        if (tanggalLahirInput) {
            const maxDate = new Date(today.getFullYear() - 40, today.getMonth(), today.getDate());
            tanggalLahirInput.max = maxDate.toISOString().split('T')[0];
        }
        
        document.querySelectorAll('input[type="number"]').forEach(input => {
            if (input.name === 'nik') {
                input.addEventListener('input', function() {
                    if (this.value.length > 16) this.value = this.value.slice(0, 16);
                });
            }
        });
        
        function calculateAge() {
            const dobValue = tanggalLahirInput ? tanggalLahirInput.value : '';
            const helper = document.getElementById('age-helper');
            if (!helper) return;
            
            if (!dobValue) {
                helper.innerHTML = '';
                return;
            }

            const dob = new Date(dobValue);
            if (isNaN(dob.getTime())) return;
            
            let y = today.getFullYear() - dob.getFullYear();
            if (today.getMonth() < dob.getMonth() || (today.getMonth() === dob.getMonth() && today.getDate() < dob.getDate())) {
                y--;
            }

            let alertClass = 'bg-emerald-50 border-emerald-100 text-emerald-700';
            let alertIcon = 'fa-check-circle text-emerald-500';
            let alertMsg = '<span class="text-[9px] bg-emerald-500 text-white px-2 py-0.5 rounded ml-2 shadow-sm uppercase tracking-widest">Valid</span>';
            
            if (y < 45) {
                alertClass = 'bg-amber-50 border-amber-200 text-amber-700';
                alertIcon = 'fa-exclamation-triangle text-amber-500';
                alertMsg = '<span class="text-[9px] bg-amber-500 text-white px-2 py-0.5 rounded ml-2 shadow-sm uppercase tracking-widest">Di Bawah Kriteria</span>';
            }

            helper.innerHTML = `<div class="inline-flex items-center ${alertClass} border px-4 py-2 rounded-xl text-xs font-bold shadow-sm"><i class="fas ${alertIcon} mr-2 text-lg"></i> Usia: ${y} Tahun ${alertMsg}</div>`;
        }
        
        if (tanggalLahirInput) {
            if (tanggalLahirInput.value) calculateAge();
            tanggalLahirInput.addEventListener('change', calculateAge);
        }
        
        const beratInput = document.getElementById('berat_badan');
        const tinggiInput = document.getElementById('tinggi_badan');
        const imtPreview = document.getElementById('imt-preview');
        const imtAngka = document.getElementById('imt-angka');
        const imtLabel = document.getElementById('imt-label');
        
        function hitungIMT() {
            if (!beratInput || !tinggiInput || !imtPreview || !imtAngka || !imtLabel) return;
            
            const bb = parseFloat(beratInput.value);
            const tb = parseFloat(tinggiInput.value);
            
            if (!bb || !tb || tb < 50) {
                imtPreview.classList.add('hidden');
                return;
            }
            
            const imt = (bb / Math.pow(tb / 100, 2)).toFixed(2);
            let kat = '', color = '';
            const imtNum = parseFloat(imt);
            
            if (imtNum < 18.5) {
                kat = 'Kurus';
                color = 'bg-amber-500 text-white';
            } else if (imtNum < 25) {
                kat = 'Normal';
                color = 'bg-emerald-500 text-white';
            } else if (imtNum < 27) {
                kat = 'Gemuk';
                color = 'bg-orange-500 text-white';
            } else {
                kat = 'Obesitas';
                color = 'bg-rose-500 text-white';
            }
            
            imtAngka.textContent = imt;
            imtLabel.textContent = kat;
            imtPreview.className = `border rounded-[16px] p-4 flex items-center gap-4 mt-3 transition-all shadow-sm ${color}`;
            imtPreview.classList.remove('hidden');
        }
        
        if (beratInput && tinggiInput) {
            beratInput.addEventListener('input', hitungIMT);
            tinggiInput.addEventListener('input', hitungIMT);
            if (beratInput.value && tinggiInput.value) hitungIMT();
        }
        
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
        
        if (form && btnSubmit) {
            form.addEventListener('submit', function(e) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fas fa-circle-notch fa-spin text-lg"></i> Memproses...';
                btnSubmit.classList.add('opacity-75', 'cursor-wait');
                showLoader();
            });
        }
        
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan Data',
                html: '<div class="text-slate-600 text-sm text-left mt-2">{!! addslashes(session('error')) !!}</div>',
                confirmButtonText: 'Kembali',
                buttonsStyling: false,
                customClass: {
                    popup: 'rounded-[28px] p-6 bg-white shadow-xl',
                    confirmButton: 'bg-rose-500 hover:bg-rose-600 text-white px-6 py-3 rounded-full font-bold text-[12px] mt-2'
                }
            }).then(() => { hideLoader(); });
        @endif
        
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                html: '<div class="text-left text-sm"><ul class="list-disc pl-4">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>',
                confirmButtonText: 'Perbaiki',
                customClass: {
                    popup: 'rounded-[28px] p-6 bg-white shadow-xl',
                    confirmButton: 'bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-full font-bold text-[12px]'
                }
            }).then(() => { hideLoader(); });
        @endif
        
        window.addEventListener('load', hideLoader);
        document.addEventListener('DOMContentLoaded', hideLoader);
        window.addEventListener('pageshow', hideLoader);
        
    })();
</script>
@endpush
@endsection