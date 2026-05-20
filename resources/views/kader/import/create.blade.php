@extends('layouts.kader')
@section('title', 'Upload Data Import')
@section('page-name', 'Smart Import Wizard')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    .animate-slide-up { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes slideUpFade { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    .file-drop-area {
        border: 3px dashed #cbd5e1; border-radius: 24px; padding: 3rem 2rem;
        text-align: center; background-color: #f8fafc; transition: all 0.3s ease;
        position: relative; overflow: hidden; cursor: pointer;
    }
    .file-drop-area:hover, .file-drop-area.is-active {
        border-color: #6366f1; background-color: #eef2ff;
        box-shadow: inset 0 0 40px rgba(99, 102, 241, 0.1);
        transform: scale(1.02);
    }
    .file-input-hidden {
        position: absolute; inset: 0; width: 100%; height: 100%;
        opacity: 0; cursor: pointer; z-index: 10;
    }
    .toggle-checkbox:checked { right: 0; border-color: #4f46e5; }
    .toggle-checkbox:checked + .toggle-label { background-color: #4f46e5; }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto animate-slide-up pb-10">
    
    <div class="text-center mb-10 mt-4">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-[24px] bg-gradient-to-br from-indigo-500 to-violet-600 text-white mb-5 shadow-[0_8px_20px_rgba(79,70,229,0.3)] transform hover:rotate-12 transition-transform">
            <i class="fas fa-cloud-upload-alt text-4xl"></i>
        </div>
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight font-poppins">Wizard Import Data</h1>
        <p class="text-slate-500 mt-3 font-medium text-[14px] max-w-xl mx-auto leading-relaxed">Unggah file Excel/CSV Anda. Kecerdasan Buatan (AI) kami akan memproses, memetakan kolom, dan memvalidasi data Anda secara otomatis.</p>
    </div>

    <form action="{{ route('kader.import.store') }}" method="POST" enctype="multipart/form-data" id="importForm">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- PANEL KIRI: Upload File --}}
            <div class="lg:col-span-8 bg-white rounded-[32px] border border-slate-200/80 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 md:p-10 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-bl-full pointer-events-none -z-10"></div>
                
                <div class="flex items-center gap-4 mb-8 border-b border-slate-100 pb-5">
                    <span class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-black shadow-md shadow-indigo-200 shrink-0">1</span>
                    <h3 class="text-xl font-black text-slate-800 font-poppins">Pilih File Master</h3>
                </div>

                <div class="mb-8 relative z-10">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-3">Kategori Database Target <span class="text-rose-500">*</span></label>
                    <select name="jenis_data" id="jenis_data" required class="w-full bg-slate-50 border-2 border-slate-200 text-slate-800 text-[14px] rounded-2xl px-5 py-4 outline-none font-bold focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all cursor-pointer">
                        <option value="">-- Pilih Tujuan Modul --</option>
                        <option value="balita" {{ old('jenis_data', $type ?? '') == 'balita' ? 'selected' : '' }}>👶 Modul Data Anak & Balita</option>
                        <option value="remaja" {{ old('jenis_data', $type ?? '') == 'remaja' ? 'selected' : '' }}>🎓 Modul Data Remaja</option>
                        <option value="lansia" {{ old('jenis_data', $type ?? '') == 'lansia' ? 'selected' : '' }}>🧓 Modul Data Lansia</option>
                    </select>
                </div>

                <div class="relative z-10">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-3">Unggah File (Excel / CSV) <span class="text-rose-500">*</span></label>
                    <div class="file-drop-area" id="dropArea">
                        <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv" required class="file-input-hidden">
                        
                        <div class="w-20 h-20 rounded-full bg-white shadow-sm border border-slate-200 text-indigo-500 flex items-center justify-center text-3xl mx-auto mb-4 transition-colors" id="fileIcon">
                            <i class="fas fa-file-excel"></i>
                        </div>
                        
                        <h4 class="text-lg font-black text-slate-800 mb-1 font-poppins" id="fileNameDisplay">Seret & Lepas File di Sini</h4>
                        <p class="text-[13px] font-medium text-slate-500 mb-4" id="fileDescDisplay">atau klik untuk menelusuri komputer Anda</p>
                        
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-200/50 text-slate-600 rounded-lg text-[10px] font-bold uppercase tracking-widest">
                            <i class="fas fa-info-circle"></i> Maksimal 10 MB (.xlsx disarankan)
                        </div>
                    </div>
                </div>
            </div>

            {{-- PANEL KANAN: Pengaturan & Template --}}
            <div class="lg:col-span-4 bg-slate-50/80 rounded-[32px] border border-slate-200/80 shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-8 md:p-10 relative overflow-hidden flex flex-col">
                <div class="absolute right-0 top-0 w-40 h-40 bg-violet-500/10 rounded-bl-full pointer-events-none blur-2xl"></div>
                
                <div class="flex items-center gap-4 mb-8 border-b border-slate-200 pb-5 relative z-10">
                    <span class="w-10 h-10 rounded-full bg-violet-500 text-white flex items-center justify-center font-black shadow-md shadow-violet-200 shrink-0">2</span>
                    <h3 class="text-xl font-black text-slate-800 font-poppins">Pengaturan</h3>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm relative z-10 mb-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-robot text-indigo-500 text-xl"></i>
                            <h4 class="font-black text-slate-800 text-[14px]">AI Smart Mapping</h4>
                        </div>
                        <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                            <input type="checkbox" name="smart_import" id="smart_import" checked class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer border-indigo-500 transition-all duration-300 z-10" style="right: 0;"/>
                            <label for="smart_import" class="toggle-label block overflow-hidden h-6 rounded-full bg-indigo-500 cursor-pointer transition-colors duration-300"></label>
                        </div>
                    </div>
                    <p class="text-[12px] font-medium text-slate-500 leading-relaxed">Sistem akan membaca dan mencocokkan nama kolom Excel Anda dengan database secara otomatis.</p>
                </div>

                <div class="bg-amber-50 p-6 rounded-2xl border border-amber-200 shadow-sm relative z-10 mt-auto hover:bg-amber-100 transition-colors">
                    <h4 class="font-black text-amber-900 text-[13px] mb-2"><i class="fas fa-exclamation-triangle mr-1"></i> Template Standar</h4>
                    <p class="text-[11px] font-medium text-amber-700 leading-relaxed mb-4">Pilih Kategori Database di sebelah kiri, lalu unduh template resmi jika Anda tidak menggunakan AI.</p>
                    <button type="button" onclick="downloadTemplate()" class="w-full py-3 bg-white border border-amber-300 text-amber-700 font-bold text-[12px] rounded-xl hover:bg-amber-50 transition-colors flex items-center justify-center gap-2 shadow-sm">
                        <i class="fas fa-file-download"></i> Unduh Template
                    </button>
                </div>
            </div>
        </div>
        
        {{-- BOTTOM ACTION BAR --}}
        <div class="mt-8 bg-white border border-slate-200 p-6 md:p-8 rounded-[24px] shadow-[0_8px_30px_rgba(0,0,0,0.04)] flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4 hidden sm:flex">
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl shrink-0"><i class="fas fa-shield-check"></i></div>
                <div>
                    <p class="text-[13px] font-black text-slate-800">Proses Aman (Encrypted)</p>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Sistem perlindungan redundansi NIK aktif.</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                <a href="{{ route('kader.import.history') }}" class="loader-trigger w-full sm:w-auto px-6 py-4 bg-slate-100 text-slate-600 font-black text-[12px] rounded-[16px] hover:bg-slate-200 transition-colors text-center uppercase tracking-widest">Lihat Log History</a>
                <button type="submit" id="btnProses" class="btn-press w-full sm:w-auto px-10 py-4 bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-black text-[12px] rounded-[16px] hover:shadow-[0_8px_20px_rgba(79,70,229,0.4)] hover:-translate-y-1 transition-all flex items-center justify-center gap-2 uppercase tracking-widest">
                    <i class="fas fa-bolt text-lg"></i> Eksekusi Impor Data
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // 1. Logika Drag & Drop File Modern
    const dropArea = document.getElementById('dropArea');
    const fileInput = document.getElementById('file');
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    const fileDescDisplay = document.getElementById('fileDescDisplay');
    const fileIcon = document.getElementById('fileIcon');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => dropArea.addEventListener(eventName, preventDefaults, false));
    function preventDefaults(e) { e.preventDefault(); e.stopPropagation(); }
    ['dragenter', 'dragover'].forEach(eventName => dropArea.addEventListener(eventName, () => dropArea.classList.add('is-active'), false));
    ['dragleave', 'drop'].forEach(eventName => dropArea.addEventListener(eventName, () => dropArea.classList.remove('is-active'), false));

    dropArea.addEventListener('drop', (e) => {
        let dt = e.dataTransfer;
        fileInput.files = dt.files;
        handleFiles(dt.files);
    });

    fileInput.addEventListener('change', function() { handleFiles(this.files); });

    function handleFiles(files) {
        if(files.length > 0) {
            fileNameDisplay.textContent = files[0].name;
            fileNameDisplay.classList.add('text-indigo-600');
            fileDescDisplay.textContent = `Ukuran file: ${(files[0].size / (1024*1024)).toFixed(2)} MB`;
            fileIcon.classList.remove('text-indigo-500', 'bg-white');
            fileIcon.classList.add('text-white', 'bg-emerald-500', 'border-emerald-500');
            fileIcon.innerHTML = '<i class="fas fa-check-circle"></i>';
            dropArea.style.borderColor = '#10b981';
        }
    }

    // 2. ✨ FIX BUG ROUTE TEMPLATE ✨
    function downloadTemplate() {
        const jenisData = document.getElementById('jenis_data').value;
        if (!jenisData) {
            Swal.fire({
                icon: 'warning', title: 'Perhatian!', text: 'Silakan pilih "Kategori Database Target" terlebih dahulu sebelum mengunduh template.',
                confirmButtonColor: '#4f46e5', confirmButtonText: 'Mengerti', customClass: { popup: 'rounded-[28px]' }
            });
            document.getElementById('jenis_data').focus();
            return;
        }
        
        // Panggil route yang sesuai di web.php yaitu 'import.template'
        let urlTemplate = "{{ route('kader.import.template', ':type') }}";
        window.location.href = urlTemplate.replace(':type', jenisData);
    }

    // 3. Failsafe Form Submission dengan AJAX (Progress Mulus)
    document.getElementById('importForm').addEventListener('submit', async function(e) {
        e.preventDefault(); 
        
        if(!fileInput.files.length) {
            Swal.fire({ icon: 'error', title: 'File Kosong', text: 'Silakan seret atau pilih file Excel terlebih dahulu.', customClass: { popup: 'rounded-[28px]' } });
            return;
        }

        Swal.fire({
            title: 'AI Sedang Memproses...',
            html: 'Mohon tunggu, sistem sedang memetakan kolom dan mengamankan integritas database.',
            allowOutsideClick: false, showConfirmButton: false,
            willOpen: () => { Swal.showLoading(); },
            customClass: { popup: 'rounded-[28px]' }
        });

        try {
            const formData = new FormData(this);
            const response = await fetch(this.action, {
                method: 'POST', body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });

            const result = await response.json();

            if (response.ok && result.status === 'success') {
                Swal.fire({
                    icon: 'success', title: 'Import Berhasil!', text: result.message,
                    confirmButtonColor: '#10b981', timer: 2000, timerProgressBar: true, customClass: { popup: 'rounded-[28px]' }
                }).then(() => {
                    window.location.href = result.redirect || "{{ route('kader.import.history') }}"; 
                });
            } else {
                Swal.fire({
                    icon: 'error', title: 'Kegagalan Sistem', text: result.message || 'Cek kembali format file Excel Anda.',
                    confirmButtonColor: '#f43f5e', customClass: { popup: 'rounded-[28px]' }
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error', title: 'Koneksi Terputus', text: 'Terjadi kesalahan jaringan atau server timeout. Coba kurangi jumlah baris di Excel Anda.',
                confirmButtonColor: '#f43f5e', customClass: { popup: 'rounded-[28px]' }
            });
        }
    });
</script>
@endpush
@endsection