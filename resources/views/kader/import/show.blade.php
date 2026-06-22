@extends('layouts.kader')

@section('title', 'Detail Riwayat Import')
@section('page-name', 'Detail Riwayat Import')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Storage;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $typeMeta = [
        'balita' => [
            'label' => 'Balita / Anak',
            'icon' => 'fa-child-reaching',
            'class' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'icon' => 'fa-user-graduate',
            'class' => 'border-sky-100 bg-sky-50 text-sky-700',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'icon' => 'fa-person-cane',
            'class' => 'border-amber-100 bg-amber-50 text-amber-700',
        ],
    ];

    $statusMeta = [
        'completed' => [
            'label' => 'Berhasil',
            'icon' => 'fa-circle-check',
            'class' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
            'soft' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        ],
        'processing' => [
            'label' => 'Diproses',
            'icon' => 'fa-clock',
            'class' => 'border-amber-100 bg-amber-50 text-amber-700',
            'soft' => 'border-amber-200 bg-amber-50 text-amber-800',
        ],
        'failed' => [
            'label' => 'Gagal',
            'icon' => 'fa-circle-xmark',
            'class' => 'border-rose-100 bg-rose-50 text-rose-700',
            'soft' => 'border-rose-200 bg-rose-50 text-rose-800',
        ],
    ];

    $statusData = $statusMeta[$import->status] ?? $statusMeta['processing'];

    $typeData = $typeMeta[$import->jenis_data] ?? [
        'label' => ucfirst($import->jenis_data ?? '-'),
        'icon' => 'fa-database',
        'class' => 'border-slate-100 bg-slate-50 text-slate-700',
    ];

    $creatorName = $import->creator?->name
        ?? $import->creator?->nama
        ?? 'Kader';

    $createdLabel = $import->created_at
        ? $import->created_at->translatedFormat('l, d F Y') . ' pukul ' . $import->created_at->format('H:i') . ' WIB'
        : '-';

    $updatedLabel = $import->updated_at
        ? $import->updated_at->translatedFormat('l, d F Y') . ' pukul ' . $import->updated_at->format('H:i') . ' WIB'
        : '-';

    $catatan = trim((string) ($import->catatan ?? 'Tidak ada catatan sistem.'));
    $filePath = $import->file_path ?? null;
    $fileExists = $filePath && Storage::exists($filePath);
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

    /* Clean Card Layout */
    .widget-card {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 2rem; 
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
        transition: all 0.3s ease;
    }

    .info-card {
        background: rgba(248, 250, 252, 0.6);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 1.5rem;
        transition: all 0.3s ease;
    }
    .info-card:hover {
        background: #ffffff;
        border-color: rgba(16, 185, 129, 0.3);
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -5px rgba(15, 23, 42, 0.05);
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.96); }

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

    /* Terminal Log Style */
    .terminal-log {
        background: #0f172a;
        color: #a7f3d0;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        border: 1px solid #1e293b;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.5);
    }
</style>
@endpush

@section('content')
<div class="max-w-[1280px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6 space-y-6">

    {{-- HERO WIDGET --}}
    <section class="bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-600 rounded-[3rem] p-8 md:p-10 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(16,185,129,.35)] border border-white/20">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/15 blur-[80px] rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-white/10 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row gap-8 lg:items-center justify-between">
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 text-white/90 text-[10px] font-black uppercase tracking-widest mb-4">
                    <span class="bg-white/20 backdrop-blur-md border border-white/30 px-3 py-1 rounded-full shadow-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-file-circle-check"></i>
                        Detail Log Import
                    </span>
                </div>

                <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-3">
                    Detail Riwayat Import
                </h1>

                <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    Detail ini menjelaskan file yang diunggah Kader, kategori database tujuan, jumlah data yang berhasil masuk, dan catatan hasil validasi dari sistem.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto shrink-0 justify-center">
                @if($routeHas('kader.import.history'))
                    <a href="{{ route('kader.import.history') }}" class="btn-pill bg-white/20 hover:bg-white/30 text-white border border-white/30 px-6 py-3.5 text-sm font-bold backdrop-blur-md flex items-center justify-center gap-2 transition-all shadow-sm">
                        <i class="fa-solid fa-arrow-left"></i> Riwayat
                    </a>
                @endif

                @if($routeHas('kader.import.create'))
                    <a href="{{ route('kader.import.create', ['type' => $import->jenis_data]) }}" class="btn-pill bg-white hover:bg-emerald-50 text-emerald-600 px-6 py-3.5 text-sm font-black shadow-[0_8px_20px_rgba(255,255,255,0.3)] flex items-center justify-center gap-2 transition-all hover:-translate-y-0.5">
                        <i class="fa-solid fa-upload"></i> Import Ulang
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- SUMMARY UTAMA --}}
    <section class="widget-card p-6 sm:p-8">
        <div class="grid gap-6 xl:grid-cols-[1.15fr_.85fr] xl:items-center">
            <div class="flex items-start gap-5">
                <div class="w-16 h-16 shrink-0 rounded-[1.5rem] bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-3xl shadow-sm">
                    <i class="fa-solid fa-file-excel"></i>
                </div>

                <div class="min-w-0">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] {{ $typeData['class'] }}">
                            <i class="fa-solid {{ $typeData['icon'] }}"></i> {{ $typeData['label'] }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] {{ $statusData['class'] }}">
                            <i class="fa-solid {{ $statusData['icon'] }}"></i> {{ $statusData['label'] }}
                        </span>
                    </div>

                    <h2 class="break-words text-2xl font-black tracking-tight text-slate-900 leading-tight">
                        {{ $import->nama_file }}
                    </h2>

                    <p class="mt-2 text-xs font-bold text-slate-500">
                        ID Log #{{ str_pad($import->id, 5, '0', STR_PAD_LEFT) }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-[1.5rem] border border-emerald-100 bg-emerald-50/80 p-5">
                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-700">Data Berhasil</p>
                    <p class="mt-2 text-4xl font-black text-slate-900">{{ number_format($import->data_berhasil ?? 0) }}</p>
                    <p class="mt-1 text-xs font-bold text-slate-500">Baris Masuk</p>
                </div>

                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-5">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Status Akhir</p>
                    <p class="mt-2 text-lg font-black text-slate-900">{{ $statusData['label'] }}</p>
                    <p class="mt-1 text-xs font-bold text-slate-500">Validasi Sistem</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ALERT STATUS KHUSUS --}}
    <section class="rounded-[1.5rem] border-2 p-5 {{ $statusData['soft'] }} flex items-start gap-4">
        <div class="w-12 h-12 shrink-0 rounded-[1rem] bg-white flex items-center justify-center text-xl shadow-sm">
            <i class="fa-solid {{ $statusData['icon'] }}"></i>
        </div>
        <div class="pt-0.5">
            <h3 class="text-base font-black">
                @if($import->status === 'completed') Import Berhasil Diproses
                @elseif($import->status === 'failed') Import Gagal Diproses
                @else Import Sedang Diproses @endif
            </h3>
            <p class="mt-1 text-sm font-semibold leading-relaxed opacity-90">
                @if($import->status === 'completed')
                    Data valid sudah masuk ke database sasaran sesuai kategori. Baris data dengan NIK duplikat secara otomatis dilewati oleh sistem agar tidak terjadi penggandaan warga.
                @elseif($import->status === 'failed')
                    File belum berhasil disimpan ke database. Periksa "Catatan Sistem" di bawah untuk melihat penyebab kegagalan, lalu perbaiki file Excel Anda dan lakukan upload ulang.
                @else
                    File sudah diterima oleh server dan masih berada dalam antrean pemrosesan. Silakan muat ulang halaman ini beberapa saat lagi.
                @endif
            </p>
        </div>
    </section>

    {{-- DETAIL GRID --}}
    <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">

        {{-- KIRI: INFORMASI FILE --}}
        <div class="widget-card p-6 sm:p-8 xl:col-span-7">
            <div class="mb-5 border-b border-slate-50 pb-4">
                <h3 class="text-lg font-black text-slate-900">Informasi File</h3>
                <p class="mt-1 text-xs font-bold text-slate-500">Metadata import dan target database.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="info-card p-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Nama File</p>
                    <p class="mt-1.5 break-words text-sm font-black text-slate-800">{{ $import->nama_file }}</p>
                </div>

                <div class="info-card p-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Jenis Sasaran</p>
                    <p class="mt-1.5 text-sm font-black text-slate-800">{{ $typeData['label'] }}</p>
                </div>

                <div class="info-card p-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Diunggah Oleh</p>
                    <p class="mt-1.5 text-sm font-black text-slate-800">{{ $creatorName }}</p>
                    <p class="mt-0.5 text-xs font-bold text-slate-500">Kader Posyandu</p>
                </div>

                <div class="info-card p-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Arsip Server</p>
                    <p class="mt-1.5 text-sm font-black {{ $fileExists ? 'text-emerald-600' : 'text-slate-500' }}">
                        {{ $fileExists ? 'Tersedia' : 'Sudah Dihapus / Hilang' }}
                    </p>
                </div>

                <div class="info-card p-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Waktu Upload</p>
                    <p class="mt-1.5 text-sm font-black text-slate-800">{{ $createdLabel }}</p>
                </div>

                <div class="info-card p-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Update Terakhir</p>
                    <p class="mt-1.5 text-sm font-black text-slate-800">{{ $updatedLabel }}</p>
                </div>
            </div>
        </div>

        {{-- KANAN: AKSI LANJUTAN --}}
        <div class="widget-card p-6 sm:p-8 xl:col-span-5">
            <div class="mb-5 border-b border-slate-50 pb-4">
                <h3 class="text-lg font-black text-slate-900">Aksi Lanjutan</h3>
                <p class="mt-1 text-xs font-bold text-slate-500">Gunakan fitur ini jika perlu memperbaiki data.</p>
            </div>

            <div class="space-y-4">
                {{-- TOMBOL UNDUH TEMPLATE INSTAN --}}
                @if($routeHas('kader.import.template'))
                    <button type="button" onclick="downloadTemplateInstan('{{ route('kader.import.template', $import->jenis_data) }}')"
                       class="w-full flex items-center justify-between gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-700 hover:-translate-y-1 hover:shadow-md transition-all text-left group">
                        <div>
                            <p class="text-sm font-black text-amber-800 group-hover:text-amber-900">Unduh Template Asli</p>
                            <p class="mt-1 text-[11px] font-bold">Gunakan template bersih ini untuk upload ulang.</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
                            <i class="fa-solid fa-download"></i>
                        </div>
                    </button>
                @endif

                @if($routeHas('kader.import.create'))
                    <a href="{{ route('kader.import.create', ['type' => $import->jenis_data]) }}"
                       class="flex items-center justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-700 hover:-translate-y-1 hover:shadow-md transition-all group">
                        <div>
                            <p class="text-sm font-black text-emerald-800 group-hover:text-emerald-900">Import Ulang</p>
                            <p class="mt-1 text-[11px] font-bold">Lakukan upload file Excel hasil perbaikan.</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600">
                            <i class="fa-solid fa-upload"></i>
                        </div>
                    </a>
                @endif

                @if($routeHas('kader.import.destroy'))
                    <button type="button" 
                            class="w-full flex items-center justify-between gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-5 text-rose-700 hover:-translate-y-1 hover:shadow-md transition-all text-left group"
                            data-confirm-submit
                            data-action-url="{{ route('kader.import.destroy', $import->id) }}"
                            data-action-method="DELETE"
                            data-confirm-title="Hapus Riwayat Import?" 
                            data-confirm-message="Hanya log riwayat dan file arsip yang dihapus. Data sasaran (warga) yang sudah berhasil masuk ke database TIDAK akan ikut terhapus." 
                            data-confirm-tone="danger">
                        <div>
                            <p class="text-sm font-black text-rose-800 group-hover:text-rose-900">Hapus Log Import</p>
                            <p class="mt-1 text-[11px] font-bold">Bersihkan jejak aktivitas ini dari riwayat.</p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600">
                            <i class="fa-solid fa-trash-can"></i>
                        </div>
                    </button>
                @endif
            </div>
        </div>
    </section>

    {{-- CATATAN SISTEM (TERMINAL LOG) --}}
    <section class="widget-card p-6 sm:p-8">
        <div class="mb-5 border-b border-slate-50 pb-4 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-900">Catatan Sistem (Log)</h3>
                <p class="mt-1 text-xs font-bold text-slate-500">Pesan dari server mengenai proses eksekusi baris data.</p>
            </div>
            <i class="fa-solid fa-terminal text-2xl text-slate-200"></i>
        </div>

        <div class="terminal-log rounded-2xl p-5 overflow-x-auto">
            <div class="mb-4 flex items-center gap-2 border-b border-slate-700 pb-3">
                <span class="h-3 w-3 rounded-full bg-rose-500"></span>
                <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                <span class="ml-2 text-[10px] font-black uppercase tracking-widest text-slate-500">server_response.log</span>
            </div>

            <pre class="whitespace-pre-wrap break-words text-xs font-semibold leading-relaxed">{{ $catatan }}</pre>
        </div>
    </section>

    {{-- ACTION BOTTOM --}}
    <div class="flex justify-center mt-8">
        @if($routeHas('kader.import.history'))
            <a href="{{ route('kader.import.history') }}" class="btn-pill inline-flex items-center justify-center gap-2 bg-slate-800 px-8 py-4 text-sm font-black text-white shadow-lg hover:bg-slate-700 hover:-translate-y-1 transition-all">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Riwayat
            </a>
        @endif
    </div>

    {{-- GLOBAL ACTION FORM (Untuk Hapus Log) --}}
    <form id="globalActionForm" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="_method" id="globalActionMethod" value="POST">
    </form>
</div>
@endsection

@push('modals')
{{-- MODAL KONFIRMASI HAPUS LOG (Z-Index Absolut Menutupi Layar Penuh) --}}
<div id="nexusConfirmModal" class="pc-modal-backdrop">
    <div class="pc-modal-card">
        <div id="nexusConfirmHeader" class="relative overflow-hidden px-6 py-6 text-white text-center bg-gradient-to-br from-rose-600 to-rose-800">
            <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-white/10 pointer-events-none"></div>
            <div class="absolute -bottom-16 left-8 h-28 w-44 rounded-t-[80px] bg-white/10 pointer-events-none"></div>

            <div class="relative z-10">
                <i id="nexusConfirmIcon" class="fa-solid fa-trash-can text-4xl mb-3 opacity-90"></i>
                <h3 id="nexusConfirmTitle" class="text-xl font-black tracking-tight mb-1">Hapus Riwayat Import?</h3>
                <p id="nexusConfirmMessage" class="text-xs font-semibold leading-relaxed opacity-80 px-4">
                    Hanya log riwayat yang dihapus. Data warga yang sudah berhasil masuk tidak akan ikut terhapus.
                </p>
            </div>
        </div>

        <div class="p-6 bg-white text-center">
            <div class="grid grid-cols-2 gap-3">
                <button type="button" id="nexusConfirmCancel" class="btn-pill h-11 border border-slate-200 bg-white text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">Batal</button>
                <button type="button" id="nexusConfirmOk" class="btn-pill h-11 bg-rose-600 text-sm font-bold text-white shadow-md hover:bg-rose-700 transition-colors">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    // FUNGSI SUPER CEPAT UNDUH TEMPLATE NATIVE HTML5
    window.downloadTemplateInstan = function(url) {
        let iframe = document.getElementById('instanDownloader');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'instanDownloader';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
        }
        iframe.src = url; 
    };

    // DEKLARASI GLOBAL SANGAT AMAN UNTUK TOMBOL AKSI
    window.nexusPendingUrl = null;
    window.nexusPendingMethod = null;

    document.addEventListener('DOMContentLoaded', function () {
        var confirmModal = document.getElementById('nexusConfirmModal');
        var confirmHeader = document.getElementById('nexusConfirmHeader');
        var confirmIcon = document.getElementById('nexusConfirmIcon');
        var confirmTitle = document.getElementById('nexusConfirmTitle');
        var confirmMessage = document.getElementById('nexusConfirmMessage');
        var confirmCancel = document.getElementById('nexusConfirmCancel');
        var confirmOk = document.getElementById('nexusConfirmOk');

        // Pindahkan Modal ke Body agar z-index max bekerja
        if (confirmModal && confirmModal.parentElement !== document.body) {
            document.body.appendChild(confirmModal);
        }

        function openConfirm(options) {
            if (!confirmModal) return;
            
            var tone = options.tone || 'danger';
            confirmTitle.textContent = options.title || 'Konfirmasi Aksi';
            confirmMessage.textContent = options.message || 'Pastikan aksi ini sudah benar.';

            if (tone === 'danger') {
                confirmHeader.className = 'relative overflow-hidden px-6 py-6 text-white text-center bg-gradient-to-br from-rose-600 to-rose-800';
                confirmIcon.className = 'fa-solid fa-trash-can text-4xl mb-3 opacity-90';
                confirmOk.className = 'btn-pill h-11 bg-rose-600 text-sm font-bold text-white shadow-md hover:bg-rose-700 transition-colors';
                confirmOk.innerHTML = 'Ya, Hapus';
            } 

            confirmModal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeConfirm() {
            window.nexusPendingUrl = null;
            window.nexusPendingMethod = null;
            
            if (confirmModal) confirmModal.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        // ==========================================
        // SISTEM EVENT DELEGATION ANTI-BUG
        // ==========================================
        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('[data-confirm-submit]');
            
            if (!trigger || trigger.disabled) return;

            event.preventDefault();

            var actionUrl = trigger.getAttribute('data-action-url');
            if (actionUrl) {
                window.nexusPendingUrl = actionUrl;
                window.nexusPendingMethod = trigger.getAttribute('data-action-method') || 'POST';
                
                openConfirm({
                    title: trigger.getAttribute('data-confirm-title'),
                    message: trigger.getAttribute('data-confirm-message'),
                    tone: trigger.getAttribute('data-confirm-tone')
                });
            }
        });

        // Eksekusi Submit
        if (confirmOk) {
            confirmOk.addEventListener('click', function () {
                if (window.nexusPendingUrl) {
                    var globalForm = document.getElementById('globalActionForm');
                    var globalMethod = document.getElementById('globalActionMethod');
                    
                    if (globalForm && globalMethod) {
                        globalForm.action = window.nexusPendingUrl;
                        globalMethod.value = window.nexusPendingMethod;
                        
                        closeConfirm();
                        
                        confirmOk.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                        confirmOk.disabled = true;
                        
                        HTMLFormElement.prototype.submit.call(globalForm);
                    }
                } 
            });
        }

        // Modal Cancel Events
        if (confirmCancel) confirmCancel.addEventListener('click', closeConfirm);
        if (confirmModal) confirmModal.addEventListener('click', function(e) { if (e.target === confirmModal) closeConfirm(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && confirmModal && confirmModal.classList.contains('is-open')) closeConfirm(); });
    });
</script>
@endpush