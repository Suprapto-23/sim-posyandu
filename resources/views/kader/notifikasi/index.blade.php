@extends('layouts.kader')

@section('title', 'Pusat Sinyal & Notifikasi')
@section('page-name', 'Pusat Notifikasi')
@section('page-title', 'Pusat Notifikasi')

@php
    use Illuminate\Support\Str;

    $filter = $filter ?? request('filter', 'semua');
    $unreadCount = (int) ($unreadCount ?? 0);

    $totalNotifikasi = method_exists($notifikasis ?? null, 'total')
        ? $notifikasis->total()
        : (is_countable($notifikasis ?? []) ? count($notifikasis) : 0);

    // Penyesuaian Palet Warna Konsisten (Emerald, Teal, Sky, Amber)
    $styleFor = function ($notif) {
        $text = strtolower(($notif->tipe ?? '') . ' ' . ($notif->judul ?? '') . ' ' . ($notif->pesan ?? ''));

        if (Str::contains($text, ['jadwal', 'agenda'])) {
            return [
                'icon' => 'fas fa-calendar-check',
                'iconWrap' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'border' => 'border-l-emerald-500',
                'label' => 'Jadwal',
            ];
        }

        if (Str::contains($text, ['pemeriksaan', 'validasi', 'rekam medis', 'kesehatan'])) {
            return [
                'icon' => 'fas fa-stethoscope',
                'iconWrap' => 'bg-sky-50 text-sky-600 border-sky-100',
                'badge' => 'bg-sky-50 text-sky-700 border-sky-200',
                'border' => 'border-l-sky-500',
                'label' => 'Kesehatan',
            ];
        }

        if (Str::contains($text, ['imunisasi', 'vaksin'])) {
            return [
                'icon' => 'fas fa-syringe',
                'iconWrap' => 'bg-teal-50 text-teal-600 border-teal-100',
                'badge' => 'bg-teal-50 text-teal-700 border-teal-200',
                'border' => 'border-l-teal-500',
                'label' => 'Imunisasi',
            ];
        }

        if (Str::contains($text, ['laporan', 'rekap'])) {
            return [
                'icon' => 'fas fa-file-medical',
                'iconWrap' => 'bg-amber-50 text-amber-600 border-amber-100',
                'badge' => 'bg-amber-50 text-amber-700 border-amber-200',
                'border' => 'border-l-amber-500',
                'label' => 'Laporan',
            ];
        }

        return [
            'icon' => $notif->tipe_icon ?? 'fas fa-bell',
            'iconWrap' => 'bg-slate-100 text-slate-600 border-slate-200',
            'badge' => 'bg-slate-100 text-slate-700 border-slate-200',
            'border' => 'border-l-slate-400',
            'label' => 'Informasi',
        ];
    };
@endphp

@push('styles')
<style>
    body {
        background-color: #f8fafc;
        background-image: radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
                          radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }

    /* Animasi hanya diaplikasikan pada Container Utama untuk menghemat memori */
    .animate-pop-in {
        animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }
    @keyframes popIn {
        from { opacity: 0; transform: scale(.96) translateY(12px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .widget-card {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 2rem; 
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05);
        transition: all 0.3s ease;
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.96); }

    /* Efek Notifikasi tanpa animasi berat */
    .notif-item {
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
    }
    .notif-item:hover {
        background-color: #ffffff;
        box-shadow: 0 5px 15px -3px rgba(15, 23, 42, 0.05);
        border-color: rgba(16, 185, 129, 0.4);
    }
    .notif-unread {
        background-color: rgba(255, 255, 255, 0.95);
    }
    .notif-read {
        background-color: rgba(248, 250, 252, 0.6);
    }

    /* Scrollbar Khusus untuk membatasi tinggi daftar notifikasi */
    .pc-scroll-container {
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: rgba(16, 185, 129, 0.4) transparent;
        padding-right: 6px;
    }
    .pc-scroll-container::-webkit-scrollbar { width: 6px; }
    .pc-scroll-container::-webkit-scrollbar-track { background: transparent; }
    .pc-scroll-container::-webkit-scrollbar-thumb { background-color: rgba(16, 185, 129, 0.4); border-radius: 999px; }

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

@section('content')
<div class="max-w-[1080px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 lg:px-8 mt-6 space-y-6">

    {{-- HERO WIDGET --}}
    <section class="bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-600 rounded-[3rem] p-8 md:p-10 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(16,185,129,.35)] border border-white/20">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/15 blur-[80px] rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-white/10 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row gap-8 lg:items-center justify-between">
            <div class="flex-1 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 text-white/90 text-[10px] font-black uppercase tracking-widest mb-4">
                    <span class="bg-white/20 backdrop-blur-md border border-white/30 px-3 py-1 rounded-full shadow-sm flex items-center gap-1.5">
                        <i class="fa-solid fa-satellite-dish"></i>
                        Pusat Informasi Kader
                    </span>
                </div>

                <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-3">
                    Pusat Notifikasi
                </h1>

                <p class="text-teal-50 text-sm font-medium leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    Pantau informasi terbaru terkait jadwal Posyandu, validasi kesehatan, hingga laporan sistem. 
                    Terdapat <strong class="font-black text-white">{{ $unreadCount }}</strong> pesan baru yang memerlukan perhatian Anda.
                </p>
            </div>

            @if($unreadCount > 0)
                <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto shrink-0 justify-center">
                    <form action="{{ route('kader.notifikasi.markAllRead') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-pill bg-white hover:bg-emerald-50 text-emerald-600 px-6 py-3.5 text-sm font-black shadow-[0_8px_20px_rgba(255,255,255,0.3)] flex items-center justify-center gap-2 transition-all hover:-translate-y-0.5 w-full">
                            <i class="fa-solid fa-check-double"></i> Tandai Semua Dibaca
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </section>

    {{-- SYSTEM ALERT --}}
    @if(session('success'))
        <div class="rounded-[2rem] p-6 shadow-sm border-2 border-emerald-200 bg-emerald-50 flex items-center gap-4 mb-6">
            <div class="bg-white rounded-full w-12 h-12 flex items-center justify-center shrink-0 shadow-inner text-emerald-500 text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <h3 class="font-black text-lg text-emerald-800">Berhasil</h3>
                <p class="font-medium text-sm mt-1 text-emerald-700 opacity-90">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- TAB FILTER & TANGGAL --}}
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-2">
        <div class="flex bg-slate-200/50 p-1 rounded-full border border-slate-200/60 shadow-inner w-full sm:w-auto">
            <a href="{{ route('kader.notifikasi.index', ['filter' => 'semua']) }}" 
               class="flex-1 sm:flex-none text-center px-6 py-2.5 rounded-full text-xs font-black uppercase tracking-widest transition-all {{ $filter === 'semua' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                Semua Arsip
            </a>
            <a href="{{ route('kader.notifikasi.index', ['filter' => 'belum_dibaca']) }}" 
               class="flex-1 sm:flex-none text-center px-6 py-2.5 rounded-full text-xs font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 {{ $filter === 'belum_dibaca' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                Belum Dibaca 
                @if($unreadCount > 0)
                    <span class="bg-rose-500 text-white px-2 py-0.5 rounded-full text-[9px]">{{ $unreadCount }}</span>
                @endif
            </a>
        </div>
        <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">
            <i class="fa-solid fa-clock mr-1"></i> {{ now('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
        </div>
    </div>

    {{-- LIST NOTIFIKASI DENGAN SCROLL & BATAS TINGGI --}}
    <section class="widget-card overflow-hidden">
        
        <div class="pc-scroll-container max-h-[550px] p-2 space-y-2 bg-slate-50/30">
            @forelse($notifikasis as $notif)
                @php
                    $isUnread = ! (bool) $notif->is_read;
                    $style = $styleFor($notif);
                    $link = $notif->link && $notif->link !== '#' ? $notif->link : null;
                @endphp

                <article class="notif-item relative flex flex-col sm:flex-row gap-5 p-5 md:p-6 rounded-[1.5rem] {{ $isUnread ? 'notif-unread border border-emerald-100 shadow-sm ' . $style['border'] : 'notif-read border border-slate-100' }}">
                    
                    {{-- Icon Kiri --}}
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[14px] border text-lg shadow-inner {{ $isUnread ? $style['iconWrap'] : 'border-slate-200 bg-slate-100 text-slate-400' }}">
                        <i class="{{ $style['icon'] }}"></i>
                    </div>

                    {{-- Konten Tengah --}}
                    <div class="min-w-0 flex-1">
                        <div class="mb-2 flex flex-col gap-1.5 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="mb-1.5 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[9px] font-black uppercase tracking-widest {{ $isUnread ? $style['badge'] : 'bg-slate-100 text-slate-500 border-slate-200' }}">
                                        {{ $style['label'] }}
                                    </span>

                                    @if($isUnread)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[9px] font-black uppercase tracking-widest text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Baru
                                        </span>
                                    @endif
                                </div>

                                <h3 class="truncate text-base font-black tracking-tight {{ $isUnread ? 'text-slate-900' : 'text-slate-600' }}" title="{{ $notif->judul }}">
                                    {{ $notif->judul ?? 'Informasi Posyandu' }}
                                </h3>
                            </div>

                            <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-widest {{ $isUnread ? 'bg-emerald-50 border border-emerald-100 text-emerald-600' : 'bg-white border border-slate-200 text-slate-400' }}">
                                <i class="far fa-clock"></i>
                                {{ optional($notif->created_at)->diffForHumans() ?? '-' }}
                            </span>
                        </div>

                        <p class="mb-4 max-w-3xl text-[13px] leading-relaxed {{ $isUnread ? 'font-semibold text-slate-600' : 'font-medium text-slate-400' }}">
                            {{ $notif->pesan ?? 'Tidak ada isi pesan.' }}
                        </p>

                        {{-- Action Buttons --}}
                        <div class="flex flex-wrap items-center gap-2">
                            @if($isUnread)
                                <form action="{{ route('kader.notifikasi.read', $notif->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-pill inline-flex items-center gap-1.5 border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white px-4 py-2 text-[10px] font-black uppercase tracking-widest shadow-sm">
                                        <i class="fa-solid fa-check"></i> Tandai Dibaca
                                    </button>
                                </form>
                            @endif

                            @if($link)
                                <a href="{{ $link }}" class="btn-pill inline-flex items-center gap-1.5 border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:text-emerald-600 px-4 py-2 text-[10px] font-black uppercase tracking-widest shadow-sm">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat Detail
                                </a>
                            @endif

                            {{-- Tombol Hapus Menggunakan Modal Global JS (Mencegah Bug Form) --}}
                            <button type="button" 
                                    class="btn-pill inline-flex items-center gap-1.5 border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white px-4 py-2 text-[10px] font-black uppercase tracking-widest shadow-sm ml-auto sm:ml-0"
                                    data-confirm-submit
                                    data-action-url="{{ route('kader.notifikasi.destroy', $notif->id) }}"
                                    data-action-method="DELETE"
                                    data-confirm-title="Hapus Notifikasi?" 
                                    data-confirm-message="Pesan notifikasi ini akan dihapus permanen dari riwayat Anda." 
                                    data-confirm-tone="danger">
                                <i class="fa-solid fa-trash-can"></i> Hapus
                            </button>
                        </div>
                    </div>
                </article>
            @empty
                <div class="flex flex-col items-center justify-center p-16 text-center">
                    <div class="mb-5 flex h-20 w-20 items-center justify-center rounded-[1.5rem] border border-slate-200 bg-slate-50 text-3xl text-slate-300 shadow-inner">
                        <i class="fa-solid fa-envelope-open-text"></i>
                    </div>
                    <h3 class="text-xl font-black tracking-tight text-slate-800">Kotak Masuk Bersih</h3>
                    <p class="mt-2 max-w-sm text-sm font-semibold leading-relaxed text-slate-500">
                        Tidak ada notifikasi {{ $filter === 'belum_dibaca' ? 'yang belum dibaca' : 'untuk ditampilkan' }} saat ini.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- PAGINATION CUSTOM (AGENDA STYLE) --}}
        @if(method_exists($notifikasis, 'hasPages') && $notifikasis->hasPages())
            <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                    Halaman <span class="text-slate-900">{{ $notifikasis->currentPage() }}</span> dari <span class="text-slate-900">{{ $notifikasis->lastPage() }}</span>
                </p>
                
                <div class="flex items-center gap-2">
                    @if ($notifikasis->onFirstPage())
                        <button type="button" disabled class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50 cursor-not-allowed opacity-60"><i class="fas fa-chevron-left text-xs"></i></button>
                    @else
                        <a href="{{ $notifikasis->appends(['filter' => $filter])->previousPageUrl() }}" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-emerald-50 hover:text-emerald-600 transition-all"><i class="fas fa-chevron-left text-xs"></i></a>
                    @endif

                    @php
                        $start = max(1, $notifikasis->currentPage() - 2);
                        $end = min($notifikasis->lastPage(), $notifikasis->currentPage() + 2);
                    @endphp
                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $notifikasis->currentPage())
                            <span class="btn-pill w-10 h-10 flex items-center justify-center bg-emerald-500 text-white font-black text-sm shadow-md pointer-events-none">{{ $page }}</span>
                        @else
                            <a href="{{ $notifikasis->appends(['filter' => $filter])->url($page) }}" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 font-bold text-sm shadow-sm hover:bg-emerald-50 hover:text-emerald-600 transition-all">{{ $page }}</a>
                        @endif
                    @endfor

                    @if ($notifikasis->hasMorePages())
                        <a href="{{ $notifikasis->appends(['filter' => $filter])->nextPageUrl() }}" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-emerald-50 hover:text-emerald-600 transition-all"><i class="fas fa-chevron-right text-xs"></i></a>
                    @else
                        <button type="button" disabled class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50 cursor-not-allowed opacity-60"><i class="fas fa-chevron-right text-xs"></i></button>
                    @endif
                </div>
            </div>
        @endif
    </section>

    {{-- GLOBAL ACTION FORM (Dipakai otomatis oleh JS untuk action Hapus Notif) --}}
    <form id="globalActionForm" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="_method" id="globalActionMethod" value="POST">
    </form>
</div>
@endsection

@push('modals')
{{-- MODAL KONFIRMASI HAPUS (Z-Index Absolut Menutupi Layar Penuh) --}}
<div id="nexusConfirmModal" class="pc-modal-backdrop">
    <div class="pc-modal-card">
        <div id="nexusConfirmHeader" class="relative overflow-hidden px-6 py-6 text-white text-center bg-gradient-to-br from-rose-600 to-rose-800">
            <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-white/10 pointer-events-none"></div>
            <div class="absolute -bottom-16 left-8 h-28 w-44 rounded-t-[80px] bg-white/10 pointer-events-none"></div>

            <div class="relative z-10">
                <i id="nexusConfirmIcon" class="fa-solid fa-trash-can text-4xl mb-3 opacity-90"></i>
                <h3 id="nexusConfirmTitle" class="text-xl font-black tracking-tight mb-1">Hapus Notifikasi?</h3>
                <p id="nexusConfirmMessage" class="text-xs font-semibold leading-relaxed opacity-80 px-4">
                    Pesan notifikasi ini akan dihapus permanen.
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
                        
                        confirmOk.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';
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