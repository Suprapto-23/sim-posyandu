@extends('layouts.bidan')

@section('title', 'Pusat Notifikasi')
@section('page-name', 'Notifikasi')
@section('page-title', 'Pusat Notifikasi Bidan')

@php
    $unreadCount = $unreadCount ?? 0;
    $activeFilter = $filter ?? request('filter', 'semua');
    
    $filters = [
        'semua' => ['label' => 'Semua Notifikasi', 'icon' => 'fa-inbox'],
        'belum_dibaca' => ['label' => 'Belum Dibaca', 'icon' => 'fa-envelope'],
        'sudah' => ['label' => 'Telah Dibaca', 'icon' => 'fa-envelope-open'],
    ];

    $getTheme = function ($tipe) {
        return match (strtolower((string)$tipe)) {
            'jadwal' => ['icon' => 'fa-calendar-check', 'bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'border' => 'border-indigo-100', 'label' => 'Jadwal Layanan'],
            'imunisasi' => ['icon' => 'fa-syringe', 'bg' => 'bg-cyan-50', 'text' => 'text-cyan-600', 'border' => 'border-cyan-100', 'label' => 'Info Imunisasi'],
            'pemeriksaan' => ['icon' => 'fa-stethoscope', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-100', 'label' => 'Pemeriksaan Klinis'],
            'import' => ['icon' => 'fa-file-excel', 'bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-100', 'label' => 'Import Data'],
            'alert' => ['icon' => 'fa-triangle-exclamation', 'bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-rose-100', 'label' => 'Peringatan Sistem'],
            default => ['icon' => 'fa-bell', 'bg' => 'bg-slate-50', 'text' => 'text-slate-600', 'border' => 'border-slate-100', 'label' => 'Informasi Umum'],
        };
    };
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
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .btn-pill { border-radius: 9999px; transition: all 0.2s ease; cursor: pointer; }
    .btn-pill:active { transform: scale(0.97); }

    .animate-pop-in { animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popIn { from { opacity: 0; transform: scale(.98) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
</style>
@endpush

@section('content')
<div class="bg-mesh-fixed"></div>

<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1000px] mx-auto space-y-6 animate-pop-in">

    {{-- HERO BANNER --}}
    <section class="relative overflow-hidden rounded-[3rem] bg-gradient-to-r from-emerald-500 via-teal-500 to-green-500 p-8 sm:p-10 shadow-2xl shadow-emerald-500/20 flex flex-col md:flex-row justify-between items-center gap-8 border-[6px] border-white/40">
        <div class="absolute inset-0 bg-white/10 backdrop-blur-[2px]"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-full flex flex-col gap-4 text-center md:text-left">
            <div class="inline-flex justify-center md:justify-start items-center gap-2 mb-2">
                <span class="btn-pill bg-white/20 border border-white/30 text-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest backdrop-blur-md shadow-inner flex items-center gap-2">
                    <i class="fa-solid fa-satellite-dish"></i> Pusat Informasi Bidan
                </span>
            </div>
            
            <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight leading-tight">
                Notifikasi Sistem
            </h1>

            <p class="text-emerald-50 text-sm font-medium leading-relaxed max-w-xl mx-auto md:mx-0">
                Pantau seluruh informasi jadwal, hasil pemeriksaan, alert imunisasi, dan rekam aktivitas Kader Posyandu yang terhubung dengan akun Anda.
            </p>
        </div>

        <div class="relative z-10 w-full md:w-auto flex justify-center md:justify-end shrink-0">
            <div class="bg-white/20 border border-white/30 backdrop-blur-md rounded-[2rem] px-8 py-6 text-center flex flex-col items-center">
                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-50 mb-1 block">Pesan Belum Dibaca</span>
                <span class="text-5xl font-black text-white block leading-none mb-4">{{ $unreadCount }}</span>
                
                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('bidan.notifikasi.markall') }}" class="w-full">
                        @csrf
                        <button type="submit" class="btn-pill w-full bg-white text-emerald-600 hover:bg-emerald-50 px-4 py-2.5 text-[10px] font-black uppercase tracking-widest shadow-md transition-all flex justify-center items-center gap-2">
                            <i class="fa-solid fa-check-double"></i> Tandai Dibaca
                        </button>
                    </form>
                @else
                    <span class="btn-pill w-full bg-emerald-600/50 text-emerald-100 border border-emerald-400/30 px-4 py-2.5 text-[10px] font-black uppercase tracking-widest shadow-inner flex justify-center items-center gap-2 cursor-not-allowed">
                        <i class="fa-solid fa-check-double"></i> Kotak Masuk Bersih
                    </span>
                @endif
            </div>
        </div>
    </section>

    {{-- ALERTS --}}
    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 rounded-3xl p-5 flex items-center gap-4 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-white text-emerald-500 flex items-center justify-center text-lg shrink-0 border border-emerald-100"><i class="fa-solid fa-check-circle"></i></div>
            <p class="text-sm font-bold text-emerald-800">{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-rose-50 border border-rose-200 rounded-3xl p-5 flex items-center gap-4 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-white text-rose-500 flex items-center justify-center text-lg shrink-0 border border-rose-100"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <p class="text-sm font-bold text-rose-800">{{ session('error') }}</p>
        </div>
    @endif

    {{-- FILTER TABS --}}
    <div class="flex flex-wrap items-center gap-3">
        @foreach ($filters as $key => $filterData)
            @php $isActive = $activeFilter === $key; @endphp
            <a href="{{ route('bidan.notifikasi.index', ['filter' => $key]) }}" 
               class="btn-pill px-5 py-3 text-[11px] font-black uppercase tracking-widest border transition-all flex items-center gap-2 shadow-sm
               {{ $isActive ? 'bg-emerald-600 text-white border-emerald-500' : 'bg-white text-slate-500 border-slate-200 hover:bg-emerald-50 hover:text-emerald-600' }}">
                <i class="fa-solid {{ $filterData['icon'] }} text-sm"></i> {{ $filterData['label'] }}
            </a>
        @endforeach
    </div>

    {{-- LIST NOTIFIKASI --}}
    <section class="widget-card overflow-hidden border-t-4 border-t-emerald-500">
        <div class="bg-slate-50/70 px-6 py-5 border-b border-slate-100 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 text-emerald-500 flex items-center justify-center text-lg shadow-sm"><i class="fa-solid fa-envelope-open-text"></i></div>
                <div>
                    <h2 class="text-base font-black text-slate-800 uppercase tracking-tight">Kotak Masuk</h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Daftar Notifikasi Bidan</p>
                </div>
            </div>
        </div>

        <div class="divide-y divide-slate-100 bg-white">
            @forelse ($notifikasis as $notif)
                @php
                    $isUnread = !($notif->is_read ?? false);
                    $theme = $getTheme($notif->tipe ?? 'info');
                    $waktu = optional($notif->created_at)->diffForHumans() ?? '-';
                @endphp

                <div class="p-5 md:p-6 transition-colors hover:bg-slate-50/50 {{ $isUnread ? 'bg-emerald-50/30' : '' }}">
                    <div class="flex gap-4">
                        {{-- Icon Ikon Notif --}}
                        <div class="w-12 h-12 rounded-2xl border {{ $theme['bg'] }} {{ $theme['text'] }} {{ $theme['border'] }} flex items-center justify-center text-xl shrink-0 shadow-sm">
                            <i class="fa-solid {{ $theme['icon'] }}"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-base font-black text-slate-800 leading-tight">
                                        {{ $notif->judul ?? 'Pemberitahuan Sistem' }}
                                    </h3>
                                    @if ($isUnread)
                                        <span class="inline-flex rounded-md bg-emerald-500 text-white px-2 py-0.5 text-[9px] font-black uppercase tracking-widest shadow-sm">Baru</span>
                                    @endif
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200 text-slate-500 text-[10px] font-bold shrink-0">
                                    <i class="fa-solid fa-clock"></i> {{ $waktu }}
                                </span>
                            </div>

                            <p class="text-[10px] font-black uppercase tracking-widest {{ $theme['text'] }} mb-2">
                                {{ $theme['label'] }}
                            </p>

                            <p class="text-sm font-semibold text-slate-600 leading-relaxed bg-white border border-slate-100 p-4 rounded-2xl shadow-sm">
                                {{ $notif->pesan ?? 'Tidak ada detail pesan.' }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-14 text-center bg-white flex flex-col items-center justify-center">
                    <div class="w-20 h-20 rounded-[1.5rem] bg-slate-50 border border-slate-100 text-slate-300 flex items-center justify-center text-4xl mb-4 shadow-inner">
                        <i class="fa-solid fa-bell-slash"></i>
                    </div>
                    <h3 class="text-base font-black text-slate-700">Tidak Ada Notifikasi</h3>
                    <p class="text-sm font-medium text-slate-500 mt-2 max-w-md mx-auto">
                        @if($activeFilter === 'belum_dibaca')
                            Hebat! Anda telah membaca semua notifikasi yang masuk. Kotak masuk Anda bersih.
                        @else
                            Sistem sedang tenang. Belum ada aktivitas atau informasi baru yang perlu Anda pantau saat ini.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        @if(method_exists($notifikasis, 'hasPages') && $notifikasis->hasPages())
            <div class="bg-white p-5 border-t border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shrink-0">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                    Menampilkan <span class="text-slate-700">{{ $notifikasis->firstItem() }}</span> - <span class="text-slate-700">{{ $notifikasis->lastItem() }}</span> dari <span class="text-slate-800">{{ $notifikasis->total() }}</span> pemberitahuan
                </p>
                
                <div class="flex items-center gap-1.5">
                    {{-- Previous --}}
                    @if ($notifikasis->onFirstPage())
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-400 cursor-not-allowed border border-slate-100"><i class="fa-solid fa-chevron-left text-[10px]"></i></span>
                    @else
                        <a href="{{ $notifikasis->previousPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm"><i class="fa-solid fa-chevron-left text-[10px]"></i></a>
                    @endif

                    {{-- Page Numbers --}}
                    @php
                        $start = max(1, $notifikasis->currentPage() - 1);
                        $end = min($notifikasis->lastPage(), $notifikasis->currentPage() + 1);
                    @endphp

                    @if($start > 1)
                        <a href="{{ $notifikasis->url(1) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm text-xs font-bold">1</a>
                        @if($start > 2)<span class="inline-flex h-8 w-8 items-center justify-center text-slate-400 text-xs font-bold">...</span>@endif
                    @endif

                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $notifikasis->currentPage())
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 text-white shadow-md shadow-emerald-500/30 text-xs font-black">{{ $page }}</span>
                        @else
                            <a href="{{ $notifikasis->url($page) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm text-xs font-bold">{{ $page }}</a>
                        @endif
                    @endfor

                    @if($end < $notifikasis->lastPage())
                        @if($end < $notifikasis->lastPage() - 1)<span class="inline-flex h-8 w-8 items-center justify-center text-slate-400 text-xs font-bold">...</span>@endif
                        <a href="{{ $notifikasis->url($notifikasis->lastPage()) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm text-xs font-bold">{{ $notifikasis->lastPage() }}</a>
                    @endif

                    {{-- Next --}}
                    @if ($notifikasis->hasMorePages())
                        <a href="{{ $notifikasis->nextPageUrl() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm"><i class="fa-solid fa-chevron-right text-[10px]"></i></a>
                    @else
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-50 text-slate-400 cursor-not-allowed border border-slate-100"><i class="fa-solid fa-chevron-right text-[10px]"></i></span>
                    @endif
                </div>
            </div>
        @endif
    </section>

</div>
@endsection