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
        box-shadow: 0 10px 20px -5px rgba(15, 23, 42, 0.05);
    }
    
    .btn-pill {
        border-radius: 9999px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.96); }

    /* Step Indicator Line */
    .step-line::before {
        content: '';
        position: absolute;
        left: 23px;
        top: 48px;
        bottom: -16px;
        width: 2px;
        background: #e2e8f0;
        z-index: 0;
    }
    .step-item:last-child .step-line::before {
        display: none;
    }
</style>
@endpush

@section('content')
<div class="max-w-[1080px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 mt-6">

    {{-- HEADER TOOL IMPORT (Disamakan dengan Palet Dashboard) --}}
    <section class="bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-600 rounded-[3rem] p-8 md:p-12 mb-8 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(16,185,129,.35)] text-center border border-white/20">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/15 blur-[80px] rounded-full pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-40 h-40 bg-white/10 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col items-center">
            <div class="w-24 h-24 rounded-[2rem] bg-white border-4 border-white/50 text-emerald-500 flex items-center justify-center text-4xl shadow-xl mb-6">
                <i class="fa-solid fa-cloud-arrow-up"></i>
            </div>

            <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight">
                Import Data Massal
            </h1>

            <p class="text-teal-50 text-sm font-medium max-w-xl mx-auto mt-3 leading-relaxed">
                Alat khusus untuk memasukkan ratusan data sasaran sekaligus. Ikuti 3 langkah mudah di bawah ini untuk memulai proses sinkronisasi database.
            </p>

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                @if($routeHas('kader.import.history'))
                    <a href="{{ route('kader.import.history') }}" class="btn-pill bg-white/20 hover:bg-white/30 text-white border border-white/30 px-6 py-3.5 text-xs font-bold uppercase tracking-widest backdrop-blur-md transition-all shadow-sm">
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

    {{-- ALERT PESAN ERROR --}}
    @if($errors->any() || session('error'))
        <div class="bg-rose-50 border-2 border-rose-200 rounded-[2rem] p-6 mb-8 flex items-start gap-4 shadow-sm">
            <div class="bg-white rounded-full w-12 h-12 flex items-center justify-center shrink-0 shadow-inner mt-0.5">
                <i class="fa-solid fa-triangle-exclamation text-rose-500 text-xl"></i>
            </div>
            <div>
                <h3 class="font-black text-base text-rose-800">Proses Import Gagal</h3>
                <p class="text-sm font-bold text-rose-600 mt-1">{{ session('error') ?: 'Terdapat kesalahan pada file atau input Anda:' }}</p>
                @if($errors->any())
                    <ul class="text-xs font-bold mt-2 text-rose-700/80 space-y-1 list-disc list-inside bg-white/50 p-3 rounded-xl">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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
                                    <i class="fa-solid fa-file-excel"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest">{{ $item['label'] }}</h4>
                                    <p class="text-[10px] font-bold text-slate-400 mt-0.5">Format .xlsx</p>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- LANGKAH 2: PILIH KATEGORI --}}
        <div class="step-item relative pb-10">
            <div class="step-line flex items-start gap-5 relative z-10">
                <div class="w-12 h-12 rounded-full bg-slate-900 text-white font-black text-lg flex items-center justify-center shrink-0 shadow-md">
                    2
                </div>
                <div class="flex-1 pt-1">
                    <h2 class="text-xl font-black text-slate-900">Pilih Kategori Sasaran</h2>
                    <p class="text-sm font-semibold text-slate-500 mt-1">Pilih jenis data yang ingin Anda masukkan. Pastikan pilihan ini sama dengan template yang Anda isi.</p>
                    
                    <div class="mt-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($typeMeta as $key => $item)
                            <label class="radio-card relative flex items-start gap-4 p-5 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                                <input type="radio" name="jenis_data" value="{{ $key }}" class="peer sr-only" {{ $type === $key ? 'checked' : '' }}>
                                
                                {{-- Background Active Highlight --}}
                                <div class="absolute inset-0 bg-emerald-50/50 opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></div>
                                <div class="absolute inset-0 border-2 border-emerald-500 rounded-2xl opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"></div>

                                <div class="w-12 h-12 rounded-2xl {{ $item['bg'] }} {{ $item['color'] }} flex items-center justify-center text-xl shrink-0 relative z-10">
                                    <i class="fa-solid {{ $item['icon'] }}"></i>
                                </div>
                                <div class="relative z-10 pt-0.5">
                                    <h4 class="text-sm font-black text-slate-900">{{ $item['label'] }}</h4>
                                    <p class="text-[10px] font-bold text-slate-500 mt-1 leading-relaxed pr-2">{{ $item['desc'] }}</p>
                                </div>
                                
                                {{-- Check Icon --}}
                                <div class="absolute top-5 right-5 text-emerald-500 opacity-0 peer-checked:opacity-100 transform scale-50 peer-checked:scale-100 transition-all duration-300">
                                    <i class="fa-solid fa-circle-check text-xl"></i>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- LANGKAH 3: UPLOAD FILE --}}
        <div class="step-item relative pb-6">
            <div class="step-line flex items-start gap-5 relative z-10">
                <div class="w-12 h-12 rounded-full bg-slate-900 text-white font-black text-lg flex items-center justify-center shrink-0 shadow-md">
                    3
                </div>
                <div class="flex-1 pt-1">
                    <h2 class="text-xl font-black text-slate-900">Unggah File Anda</h2>
                    <p class="text-sm font-semibold text-slate-500 mt-1">Masukkan file template yang sudah diisi. Sistem akan otomatis mendeteksi jika ada NIK ganda.</p>
                    
                    <div class="mt-5">
                        <label id="dropzoneBox" for="fileInput" class="upload-dropzone flex flex-col items-center justify-center min-h-[220px] rounded-3xl p-8 text-center relative overflow-hidden group">
                            
                            {{-- State Awal --}}
                            <div id="uploadStateEmpty" class="flex flex-col items-center transition-all duration-300">
                                <div class="w-16 h-16 rounded-3xl bg-white text-emerald-500 flex items-center justify-center text-2xl shadow-sm border border-slate-100 mb-4 group-hover:scale-110 transition-transform duration-300">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <h3 class="text-lg font-black text-slate-800">Klik atau Seret file ke sini</h3>
                                <p class="text-xs font-bold text-slate-500 mt-2">Hanya mendukung file <span class="text-slate-700">.xlsx, .xls, .csv</span> (Maks. 10MB)</p>
                            </div>

                            {{-- State File Terpilih --}}
                            <div id="uploadStateFilled" class="flex flex-col items-center absolute inset-0 bg-emerald-50 opacity-0 pointer-events-none transition-all duration-300 transform translate-y-4">
                                <div class="flex flex-col items-center justify-center h-full w-full">
                                    <div class="w-16 h-16 rounded-3xl bg-emerald-500 text-white flex items-center justify-center text-3xl shadow-lg mb-3">
                                        <i class="fa-solid fa-file-excel"></i>
                                    </div>
                                    <h3 id="fileNameDisplay" class="text-lg font-black text-emerald-900 px-4 truncate max-w-sm">nama_file.xlsx</h3>
                                    <p id="fileSizeDisplay" class="text-xs font-bold text-emerald-600 mt-1">0 MB • Siap diproses</p>
                                    <div class="mt-4 px-4 py-1.5 rounded-full bg-white border border-emerald-200 text-[10px] font-black uppercase tracking-widest text-emerald-600 shadow-sm cursor-pointer hover:bg-emerald-100 transition-colors">
                                        Ganti File
                                    </div>
                                </div>
                            </div>

                            <input id="fileInput" type="file" name="file" class="hidden" accept=".xlsx,.xls,.csv" required>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTION PANEL --}}
        <div class="mt-8 ml-16 bg-slate-50 border border-slate-200 p-5 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h4 class="text-sm font-black text-slate-800"><i class="fa-solid fa-shield-halved text-emerald-500 mr-1.5"></i> Validasi Aman</h4>
                <p class="text-[11px] font-bold text-slate-500 mt-0.5">Sistem memblokir data dengan format salah atau NIK duplikat.</p>
            </div>
            
            <button type="submit" id="submitBtn" class="btn-pill w-full sm:w-auto flex items-center justify-center gap-2 bg-emerald-600 px-8 py-3.5 text-sm font-black text-white shadow-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fa-solid fa-rocket"></i> Proses Import
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // FUNGSI SUPER CEPAT UNDUH TEMPLATE NATIVE
    window.downloadTemplateInstan = function(url) {
        let iframe = document.getElementById('instanDownloader');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'instanDownloader';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
        }
        iframe.src = url; // Langsung tembak link download (Native HTML5)
    };

    document.addEventListener('DOMContentLoaded', () => {
        const dropzoneBox = document.getElementById('dropzoneBox');
        const fileInput = document.getElementById('fileInput');
        const stateEmpty = document.getElementById('uploadStateEmpty');
        const stateFilled = document.getElementById('uploadStateFilled');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        const fileSizeDisplay = document.getElementById('fileSizeDisplay');
        const form = document.getElementById('importForm');
        const submitBtn = document.getElementById('submitBtn');

        // Drag & Drop Styling
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzoneBox.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzoneBox.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzoneBox.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropzoneBox.classList.add('is-dragover');
        }

        function unhighlight(e) {
            dropzoneBox.classList.remove('is-dragover');
        }

        // Handle Drop File
        dropzoneBox.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length) {
                fileInput.files = files; // Assign files to input
                updateFileUI(files[0]);
            }
        }

        // Handle Browse File
        fileInput.addEventListener('change', function() {
            if (this.files.length) {
                updateFileUI(this.files[0]);
            }
        });

        // Update UI untuk File
        function updateFileUI(file) {
            // Validasi Format
            const allowedExtensions = ['xlsx', 'xls', 'csv'];
            const extension = file.name.split('.').pop().toLowerCase();
            
            if (!allowedExtensions.includes(extension)) {
                alert('Tolong pilih file Excel (.xlsx, .xls) atau CSV.');
                fileInput.value = '';
                resetFileUI();
                return;
            }

            // Validasi Ukuran (Max 10MB)
            const maxSize = 10 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('Ukuran file terlalu besar. Maksimal 10 MB.');
                fileInput.value = '';
                resetFileUI();
                return;
            }

            // Ganti UI ke Mode Terisi
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

        function resetFileUI() {
            stateFilled.style.opacity = '0';
            stateFilled.style.pointerEvents = 'none';
            stateFilled.style.transform = 'translateY(10px)';

            stateEmpty.style.opacity = '1';
            stateEmpty.style.pointerEvents = 'auto';
            stateEmpty.style.transform = 'translateY(0)';

            dropzoneBox.style.borderColor = '#cbd5e1';
            dropzoneBox.style.background = '#f8fafc';
        }

        // Form Submit Loading State
        form.addEventListener('submit', function(e) {
            if (!fileInput.files.length) {
                e.preventDefault();
                alert('Anda belum mengunggah file Excel.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Memproses Import...';
        });
    });
</script>
@endpush