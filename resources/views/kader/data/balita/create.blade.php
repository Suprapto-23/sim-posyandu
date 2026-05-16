@extends('layouts.kader')

@section('title', 'Pendaftaran Balita Baru')
@section('page-name', 'Meja 4: Pendaftaran')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    /* =========================================================
     * NEXUS PREMIUM DESIGN SYSTEM 
     * UI/UX Terintegrasi - Presisi, Clean, & Smooth Transitions
     * ========================================================= */
    
    .animate-nexus-in { 
        opacity: 0; 
        animation: nexusIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; 
    }
    @keyframes nexusIn { 
        from { opacity: 0; transform: translateY(30px) scale(0.99); } 
        to { opacity: 1; transform: translateY(0) scale(1); } 
    }

    /* CARD SYSTEM */
    .card-nexus {
        background: #ffffff;
        border-radius: 2.5rem;
        border: 1px solid rgba(241, 245, 249, 0.8);
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.05);
        transition: all 0.4s ease;
    }

    /* INPUT SYSTEM */
    .form-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 0.8rem;
        padding-left: 0.5rem;
    }

    .form-input-nexus {
        width: 100%;
        background-color: #f8fafc;
        border: 2px solid #f1f5f9;
        color: #0f172a;
        font-size: 0.95rem;
        border-radius: 1.25rem;
        padding: 1.1rem 1.5rem;
        outline: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-weight: 700;
    }

    .form-input-nexus:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.1);
    }
    
    .form-input-nexus::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }

    /* RADIO BUTTON CARD SYSTEM */
    .nexus-radio-group { display: flex; gap: 1rem; }
    .nexus-radio-card { flex: 1; cursor: pointer; position: relative; }
    .nexus-radio-card input { position: absolute; opacity: 0; }
    .nexus-radio-ui {
        padding: 1.1rem; text-align: center; border-radius: 1.25rem; border: 2px solid #f1f5f9;
        background: #f8fafc; color: #94a3b8; font-weight: 800; font-size: 0.85rem;
        transition: all 0.3s ease; display: block; letter-spacing: 0.05em;
    }
    .nexus-radio-card input:checked + .nexus-radio-ui {
        border-color: #3b82f6; background: #eff6ff; color: #2563eb;
        box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.15);
    }

    /* BUTTON SYSTEM */
    .btn-nexus-save {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: white; padding: 1.2rem 3rem; border-radius: 1.5rem;
        font-weight: 800; font-size: 0.9rem; text-transform: uppercase;
        letter-spacing: 0.05em; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none; box-shadow: 0 15px 30px -10px rgba(37, 99, 235, 0.4);
        display: inline-flex; items-center; justify-content: center; gap: 0.75rem;
        cursor: pointer;
    }
    .btn-nexus-save:hover:not(:disabled) { 
        transform: translateY(-3px); 
        box-shadow: 0 20px 40px -10px rgba(37, 99, 235, 0.5); 
    }
    .btn-nexus-save:disabled {
        background: #94a3b8; box-shadow: none; cursor: not-allowed; transform: none;
    }

    .btn-nexus-back {
        color: #64748b; font-weight: 800; font-size: 0.85rem; text-transform: uppercase;
        letter-spacing: 0.05em; padding: 1.2rem 2.5rem; transition: all 0.3s ease;
        border-radius: 1.5rem; border: 2px solid transparent; display: inline-flex; items-center; gap: 0.75rem;
    }
    .btn-nexus-back:hover { 
        color: #334155; background: #f8fafc; border-color: #e2e8f0; 
    }

    /* =========================================================
     * SWEETALERT 2 - NEXUS CRM THEME (Soft, Rounded, Glassy)
     * ========================================================= */
    .swal2-container.nexus-swal-backdrop {
        background: rgba(15, 23, 42, 0.4) !important;
        backdrop-filter: blur(8px) !important;
    }
    .nexus-swal-popup {
        border-radius: 2.5rem !important;
        padding: 2.5rem 2rem !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.2) !important;
        border: 1px solid rgba(255, 255, 255, 0.8) !important;
        background: #ffffff !important;
    }
    .nexus-swal-title {
        font-weight: 900 !important; color: #0f172a !important;
        font-size: 1.75rem !important; margin-bottom: 1rem !important; letter-spacing: -0.025em !important;
    }
    .nexus-swal-html { margin: 0 !important; padding: 0 !important; }
    .nexus-swal-icon { border: none !important; margin: 0 auto 1.5rem auto !important; }
    .nexus-swal-confirm {
        background: #2563eb !important; border-radius: 1.25rem !important;
        padding: 1rem 3rem !important; font-weight: 800 !important; font-size: 0.9rem !important;
        text-transform: uppercase !important; letter-spacing: 0.05em !important;
        box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.3) !important;
        border: none !important; transition: all 0.3s ease !important;
    }
    .nexus-swal-confirm:hover { background: #1d4ed8 !important; transform: translateY(-2px) !important; }

    /* FULLSCREEN LOADER */
    #nexusLoader {
        position: fixed; inset: 0; background: rgba(255,255,255,0.85);
        backdrop-filter: blur(12px); z-index: 9999; display: flex;
        flex-direction: column; align-items: center; justify-content: center;
        transition: opacity 0.5s ease;
    }
</style>
@endpush

@section('content')
{{-- Animated Global Loader --}}
<div id="nexusLoader" class="opacity-0 pointer-events-none">
    <div class="relative flex items-center justify-center">
        <div class="w-24 h-24 border-[6px] border-blue-600/10 border-t-blue-600 rounded-full animate-spin"></div>
        <i class="fas fa-child absolute text-blue-600 text-3xl"></i>
    </div>
    <h2 class="mt-8 text-xl font-black text-slate-800">Menyinkronkan Data</h2>
    <p class="mt-2 text-xs font-bold text-blue-600 uppercase tracking-[0.2em]">Sistem Manajemen Posyandu</p>
</div>

<div class="max-w-4xl mx-auto space-y-10 pb-24">
    {{-- Header Content --}}
    <div class="flex flex-col items-center text-center space-y-4 animate-nexus-in">
        <div class="w-20 h-20 bg-gradient-to-br from-blue-50 to-sky-100 rounded-[1.75rem] flex items-center justify-center text-blue-600 text-3xl shadow-sm border border-blue-200/50">
            <i class="fas fa-clipboard-user"></i>
        </div>
        <div>
            <h1 class="text-3xl md:text-4xl font-black text-slate-800 tracking-tight">Registrasi Balita</h1>
            <p class="text-slate-500 text-sm font-semibold mt-2">Gunakan <b class="text-blue-600">NIK Anak</b> sebagai kredensial utama sistem.</p>
        </div>
    </div>

    {{-- Perhatikan Action Route menggunakan kader.data.balita.store --}}
    <form action="{{ route('kader.data.balita.store') }}" method="POST" id="formBalita" class="space-y-8 animate-nexus-in" style="animation-delay: 0.1s">
        @csrf

        {{-- SEKSI 1: IDENTITAS UTAMA --}}
        <div class="card-nexus p-8 md:p-12">
            <div class="flex items-center gap-4 mb-10 pb-6 border-b border-slate-100">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-id-card"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tight">Identitas Anak</h3>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">Sesuai Kartu Keluarga / KIA</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                <div class="md:col-span-2">
                    <label class="form-label">Nama Lengkap Balita <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_lengkap" class="form-input-nexus" placeholder="Masukkan nama anak..." required value="{{ old('nama_lengkap') }}">
                </div>

                <div>
                    <label class="form-label">NIK Anak (16 Digit) <span class="text-rose-500">*</span></label>
                    <input type="text" name="nik" id="nik" maxlength="16" minlength="16" class="form-input-nexus" placeholder="Contoh: 3326..." required value="{{ old('nik') }}">
                </div>

                <div>
                    <label class="form-label">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <div class="nexus-radio-group">
                        <label class="nexus-radio-card">
                            <input type="radio" name="jenis_kelamin" value="L" required {{ old('jenis_kelamin') == 'L' ? 'checked' : '' }}>
                            <span class="nexus-radio-ui">LAKI-LAKI</span>
                        </label>
                        <label class="nexus-radio-card">
                            <input type="radio" name="jenis_kelamin" value="P" required {{ old('jenis_kelamin') == 'P' ? 'checked' : '' }}>
                            <span class="nexus-radio-ui">PEREMPUAN</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="form-label">Kota Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="form-input-nexus" placeholder="Sesuai akta kelahiran" value="{{ old('tempat_lahir') }}">
                </div>

                <div>
                    <label class="form-label">Tanggal Lahir <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-input-nexus" required value="{{ old('tanggal_lahir') }}">
                </div>
            </div>
        </div>

        {{-- SEKSI 2: KELUARGA --}}
        <div class="card-nexus p-8 md:p-12">
            <div class="flex items-center gap-4 mb-10 pb-6 border-b border-slate-100">
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-800 tracking-tight">Keluarga & Domisili</h3>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">Data Orang Tua Wali</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                <div>
                    <label class="form-label">Nama Ibu Kandung <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_ibu" class="form-input-nexus" placeholder="Masukkan nama ibu..." required value="{{ old('nama_ibu') }}">
                </div>

                <div>
                    <label class="form-label">Nama Ayah</label>
                    <input type="text" name="nama_ayah" class="form-input-nexus" placeholder="Masukkan nama ayah..." value="{{ old('nama_ayah') }}">
                </div>

                <div class="md:col-span-2">
                    <label class="form-label">Alamat Tinggal / Domisili <span class="text-rose-500">*</span></label>
                    <textarea name="alamat" rows="3" class="form-input-nexus" placeholder="Contoh: Jl. Diponegoro RT 01 RW 02..." required>{{ old('alamat') }}</textarea>
                </div>
            </div>
        </div>

        {{-- TOMBOL AKSI --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-6 mt-12">
            <a href="{{ route('kader.data.balita.index') }}" class="btn-nexus-back">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button type="submit" id="btnSubmit" class="btn-nexus-save">
                <i class="fas fa-save"></i> Simpan Pendaftaran
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formBalita');
        const btnSubmit = document.getElementById('btnSubmit');
        const loader = document.getElementById('nexusLoader');

        // ==========================================
        // 1. SETUP SWEETALERT NEXUS STYLE
        // ==========================================
        const nexusAlert = Swal.mixin({
            customClass: {
                container: 'nexus-swal-backdrop',
                popup: 'nexus-swal-popup',
                title: 'nexus-swal-title',
                htmlContainer: 'nexus-swal-html',
                icon: 'nexus-swal-icon',
                confirmButton: 'nexus-swal-confirm'
            },
            buttonsStyling: false,
            showClass: { popup: 'animate__animated animate__zoomIn animate__faster' },
            hideClass: { popup: 'animate__animated animate__fadeOut animate__faster' }
        });

        // ==========================================
        // 2. INPUT FORMATTER
        // ==========================================
        document.getElementById('nik').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // ==========================================
        // 3. UI HANDLER (LOADER)
        // ==========================================
        function setLoader(state) {
            if (state) {
                loader.classList.remove('opacity-0', 'pointer-events-none');
                loader.classList.add('opacity-100', 'pointer-events-auto');
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = `<i class="fas fa-circle-notch fa-spin"></i> Menyimpan...`;
            } else {
                loader.classList.add('opacity-0', 'pointer-events-none');
                loader.classList.remove('opacity-100', 'pointer-events-auto');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = `<i class="fas fa-save"></i> Simpan Pendaftaran`;
            }
        }

        // Matikan loader saat user menekan tombol 'Back' di browser
        window.addEventListener('pageshow', function() { setLoader(false); });

        // ==========================================
        // 4. PENANGKAP ERROR BACKEND (LARAVEL)
        // ==========================================
        @if ($errors->any())
            nexusAlert.fire({
                icon: 'error',
                title: 'Verifikasi Gagal',
                html: `
                    <div class="bg-rose-50/50 border border-rose-100 rounded-3xl p-6 mt-2 text-left">
                        <ul class="space-y-3">
                            @foreach ($errors->all() as $error)
                                @php
                                    // Menerjemahkan error NIK bawaan Laravel agar lebih profesional
                                    $pesanError = str_replace('nik has already been taken.', 'Sistem mendeteksi NIK ini sudah terdaftar. Gunakan NIK yang belum didaftarkan.', $error);
                                    $pesanError = str_replace('The nik must be at least 16 characters.', 'NIK harus berisi tepat 16 digit angka.', $pesanError);
                                @endphp
                                <li class="flex items-start gap-3 text-rose-700">
                                    <div class="w-6 h-6 rounded-full bg-rose-200/50 flex items-center justify-center shrink-0 mt-0.5"><i class="fas fa-exclamation text-rose-600 text-xs"></i></div>
                                    <span class="font-bold text-[13px] leading-relaxed">{{ $pesanError }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                `,
                confirmButtonText: 'Koreksi Data'
            });
        @endif

        // ==========================================
        // 5. CORE LOGIC SUBMIT (ANTI-STUCK & BUG FIX)
        // ==========================================
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // KUNCI: Selalu tahan form untuk validasi UI

            const tglLahirVal = document.getElementById('tanggal_lahir').value;
            if (!tglLahirVal) return;

            // Kalkulasi Usia Bulan
            const dob = new Date(tglLahirVal);
            const today = new Date();
            let diffMonths = (today.getFullYear() - dob.getFullYear()) * 12;
            diffMonths -= dob.getMonth();
            diffMonths += today.getMonth();
            if (today.getDate() < dob.getDate()) diffMonths--;

            // Validasi Usia Lokal
            if (diffMonths >= 60 || diffMonths < 0) {
                nexusAlert.fire({
                    icon: 'warning',
                    title: 'Usia Tidak Memenuhi',
                    html: `
                        <div class="bg-amber-50 border border-amber-100 rounded-3xl p-5 mt-2">
                            <p class="text-[13px] font-bold text-amber-700 leading-relaxed">
                                Usia anak terdeteksi <b class="text-amber-900">${diffMonths} bulan</b>. Pendaftaran balita dibatasi maksimal 59 bulan.
                            </p>
                        </div>
                    `,
                    confirmButtonText: 'Tutup Peringatan',
                    customClass: { confirmButton: 'nexus-swal-confirm !bg-amber-500 hover:!bg-amber-600 !shadow-amber-500/30' }
                });
                return;
            }

            // Jika lulus semua validasi lokal -> Eksekusi Simpan
            setLoader(true);
            
            setTimeout(() => {
                // BYPASS EVENT LISTENER: Kirim data langsung ke Laravel
                HTMLFormElement.prototype.submit.call(form);
            }, 400); // Jeda smooth transisi animasi
        });
    });
</script>
@endpush