@extends('layouts.bidan')

@section('title', 'Validasi Pemeriksaan')
@section('page-name', 'Validasi Pemeriksaan')
@section('page-title', 'Validasi Pemeriksaan')

@php
    use Carbon\Carbon;

    $kategori = $kategori ?? data_get($ringkasan ?? [], 'kategori', 'balita');
    $parameter = collect($parameter ?? []);
    $ringkasan = $ringkasan ?? [];

    $kategoriMeta = [
        'balita' => ['label' => 'Balita', 'icon' => 'fa-baby', 'color' => 'emerald', 'bg' => 'bg-gradient-to-br from-emerald-500 to-teal-500'],
        'remaja' => ['label' => 'Remaja', 'icon' => 'fa-user-graduate', 'color' => 'teal', 'bg' => 'bg-gradient-to-br from-teal-500 to-cyan-500'],
        'lansia' => ['label' => 'Lansia', 'icon' => 'fa-person-cane', 'color' => 'sky', 'bg' => 'bg-gradient-to-br from-sky-500 to-blue-500'],
    ];

    $meta = $kategoriMeta[$kategori] ?? $kategoriMeta['balita'];

    $getValue = function ($item, array $keys, mixed $default = '-') {
        foreach ($keys as $key) {
            $value = data_get($item, $key);
            if ($value !== null && $value !== '') return $value;
        }
        return $default;
    };

    $formatDate = fn($date) => $date ? Carbon::parse($date)->translatedFormat('d M Y') : '-';
    $formatParameterValue = fn($value) => ($value === null || $value === '' || $value === '-') ? 'Belum diisi' : $value;
    $isEmptyParameter = fn($value) => $value === null || $value === '' || $value === '-' || $value === 'Belum diisi';

    $pasienNama = data_get($ringkasan, 'nama_pasien') ?? $getValue($pasien, ['nama_lengkap', 'nama', 'nama_balita', 'nama_remaja', 'nama_lansia'], 'Nama tidak tersedia');
    $pasienNik = data_get($ringkasan, 'nik_pasien') ?? $getValue($pasien, ['nik', 'nik_anak'], '-');
    $tanggalKunjungan = data_get($ringkasan, 'tanggal_kunjungan') ?? $formatDate(data_get($pemeriksaan, 'kunjungan.tanggal_kunjungan') ?? data_get($pemeriksaan, 'created_at'));

    $statusRingkasLabel = $kategori === 'balita' ? 'Status Gizi' : 'Status Ringkas Pemeriksaan';
    $statusRingkasPlaceholder = $kategori === 'balita' ? 'Contoh: Gizi baik' : 'Contoh: Dalam batas pemantauan';

    $statusRingkas = old('status_gizi', data_get($pemeriksaan, 'status_gizi', ''));
    $kesimpulan = old('kesimpulan_pemeriksaan', data_get($pemeriksaan, 'kesimpulan_pemeriksaan') ?? data_get($pemeriksaan, 'diagnosa') ?? '');
    $tindakan = old('tindakan', data_get($pemeriksaan, 'tindakan') ?? '');
    $catatanEdukasi = old('catatan_edukasi', data_get($pemeriksaan, 'catatan_edukasi') ?? data_get($pemeriksaan, 'edukasi') ?? '');
@endphp

@push('styles')
<style>
    /* OPTIMASI SCROLLING */
    html { scroll-behavior: smooth; }
    body { 
        background-color: #f1f5f9; 
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(150, 100%, 94%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }
    
    /* Widget Card Super Membulat (Squircle) */
    .widget-card {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: 2.5rem; 
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        transform: translateZ(0);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .widget-card:hover {
        transform: translateY(-2px) translateZ(0);
        box-shadow: 0 20px 40px -10px rgba(20, 184, 166, 0.15);
        background: rgba(255, 255, 255, 0.95);
    }

    /* Input Glass Soft */
    .input-soft {
        width: 100%;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 1.2rem;
        padding: 14px 16px;
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        outline: none;
        transition: all .25s ease;
    }
    .input-soft:focus {
        background: #ffffff;
        border-color: #14b8a6; 
        box-shadow: 0 0 0 4px rgba(20, 184, 166, .15);
    }
    .input-soft.is-invalid {
        border-color: #f43f5e;
        background: #fff1f2;
    }
    .input-soft.is-invalid:focus {
        box-shadow: 0 0 0 4px rgba(244, 63, 94, .15);
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #64748b;
    }
    .req-star { color: #f43f5e; margin-left: 2px; }
    .btn-pill { border-radius: 9999px; transition: all 0.2s ease; cursor: pointer;}
    .btn-pill:active { transform: scale(0.97); }

    .animate-pop-in { animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popIn { from { opacity: 0; transform: scale(.98) translateY(16px); } to { opacity: 1; transform: scale(1) translateY(0); } }

    /* Nexus Premium Modal/Alerts - Diubah Z-indexnya agar pasti di atas sidebar */
    .pc-modal-backdrop {
        position: fixed; inset: 0; z-index: 999999; display: none;
        align-items: center; justify-content: center;
        background: rgba(15, 23, 42, .6); backdrop-filter: blur(10px);
        padding: 1rem; opacity: 0; transition: opacity 0.3s ease;
    }
    .pc-modal-backdrop.is-open { display: flex; opacity: 1; }
    
    .pc-modal-card {
        width: 100%; max-width: 440px; background: white;
        border-radius: 2.5rem; padding: 2.5rem 2rem;
        transform: scale(0.9) translateY(20px); opacity: 0;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        position: relative; overflow: hidden;
    }
    .pc-modal-backdrop.is-open .pc-modal-card { transform: scale(1) translateY(0); opacity: 1; }
</style>
@endpush

@section('content')
<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1000px] mx-auto space-y-8 animate-pop-in">

    {{-- HEADER BANNER HIJAU MEWAH --}}
    <section class="relative overflow-hidden rounded-[3rem] bg-gradient-to-r from-teal-500 via-teal-400 to-emerald-400 p-8 sm:p-10 shadow-2xl shadow-teal-500/20 flex flex-col md:flex-row justify-between items-center gap-8 border-[6px] border-white/40">
        <div class="absolute inset-0 bg-white/10 backdrop-blur-[2px]"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-full flex flex-col gap-4 text-center md:text-left">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <a href="{{ route('bidan.pemeriksaan.index', ['tab' => 'pending']) }}" class="btn-pill inline-flex items-center gap-2 border border-white/30 bg-white/20 px-5 py-2 text-[10px] font-black uppercase tracking-widest text-white hover:bg-white/30 shadow-sm backdrop-blur-md">
                    <i class="fa-solid fa-arrow-left"></i> Batal Validasi
                </a>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-300 bg-amber-400/80 px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-white shadow-sm backdrop-blur-md">
                    <i class="fa-solid fa-clock-rotate-left"></i> Menunggu Keputusan
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/30 bg-white/20 px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-white shadow-sm backdrop-blur-md">
                    <i class="fa-solid {{ $meta['icon'] }}"></i> {{ $meta['label'] }}
                </span>
            </div>
            
            <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight leading-tight">
                Form Validasi Klinis
            </h1>

            <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-xl mx-auto md:mx-0">
                Tinjau parameter awal yang diinputkan Kader, kemudian berikan diagnosa akhir untuk disahkan ke sistem rekam medis terpadu.
            </p>
        </div>
    </section>

    {{-- System Alerts --}}
    @if(session('error') || $errors->any())
        <div class="bg-rose-50 border border-rose-200 rounded-3xl p-5 flex items-start gap-4 shadow-sm relative overflow-hidden">
            <div class="w-12 h-12 rounded-2xl bg-white text-rose-500 flex items-center justify-center text-xl shrink-0 shadow-sm border border-rose-100">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <h4 class="text-sm font-black text-rose-800">Gagal Menyimpan</h4>
                <p class="text-xs font-semibold text-rose-600 mt-0.5 mb-2">{{ session('error') ?? 'Mohon periksa kembali isian form Anda.' }}</p>
                @if($errors->any())
                    <ul class="text-[11px] font-bold text-rose-500/80 list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {{-- KOLOM KIRI (Info Warga & Parameter - Col 5) --}}
        <div class="lg:col-span-5 space-y-8">
            <section class="widget-card overflow-hidden">
                <div class="bg-slate-50/70 px-6 py-5 border-b border-slate-100 flex items-center justify-center">
                    <h5 class="font-black text-slate-700 text-xs uppercase tracking-widest flex items-center gap-2">
                        <i class="fa-solid fa-user-circle text-teal-500"></i> Identitas Pasien
                    </h5>
                </div>
                <div class="p-6 space-y-4">
                    <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Nama Lengkap</p>
                        <p class="text-base font-bold text-slate-800">{{ $pasienNama }}</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-1 bg-white border border-slate-100 rounded-2xl p-4 shadow-sm">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">NIK</p>
                            <p class="text-sm font-bold text-slate-600 font-mono">{{ $pasienNik }}</p>
                        </div>
                        <div class="flex-1 bg-white border border-slate-100 rounded-2xl p-4 shadow-sm">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Tanggal</p>
                            <p class="text-sm font-bold text-slate-600">{{ $tanggalKunjungan }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="widget-card overflow-hidden">
                <div class="bg-{{ $meta['color'] }}-50/50 px-6 py-5 border-b border-{{ $meta['color'] }}-100 flex items-center justify-center">
                    <h5 class="font-black text-{{ $meta['color'] }}-600 text-xs uppercase tracking-widest flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-list text-{{ $meta['color'] }}-500"></i> Parameter Kader
                    </h5>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-3">
                        @forelse($parameter as $item)
                            @php
                                $val = $formatParameterValue(data_get($item, 'value', '-'));
                                $isEmpty = $isEmptyParameter($val);
                                $icon = str_replace('ph-dot', 'fa-circle text-[8px]', data_get($item, 'icon', 'fa-circle text-[8px]'));
                                $icon = str_replace('ph-', 'fa-', $icon);
                            @endphp
                            <div class="bg-white border border-slate-100 rounded-[1.2rem] p-4 shadow-sm text-center">
                                <div class="flex items-center justify-center gap-1.5 mb-1.5">
                                    <i class="fa-solid {{ $icon }} text-slate-300"></i>
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 truncate">{{ data_get($item, 'label', '-') }}</p>
                                </div>
                                <p class="text-base font-black {{ $isEmpty ? 'text-slate-300' : 'text-slate-700' }}">{{ $val }}</p>
                            </div>
                        @empty
                            <div class="col-span-2 text-center py-6 opacity-70 bg-slate-50 rounded-[1.2rem] border border-dashed border-slate-200">
                                <p class="text-xs font-bold text-slate-400">Belum ada parameter</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>

        {{-- KOLOM KANAN (Form Input - Col 7) --}}
        <div class="lg:col-span-7">
            <form id="validasiForm" method="POST" action="{{ route('bidan.pemeriksaan.simpan-validasi', $pemeriksaan->id) }}">
                @csrf
                @method('PUT')

                <section class="widget-card overflow-hidden">
                    <div class="bg-slate-50/70 px-6 py-6 border-b border-slate-100 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-white border border-slate-100 text-teal-500 flex items-center justify-center text-xl shadow-sm">
                            <i class="fa-solid fa-file-signature"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-black text-slate-800 uppercase tracking-tight">Keputusan Medis</h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Inputan Validasi Bidan</p>
                        </div>
                    </div>

                    <div class="p-6 md:p-8 space-y-6">
                        <div>
                            <label for="status_gizi" class="form-label text-teal-600">
                                <i class="fa-solid fa-tag mr-1"></i> {{ $statusRingkasLabel }}
                            </label>
                            <input type="text" id="status_gizi" name="status_gizi" value="{{ $statusRingkas }}" maxlength="100" placeholder="{{ $statusRingkasPlaceholder }}"
                                   class="input-soft @error('status_gizi') is-invalid @enderror">
                        </div>

                        <div>
                            <label for="kesimpulan_pemeriksaan" class="form-label">
                                <i class="fa-solid fa-stethoscope text-teal-500 mr-1"></i> Diagnosa / Kesimpulan <span class="req-star">*</span>
                            </label>
                            <textarea id="kesimpulan_pemeriksaan" name="kesimpulan_pemeriksaan" rows="4" maxlength="1000" required placeholder="Contoh: Pertumbuhan normal, tidak ada indikasi penyakit penyerta."
                                      class="input-soft resize-none @error('kesimpulan_pemeriksaan') is-invalid @enderror">{{ $kesimpulan }}</textarea>
                            <div class="flex justify-between mt-1 px-1">
                                <p class="text-[10px] font-semibold text-slate-400">Wajib diisi.</p>
                                <p id="kesimpulanCounter" class="text-[10px] font-black text-slate-400">0/1000</p>
                            </div>
                        </div>

                        <div>
                            <label for="tindakan" class="form-label">
                                <i class="fa-solid fa-pills text-teal-500 mr-1"></i> Tindakan / Layanan <span class="req-star">*</span>
                            </label>
                            <textarea id="tindakan" name="tindakan" rows="3" maxlength="1000" required placeholder="Contoh: Pemberian vitamin, rujukan ke Puskesmas, dll."
                                      class="input-soft resize-none @error('tindakan') is-invalid @enderror">{{ $tindakan }}</textarea>
                            <div class="flex justify-between mt-1 px-1">
                                <p class="text-[10px] font-semibold text-slate-400">Wajib diisi.</p>
                                <p id="tindakanCounter" class="text-[10px] font-black text-slate-400">0/1000</p>
                            </div>
                        </div>

                        <div>
                            <label for="catatan_edukasi" class="form-label">
                                <i class="fa-solid fa-chalkboard-user text-teal-500 mr-1"></i> Edukasi Kesehatan
                            </label>
                            <textarea id="catatan_edukasi" name="catatan_edukasi" rows="3" maxlength="1000" placeholder="Contoh: Menjaga pola makan gizi seimbang."
                                      class="input-soft resize-none @error('catatan_edukasi') is-invalid @enderror">{{ $catatanEdukasi }}</textarea>
                            <div class="flex justify-between mt-1 px-1">
                                <p class="text-[10px] font-semibold text-slate-400">Opsional.</p>
                                <p id="edukasiCounter" class="text-[10px] font-black text-slate-400">0/1000</p>
                            </div>
                        </div>
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="p-6 md:p-8 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="{{ route('bidan.pemeriksaan.index', ['tab' => 'pending']) }}" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl font-bold text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-700 transition-all shadow-sm text-sm text-center">
                            <i class="fa-solid fa-times mr-1"></i> Batal
                        </a>
            
                        <button type="button" id="openSubmitModalBtn" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl font-black uppercase tracking-widest text-white bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 transition-all shadow-[0_4px_15px_rgba(16,185,129,.30)] hover:-translate-y-0.5 text-xs flex items-center justify-center gap-2">
                            <i class="fa-solid fa-check-circle text-sm"></i> Simpan Validasi
                        </button>
                    </div>
                </section>
            </form>
        </div>
    </div>
</div>

{{-- 
    MODAL AREA 
    Ditulis di luar container utama agar tidak terpengaruh CSS Transform/Overflow.
    JavaScript akan memastikannya ter-teleportasi ke <body>
--}}
<div id="pcSubmitModal" class="pc-modal-backdrop">
    <div class="pc-modal-card text-center">
        <div class="absolute -top-16 -left-16 w-32 h-32 bg-emerald-400/20 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-16 -right-16 w-32 h-32 bg-teal-400/20 rounded-full blur-2xl"></div>
        
        <div class="relative z-10">
            <div class="w-20 h-20 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center mx-auto mb-5 text-emerald-500 shadow-inner">
                <i class="fa-solid fa-cloud-arrow-up text-3xl"></i>
            </div>
            <h3 class="text-2xl font-black text-slate-800 mb-2">Simpan Validasi?</h3>
            <p class="text-sm font-medium text-slate-500 mb-8 leading-relaxed px-4">
                Data pemeriksaan ini akan disahkan dan disimpan permanen sebagai riwayat Rekam Medis pasien.
            </p>
            <div class="flex gap-3">
                <button type="button" id="pcCancelSubmit" class="btn-pill w-full flex-1 border border-slate-200 bg-white text-slate-700 px-4 py-3.5 text-sm font-bold shadow-sm hover:bg-slate-50 transition-all">
                    Kembali
                </button>
                <button type="button" id="pcConfirmSubmit" class="btn-pill w-full flex-1 bg-gradient-to-r from-teal-500 to-emerald-500 text-white px-4 py-3.5 text-sm font-bold shadow-md hover:from-teal-600 hover:to-emerald-600 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-check"></i> Sahkan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        'use strict';

        // --- TELEPORTASI MODAL KE BODY ---
        // Ini adalah kunci agar modal tampil full selayar tanpa terkurung elemen parent
        const modal = document.querySelector('#pcSubmitModal');
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        const form = document.querySelector('#validasiForm');
        
        // Character Counters
        const counters = [
            ['kesimpulan_pemeriksaan', 'kesimpulanCounter', 1000],
            ['tindakan', 'tindakanCounter', 1000],
            ['catatan_edukasi', 'edukasiCounter', 1000],
        ];

        counters.forEach(([inputId, counterId, max]) => {
            const input = document.getElementById(inputId);
            const counter = document.getElementById(counterId);
            if (!input || !counter) return;
            const update = () => { counter.textContent = `${input.value.length}/${max}`; };
            input.addEventListener('input', update, { passive: true });
            update();
        });

        // Modal Logic
        const submitBtn = document.querySelector('#openSubmitModalBtn');
        const cancelSubmit = document.querySelector('#pcCancelSubmit');
        const confirmSubmit = document.querySelector('#pcConfirmSubmit');

        function openModal() {
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            if (!modal) { form.submit(); return; }
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            if (!modal) return;
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        if (submitBtn) {
            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openModal();
            });
        }

        if (cancelSubmit) cancelSubmit.addEventListener('click', closeModal);
        if (modal) {
            modal.addEventListener('click', function(e) {
                if(e.target === modal) closeModal();
            });
        }

        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

        if (confirmSubmit) {
            confirmSubmit.addEventListener('click', function () {
                confirmSubmit.disabled = true;
                confirmSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
                if (submitBtn) submitBtn.disabled = true;
                form.submit();
            });
        }
    });
</script>
@endpush