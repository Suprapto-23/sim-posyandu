@extends('layouts.kader')

@section('title', 'Upload Data Import')
@section('page-name', 'Upload Data Import')

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
            'desc' => 'Template berisi nama lengkap, NIK balita, nama ibu, NIK ibu, tanggal lahir, dan data lahir.',
            'icon' => 'fa-child-reaching',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'desc' => 'Template berisi NIK, nama lengkap, sekolah, kelas, orang tua, dan alamat.',
            'icon' => 'fa-user-graduate',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'desc' => 'Template berisi NIK, nama lengkap, tanggal lahir, riwayat penyakit, dan alamat.',
            'icon' => 'fa-person-cane',
        ],
    ];

    $templateBaseUrl = url('/kader/import/template');
@endphp

@push('styles')
<style>
    .import-create-page {
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        position: relative;
        isolation: isolate;
    }

    .import-create-page::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background:
            radial-gradient(circle at 8% 8%, rgba(16,185,129,.13), transparent 28%),
            radial-gradient(circle at 92% 12%, rgba(245,158,11,.10), transparent 26%),
            radial-gradient(circle at 50% 100%, rgba(14,165,233,.08), transparent 32%),
            linear-gradient(135deg, #f8fffc 0%, #f8fafc 58%, #fffaf0 100%);
    }

    .glass-panel {
        border: 1px solid rgba(255,255,255,.78);
        background: rgba(255,255,255,.64);
        backdrop-filter: blur(18px);
        box-shadow: 0 18px 42px rgba(15,23,42,.06);
    }

    .hero-panel {
        border: 1px solid rgba(167,243,208,.72);
        background:
            radial-gradient(circle at 12% 18%, rgba(16,185,129,.16), transparent 32%),
            radial-gradient(circle at 88% 16%, rgba(245,158,11,.13), transparent 32%),
            linear-gradient(135deg, rgba(255,255,255,.72), rgba(236,253,245,.70));
        backdrop-filter: blur(18px);
        box-shadow: 0 18px 42px rgba(15,23,42,.06);
    }

    .input-premium {
        border: 1px solid rgba(226,232,240,.9);
        background: rgba(255,255,255,.72);
        outline: none;
        transition: all .3s ease-in-out;
    }

    .input-premium:focus {
        border-color: rgba(16,185,129,.42);
        box-shadow: 0 0 0 4px rgba(16,185,129,.08);
        background: rgba(255,255,255,.86);
    }

    .type-card {
        border: 1px solid rgba(226,232,240,.78);
        background: rgba(255,255,255,.58);
        backdrop-filter: blur(14px);
        transition: all .3s ease-in-out;
    }

    .type-card.active {
        border-color: rgba(16,185,129,.35);
        background: rgba(236,253,245,.82);
        box-shadow: 0 14px 32px rgba(5,150,105,.08);
    }

    .type-card:hover,
    .dropzone:hover {
        transform: translateY(-2px);
        border-color: rgba(16,185,129,.28);
        box-shadow: 0 18px 38px rgba(15,23,42,.06);
    }

    .dropzone {
        border: 2px dashed rgba(16,185,129,.32);
        background: rgba(236,253,245,.34);
        transition: all .3s ease-in-out;
    }

    .dropzone.dragging {
        border-color: rgba(5,150,105,.72);
        background: rgba(209,250,229,.72);
        transform: translateY(-2px);
    }

    .toast-custom {
        position: fixed;
        right: 24px;
        top: 96px;
        z-index: 90;
        width: min(390px, calc(100vw - 32px));
        opacity: 0;
        pointer-events: none;
        transform: translateY(-10px);
        transition: all .3s ease-in-out;
    }

    .toast-custom.show {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0);
    }

    @media (max-width: 640px) {
        .toast-custom {
            left: 16px;
            right: 16px;
            top: 82px;
        }
    }
</style>
@endpush

@section('content')
<div class="import-create-page space-y-5">

    {{-- CUSTOM TOAST --}}
    <div id="customToast" class="toast-custom">
        <div class="rounded-[24px] border border-rose-100 bg-white/80 p-4 shadow-[0_22px_60px_rgba(15,23,42,.22)] backdrop-blur-xl">
            <div class="flex gap-3">
                <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-rose-50 text-rose-600">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>
                <div>
                    <p class="text-sm font-black text-slate-900">Import belum siap</p>
                    <p id="customToastText" class="mt-1 text-xs font-bold leading-5 text-slate-500">
                        Lengkapi data terlebih dahulu.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- HERO --}}
    <section class="hero-panel rounded-[30px] p-5 sm:p-6">
        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/80 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-emerald-700">
                    <i class="fa-solid fa-upload"></i>
                    Upload Template Excel
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Import Data Sasaran Posyandu
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Pilih kategori data, unduh template resmi, lalu unggah file Excel yang sudah diisi. Sistem akan memvalidasi format dan menolak data yang tidak sesuai.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @if($routeHas('kader.import.index'))
                    <a href="{{ route('kader.import.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-white/60 px-5 py-3 text-sm font-black text-emerald-700 backdrop-blur-md transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-50">
                        <i class="fa-solid fa-arrow-left"></i>
                        Pusat Import
                    </a>
                @endif

                @if($routeHas('kader.import.history'))
                    <a href="{{ route('kader.import.history') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white shadow-[0_14px_28px_rgba(15,23,42,.18)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-slate-800">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        Riwayat
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- SERVER ERRORS --}}
    @if($errors->any() || session('error'))
        <section class="rounded-[24px] border border-rose-100 bg-rose-50/80 p-4 text-sm font-bold text-rose-700">
            <div class="mb-2 flex items-center gap-2 font-black">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Import gagal diproses
            </div>

            @if(session('error'))
                <p class="leading-6">{{ session('error') }}</p>
            @endif

            @if($errors->any())
                <ul class="ml-5 list-disc space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif

    <form id="importForm" method="POST" action="{{ route('kader.import.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-5 xl:grid-cols-12" novalidate>
        @csrf

        {{-- LEFT --}}
        <section class="glass-panel rounded-[30px] p-4 sm:p-5 xl:col-span-8">
            <div class="mb-5">
                <h2 class="text-lg font-black text-slate-900">1. Pilih Kategori Data</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Kategori harus sesuai dengan template yang diunggah.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">
                @foreach($typeMeta as $key => $item)
                    <label class="type-card {{ $type === $key ? 'active' : '' }} cursor-pointer rounded-[24px] p-4" data-type-card="{{ $key }}">
                        <input type="radio" name="jenis_data" value="{{ $key }}" class="sr-only type-radio" {{ $type === $key ? 'checked' : '' }}>

                        <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50/90 text-emerald-700">
                            <i class="fa-solid {{ $item['icon'] }}"></i>
                        </div>

                        <h3 class="text-sm font-black text-slate-900">{{ $item['label'] }}</h3>
                        <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                            {{ $item['desc'] }}
                        </p>
                    </label>
                @endforeach
            </div>

            <div class="mt-6">
                <h2 class="text-lg font-black text-slate-900">2. Unggah File Excel</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Gunakan format .xlsx agar NIK tidak berubah menjadi notasi ilmiah Excel.
                </p>

                <label id="dropzone" for="fileInput" class="dropzone mt-4 flex min-h-[230px] cursor-pointer flex-col items-center justify-center rounded-[28px] p-6 text-center">
                    <div class="grid h-16 w-16 place-items-center rounded-3xl bg-white/70 text-emerald-700 shadow-[0_12px_24px_rgba(5,150,105,.10)] backdrop-blur-md">
                        <i class="fa-solid fa-cloud-arrow-up text-2xl"></i>
                    </div>

                    <h3 id="fileTitle" class="mt-4 text-lg font-black text-slate-900">
                        Seret file ke sini atau klik untuk memilih
                    </h3>

                    <p id="fileDesc" class="mt-2 max-w-md text-sm font-bold leading-6 text-slate-500">
                        Format yang didukung: xlsx, xls, csv. Maksimal 10 MB.
                    </p>

                    <input id="fileInput" type="file" name="file" class="hidden" accept=".xlsx,.xls,.csv">
                </label>
            </div>

            <div class="mt-5 rounded-[24px] border border-emerald-100 bg-emerald-50/70 p-4">
                <div class="flex items-start gap-3">
                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white/70 text-emerald-700">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <div>
                        <h3 class="text-sm font-black text-emerald-800">Validasi Import Aktif</h3>
                        <p class="mt-1 text-xs font-bold leading-5 text-emerald-700">
                            Sistem mengecek NIK 16 digit, jenis kelamin, tanggal lahir, dan duplikasi data sebelum menyimpan ke database.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- RIGHT --}}
        <aside class="space-y-5 xl:col-span-4">
            <section class="glass-panel rounded-[30px] p-4 sm:p-5">
                <div class="mb-4 flex items-center gap-3">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-50/90 text-amber-700">
                        <i class="fa-solid fa-file-excel"></i>
                    </div>

                    <div>
                        <h2 class="text-lg font-black text-slate-900">Template Resmi</h2>
                        <p class="mt-1 text-xs font-bold text-slate-400">
                            Unduh sesuai kategori.
                        </p>
                    </div>
                </div>

                <div class="rounded-[24px] border border-amber-100 bg-amber-50/60 p-4">
                    <p class="text-xs font-bold leading-5 text-amber-700">
                        Jangan mengubah nama kolom pada baris ke-3. Isi data mulai baris ke-4.
                    </p>
                </div>

                <a id="downloadTemplateBtn"
                   href="{{ $templateBaseUrl }}/{{ $type }}"
                   class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-amber-100 bg-amber-500/90 px-5 py-3 text-sm font-black text-white shadow-[0_12px_24px_rgba(245,158,11,.16)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-amber-600">
                    <i class="fa-solid fa-download"></i>
                    Unduh Template
                </a>
            </section>

            <section class="glass-panel rounded-[30px] p-4 sm:p-5">
                <div class="mb-4 flex items-center gap-3">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-sky-50/90 text-sky-700">
                        <i class="fa-solid fa-list-check"></i>
                    </div>

                    <div>
                        <h2 class="text-lg font-black text-slate-900">Checklist</h2>
                        <p class="mt-1 text-xs font-bold text-slate-400">
                            Cek dulu sebelum upload.
                        </p>
                    </div>
                </div>

                <div class="space-y-3 text-xs font-bold text-slate-500">
                    <div class="flex gap-3 rounded-2xl bg-slate-50/70 p-3">
                        <i class="fa-solid fa-check text-emerald-600"></i>
                        <p>NIK tidak berubah menjadi format angka ilmiah.</p>
                    </div>

                    <div class="flex gap-3 rounded-2xl bg-slate-50/70 p-3">
                        <i class="fa-solid fa-check text-emerald-600"></i>
                        <p>Tanggal lahir memakai format YYYY-MM-DD.</p>
                    </div>

                    <div class="flex gap-3 rounded-2xl bg-slate-50/70 p-3">
                        <i class="fa-solid fa-check text-emerald-600"></i>
                        <p>Kategori yang dipilih sama dengan template file.</p>
                    </div>

                    <div class="flex gap-3 rounded-2xl bg-slate-50/70 p-3">
                        <i class="fa-solid fa-check text-emerald-600"></i>
                        <p>Data yang sudah ada berdasarkan NIK akan dilewati agar tidak duplikat.</p>
                    </div>
                </div>
            </section>
        </aside>

        {{-- ACTION --}}
        <section class="glass-panel rounded-[26px] p-4 xl:col-span-12">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-sm font-black text-slate-900">Siap menjalankan import</h3>
                    <p class="mt-1 text-xs font-bold text-slate-400">
                        Pastikan kategori dan file sudah sesuai sebelum menekan tombol proses.
                    </p>
                </div>

                <button type="submit"
                        id="submitBtn"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-black text-white shadow-[0_14px_28px_rgba(5,150,105,.18)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                    <i class="fa-solid fa-database"></i>
                    Proses Import Data
                </button>
            </div>
        </section>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const templateBaseUrl = @json($templateBaseUrl);
    const typeRadios = document.querySelectorAll('.type-radio');
    const typeCards = document.querySelectorAll('[data-type-card]');
    const downloadTemplateBtn = document.getElementById('downloadTemplateBtn');
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const fileTitle = document.getElementById('fileTitle');
    const fileDesc = document.getElementById('fileDesc');
    const form = document.getElementById('importForm');
    const submitBtn = document.getElementById('submitBtn');
    const toast = document.getElementById('customToast');
    const toastText = document.getElementById('customToastText');

    let toastTimer = null;

    const showToast = (message) => {
        toastText.textContent = message;
        toast.classList.add('show');

        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.classList.remove('show');
        }, 3600);
    };

    const getSelectedType = () => {
        return document.querySelector('.type-radio:checked')?.value || 'balita';
    };

    const updateTypeUI = () => {
        const selected = getSelectedType();

        typeCards.forEach(card => {
            card.classList.toggle('active', card.dataset.typeCard === selected);
        });

        downloadTemplateBtn.href = `${templateBaseUrl}/${selected}`;
    };

    typeRadios.forEach(radio => {
        radio.addEventListener('change', updateTypeUI);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, event => {
            event.preventDefault();
            dropzone.classList.add('dragging');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, event => {
            event.preventDefault();
            dropzone.classList.remove('dragging');
        });
    });

    dropzone.addEventListener('drop', event => {
        const file = event.dataTransfer.files?.[0];

        if (!file) {
            return;
        }

        fileInput.files = event.dataTransfer.files;
        updateFilePreview(file);
    });

    fileInput.addEventListener('change', () => {
        const file = fileInput.files?.[0];

        if (file) {
            updateFilePreview(file);
        }
    });

    const updateFilePreview = (file) => {
        const sizeMb = (file.size / 1024 / 1024).toFixed(2);

        fileTitle.textContent = file.name;
        fileDesc.textContent = `${sizeMb} MB, siap diproses.`;
    };

    form.addEventListener('submit', event => {
        const file = fileInput.files?.[0];

        if (!getSelectedType()) {
            event.preventDefault();
            showToast('Pilih kategori data terlebih dahulu.');
            return;
        }

        if (!file) {
            event.preventDefault();
            showToast('Pilih file Excel terlebih dahulu sebelum memproses import.');
            return;
        }

        const allowed = ['xlsx', 'xls', 'csv'];
        const extension = file.name.split('.').pop().toLowerCase();

        if (!allowed.includes(extension)) {
            event.preventDefault();
            showToast('Format file harus xlsx, xls, atau csv.');
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            event.preventDefault();
            showToast('Ukuran file maksimal 10 MB.');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Memproses Import...';
    });

    updateTypeUI();
});
</script>
@endpush