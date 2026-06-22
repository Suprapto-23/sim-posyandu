@extends('layouts.kader')

@section('title', 'Riwayat Import Data')
@section('page-name', 'Riwayat Import Data')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;

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
        ],
        'processing' => [
            'label' => 'Diproses',
            'icon' => 'fa-clock',
            'class' => 'border-amber-100 bg-amber-50 text-amber-700',
        ],
        'failed' => [
            'label' => 'Gagal',
            'icon' => 'fa-circle-xmark',
            'class' => 'border-rose-100 bg-rose-50 text-rose-700',
        ],
    ];

    $jenisData = $jenisData ?? request('jenis_data', 'semua');
    $status = $status ?? request('status', 'semua');
    $tanggal = $tanggal ?? request('tanggal');
    $search = $search ?? request('search', '');

    $totalImport = $imports->total() ?? 0;
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

    .card-hover:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.08);
        border-color: rgba(16, 185, 129, 0.3);
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.96); }

    /* Custom Input / Select */
    .input-soft {
        width: 100%;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 9999px;
        padding: 12px 16px;
        font-size: 13px;
        font-weight: 700;
        color: #1e293b;
        outline: none;
        transition: all .22s ease;
    }
    .input-soft:focus {
        background: #ffffff;
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, .12);
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

    /* Custom Scrollbar */
    .pc-scroll-container {
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: rgba(16, 185, 129, 0.4) transparent;
        padding-right: 8px;
    }
    .pc-scroll-container::-webkit-scrollbar { width: 6px; }
    .pc-scroll-container::-webkit-scrollbar-track { background: transparent; }
    .pc-scroll-container::-webkit-scrollbar-thumb { background-color: rgba(16, 185, 129, 0.4); border-radius: 999px; }
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
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        Pusat Monitoring
                    </span>
                </div>

                <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-3">
                    Riwayat Import Data
                </h1>

                <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    Halaman ini menampilkan seluruh jejak upload file Excel. Gunakan riwayat ini untuk melacak file, memeriksa jumlah baris data yang masuk, dan melihat status kegagalan validasi.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto shrink-0 justify-center">
                @if($routeHas('kader.import.index'))
                    <a href="{{ route('kader.import.index') }}" class="btn-pill bg-white/20 hover:bg-white/30 text-white border border-white/30 px-6 py-3.5 text-sm font-bold backdrop-blur-md flex items-center justify-center gap-2 transition-all shadow-sm">
                        <i class="fa-solid fa-layer-group"></i> Pusat Import
                    </a>
                @endif

                @if($routeHas('kader.import.create'))
                    <a href="{{ route('kader.import.create') }}" class="btn-pill bg-white hover:bg-emerald-50 text-emerald-600 px-6 py-3.5 text-sm font-black shadow-[0_8px_20px_rgba(255,255,255,0.3)] flex items-center justify-center gap-2 transition-all hover:-translate-y-0.5">
                        <i class="fa-solid fa-upload"></i> Import Baru
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- FILTER WIDGET --}}
    <section class="widget-card p-5 sm:p-6">
        <form method="GET" action="{{ route('kader.import.history') }}" class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_180px_180px_180px_auto] items-end">
            
            <div class="w-full">
                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-400">Pencarian Log</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 z-10"></i>
                    <input type="text" name="search" value="{{ $search }}" class="input-soft pl-11" placeholder="Cari nama file atau catatan...">
                </div>
            </div>

            <div class="w-full">
                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-400">Pilih Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="input-soft">
            </div>

            <div class="w-full">
                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-400">Jenis Sasaran</label>
                <select name="jenis_data" class="input-soft appearance-none cursor-pointer">
                    <option value="semua" {{ $jenisData === 'semua' ? 'selected' : '' }}>Semua Data</option>
                    @foreach($typeMeta as $key => $item)
                        <option value="{{ $key }}" {{ $jenisData === $key ? 'selected' : '' }}>
                            {{ $item['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-full">
                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-400">Status Import</label>
                <select name="status" class="input-soft appearance-none cursor-pointer">
                    <option value="semua" {{ $status === 'semua' ? 'selected' : '' }}>Semua Status</option>
                    @foreach($statusMeta as $key => $item)
                        <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>
                            {{ $item['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2 w-full lg:w-auto mt-2 lg:mt-0">
                <button type="submit" class="flex-1 lg:flex-none btn-pill bg-slate-800 hover:bg-slate-700 px-6 py-3 text-sm font-bold text-white shadow-md transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="{{ route('kader.import.history') }}" class="btn-pill border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 w-11 flex items-center justify-center text-slate-400 shadow-sm transition-all" title="Reset Filter">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </section>

    {{-- LIST DATA --}}
    <section class="widget-card overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-900">Daftar Riwayat Import</h2>
                <p class="mt-1 text-xs font-semibold text-slate-500">Menampilkan log upload file Excel berdasarkan filter aktif.</p>
            </div>
            <span class="btn-pill border border-emerald-200 bg-emerald-50 px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-700 cursor-default shadow-sm">
                {{ number_format($totalImport) }} Log Import
            </span>
        </div>

        @if(isset($imports) && $imports->count())
            <div class="pc-scroll-container max-h-[640px] p-4 sm:p-6 space-y-4 bg-white/40">
                @foreach($imports as $import)
                    @php
                        $totalTerbaca = $import->total_data ?? null;
                        $dataBaru = $import->data_berhasil ?? 0;
                        $dataTidakMasuk = $import->data_gagal ?? ($totalTerbaca !== null ? max(0, $totalTerbaca - $dataBaru) : null);
                        
                        $statusData = $statusMeta[$import->status] ?? $statusMeta['processing'];
                        $typeData = $typeMeta[$import->jenis_data] ?? [
                            'label' => ucfirst($import->jenis_data ?? '-'),
                            'icon' => 'fa-database',
                            'class' => 'border-slate-100 bg-slate-50 text-slate-700',
                        ];

                        $tanggalImport = $import->created_at ? $import->created_at->translatedFormat('d F Y') : '-';
                        $jamImport = $import->created_at ? $import->created_at->format('H:i') . ' WIB' : '-';
                        $creatorName = $import->creator?->name ?? $import->creator?->nama ?? 'Kader';
                    @endphp

                    <article class="card-hover rounded-[1.5rem] border border-slate-100 bg-white p-5 shadow-sm">
                        <div class="grid gap-5 xl:grid-cols-[1.3fr_150px_150px_130px_auto] xl:items-center">
                            
                            {{-- Info File & Status --}}
                            <div class="min-w-0">
                                <div class="mb-2.5 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-black uppercase tracking-wider {{ $typeData['class'] }}">
                                        <i class="fa-solid {{ $typeData['icon'] }}"></i> {{ $typeData['label'] }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-black uppercase tracking-wider {{ $statusData['class'] }}">
                                        <i class="fa-solid {{ $statusData['icon'] }}"></i> {{ $statusData['label'] }}
                                    </span>
                                </div>
                                <h3 class="truncate text-base font-black text-slate-900" title="{{ $import->nama_file }}">
                                    {{ $import->nama_file }}
                                </h3>
                                <p class="mt-1 text-xs font-bold text-slate-400">ID Log #{{ str_pad($import->id, 5, '0', STR_PAD_LEFT) }}</p>
                            </div>

                            {{-- Waktu --}}
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Waktu Upload</p>
                                <p class="mt-1 text-sm font-black text-slate-800">{{ $tanggalImport }}</p>
                                <p class="mt-0.5 text-xs font-bold text-slate-500">{{ $jamImport }}</p>
                            </div>

                            {{-- User --}}
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Diunggah Oleh</p>
                                <p class="mt-1 text-sm font-black text-slate-800">{{ $creatorName }}</p>
                                <p class="mt-0.5 text-xs font-bold text-slate-500">Akses Kader</p>
                            </div>

                            {{-- Data Statistik --}}
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Data Baru</p>
                                <p class="mt-1 text-xl font-black text-emerald-600">{{ number_format($dataBaru) }} <span class="text-xs text-slate-800 font-bold">Baris</span></p>
                                <p class="mt-0.5 text-[11px] font-bold text-slate-500">
                                    @if($totalTerbaca !== null) dari {{ number_format($totalTerbaca) }} total @else Log lama @endif
                                </p>
                            </div>

                            {{-- Action --}}
                            <div class="flex flex-wrap gap-2 xl:justify-end">
                                @if($routeHas('kader.import.show'))
                                    <a href="{{ route('kader.import.show', $import->id) }}" class="btn-pill flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 px-4 py-2.5 text-xs font-black text-white shadow-sm w-full sm:w-auto">
                                        <i class="fa-solid fa-eye"></i> Detail
                                    </a>
                                @endif

                                @if($routeHas('kader.import.destroy'))
                                    <button type="button" 
                                            class="btn-pill flex items-center justify-center gap-2 border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100 hover:border-rose-300 px-4 py-2.5 text-xs font-black shadow-sm w-full sm:w-auto"
                                            data-confirm-submit
                                            data-action-url="{{ route('kader.import.destroy', $import->id) }}"
                                            data-action-method="DELETE"
                                            data-confirm-title="Hapus Riwayat Import?" 
                                            data-confirm-message="Hanya log riwayat yang dihapus. Data sasaran (warga) yang sudah berhasil masuk ke database TIDAK akan ikut terhapus." 
                                            data-confirm-tone="danger">
                                        <i class="fa-solid fa-trash-can"></i> Hapus
                                    </button>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- PAGINATION --}}
            @if($imports->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                    {{ $imports->links() }}
                </div>
            @endif

        @else
            <div class="rounded-[2rem] border-2 border-dashed border-slate-200 bg-slate-50/70 p-12 text-center m-6">
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-[1.5rem] bg-white text-slate-300 shadow-sm border border-slate-100 mb-4">
                    <i class="fa-solid fa-folder-open text-2xl"></i>
                </div>
                <h3 class="mt-2 text-lg font-black text-slate-800">Riwayat Import Kosong</h3>
                <p class="mx-auto mt-1 max-w-sm text-xs font-semibold leading-relaxed text-slate-500">
                    Belum ada aktivitas import yang cocok dengan filter saat ini.
                </p>
                @if($routeHas('kader.import.create'))
                    <a href="{{ route('kader.import.create') }}" class="btn-pill mt-5 inline-flex items-center justify-center gap-2 bg-emerald-600 px-6 py-3 text-sm font-black text-white shadow-md hover:bg-emerald-700">
                        <i class="fa-solid fa-upload"></i> Import Pertama
                    </a>
                @endif
            </div>
        @endif
    </section>

    {{-- GLOBAL ACTION FORM (Dipakai otomatis oleh JS untuk action Hapus Log) --}}
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

            // Karena di halaman ini mayoritas aksi adalah Hapus (Danger)
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