@extends('layouts.kader')

@section('title', 'Import Data Massal')
@section('page-name', 'Import Data Massal')

@php
    use Illuminate\Support\Facades\Route;

    $routeHas = fn ($name) => Route::has($name);

    $type = $type ?? request('type', old('jenis_data', 'balita'));

    if (!in_array($type, ['balita', 'remaja', 'lansia'], true)) {
        $type = 'balita';
    }

    $typeMeta = [
        'balita' => [
            'label' => 'Balita / Anak',
            'desc' => 'Template berisi NIK balita, nama, data orang tua, dan data lahir.',
            'icon' => 'fa-child-reaching',
            'color' => 'text-emerald-600',
            'bg' => 'bg-emerald-50',
            'border' => 'border-emerald-200',
            'ring' => 'peer-checked:ring-emerald-500',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'desc' => 'Template berisi NIK, nama, sekolah, orang tua, dan domisili.',
            'icon' => 'fa-user-graduate',
            'color' => 'text-sky-600',
            'bg' => 'bg-sky-50',
            'border' => 'border-sky-200',
            'ring' => 'peer-checked:ring-sky-500',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'desc' => 'Template berisi NIK, identitas, metrik kesehatan dasar, dan keluarga.',
            'icon' => 'fa-person-cane',
            'color' => 'text-amber-600',
            'bg' => 'bg-amber-50',
            'border' => 'border-amber-200',
            'ring' => 'peer-checked:ring-amber-500',
        ],
    ];

    $templateBaseUrl = url('/kader/import/template');
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

    /* Glass Card untuk Wrapper Utama */
    .import-wrapper {
        background: rgba(255, 255, 255, 0.90);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 1);
        border-radius: 2.5rem; 
        box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.05);
    }

    /* Style Khusus Dropzone */
    .upload-dropzone {
        border: 2px dashed #cbd5e1;
        background: #f8fafc;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    .upload-dropzone:hover, .upload-dropzone.is-dragover {
        border-color: #10b981;
        background: #ecfdf5;
        box-shadow: inset 0 0 0 4px rgba(16, 185, 129, 0.05);
    }

    /* Custom Radio Cards */
    .radio-card {
        transition: all 0.2s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }
    .radio-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto pb-12 animate-pop-in">
    {{-- HEADER HALAMAN --}}
    <section class="mb-8 relative z-10">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold mb-3">
                    <i class="fa-solid fa-bolt text-amber-500"></i> Mode Cepat
                </span>
                <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                    Import Data Massal
                </h1>
                <p class="text-slate-500 font-medium mt-2 text-sm md:text-base max-w-2xl">
                    Unggah puluhan hingga ratusan data warga sekaligus menggunakan format Excel. Menghemat waktu kader tanpa perlu menginput satu per satu.
                </p>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                @if($routeHas('kader.import.history'))
                    <a href="{{ route('kader.import.history') }}" class="btn-pill bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-3.5 text-xs font-bold uppercase tracking-widest transition-all">
                        <i class="fa-solid fa-clock-rotate-left mr-1.5"></i> Riwayat Import
                    </a>
                @endif
                @if($routeHas('kader.import.index'))
                    <a href="{{ route('kader.import.index') }}" class="btn-pill bg-white hover:bg-emerald-50 text-emerald-600 px-6 py-3.5 text-xs font-bold uppercase tracking-widest transition-all shadow-[0_8px_20px_rgba(255,255,255,0.3)] hover:-translate-y-0.5">
                        <i class="fa-solid fa-layer-group mr-1.5"></i> Pusat Import
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- ALERT PESAN ERROR (SMART & FRIENDLY ERROR HANDLING) --}}
    @if($errors->any() || session('error'))
        <div class="bg-rose-50 border-2 border-rose-200 rounded-[2rem] p-6 mb-8 flex items-start gap-5 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-rose-500/5 rounded-full blur-2xl -mr-10 -mt-10 pointer-events-none"></div>
            
            <div class="bg-white rounded-full w-14 h-14 flex items-center justify-center shrink-0 shadow-sm mt-0.5 relative z-10 border border-rose-100">
                <i class="fa-solid fa-triangle-exclamation text-rose-500 text-2xl"></i>
            </div>
            
            <div class="relative z-10 flex-1">
                <h3 class="font-black text-lg text-rose-900 leading-tight">Gagal Memproses Dokumen</h3>
                
                @if(session('error'))
                    @php
                        $rawError = session('error');
                        $smartMessage = 'Terjadi kendala saat sistem membaca dokumen Excel Anda.';
                        $smartSolution = 'Periksa kembali data Anda dan pastikan sesuai dengan petunjuk pengisian template resmi ya.';
                        
                        // KECERDASAN DETEKSI ERROR UNTUK KADER (BAHASA FRIENDLY)
                        $lowerError = strtolower($rawError);
                        
                        if (str_contains($lowerError, 'out of range') || str_contains($lowerError, '22003')) {
                            $smartMessage = 'Ada angka yang terlalu besar atau format tanggal yang terselubung pada kolom angka.';
                            $smartSolution = 'Buka Excel Anda, blok kolom angka (seperti Berat Badan / Asam Urat), lalu ubah format cell-nya menjadi <strong>Text</strong>. Pastikan Anda mengetik desimal pakai titik/koma (misal: 5.5). Jika Excel malah mengubahnya jadi angka puluhan ribu, ketik ulang saja.';
                        } elseif (str_contains($lowerError, '1364') || str_contains($lowerError, 'doesn\'t have a default value') || str_contains($lowerError, 'cannot be null')) {
                            // Cerdas mencari kolom apa yang kosong
                            preg_match("/(?:Field|Column) '([^']+)'/i", $rawError, $matches);
                            $field = $matches[1] ?? 'data penting';
                            $fieldSopan = ucwords(str_replace('_', ' ', $field));
                            
                            $smartMessage = "Oops! Ada sel yang masih kosong pada kolom '{$fieldSopan}'.";
                            $smartSolution = "Silakan buka lagi file Excel Anda, dan lengkapi kolom <strong>{$fieldSopan}</strong> (atau kolom dengan judul mirip). Pastikan tidak ada NIK, Nama, atau kelengkapan wajib lainnya yang dibiarkan kosong, lalu coba unggah lagi ya.";
                        } elseif (str_contains($lowerError, 'gagal di baris')) {
                            $smartMessage = 'Beberapa data tidak memenuhi standar isian Posyandu.';
                            $smartSolution = 'Coba cek lagi baris Excel yang disebutkan di bawah ini. Pastikan NIK sudah pas 16 digit dan tidak ada nama yang terlewat.';
                        } elseif (str_contains($lowerError, 'mimes') || str_contains($lowerError, 'format')) {
                            $smartMessage = 'Ups, format dokumennya tidak sesuai.';
                            $smartSolution = 'Sistem hanya bisa membaca file Excel (.xlsx atau .xls). Jangan mengunggah dokumen bentuk PDF, Word, ataupun foto/gambar ya.';
                        } elseif (str_contains($lowerError, 'kosong') || str_contains($lowerError, 'undefined array key')) {
                            $smartMessage = 'Dokumen Excel terbaca kosong atau judul kolomnya ada yang hilang/berubah.';
                            $smartSolution = 'Pastikan Anda tidak mengubah, mewarnai, atau menghapus <strong>baris judul (Header)</strong> bawaan dari template. Sistem akan otomatis mencari tulisan seperti "nik" untuk mulai membaca data.';
                        }
                    @endphp
                    
                    <div class="mt-4 space-y-3">
                        {{-- Kotak Penyebab Error --}}
                        <div class="bg-white/70 rounded-xl p-3 border border-rose-100">
                            <p class="text-sm font-bold text-rose-800 flex items-center gap-2">
                                <i class="fa-solid fa-circle-info opacity-50"></i>
                                <span>Penyebab Masalah:</span>
                            </p>
                            <p class="text-sm font-medium text-rose-600 mt-1 pl-6">{{ $smartMessage }}</p>
                            
                            @if($smartMessage !== $rawError && !str_contains($rawError, '[SERVER ERROR]'))
                                <p class="text-xs font-medium text-rose-400/80 mt-1 pl-6 break-words">
                                    <i>Kode sistem: {{ $rawError }}</i>
                                </p>
                            @endif
                        </div>
                        
                        {{-- Kotak Solusi Cerdas --}}
                        <div class="bg-emerald-50 rounded-xl p-3 border border-emerald-100 shadow-inner">
                            <p class="text-sm font-bold text-emerald-800 flex items-center gap-2">
                                <i class="fa-solid fa-lightbulb text-amber-500"></i>
                                <span>Saran dari Sistem:</span>
                            </p>
                            <p class="text-sm font-medium text-emerald-700 mt-1 pl-6 leading-relaxed">{!! $smartSolution !!}</p>
                        </div>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="mt-4 bg-white/70 rounded-xl p-3 border border-rose-100">
                        <p class="text-sm font-bold text-rose-800 flex items-center gap-2 mb-2">
                            <i class="fa-regular fa-circle-xmark opacity-50"></i>
                            <span>Peringatan Formulir:</span>
                        </p>
                        <ul class="text-sm font-medium text-rose-600 space-y-1 list-disc list-inside pl-6">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- FORMULIR PIPELINE WIZARD --}}
    <form id="importForm" method="POST" action="{{ route('kader.import.store') }}" enctype="multipart/form-data" class="import-wrapper p-6 sm:p-10">
        @csrf

        {{-- LANGKAH 1: UNDUH TEMPLATE --}}
        <div class="step-item relative pb-10">
            <div class="step-line flex items-start gap-5 relative z-10">
                <div class="w-12 h-12 rounded-full bg-slate-900 text-white font-black text-lg flex items-center justify-center shrink-0 shadow-md">
                    1
                </div>
                <div class="flex-1 pt-1">
                    <h2 class="text-xl font-black text-slate-900">Siapkan File Template</h2>
                    <p class="text-sm font-semibold text-slate-500 mt-1">Unduh format Excel resmi di bawah ini. Jangan mengubah struktur baris judul agar sistem dapat membacanya.</p>
                    
                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach($typeMeta as $key => $item)
                            <button type="button" onclick="downloadTemplateInstan('{{ $templateBaseUrl }}/{{ $key }}')" class="flex flex-col items-center text-center gap-2 p-4 rounded-2xl border border-slate-200 bg-white hover:border-emerald-400 hover:shadow-md transition-all group">
                                <div class="w-10 h-10 rounded-full {{ $item['bg'] }} {{ $item['color'] }} flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                                    <i class="fa-solid {{ $item['icon'] }}"></i>
                                </div>
                                <span class="text-sm font-bold text-slate-700">Template {{ $item['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="absolute left-6 top-12 bottom-0 w-0.5 bg-slate-200 -ml-[1px]"></div>
        </div>

        {{-- LANGKAH 2: PILIH JENIS DATA --}}
        <div class="step-item relative pb-10">
            <div class="step-line flex items-start gap-5 relative z-10">
                <div class="w-12 h-12 rounded-full bg-slate-900 text-white font-black text-lg flex items-center justify-center shrink-0 shadow-md">
                    2
                </div>
                <div class="flex-1 pt-1">
                    <h2 class="text-xl font-black text-slate-900">Pilih Kategori Data</h2>
                    <p class="text-sm font-semibold text-slate-500 mt-1">Pastikan jenis data yang dipilih sesuai dengan template yang Anda isi.</p>

                    <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($typeMeta as $key => $item)
                            <label class="radio-card block cursor-pointer">
                                <input type="radio" name="jenis_data" value="{{ $key }}" class="peer sr-only" {{ $type === $key ? 'checked' : '' }}>
                                <div class="rounded-2xl border-2 border-slate-200 bg-white p-5 peer-checked:border-emerald-500 peer-checked:bg-emerald-50/50 transition-all shadow-sm">
                                    <div class="flex items-center gap-4 mb-2">
                                        <div class="w-10 h-10 rounded-full {{ $item['bg'] }} {{ $item['color'] }} flex items-center justify-center text-lg">
                                            <i class="fa-solid {{ $item['icon'] }}"></i>
                                        </div>
                                        <h3 class="font-bold text-slate-800 text-base">{{ $item['label'] }}</h3>
                                    </div>
                                    <p class="text-xs text-slate-500 font-medium leading-relaxed">
                                        {{ $item['desc'] }}
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="absolute left-6 top-12 bottom-0 w-0.5 bg-slate-200 -ml-[1px]"></div>
        </div>

        {{-- LANGKAH 3: UNGGAH FILE --}}
        <div class="step-item relative pb-6">
            <div class="step-line flex items-start gap-5 relative z-10">
                <div class="w-12 h-12 rounded-full bg-emerald-500 text-white font-black text-lg flex items-center justify-center shrink-0 shadow-[0_4px_15px_rgba(16,185,129,0.4)]">
                    3
                </div>
                <div class="flex-1 pt-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">Unggah Dokumen</h2>
                            <p class="text-sm font-semibold text-slate-500 mt-1">Masukkan file Excel (.xlsx atau .xls) yang telah Anda isi dengan lengkap.</p>
                        </div>
                        <div class="flex items-center gap-2 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100" title="Smart Import aktif secara otomatis membaca posisi kolom">
                            <i class="fa-solid fa-wand-magic-sparkles text-indigo-500 text-xs"></i>
                            <span class="text-xs font-bold text-indigo-700">Auto-Detect Aktif</span>
                            <input type="hidden" name="smart_import" value="1">
                        </div>
                    </div>

                    <div class="mt-5 relative">
                        <input type="file" name="file" id="file" class="hidden" accept=".xlsx, .xls, .csv">
                        
                        <label for="file" id="dropzone" class="upload-dropzone block rounded-[2rem] p-8 text-center relative overflow-hidden">
                            <div id="state-empty" class="transition-all duration-300">
                                <div class="w-20 h-20 mx-auto bg-white rounded-full shadow-sm flex items-center justify-center border border-slate-100 mb-4 text-emerald-500 text-3xl">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <h3 class="text-lg font-black text-slate-700">Klik untuk Mencari File</h3>
                                <p class="text-sm font-medium text-slate-500 mt-2">Atau seret dan lepas file Excel Anda ke area ini</p>
                                <div class="mt-4 flex items-center justify-center gap-4 text-xs font-bold text-slate-400">
                                    <span class="flex items-center gap-1"><i class="fa-solid fa-check text-emerald-500"></i> Maks 10 MB</span>
                                    <span class="flex items-center gap-1"><i class="fa-solid fa-check text-emerald-500"></i> .xlsx / .xls</span>
                                </div>
                            </div>

                            <div id="state-filled" class="absolute inset-0 flex flex-col items-center justify-center bg-emerald-50/90 opacity-0 pointer-events-none transition-all duration-300 translate-y-4">
                                <div class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center border border-emerald-100 mb-3 text-emerald-600 text-3xl">
                                    <i class="fa-solid fa-file-excel"></i>
                                </div>
                                <h3 id="file-name-display" class="text-base font-black text-emerald-900 truncate max-w-[80%]">NamaFile.xlsx</h3>
                                <p id="file-size-display" class="text-xs font-bold text-emerald-600 mt-1">2.5 MB • Format Sesuai</p>
                                <button type="button" onclick="resetFileUI(); document.getElementById('file').value='';" class="mt-4 px-4 py-1.5 rounded-full bg-white text-rose-500 text-xs font-bold border border-rose-100 hover:bg-rose-50 transition-colors z-20 relative">
                                    <i class="fa-solid fa-xmark mr-1"></i> Batal / Ganti File
                                </button>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- TOMBOL SUBMIT --}}
        <div class="mt-8 pt-8 border-t border-slate-100 flex justify-end relative z-10">
            <button type="submit" id="btnSubmit" class="btn-primary w-full sm:w-auto px-8 py-4 rounded-2xl text-sm font-black uppercase tracking-widest bg-emerald-600 hover:bg-emerald-700 text-white transition-all shadow-[0_10px_20px_rgba(16,185,129,0.3)] hover:-translate-y-1 flex items-center justify-center gap-2">
                <i class="fa-solid fa-rocket"></i> Mulai Proses Import Data
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function downloadTemplateInstan(url) {
        const type = document.querySelector('input[name="jenis_data"]:checked').value;
        window.location.href = url + '?type=' + type;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('file');
        const dropzone = document.getElementById('dropzone');
        const stateEmpty = document.getElementById('state-empty');
        const stateFilled = document.getElementById('state-filled');
        const fileNameDisplay = document.getElementById('file-name-display');
        const fileSizeDisplay = document.getElementById('file-size-display');
        const dropzoneBox = document.querySelector('.upload-dropzone');
        const form = document.getElementById('importForm');
        const submitBtn = document.getElementById('btnSubmit');

        // Drag & Drop Events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => {
                dropzoneBox.classList.add('is-dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => {
                dropzoneBox.classList.remove('is-dragover');
            }, false);
        });

        dropzone.addEventListener('drop', function(e) {
            let dt = e.dataTransfer;
            let files = dt.files;
            
            if (files.length) {
                fileInput.files = files;
                updateFileUI();
            }
        });

        fileInput.addEventListener('change', updateFileUI);

        function updateFileUI() {
            if (!fileInput.files.length) return;
            
            const file = fileInput.files[0];
            
            const validTypes = [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/csv'
            ];
            
            const extension = file.name.split('.').pop().toLowerCase();
            const validExtensions = ['xls', 'xlsx', 'csv'];

            if (!validTypes.includes(file.type) && !validExtensions.includes(extension)) {
                alert('Tolong pilih file Excel (.xlsx, .xls) atau CSV.');
                resetFileUI();
                fileInput.value = '';
                return;
            }

            if (file.size > 10 * 1024 * 1024) { // 10MB
                alert('Ukuran file terlalu besar. Maksimal 10 MB.');
                resetFileUI();
                fileInput.value = '';
                return;
            }

            const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
            
            fileNameDisplay.textContent = file.name;
            fileSizeDisplay.textContent = sizeMb + ' MB • Format Sesuai';

            stateEmpty.style.opacity = '0';
            stateEmpty.style.pointerEvents = 'none';
            stateEmpty.style.transform = 'translateY(-10px)';

            stateFilled.style.opacity = '1';
            stateFilled.style.pointerEvents = 'auto';
            stateFilled.style.transform = 'translateY(0)';
            
            dropzoneBox.style.borderColor = '#10b981';
            dropzoneBox.style.background = '#ecfdf5';
        }

        window.resetFileUI = function() {
            stateFilled.style.opacity = '0';
            stateFilled.style.pointerEvents = 'none';
            stateFilled.style.transform = 'translateY(10px)';

            stateEmpty.style.opacity = '1';
            stateEmpty.style.pointerEvents = 'auto';
            stateEmpty.style.transform = 'translateY(0)';

            dropzoneBox.style.borderColor = '#cbd5e1';
            dropzoneBox.style.background = '#f8fafc';
        }

        form.addEventListener('submit', function(e) {
            if (!fileInput.files.length) {
                e.preventDefault();
                alert('Oops! Anda belum memilih atau mengunggah file Excel.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Memproses Import...';
        });
    });
</script>
@endpush