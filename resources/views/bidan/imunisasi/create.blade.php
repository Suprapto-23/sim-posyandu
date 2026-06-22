@extends('layouts.bidan')

@section('title', ($mode ?? 'create') === 'edit' ? 'Perbaiki Catatan Imunisasi' : 'Catat Imunisasi Balita')
@section('page-name', 'Imunisasi')
@section('page-title', ($mode ?? 'create') === 'edit' ? 'Perbaiki Catatan Imunisasi' : 'Catat Imunisasi Balita')

@php
    use Carbon\Carbon;

    $mode = $mode ?? 'create';
    $isEdit = $mode === 'edit' && !empty($imunisasi);
    $balitas = collect($balitas ?? []);

    $programOptions = $programOptions ?? [
        'BCG' => 'BCG',
        'Polio' => 'Polio',
        'DPT-HB-Hib' => 'DPT-HB-Hib',
        'Hepatitis B' => 'Hepatitis B',
        'Campak / MR' => 'Campak / MR',
        'IPV' => 'IPV',
        'PCV' => 'PCV',
        'Rotavirus' => 'Rotavirus',
        'Lainnya' => 'Lainnya',
    ];

    $getValue = function ($item, array $keys, mixed $default = '-') {
        foreach ($keys as $key) {
            $value = data_get($item, $key);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }
        return $default;
    };

    $formatInputDate = function ($date) {
        if (!$date || $date === '-') return now()->format('Y-m-d');
        try { return Carbon::parse($date)->format('Y-m-d'); } catch (\Throwable $e) { return now()->format('Y-m-d'); }
    };

    $getBalitaName = fn($balita) => $getValue($balita, ['nama_lengkap', 'nama', 'nama_balita'], 'Nama tidak tersedia');
    $getBalitaNik = fn($balita) => $getValue($balita, ['nik', 'nik_anak'], '-');
    $getBalitaOrtu = fn($balita) => $getValue($balita, ['nama_ibu', 'nama_ayah', 'nama_wali'], '-');
    $getBalitaAlamat = fn($balita) => $getValue($balita, ['alamat', 'alamat_lengkap', 'dusun'], '-');

    $formAction = $isEdit ? route('bidan.imunisasi.update', $imunisasi->id) : route('bidan.imunisasi.store');

    $selectedBalitaId = old('balita_id', data_get($selectedBalita, 'id') ?? data_get($imunisasi, 'balita_id') ?? data_get($imunisasi, 'kunjungan.pasien_id') ?? '');
    $selectedJenis = old('jenis_imunisasi', data_get($imunisasi, 'jenis_imunisasi') ?? data_get($imunisasi, 'nama_imunisasi') ?? '');
    $selectedVaksin = old('vaksin', data_get($imunisasi, 'vaksin') ?? data_get($imunisasi, 'nama_vaksin') ?? '');
    $selectedDosis = old('dosis', data_get($imunisasi, 'dosis') ?? data_get($imunisasi, 'dosis_ke') ?? '');
    $selectedBatch = old('batch_number', data_get($imunisasi, 'batch_number') ?? data_get($imunisasi, 'no_batch') ?? data_get($imunisasi, 'nomor_batch') ?? '');
    $selectedTanggal = old('tanggal_imunisasi', $formatInputDate(data_get($imunisasi, 'tanggal_imunisasi') ?? data_get($imunisasi, 'tanggal') ?? null));
    $selectedKeterangan = old('keterangan', data_get($imunisasi, 'keterangan') ?? data_get($imunisasi, 'catatan') ?? '');

    $selectedBalita = $selectedBalita ?? $balitas->firstWhere('id', $selectedBalitaId);

    $selectedBalitaName = $selectedBalita ? $getBalitaName($selectedBalita) : '-';
    $selectedBalitaNik = $selectedBalita ? $getBalitaNik($selectedBalita) : '-';
    $selectedBalitaOrtu = $selectedBalita ? $getBalitaOrtu($selectedBalita) : '-';
    $selectedBalitaAlamat = $selectedBalita ? $getBalitaAlamat($selectedBalita) : '-';

    $pageTitle = $isEdit ? 'Perbaiki Catatan Imunisasi' : 'Catat Imunisasi Balita';
    $pageDesc = $isEdit ? 'Perbaiki catatan imunisasi Balita yang sudah tersimpan di sistem.' : 'Catat layanan imunisasi baru untuk Balita yang terdaftar.';
    $submitLabel = $isEdit ? 'Simpan Perbaikan' : 'Simpan Imunisasi';
@endphp

@push('styles')
<style>
    html { scroll-behavior: smooth; }
    body { background-color: #f1f5f9; }
    .bg-mesh-fixed {
        position: fixed; inset: 0; z-index: -10;
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(150, 100%, 94%, 1) 0px, transparent 50%);
        pointer-events: none;
    }

    .widget-card {
        background: #ffffff; 
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 2.5rem; 
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        transform: translateZ(0); 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .input-soft {
        width: 100%; background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: 1.2rem; padding: 14px 16px; font-size: 13px;
        font-weight: 700; color: #1e293b; outline: none; transition: all .25s ease;
    }
    .input-soft:focus {
        background: #ffffff; border-color: #14b8a6; box-shadow: 0 0 0 4px rgba(20, 184, 166, .15);
    }
    .input-soft.is-invalid { border-color: #f43f5e; background: #fff1f2; }

    .form-label { display: block; margin-bottom: 0.5rem; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: #64748b; }
    .req-star { color: #f43f5e; margin-left: 2px; }
    .btn-pill { border-radius: 9999px; transition: all 0.2s ease; cursor: pointer;}
    .btn-pill:active { transform: scale(0.97); }

    .animate-pop-in { animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popIn { from { opacity: 0; transform: scale(.98) translateY(16px); } to { opacity: 1; transform: scale(1) translateY(0); } }

    /* Custom Live Search */
    .slim-scroll::-webkit-scrollbar { width: 6px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: rgba(20, 184, 166, 0.3); border-radius: 9999px; }
    
    .balita-picker-result.is-selected { border-color: rgba(20, 184, 166, .45); background: rgba(240, 253, 250, .9); }
    .balita-picker-result.is-hidden { display: none !important; }

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

<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1280px] mx-auto space-y-8 animate-pop-in">

    {{-- HERO BANNER --}}
    <section class="relative overflow-hidden rounded-[3rem] bg-gradient-to-r from-teal-500 via-teal-400 to-emerald-400 p-8 sm:p-10 shadow-2xl shadow-teal-500/20 flex flex-col md:flex-row justify-between items-center gap-8 border-[6px] border-white/40">
        <div class="absolute inset-0 bg-white/10 backdrop-blur-[2px]"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-full flex flex-col gap-4 text-center md:text-left">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <a href="{{ route('bidan.imunisasi.index') }}" class="btn-pill inline-flex items-center gap-2 border border-white/30 bg-white/20 px-5 py-2 text-[10px] font-black uppercase tracking-widest text-white hover:bg-white/30 shadow-sm backdrop-blur-md">
                    <i class="fa-solid fa-arrow-left"></i> Batal / Kembali
                </a>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/30 bg-white/20 px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-white shadow-sm backdrop-blur-md">
                    <i class="fa-solid fa-baby"></i> Khusus Balita
                </span>
            </div>
            
            <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight leading-tight">
                {{ $pageTitle }}
            </h1>
            <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-xl mx-auto md:mx-0">
                {{ $pageDesc }}
            </p>
        </div>
    </section>

    {{-- System Alerts --}}
    @if(session('error') || $errors->any())
        <div class="bg-rose-50 border border-rose-200 rounded-3xl p-5 flex items-start gap-4 shadow-sm">
            <div class="w-12 h-12 rounded-2xl bg-white text-rose-500 flex items-center justify-center text-xl shrink-0 shadow-sm border border-rose-100">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <h4 class="text-sm font-black text-rose-800">Terdapat Kesalahan</h4>
                <p class="text-xs font-semibold text-rose-600 mt-0.5 mb-2">{{ session('error') ?? 'Mohon periksa kembali isian formulir Anda di bawah.' }}</p>
                @if($errors->any())
                    <ul class="text-[11px] font-bold text-rose-500/80 list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif

    <form method="POST" action="{{ $formAction }}" id="imunisasiForm">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <input type="hidden" name="kategori" value="balita">
        <input type="hidden" id="balita_id" name="balita_id" value="{{ $selectedBalitaId }}">

        {{-- GRID UTAMA: Presisi penuh dengan items-stretch --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            
            {{-- KOLOM KIRI: Pilih Balita (Col 5) - Wajib memiliki h-full --}}
            <div class="lg:col-span-5 flex flex-col gap-6 h-full">
                
                {{-- Card Pencarian Balita --}}
                <section class="widget-card overflow-hidden flex flex-col shrink-0">
                    <div class="bg-slate-50/70 px-6 py-5 border-b border-slate-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white text-sky-500 border border-slate-100 flex items-center justify-center text-lg shadow-sm"><i class="fa-solid fa-magnifying-glass"></i></div>
                        <div>
                            <h5 class="font-black text-slate-800 text-sm uppercase tracking-widest">Pencarian Balita</h5>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Ketik Nama atau NIK</p>
                        </div>
                    </div>

                    <div class="p-6 flex flex-col">
                        @if($balitas->count() > 0)
                            <div class="relative mb-4">
                                <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" id="balitaSearchInput" autocomplete="off" spellcheck="false" placeholder="Contoh: Salsa / 3201..."
                                       class="w-full rounded-[1.2rem] border border-slate-200 bg-slate-50 py-3 pl-11 pr-11 text-xs font-bold text-slate-700 outline-none transition focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100">
                                <button type="button" id="balitaSearchClear" class="absolute right-3 top-1/2 hidden h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-slate-400 hover:bg-slate-200 transition"><i class="fa-solid fa-xmark"></i></button>
                            </div>

                            <div class="bg-slate-50/50 border border-slate-100 rounded-[1.2rem] p-2">
                                <div class="flex justify-between items-center px-2 py-2 mb-2 border-b border-slate-100">
                                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Hasil Pencarian</span>
                                    <span class="text-[9px] font-black text-teal-600 bg-teal-50 px-2 py-0.5 rounded-md"><span id="balitaResultCount">{{ $balitas->count() }}</span> Data</span>
                                </div>

                                <div id="balitaResults" class="max-h-[250px] overflow-y-auto slim-scroll space-y-1.5 pr-1">
                                    @foreach($balitas as $balita)
                                        @php
                                            $bId = data_get($balita, 'id');
                                            $bNama = $getBalitaName($balita);
                                            $bNik = $getBalitaNik($balita);
                                            $bOrtu = $getBalitaOrtu($balita);
                                            $bAlamat = $getBalitaAlamat($balita);
                                            $sName = mb_strtolower(trim((string) $bNama), 'UTF-8');
                                            $sNik = mb_strtolower(trim((string) $bNik), 'UTF-8');
                                            $isSelected = (string) $selectedBalitaId === (string) $bId;
                                        @endphp

                                        <button type="button" class="balita-picker-result w-full rounded-xl border p-3 text-left transition hover:border-teal-300 hover:bg-teal-50/50 {{ $isSelected ? 'is-selected' : 'border-white bg-white shadow-sm' }}"
                                            data-id="{{ $bId }}" data-name="{{ $bNama }}" data-nik="{{ $bNik }}" data-ortu="{{ $bOrtu }}" data-alamat="{{ $bAlamat }}" data-search-name="{{ $sName }}" data-search-nik="{{ $sNik }}" data-order="{{ $loop->index }}">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-50 text-sky-500 border border-sky-100"><i class="fa-solid fa-baby"></i></div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex justify-between items-start">
                                                        <h3 class="truncate text-xs font-black text-slate-800">{{ $bNama }}</h3>
                                                        <span class="selected-badge {{ $isSelected ? 'inline-flex' : 'hidden' }} rounded-md bg-teal-500 px-1.5 py-0.5 text-[8px] font-black text-white">Terpilih</span>
                                                    </div>
                                                    <p class="mt-0.5 truncate text-[10px] font-bold text-slate-400 font-mono">{{ $bNik }}</p>
                                                </div>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                                <div id="balitaEmptyResult" class="hidden py-8 text-center"><p class="text-xs font-bold text-slate-400">Pencarian tidak cocok.</p></div>
                            </div>
                        @else
                            <div class="text-center py-10 bg-slate-50 border border-dashed border-slate-200 rounded-2xl flex flex-col items-center justify-center">
                                <i class="fa-solid fa-folder-open text-3xl text-slate-300 mb-3"></i>
                                <p class="text-sm font-bold text-slate-600">Data Balita Kosong</p>
                                <p class="text-xs text-slate-400 mt-1">Tidak ada balita yang terdaftar di sistem.</p>
                            </div>
                        @endif
                    </div>
                </section>

                {{-- Card Data Terpilih (Kunci Presisi: flex-1 mengisi sisa ruang bawah) --}}
                <section class="widget-card overflow-hidden border-t-4 border-t-sky-500 flex flex-col flex-1">
                    <div class="bg-sky-50/50 px-6 py-5 border-b border-sky-100 flex items-center gap-3 shrink-0">
                        <div class="w-10 h-10 rounded-xl bg-white text-sky-600 border border-sky-100 flex items-center justify-center text-lg shadow-sm"><i class="fa-solid fa-address-card"></i></div>
                        <div>
                            <h5 class="font-black text-sky-800 text-sm uppercase tracking-widest">Data Terpilih</h5>
                            <p class="text-[9px] font-bold text-sky-600 uppercase tracking-widest mt-0.5">Identitas Balita</p>
                        </div>
                    </div>
                    
                    {{-- flex-1 & justify-center memastikan isinya selalu rapi di tengah dan mengisi kotak sampai bawah --}}
                    <div class="p-6 bg-white space-y-4 flex-1 flex flex-col justify-center">
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-3"><p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Nama Balita</p><p id="previewBalitaNama" class="text-sm font-black text-slate-800">{{ $selectedBalitaName }}</p></div>
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-3"><p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">NIK</p><p id="previewBalitaNik" class="text-sm font-bold text-slate-600 font-mono">{{ $selectedBalitaNik }}</p></div>
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-3"><p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Nama Ibu / Orang Tua</p><p id="previewBalitaOrtu" class="text-sm font-bold text-slate-700">{{ $selectedBalitaOrtu }}</p></div>
                        
                        <p id="balitaSelectionStatus" class="text-[10px] font-bold text-center text-rose-500 mt-2 shrink-0">{{ $selectedBalitaId ? '' : '* Silakan pilih balita dari daftar di atas.' }}</p>
                    </div>
                </section>
            </div>

            {{-- KOLOM KANAN: Form Imunisasi (Col 7) --}}
            <div class="lg:col-span-7 flex flex-col h-full">
                <section class="widget-card overflow-hidden border-t-4 border-t-teal-500 flex flex-col h-full">
                    
                    {{-- Form Header --}}
                    <div class="bg-slate-50/70 px-6 py-6 border-b border-slate-100 flex items-center gap-4 shrink-0">
                        <div class="w-12 h-12 rounded-2xl bg-white border border-slate-100 text-teal-500 flex items-center justify-center text-xl shadow-sm"><i class="fa-solid fa-syringe"></i></div>
                        <div>
                            <h2 class="text-base font-black text-slate-800 uppercase tracking-tight">Formulir Layanan</h2>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Lengkapi Data Imunisasi</p>
                        </div>
                    </div>

                    {{-- Form Inputs --}}
                    <div class="p-6 md:p-8 space-y-6 flex-1">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="jenis_imunisasi" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-teal-600">Jenis Program <span class="req-star">*</span></label>
                                <select id="jenis_imunisasi" name="jenis_imunisasi" required class="input-soft cursor-pointer @error('jenis_imunisasi') is-invalid @enderror">
                                    <option value="">Pilih Program...</option>
                                    @foreach($programOptions as $value => $label)
                                        <option value="{{ $value }}" @selected($selectedJenis === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="vaksin" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Nama Vaksin <span class="req-star">*</span></label>
                                <input type="text" id="vaksin" name="vaksin" value="{{ $selectedVaksin }}" required maxlength="100" placeholder="Contoh: BCG / Polio Tetes" class="input-soft @error('vaksin') is-invalid @enderror">
                            </div>

                            <div>
                                <label for="dosis" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Dosis (Ke / Jumlah) <span class="req-star">*</span></label>
                                <input type="text" id="dosis" name="dosis" value="{{ $selectedDosis }}" required maxlength="50" placeholder="Contoh: Dosis 1 / 0,5 ml" class="input-soft @error('dosis') is-invalid @enderror">
                            </div>

                            <div>
                                <label for="batch_number" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Nomor Batch Vaksin</label>
                                <input type="text" id="batch_number" name="batch_number" value="{{ $selectedBatch }}" maxlength="100" placeholder="Opsional (Jika ada)" class="input-soft @error('batch_number') is-invalid @enderror">
                            </div>
                        </div>

                        <div>
                            <label for="tanggal_imunisasi" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Tanggal Dilakukan Layanan <span class="req-star">*</span></label>
                            <input type="date" id="tanggal_imunisasi" name="tanggal_imunisasi" value="{{ $selectedTanggal }}" max="{{ now()->format('Y-m-d') }}" required class="input-soft cursor-pointer w-full sm:w-1/2 @error('tanggal_imunisasi') is-invalid @enderror">
                        </div>

                        <div>
                            <label for="keterangan" class="mb-2 block text-[10px] font-black uppercase tracking-widest text-slate-500">Catatan Tambahan (Kondisi/KIPI)</label>
                            <textarea id="keterangan" name="keterangan" rows="6" maxlength="1000" placeholder="Tuliskan keterangan mengenai kondisi anak, keluhan pasca imunisasi, atau pesan untuk kunjungan berikutnya." class="input-soft resize-none @error('keterangan') is-invalid @enderror">{{ $selectedKeterangan }}</textarea>
                            <div class="flex justify-between mt-1 px-1">
                                <p class="text-[10px] font-semibold text-slate-400">Opsional.</p>
                                <p id="keteranganCounter" class="text-[10px] font-black text-slate-400">0/1000</p>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons Bottom (Presisi rapat di bawah karena mt-auto) --}}
                    <div class="p-6 md:p-8 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-end gap-3 mt-auto shrink-0">
                        <a href="{{ $isEdit ? route('bidan.imunisasi.show', $imunisasi->id) : route('bidan.imunisasi.index') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl font-bold text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-700 transition-all shadow-sm text-sm text-center">
                            Batal
                        </a>
                        <button type="button" id="openSubmitModalBtn" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl font-black uppercase tracking-widest text-white bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 transition-all shadow-[0_4px_15px_rgba(16,185,129,.30)] hover:-translate-y-0.5 text-xs flex items-center justify-center gap-2">
                            <i class="fa-solid fa-save text-sm"></i> {{ $submitLabel }}
                        </button>
                    </div>
                </section>
            </div>
        </div>
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
                <h3 class="text-2xl font-black text-slate-800 mb-2">Simpan Catatan?</h3>
                <p class="text-sm font-medium text-slate-500 mb-6 leading-relaxed px-4">
                    Pastikan jenis vaksin dan dosis yang dipilih sudah benar sebelum menyimpan ke arsip rekam medis.
                </p>

                <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 mb-6 text-left">
                    <div class="flex justify-between mb-1"><span class="text-[10px] font-black text-slate-400 uppercase">Balita</span><span id="summaryBalita" class="text-xs font-bold text-slate-700 truncate max-w-[150px]">-</span></div>
                    <div class="flex justify-between mb-1"><span class="text-[10px] font-black text-slate-400 uppercase">Vaksin</span><span id="summaryVaksin" class="text-xs font-bold text-teal-600 truncate max-w-[150px]">-</span></div>
                </div>

                <div class="flex gap-3">
                    <button type="button" id="pcCancelSubmit" class="btn-pill w-full flex-1 border border-slate-200 bg-white text-slate-700 px-4 py-3.5 text-sm font-bold shadow-sm hover:bg-slate-50 transition-all">
                        Kembali
                    </button>
                    <button type="button" id="pcConfirmSubmit" class="btn-pill w-full flex-1 bg-gradient-to-r from-teal-500 to-emerald-500 text-white px-4 py-3.5 text-sm font-bold shadow-md hover:from-teal-600 hover:to-emerald-600 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(() => {
    // Balita Picker Logic
    const balitaIdInput = document.getElementById('balita_id');
    const searchInput = document.getElementById('balitaSearchInput');
    const clearButton = document.getElementById('balitaSearchClear');
    const resultCount = document.getElementById('balitaResultCount');
    const emptyResult = document.getElementById('balitaEmptyResult');
    const resultButtons = Array.from(document.querySelectorAll('.balita-picker-result'));
    
    const previewBalitaNama = document.getElementById('previewBalitaNama');
    const previewBalitaNik = document.getElementById('previewBalitaNik');
    const previewBalitaOrtu = document.getElementById('previewBalitaOrtu');
    const balitaSelectionStatus = document.getElementById('balitaSelectionStatus');
    const summaryBalita = document.getElementById('summaryBalita');

    const form = document.getElementById('imunisasiForm');
    const vaksinInput = document.getElementById('vaksin');
    const summaryVaksin = document.getElementById('summaryVaksin');
    const ketInput = document.getElementById('keterangan');
    const ketCounter = document.getElementById('keteranganCounter');

    const dash = (value) => String(value || '').trim() === '' ? '-' : String(value).trim();
    const normalize = (value) => String(value || '').toLowerCase().trim();
    const isNumericKeyword = (keyword) => /^[0-9]+$/.test(keyword);

    const setSelectedBalita = (button) => {
        if (!button || !balitaIdInput) return;
        balitaIdInput.value = button.dataset.id || '';
        
        resultButtons.forEach(item => {
            item.classList.remove('is-selected');
            const badge = item.querySelector('.selected-badge');
            if(badge) { badge.classList.add('hidden'); badge.classList.remove('inline-flex'); }
        });

        button.classList.add('is-selected');
        const activeBadge = button.querySelector('.selected-badge');
        if(activeBadge) { activeBadge.classList.remove('hidden'); activeBadge.classList.add('inline-flex'); }

        if (previewBalitaNama) previewBalitaNama.textContent = dash(button.dataset.name);
        if (previewBalitaNik) previewBalitaNik.textContent = dash(button.dataset.nik);
        if (previewBalitaOrtu) previewBalitaOrtu.textContent = dash(button.dataset.ortu);
        if (summaryBalita) summaryBalita.textContent = dash(button.dataset.name);
        if (balitaSelectionStatus) balitaSelectionStatus.textContent = '';
    };

    resultButtons.forEach(button => button.addEventListener('click', () => setSelectedBalita(button)));

    const filterResults = () => {
        const keyword = normalize(searchInput?.value);
        let visible = 0;

        resultButtons.forEach(item => {
            const name = normalize(item.dataset.searchName);
            const nik = normalize(item.dataset.searchNik);
            let matched = false;

            if (keyword === '') matched = true;
            else if (isNumericKeyword(keyword)) matched = nik.includes(keyword);
            else matched = name.includes(keyword);

            item.classList.toggle('is-hidden', !matched);
            if (matched) visible++;
        });

        if (resultCount) resultCount.textContent = String(visible);
        if (emptyResult) emptyResult.classList.toggle('hidden', visible > 0);
        if (clearButton) {
            clearButton.classList.toggle('hidden', keyword === '');
            clearButton.classList.toggle('inline-flex', keyword !== '');
        }
    };

    searchInput?.addEventListener('input', filterResults);
    clearButton?.addEventListener('click', () => { if (searchInput) { searchInput.value = ''; filterResults(); } });

    // Pre-select if editing or returning from validation fail
    const preselectedBtn = resultButtons.find(b => String(b.dataset.id) === String(balitaIdInput?.value));
    if(preselectedBtn) setSelectedBalita(preselectedBtn);

    vaksinInput?.addEventListener('input', () => { if(summaryVaksin) summaryVaksin.textContent = dash(vaksinInput.value); });
    if(summaryVaksin) summaryVaksin.textContent = dash(vaksinInput?.value);

    ketInput?.addEventListener('input', () => { if(ketCounter) ketCounter.textContent = `${ketInput.value.length}/1000`; });
    if(ketCounter && ketInput) ketCounter.textContent = `${ketInput.value.length}/1000`;

    // Modal Logic
    const openBtn = document.getElementById('openSubmitModalBtn');
    const modal = document.getElementById('pcSubmitModal');
    const cancelBtn = document.getElementById('pcCancelSubmit');
    const confirmBtn = document.getElementById('pcConfirmSubmit');

    openBtn?.addEventListener('click', () => {
        if (!balitaIdInput.value) {
            alert('Silakan pilih Balita dari daftar pencarian terlebih dahulu!');
            searchInput?.focus();
            return;
        }
        if (!form.checkValidity()) { form.reportValidity(); return; }
        modal.classList.add('is-open');
    });

    cancelBtn?.addEventListener('click', () => modal.classList.remove('is-open'));
    modal?.addEventListener('click', (e) => { if(e.target === modal) modal.classList.remove('is-open'); });

    confirmBtn?.addEventListener('click', () => {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
        form.submit();
    });
})();
</script>
@endpush