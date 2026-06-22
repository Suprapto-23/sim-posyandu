@extends('layouts.bidan')

@section('title', 'Edit Jadwal Posyandu')
@section('page-name', 'Kelola Jadwal')
@section('page-title', 'Edit Jadwal Posyandu')

@php
    use Carbon\Carbon;

    // Memastikan mode edit aktif
    $mode = 'edit';
    $isEdit = true;

    $kategoriOptions = [
        'posyandu'    => ['label' => 'Posyandu Rutin', 'desc' => 'Pelayanan umum', 'icon' => 'fa-house-medical'],
        'imunisasi'   => ['label' => 'Imunisasi Balita', 'desc' => 'Khusus imunisasi', 'icon' => 'fa-syringe'],
        'pemeriksaan' => ['label' => 'Pemeriksaan Klinis', 'desc' => 'Tinjauan Bidan', 'icon' => 'fa-stethoscope'],
        'lainnya'     => ['label' => 'Kegiatan Lainnya', 'desc' => 'Agenda tambahan', 'icon' => 'fa-calendar-plus'],
    ];

    $targetOptions = [
        'semua'  => ['label' => 'Semua Sasaran', 'desc' => 'Balita, Remaja, Lansia', 'icon' => 'fa-users'],
        'balita' => ['label' => 'Balita', 'desc' => 'Khusus Balita', 'icon' => 'fa-baby'],
        'remaja' => ['label' => 'Remaja', 'desc' => 'Khusus Remaja', 'icon' => 'fa-user-graduate'],
        'lansia' => ['label' => 'Lansia', 'desc' => 'Khusus Lansia', 'icon' => 'fa-person-cane'],
    ];

    $statusOptions = [
        'aktif'      => ['label' => 'Aktif', 'desc' => 'Jadwal berlaku', 'icon' => 'fa-circle-check'],
        'selesai'    => ['label' => 'Selesai', 'desc' => 'Telah terlaksana', 'icon' => 'fa-flag-checkered'],
        'dibatalkan' => ['label' => 'Dibatalkan', 'desc' => 'Batal / Ditunda', 'icon' => 'fa-circle-xmark'],
    ];

    $selectedKategori = old('kategori', data_get($jadwal ?? [], 'kategori', 'posyandu'));
    $selectedTarget = old('target_peserta', data_get($jadwal ?? [], 'target_peserta', 'semua'));
    $selectedStatus = old('status', data_get($jadwal ?? [], 'status', 'aktif'));

    if ($selectedKategori === 'imunisasi') $selectedTarget = 'balita';

    $judulValue = old('judul', data_get($jadwal ?? [], 'judul', ''));
    $tanggalValue = old('tanggal', data_get($jadwal ?? [], 'tanggal') ? Carbon::parse($jadwal->tanggal)->format('Y-m-d') : now()->format('Y-m-d'));
    $mulaiValue = old('waktu_mulai', data_get($jadwal ?? [], 'waktu_mulai') ? Carbon::parse($jadwal->waktu_mulai)->format('H:i') : '08:00');
    $selesaiValue = old('waktu_selesai', data_get($jadwal ?? [], 'waktu_selesai') ? Carbon::parse($jadwal->waktu_selesai)->format('H:i') : '10:00');
    $lokasiValue = old('lokasi', data_get($jadwal ?? [], 'lokasi', ''));
    $deskripsiValue = old('deskripsi', data_get($jadwal ?? [], 'deskripsi', ''));

    $pageTitle = 'Edit Jadwal Posyandu';
    $pageDesc = 'Perbaiki rincian agenda pelayanan selama jadwal masih berstatus aktif.';
    $formAction = route('bidan.jadwal.update', $jadwal->id);
    $submitLabel = 'Simpan Perubahan';
@endphp

@push('styles')
<style>
    html { scroll-behavior: smooth; }
    body { background-color: #f4f7f6; } 

    .bg-mesh-fixed {
        position: fixed; inset: 0; z-index: -10;
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(150, 100%, 94%, 1) 0px, transparent 50%);
        pointer-events: none;
    }

    .widget-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        transform: translateZ(0); 
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .input-soft {
        width: 100%; background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: 1.2rem; padding: 14px 16px; font-size: 13px; font-weight: 700; color: #1e293b; outline: none; transition: all .25s ease;
    }
    .input-soft:focus { background: #ffffff; border-color: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, .15); }
    .input-soft.is-invalid { border-color: #f43f5e; background: #fff1f2; }
    
    .form-label { display: block; margin-bottom: 0.5rem; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: #64748b; }
    .req-star { color: #f43f5e; margin-left: 2px; }
    .btn-pill { border-radius: 9999px; transition: all 0.2s ease; cursor: pointer;}
    .btn-pill:active { transform: scale(0.97); }

    /* Tombol Pilihan Interaktif */
    .jadwal-choice {
        border: 2px solid #f1f5f9; background: #f8fafc;
        transition: all .2s ease; cursor: pointer; text-align: left;
    }
    .jadwal-choice:hover { border-color: rgba(16, 185, 129, .5); background: #ecfdf5; }
    .jadwal-choice.is-active {
        border-color: #10b981; background: #ecfdf5;
        box-shadow: 0 4px 15px -3px rgba(16, 185, 129, .2);
    }
    .jadwal-choice.is-disabled { opacity: 0.5; cursor: not-allowed; filter: grayscale(1); }

    .animate-pop-in { animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popIn { from { opacity: 0; transform: scale(.98) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }

    /* Nexus Modal */
    .pc-modal-backdrop {
        position: fixed !important; inset: 0 !important; z-index: 9999 !important; display: none;
        align-items: center; justify-content: center; background: rgba(15, 23, 42, .6); backdrop-filter: blur(10px); padding: 1rem; opacity: 0; transition: opacity 0.3s ease;
    }
    .pc-modal-backdrop.is-open { display: flex !important; opacity: 1; }
    .pc-modal-card {
        width: 100%; max-width: 440px; background: white; border-radius: 2.5rem; padding: 2.5rem 2rem;
        transform: scale(0.9) translateY(20px); opacity: 0; transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); position: relative; overflow: hidden;
    }
    .pc-modal-backdrop.is-open .pc-modal-card { transform: scale(1) translateY(0); opacity: 1; }
</style>
@endpush

@section('content')
<div class="bg-mesh-fixed"></div>

{{-- Wrapper difokuskan ke tengah (max-w-[1000px]) tanpa preview --}}
<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1000px] mx-auto space-y-8 animate-pop-in">

    {{-- HERO BANNER --}}
    <section class="relative overflow-hidden rounded-[3rem] bg-gradient-to-r from-emerald-500 via-teal-500 to-green-500 p-8 sm:p-10 shadow-2xl shadow-emerald-500/20 flex flex-col md:flex-row justify-between items-center gap-8 border-[6px] border-white/40">
        <div class="absolute inset-0 bg-white/10 backdrop-blur-[2px]"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-full flex flex-col gap-4 text-center md:text-left">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <a href="{{ route('bidan.jadwal.show', $jadwal->id) }}" class="btn-pill inline-flex items-center gap-2 border border-white/30 bg-white/20 px-5 py-2 text-[10px] font-black uppercase tracking-widest text-white hover:bg-white/30 shadow-sm backdrop-blur-md transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Batal / Kembali
                </a>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/30 bg-white/20 px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-white shadow-sm backdrop-blur-md">
                    <i class="fa-solid fa-pen"></i> Mode Edit
                </span>
            </div>
            
            <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight leading-tight">
                {{ $pageTitle }}
            </h1>
            <p class="text-emerald-50 text-sm font-medium leading-relaxed max-w-xl mx-auto md:mx-0">
                {{ $pageDesc }}
            </p>
        </div>
    </section>

    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 rounded-3xl p-5 flex items-start gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-2xl bg-white text-rose-500 flex items-center justify-center text-xl shrink-0 shadow-sm border border-rose-100"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div>
                <h4 class="text-sm font-black text-rose-800">Terdapat Kesalahan Input</h4>
                <ul class="mt-1 text-[11px] font-bold text-rose-500/80 list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    <form id="jadwalForm" method="POST" action="{{ $formAction }}">
        @csrf
        @method('PUT')
        
        <input type="hidden" name="kategori" id="kategoriInput" value="{{ $selectedKategori }}">
        <input type="hidden" name="target_peserta" id="targetInput" value="{{ $selectedTarget }}">
        <input type="hidden" name="status" id="statusInput" value="{{ $selectedStatus }}">

        {{-- FORMULIR UTAMA FOKUS TENGAH --}}
        <section class="widget-card overflow-hidden flex flex-col w-full border-t-4 border-t-teal-500">
            
            {{-- Form Header --}}
            <div class="bg-slate-50/70 px-6 py-6 border-b border-slate-100 flex items-center gap-4 shrink-0">
                <div class="w-12 h-12 rounded-2xl bg-white border border-slate-100 text-teal-500 flex items-center justify-center text-xl shadow-sm"><i class="fa-solid fa-pen-to-square"></i></div>
                <div>
                    <h2 class="text-base font-black text-slate-800 uppercase tracking-tight">Formulir Perubahan</h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Perbarui parameter jadwal di bawah</p>
                </div>
            </div>

            <div class="p-6 md:p-8 space-y-8">
                
                {{-- Kategori & Sasaran --}}
                <div class="space-y-6 bg-slate-50/50 p-5 md:p-6 rounded-3xl border border-slate-100">
                    <div>
                        <label class="form-label mb-3">1. Kategori Layanan <span class="req-star">*</span></label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach($kategoriOptions as $key => $option)
                                <button type="button" data-kategori="{{ $key }}" class="js-kategori-btn jadwal-choice rounded-2xl p-4 flex flex-col items-center justify-center text-center {{ $selectedKategori === $key ? 'is-active' : '' }}">
                                    <i class="fa-solid {{ $option['icon'] }} text-2xl mb-2 text-slate-300"></i>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-700">{{ $option['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="form-label mb-3">2. Target Sasaran <span class="req-star">*</span> <span id="targetHint" class="text-rose-500 font-semibold normal-case float-right text-[10px]"></span></label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach($targetOptions as $key => $option)
                                <button type="button" data-target="{{ $key }}" class="js-target-btn jadwal-choice rounded-2xl p-4 flex flex-col items-center justify-center text-center {{ $selectedTarget === $key ? 'is-active' : '' }}">
                                    <i class="fa-solid {{ $option['icon'] }} text-2xl mb-2 text-slate-300"></i>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-700">{{ $option['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Status Khusus Edit --}}
                <div class="bg-amber-50/50 p-5 md:p-6 rounded-3xl border border-amber-100">
                    <label class="form-label mb-3 text-amber-700">3. Status Pelaksanaan <span class="req-star">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach($statusOptions as $key => $option)
                            <button type="button" data-status="{{ $key }}" class="js-status-btn jadwal-choice rounded-2xl p-4 flex flex-col items-center justify-center text-center {{ $selectedStatus === $key ? 'is-active' : '' }}">
                                <i class="fa-solid {{ $option['icon'] }} text-2xl mb-2 text-slate-400"></i>
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-700">{{ $option['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Informasi Dasar --}}
                <div class="space-y-6">
                    <div>
                        <label class="form-label">Judul Kegiatan <span class="req-star">*</span></label>
                        <input type="text" id="judul" name="judul" value="{{ $judulValue }}" maxlength="191" required placeholder="Contoh: Posyandu Balita Bulan Juni" class="input-soft @error('judul') is-invalid @enderror">
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="form-label">Tanggal Pelaksanaan <span class="req-star">*</span></label>
                            <input type="date" id="tanggal" name="tanggal" value="{{ $tanggalValue }}" required class="input-soft cursor-pointer @error('tanggal') is-invalid @enderror">
                        </div>
                        <div>
                            <label class="form-label">Jam Mulai <span class="req-star">*</span></label>
                            <input type="time" id="waktu_mulai" name="waktu_mulai" value="{{ $mulaiValue }}" required class="input-soft cursor-pointer @error('waktu_mulai') is-invalid @enderror">
                        </div>
                        <div>
                            <label class="form-label">Jam Selesai <span class="req-star">*</span></label>
                            <input type="time" id="waktu_selesai" name="waktu_selesai" value="{{ $selesaiValue }}" required class="input-soft cursor-pointer @error('waktu_selesai') is-invalid @enderror">
                        </div>
                    </div>
                    
                    <div>
                        <label class="form-label">Lokasi / Tempat <span class="req-star">*</span></label>
                        <input type="text" id="lokasi" name="lokasi" value="{{ $lokasiValue }}" maxlength="191" required placeholder="Contoh: Balai Desa Bantarkulon" class="input-soft @error('lokasi') is-invalid @enderror">
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="form-label">Deskripsi Tambahan (Opsional)</label>
                    <textarea id="deskripsi" name="deskripsi" rows="3" maxlength="1000" placeholder="Keterangan tambahan untuk warga (misal: Bawa buku KIA)..." class="input-soft resize-none">{{ $deskripsiValue }}</textarea>
                </div>

            </div>

            {{-- Action Buttons Bottom --}}
            <div class="p-6 md:p-8 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-end gap-3 mt-auto shrink-0">
                <a href="{{ route('bidan.jadwal.show', $jadwal->id) }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-bold text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-700 transition-all shadow-sm text-sm text-center">
                    Batal
                </a>
                <button type="button" id="openSubmitModalBtn" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-black uppercase tracking-widest text-white bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 transition-all shadow-[0_4px_15px_rgba(16,185,129,.30)] hover:-translate-y-0.5 text-xs flex items-center justify-center gap-2">
                    <i class="fa-solid fa-save text-sm"></i> {{ $submitLabel }}
                </button>
            </div>
        </section>
    </form>

    {{-- MODAL KONFIRMASI --}}
    <div id="pcSubmitModal" class="pc-modal-backdrop">
        <div class="pc-modal-card text-center">
            <div class="absolute -top-16 -left-16 w-32 h-32 bg-emerald-400/20 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -bottom-16 -right-16 w-32 h-32 bg-teal-400/20 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="relative z-10">
                <div class="w-20 h-20 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center mx-auto mb-5 text-emerald-500 shadow-inner">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-800 mb-2">Simpan Perubahan?</h3>
                <p class="text-sm font-medium text-slate-500 mb-8 leading-relaxed px-4">Pastikan data tanggal, waktu, target, serta status jadwal sudah sesuai agar tidak membingungkan peserta Posyandu.</p>
                <div class="flex gap-3">
                    <button type="button" id="pcCancelSubmit" class="btn-pill w-full flex-1 border border-slate-200 bg-white text-slate-700 px-4 py-3.5 text-sm font-bold shadow-sm hover:bg-slate-50 transition-all">Kembali</button>
                    <button type="button" id="pcConfirmSubmit" class="btn-pill w-full flex-1 bg-gradient-to-r from-teal-500 to-emerald-500 text-white px-4 py-3.5 text-sm font-bold shadow-md hover:from-teal-600 hover:to-emerald-600 transition-all flex items-center justify-center gap-2"><i class="fa-solid fa-check"></i> Simpan</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(() => {
    // Variable Deklarasi
    const form = document.getElementById('jadwalForm');
    const kategoriInput = document.getElementById('kategoriInput');
    const targetInput = document.getElementById('targetInput');
    const statusInput = document.getElementById('statusInput');

    const katBtns = Array.from(document.querySelectorAll('.js-kategori-btn'));
    const targBtns = Array.from(document.querySelectorAll('.js-target-btn'));
    const statBtns = Array.from(document.querySelectorAll('.js-status-btn'));
    const targetHint = document.getElementById('targetHint');

    let sKat = kategoriInput.value || 'posyandu';
    let sTarg = targetInput.value || 'semua';
    let sStat = statusInput?.value || 'aktif';

    // Helpers
    const setBtnActive = (btns, key, dataset) => btns.forEach(b => {
        const isMatched = b.dataset[dataset] === key;
        b.classList.toggle('is-active', isMatched);
        const icon = b.querySelector('i');
        if(icon) {
            icon.classList.toggle('text-slate-300', !isMatched);
            icon.classList.toggle('text-emerald-500', isMatched);
        }
    });

    const lockTarget = () => {
        const isImun = sKat === 'imunisasi';
        if (isImun) { sTarg = 'balita'; targetInput.value = 'balita'; }
        targBtns.forEach(b => {
            const dis = isImun && b.dataset.target !== 'balita';
            b.classList.toggle('is-disabled', dis);
            b.disabled = dis;
        });
        if (targetHint) targetHint.textContent = isImun ? '(Terkunci untuk Imunisasi)' : '';
        setBtnActive(targBtns, sTarg, 'target');
    };

    // Button Listeners
    katBtns.forEach(b => b.addEventListener('click', () => { sKat = b.dataset.kategori; kategoriInput.value = sKat; setBtnActive(katBtns, sKat, 'kategori'); lockTarget(); }));
    targBtns.forEach(b => b.addEventListener('click', () => { if(sKat==='imunisasi' && b.dataset.target!=='balita') return; sTarg = b.dataset.target; targetInput.value = sTarg; setBtnActive(targBtns, sTarg, 'target'); }));
    statBtns.forEach(b => b.addEventListener('click', () => { sStat = b.dataset.status; statusInput.value = sStat; setBtnActive(statBtns, sStat, 'status'); }));

    // Modal Logic
    const modal = document.getElementById('pcSubmitModal');
    document.getElementById('openSubmitModalBtn').addEventListener('click', () => { if(form.reportValidity()) modal.classList.add('is-open'); });
    document.getElementById('pcCancelSubmit').addEventListener('click', () => modal.classList.remove('is-open'));
    document.getElementById('pcConfirmSubmit').addEventListener('click', function() {
        this.disabled = true; this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...'; form.submit();
    });

    // Initial Trigger
    setBtnActive(katBtns, sKat, 'kategori');
    setBtnActive(targBtns, sTarg, 'target');
    if(statusInput) setBtnActive(statBtns, sStat, 'status');
    lockTarget();
})();
</script>
@endpush