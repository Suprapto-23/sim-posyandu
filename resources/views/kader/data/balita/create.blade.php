@extends('layouts.kader')

@section('title', 'Tambah Data Balita')
@section('page-name', 'Tambah Data Balita')
@section('page-title', 'Tambah Data Balita')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $routeHas = fn ($name) => Route::has($name);

    $backRoute = $routeHas('kader.data.balita.index')
        ? route('kader.data.balita.index')
        : url('/kader/data/balita');

    $storeRoute = $routeHas('kader.data.balita.store')
        ? route('kader.data.balita.store')
        : url('/kader/data/balita');

    $selectedGender = old('jenis_kelamin');

    $sessionType = session('success')
        ? 'success'
        : (session('warning') ? 'warning' : (session('error') ? 'error' : null));

    $sessionMessage = session('success') ?? session('warning') ?? session('error');
@endphp

@push('styles')
<style>
    body {
        background-color: #f8fafc;
        background-image: radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
                          radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }

    .animate-pop-in {
        animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }

    @keyframes popIn {
        from { opacity: 0; transform: scale(.96) translateY(12px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    /* Input Form Clean Style */
    .input-soft {
        width: 100%;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 12px 16px;
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        outline: none;
        transition: all .22s ease;
    }
    
    textarea.input-soft {
        min-height: 100px;
        resize: vertical;
        line-height: 1.6;
    }

    .input-soft:focus {
        background: #ffffff;
        border-color: #10b981; 
        box-shadow: 0 0 0 4px rgba(16, 185, 129, .12);
    }

    .input-soft::placeholder { color: #94a3b8; font-weight: 600; }

    .form-error {
        border-color: #f43f5e !important; 
        box-shadow: 0 0 0 4px rgba(244, 63, 94, .12) !important;
    }

    /* Gender Card Clean Style */
    .gender-card {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .gender-card:hover { transform: translateY(-1px); border-color: rgba(16, 185, 129, 0.4); }
    .gender-card:has(input:checked) {
        border-color: #10b981;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
    }

    .gender-dot {
        display: flex; height: 2rem; width: 2rem; flex-shrink: 0; align-items: center; justify-content: center;
        border-radius: 0.75rem; font-size: 0.8rem; font-weight: 900; transition: all 0.2s ease;
    }

    /* Modal Full Screen */
    .pc-modal-backdrop {
        position: fixed; inset: 0; z-index: 999999; display: none; align-items: center; justify-content: center;
        background: rgba(15, 23, 42, .65); backdrop-filter: blur(12px); padding: 1rem; width: 100vw; height: 100vh;
    }
    .pc-modal-backdrop.is-open { display: flex; }
    .pc-modal-card {
        width: 100%; max-width: 420px; background: white; border-radius: 2rem; padding: 0; overflow: hidden;
        transform: scale(0.95) translateY(15px); opacity: 0; transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35);
    }
    .pc-modal-backdrop.is-open .pc-modal-card { transform: scale(1) translateY(0); opacity: 1; }
</style>
@endpush

<div class="max-w-[1080px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 mt-6">

    {{-- Hero Section --}}
    <section class="bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-600 rounded-[2.5rem] p-6 sm:p-8 md:p-10 mb-6 sm:mb-8 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(16,185,129,.35)] border border-white/20 text-center lg:text-left">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[70px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row gap-6 lg:gap-8 lg:items-center justify-between">
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-flex items-center justify-center lg:justify-start gap-2 text-white/85 text-[10px] font-black uppercase tracking-widest mb-3 sm:mb-4">
                    <a href="{{ $backRoute }}" class="hover:text-white transition-colors">Data Sasaran</a>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span class="text-white">Tambah Baru</span>
                </div>

                <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-white tracking-tight">
                    Registrasi Balita
                </h1>

                <p class="text-teal-50 text-xs sm:text-sm font-medium max-w-xl mx-auto lg:mx-0 mt-2 sm:mt-3 leading-relaxed">
                    Tambahkan data sasaran Balita baru untuk keperluan pencatatan pengukuran fisik, imunisasi, dan pelaporan Posyandu.
                </p>
            </div>

            <div class="flex w-full lg:w-auto shrink-0 justify-center">
                <a href="{{ $backRoute }}" class="inline-flex items-center justify-center w-full lg:w-auto bg-white/20 hover:bg-white/30 text-white border border-white/30 px-6 py-3 sm:py-3.5 rounded-full text-xs sm:text-sm font-bold backdrop-blur-md transition-all shadow-sm">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>
    </section>

    {{-- SYSTEM ALERT & FIX REDIRECT BACKEND --}}
    @if($sessionType && $sessionMessage)
        @php
            $alertClass = match ($sessionType) {
                'error' => 'border-rose-200 bg-rose-50 text-rose-800',
                'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
                default => 'border-emerald-200 bg-emerald-50 text-emerald-800',
            };
            $alertIcon = match ($sessionType) {
                'error' => 'fa-triangle-exclamation text-rose-500',
                'warning' => 'fa-circle-exclamation text-amber-500',
                default => 'fa-circle-check text-emerald-500',
            };
            $alertTitle = match ($sessionType) {
                'error' => 'Gagal Disimpan',
                'warning' => 'Perhatian',
                default => 'Berhasil Disimpan!',
            };
        @endphp

        <div class="rounded-2xl p-4 sm:p-5 shadow-sm border flex items-center gap-3 sm:gap-4 {{ $alertClass }} mb-6 sm:mb-8">
            <div class="bg-white rounded-full w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center shrink-0 shadow-inner">
                <i class="fa-solid {{ $alertIcon }} text-base sm:text-lg"></i>
            </div>
            <div class="flex-1">
                <h3 class="font-black text-xs sm:text-sm">{{ $alertTitle }}</h3>
                <p class="font-medium text-[11px] sm:text-xs mt-0.5 opacity-80">{{ $sessionMessage }}</p>
            </div>
            
            @if($sessionType === 'success')
                <div class="shrink-0">
                    <i class="fa-solid fa-circle-notch fa-spin text-emerald-500 text-lg"></i>
                </div>
            @endif
        </div>

        @if($sessionType === 'success')
            {{-- AUTO REDIRECT SCRIPT (Menambal Bug Controller) --}}
            <script>
                setTimeout(function() {
                    window.location.href = "{{ $backRoute }}";
                }, 1500); 
            </script>
        @endif
    @endif

    {{-- VALIDATION ERROR ALERT --}}
    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 sm:p-5 mb-6 sm:mb-8 flex items-start gap-3 sm:gap-4 shadow-sm">
            <div class="bg-white rounded-full w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center shrink-0 shadow-inner mt-0.5">
                <i class="fa-solid fa-triangle-exclamation text-rose-500 text-base sm:text-lg"></i>
            </div>
            <div>
                <h3 class="font-black text-xs sm:text-sm text-rose-800">Data belum bisa disimpan</h3>
                <ul class="text-[11px] sm:text-xs font-medium mt-1.5 text-rose-700/80 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>- {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Form Container --}}
    <form action="{{ $storeRoute }}" method="POST" id="balitaCreateForm">
        @csrf

        {{-- BAGIAN 1: IDENTITAS BALITA --}}
        <section class="bg-white rounded-[1.5rem] sm:rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden mb-6 sm:mb-8">
            <div class="bg-slate-50/70 px-5 py-4 sm:px-8 sm:py-6 border-b border-slate-100 flex items-center justify-between">
                <h5 class="font-black text-slate-700 text-xs sm:text-sm uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-child-reaching text-emerald-500"></i>
                    1. Identitas Balita
                </h5>
                <span class="text-[9px] sm:text-[10px] font-bold text-rose-500 bg-rose-50 px-2.5 py-1 rounded-full border border-rose-100">* Wajib Diisi</span>
            </div>

            <div class="p-5 sm:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">

                    <div class="space-y-1.5 sm:space-y-2 md:col-span-2">
                        <label class="text-[10px] sm:text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required class="input-soft @error('nama_lengkap') form-error @enderror" placeholder="Contoh: Aisyah Putri Ramadhani">
                    </div>

                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[10px] sm:text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            NIK Balita <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="nik" name="nik" value="{{ old('nik') }}" required maxlength="16" inputmode="numeric" class="input-soft @error('nik') form-error @enderror" placeholder="16 Digit NIK">
                    </div>

                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[10px] sm:text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Jenis Kelamin <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                            <label class="gender-card">
                                <input type="radio" name="jenis_kelamin" value="L" class="peer sr-only" {{ $selectedGender === 'L' ? 'checked' : '' }} required>
                                <span class="gender-dot bg-sky-50 text-sky-600 peer-checked:bg-sky-500 peer-checked:text-white"><i class="fa-solid fa-mars"></i></span>
                                <span class="text-[11px] sm:text-xs font-bold text-slate-700">Laki-laki</span>
                            </label>
                            <label class="gender-card">
                                <input type="radio" name="jenis_kelamin" value="P" class="peer sr-only" {{ $selectedGender === 'P' ? 'checked' : '' }} required>
                                <span class="gender-dot bg-rose-50 text-rose-500 peer-checked:bg-rose-500 peer-checked:text-white"><i class="fa-solid fa-venus"></i></span>
                                <span class="text-[11px] sm:text-xs font-bold text-slate-700">Perempuan</span>
                            </label>
                        </div>
                    </div>

                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[10px] sm:text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Tempat Lahir <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" required class="input-soft @error('tempat_lahir') form-error @enderror" placeholder="Kota/Kabupaten Lahir">
                    </div>

                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[10px] sm:text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Tanggal Lahir <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" max="{{ now()->format('Y-m-d') }}" required class="input-soft @error('tanggal_lahir') form-error @enderror">
                    </div>

                </div>
            </div>
        </section>

        {{-- BAGIAN 2: KELUARGA & DOMISILI --}}
        <section class="bg-white rounded-[1.5rem] sm:rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden mb-6 sm:mb-8">
            <div class="bg-slate-50/70 px-5 py-4 sm:px-8 sm:py-6 border-b border-slate-100 flex items-center justify-between">
                <h5 class="font-black text-slate-700 text-xs sm:text-sm uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-house-chimney-user text-emerald-500"></i>
                    2. Orang Tua & Domisili
                </h5>
            </div>

            <div class="p-5 sm:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">

                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[10px] sm:text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Nama Ibu <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_ibu" value="{{ old('nama_ibu') }}" required class="input-soft @error('nama_ibu') form-error @enderror" placeholder="Nama Ibu Kandung">
                    </div>

                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[10px] sm:text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Nama Ayah
                        </label>
                        <input type="text" name="nama_ayah" value="{{ old('nama_ayah') }}" class="input-soft" placeholder="Opsional">
                    </div>

                    <div class="space-y-1.5 sm:space-y-2 md:col-span-2">
                        <label class="text-[10px] sm:text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Alamat Tinggal <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="alamat" required class="input-soft @error('alamat') form-error @enderror" placeholder="Tuliskan nama jalan, RT/RW, dan Desa">{{ old('alamat') }}</textarea>
                    </div>

                </div>
            </div>
        </section>

        {{-- BAGIAN 3: DATA LAHIR --}}
        <section class="bg-white rounded-[1.5rem] sm:rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden mb-6 sm:mb-8">
            <div class="bg-slate-50/70 px-5 py-4 sm:px-8 sm:py-6 border-b border-slate-100 flex items-center justify-between">
                <h5 class="font-black text-slate-700 text-xs sm:text-sm uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-weight-scale text-emerald-500"></i>
                    3. Data Antropometri Saat Lahir
                </h5>
                <span class="text-[9px] sm:text-[10px] font-bold text-slate-500 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-full">Opsional</span>
            </div>

            <div class="p-5 sm:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">

                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[10px] sm:text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Berat Lahir
                        </label>
                        <div class="relative">
                            <input type="number" name="berat_lahir" value="{{ old('berat_lahir') }}" step="0.01" min="0" max="20" class="input-soft pr-12" placeholder="Contoh: 3.2">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] sm:text-xs font-bold text-slate-400">kg</span>
                        </div>
                    </div>

                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-[10px] sm:text-[11px] font-black text-slate-500 uppercase tracking-widest">
                            Panjang Lahir
                        </label>
                        <div class="relative">
                            <input type="number" name="panjang_lahir" value="{{ old('panjang_lahir') }}" step="0.1" min="0" max="100" class="input-soft pr-12" placeholder="Contoh: 49.0">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] sm:text-xs font-bold text-slate-400">cm</span>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- ACTION BUTTONS --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 mt-8 sm:mt-10">
            <a href="{{ $backRoute }}" class="w-full sm:w-auto px-6 sm:px-8 py-3 sm:py-3.5 rounded-xl font-bold text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-700 transition-all shadow-sm text-xs sm:text-sm text-center">
                Batal
            </a>

            <button type="button" id="openCreateConfirm" class="w-full sm:w-auto px-8 sm:px-10 py-3 sm:py-3.5 rounded-xl font-bold text-white bg-emerald-600 hover:bg-emerald-700 hover:-translate-y-0.5 transition-all shadow-[0_4px_15px_rgba(16,185,129,.30)] text-xs sm:text-sm flex items-center justify-center gap-2">
                <i class="fas fa-save"></i>
                Simpan Data Balita
            </button>
        </div>
    </form>
</div>
@endsection

@push('modals')
{{-- MODAL KONFIRMASI --}}
<div id="nexusCreateConfirm" class="pc-modal-backdrop">
    <div class="pc-modal-card">
        <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 to-teal-700 px-6 py-6 text-white text-center">
            <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-white/10 pointer-events-none"></div>
            <div class="absolute -bottom-16 left-8 h-28 w-44 rounded-t-[80px] bg-amber-300/15 pointer-events-none"></div>

            <div class="relative z-10">
                <i class="fa-solid fa-circle-exclamation text-3xl sm:text-4xl mb-2 sm:mb-3 opacity-90"></i>
                <h3 class="text-lg sm:text-xl font-black tracking-tight mb-1">Simpan Data Balita?</h3>
                <p class="text-[11px] sm:text-xs font-semibold leading-relaxed opacity-80 px-2 sm:px-4">
                    Sistem akan menyimpan dan memeriksa ketersediaan akun warga terkait.
                </p>
            </div>
        </div>

        <div class="p-5 sm:p-6 bg-white text-center">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-[11px] sm:text-xs font-bold leading-5 sm:leading-6 text-amber-800 mb-4 sm:mb-5 text-left">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Pastikan NIK, nama, dan tanggal lahir sudah sesuai dengan buku KIA/KMS.
            </div>

            <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                <button type="button" id="nexusCreateCancel" class="w-full h-10 sm:h-11 rounded-xl border border-slate-200 bg-white text-xs sm:text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                    Periksa Lagi
                </button>
                <button type="button" id="nexusCreateOk" class="w-full h-10 sm:h-11 rounded-xl bg-emerald-600 text-xs sm:text-sm font-bold text-white shadow-md hover:bg-emerald-700 transition-colors">
                    Ya, Simpan
                </button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    const initCreateForm = () => {
        const form = document.getElementById('balitaCreateForm');
        
        // Mencegah script diinisialisasi berkali-kali pada navigasi SPA
        if (!form || form.dataset.jsInitialized) return;
        form.dataset.jsInitialized = 'true';

        const nikInput = document.getElementById('nik');
        const openButton = document.getElementById('openCreateConfirm');
        const cancelButton = document.getElementById('nexusCreateCancel');
        const okButton = document.getElementById('nexusCreateOk');
        let modal = document.getElementById('nexusCreateConfirm');

        // Pembersihan (Cleanup) duplikasi modal jika ada efek dari Turbolinks/Livewire navigate
        const existingModals = document.querySelectorAll('.pc-modal-backdrop#nexusCreateConfirm');
        if (existingModals.length > 1) {
            existingModals.forEach((el, index) => {
                if (index > 0) el.remove();
            });
        }
        modal = document.getElementById('nexusCreateConfirm'); 

        // Pindahkan modal ke luar kontainer form/body agar z-index layar penuh aman
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        function openModal() {
            modal?.classList.add('is-open');
            document.body.style.overflow = 'hidden'; 
        }

        function closeModal() {
            modal?.classList.remove('is-open');
            document.body.style.overflow = ''; 
        }

        // Filter validasi input NIK
        nikInput?.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 16);
        });

        // Event trigger dengan cek validasi form bawaan HTML5
        openButton?.addEventListener('click', function (event) {
            event.preventDefault();
            if (form.checkValidity()) {
                openModal();
            } else {
                form.reportValidity();
            }
        });

        // Event Penutupan Modal
        cancelButton?.addEventListener('click', closeModal);
        modal?.addEventListener('click', function (event) {
            if (event.target === modal) { closeModal(); }
        });
        
        // Listener keyboard Escape
        if (!window.createModalKeyListenerAdded) {
            document.addEventListener('keydown', function (event) {
                const currentModal = document.getElementById('nexusCreateConfirm');
                if (event.key === 'Escape' && currentModal?.classList.contains('is-open')) {
                    currentModal.classList.remove('is-open');
                    document.body.style.overflow = '';
                }
            });
            window.createModalKeyListenerAdded = true;
        }

        // Event Submit dan Loading Status
        okButton?.addEventListener('click', function () {
            closeModal();
            
            okButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            okButton.disabled = true;

            HTMLFormElement.prototype.submit.call(form);
        });
    };

    // Eksekusi fungsi inisialisasi menyesuaikan arsitektur request halaman (Hard-Reload & SPA Navigate)
    document.addEventListener('DOMContentLoaded', initCreateForm);
    document.addEventListener('livewire:navigated', initCreateForm);
    document.addEventListener('turbo:load', initCreateForm);
</script>
@endpush