@extends('layouts.kader')

@section('title', 'Pusat Sinyal & Notifikasi')
@section('page-name', 'Pusat Notifikasi')
@section('page-title', 'Pusat Notifikasi')
@section('page_title', 'Pusat Notifikasi')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<style>
    .fade-in-up {
        animation: fadeInUp .55s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(18px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .nexus-orb {
        position: absolute;
        inset: auto 0 0 auto;
        width: 460px;
        height: 460px;
        background: radial-gradient(circle, rgba(16,185,129,.12), rgba(99,102,241,.08), transparent 68%);
        filter: blur(26px);
        pointer-events: none;
        z-index: 0;
    }

    .nexus-card {
        background: rgba(255, 255, 255, .82);
        backdrop-filter: blur(18px);
        border: 1px solid rgba(255, 255, 255, .78);
        border-radius: 34px;
        box-shadow: 0 24px 70px -34px rgba(15, 23, 42, .35);
    }

    .notif-item {
        transition: all .28s cubic-bezier(.4, 0, .2, 1);
        border-left: 5px solid transparent;
    }

    .notif-item:hover {
        background: rgba(248, 250, 252, .92);
        transform: translateX(7px);
        box-shadow: -18px 0 36px -18px rgba(15, 23, 42, .14);
    }

    .notif-unread {
        background: linear-gradient(90deg, rgba(79,70,229,.055), rgba(255,255,255,.92));
    }

    .nexus-tabs {
        background: rgba(241, 245, 249, .9);
        padding: 5px;
        border-radius: 9999px;
        display: inline-flex;
        gap: 4px;
        border: 1px solid rgba(226, 232, 240, .9);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.85);
    }

    .tab-item {
        padding: 9px 24px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .1em;
        transition: all .25s ease;
        white-space: nowrap;
    }

    .tab-item.active {
        background: #ffffff;
        color: #4f46e5;
        box-shadow: 0 8px 18px rgba(15,23,42,.07);
    }

    .tab-item.inactive {
        color: #64748b;
    }

    .tab-item.inactive:hover {
        background: rgba(255,255,255,.65);
        color: #334155;
    }

    .btn-nexus-sm {
        padding: 7px 16px;
        border-radius: 14px;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .07em;
        transition: all .22s ease;
    }

    .notif-actions {
        opacity: 0;
        transform: translateY(8px);
        transition: all .25s ease;
    }

    .notif-item:hover .notif-actions {
        opacity: 1;
        transform: translateY(0);
    }

    div:where(.swal2-container) {
        backdrop-filter: blur(9px) !important;
        background: rgba(15, 23, 42, .48) !important;
        z-index: 99999 !important;
    }

    div:where(.swal2-popup) {
        border-radius: 34px !important;
        padding: 2.4rem 2rem !important;
        background: rgba(255,255,255,.98) !important;
        border: 1px solid rgba(255,255,255,.8) !important;
        box-shadow: 0 28px 70px -18px rgba(15,23,42,.3) !important;
    }

    @media (max-width: 640px) {
        .notif-actions {
            opacity: 1;
            transform: translateY(0);
        }

        .notif-item:hover {
            transform: none;
        }

        .tab-item {
            padding-inline: 16px;
        }
    }
</style>
@endpush

@section('content')
@php
    use Illuminate\Support\Str;

    $filter = $filter ?? request('filter', 'semua');
    $unreadCount = (int) ($unreadCount ?? 0);

    $totalNotifikasi = method_exists($notifikasis ?? null, 'total')
        ? $notifikasis->total()
        : (is_countable($notifikasis ?? []) ? count($notifikasis) : 0);

    $styleFor = function ($notif) {
        $text = strtolower(($notif->tipe ?? '') . ' ' . ($notif->judul ?? '') . ' ' . ($notif->pesan ?? ''));

        if (Str::contains($text, ['jadwal', 'agenda'])) {
            return [
                'icon' => 'fas fa-calendar-check',
                'iconWrap' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                'border' => 'border-l-emerald-500',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                'label' => 'Jadwal',
            ];
        }

        if (Str::contains($text, ['pemeriksaan', 'validasi', 'rekam medis', 'kesehatan'])) {
            return [
                'icon' => 'fas fa-stethoscope',
                'iconWrap' => 'bg-sky-50 text-sky-600 border-sky-100',
                'border' => 'border-l-sky-500',
                'badge' => 'bg-sky-50 text-sky-700 border-sky-100',
                'label' => 'Kesehatan',
            ];
        }

        if (Str::contains($text, ['imunisasi', 'vaksin'])) {
            return [
                'icon' => 'fas fa-syringe',
                'iconWrap' => 'bg-violet-50 text-violet-600 border-violet-100',
                'border' => 'border-l-violet-500',
                'badge' => 'bg-violet-50 text-violet-700 border-violet-100',
                'label' => 'Imunisasi',
            ];
        }

        if (Str::contains($text, ['laporan', 'rekap'])) {
            return [
                'icon' => 'fas fa-file-medical',
                'iconWrap' => 'bg-amber-50 text-amber-600 border-amber-100',
                'border' => 'border-l-amber-500',
                'badge' => 'bg-amber-50 text-amber-700 border-amber-100',
                'label' => 'Laporan',
            ];
        }

        return [
            'icon' => $notif->tipe_icon ?? 'fas fa-bell',
            'iconWrap' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
            'border' => 'border-l-indigo-500',
            'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
            'label' => 'Informasi',
        ];
    };
@endphp

<div class="relative z-10 mx-auto max-w-[1040px] pb-16 fade-in-up">
    <div class="nexus-orb"></div>

    <section class="relative z-10 mb-7 overflow-hidden rounded-[36px] border border-white/80 bg-gradient-to-br from-white/90 via-emerald-50/45 to-indigo-50/40 p-7 shadow-[0_24px_70px_-38px_rgba(15,23,42,.38)] backdrop-blur-xl md:p-9">
        <div class="flex flex-col gap-7 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-5">
                <div class="relative flex h-16 w-16 shrink-0 -rotate-3 items-center justify-center rounded-[23px] bg-gradient-to-br from-indigo-600 to-emerald-500 text-3xl text-white shadow-[0_16px_30px_rgba(79,70,229,.25)]">
                    <i class="fas fa-satellite-dish"></i>

                    @if($unreadCount > 0)
                        <span class="absolute -right-1 -top-1 h-5 w-5 rounded-full border-4 border-white bg-rose-500 shadow-sm animate-pulse"></span>
                    @endif
                </div>

                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white/65 px-3 py-1 text-[10px] font-black tracking-[0.14em] text-emerald-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Pusat Informasi Kader
                    </div>

                    <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900 md:text-4xl">
                        Pusat Sinyal
                    </h1>

                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-500">
                        Terdeteksi <strong class="font-black text-indigo-600">{{ $unreadCount }}</strong> pesan baru yang memerlukan perhatian.
                    </p>
                </div>
            </div>

            @if($unreadCount > 0)
                <form action="{{ route('kader.notifikasi.markAllRead') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-slate-900 px-7 text-[11px] font-black tracking-[0.12em] text-white shadow-[0_16px_32px_rgba(15,23,42,.18)] transition hover:-translate-y-1 hover:bg-indigo-600 hover:shadow-indigo-200">
                        <i class="fas fa-check-double text-indigo-300"></i>
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>
    </section>

    <div class="relative z-10 mb-5 flex flex-col gap-3 px-1 sm:flex-row sm:items-center sm:justify-between">
        <div class="nexus-tabs">
            <a href="{{ route('kader.notifikasi.index', ['filter' => 'semua']) }}" class="tab-item {{ $filter === 'semua' ? 'active' : 'inactive' }}">
                Semua Arsip
            </a>

            <a href="{{ route('kader.notifikasi.index', ['filter' => 'belum_dibaca']) }}" class="tab-item {{ $filter === 'belum_dibaca' ? 'active' : 'inactive' }}">
                Belum Dibaca
                <span class="ml-1 opacity-60">({{ $unreadCount }})</span>
            </a>
        </div>

        <div class="text-[10px] font-black tracking-[0.14em] text-slate-400">
            Update terakhir: {{ now('Asia/Jakarta')->format('H:i') }} WIB
        </div>
    </div>

    <section class="nexus-card relative z-10 overflow-hidden">
        <div class="divide-y divide-slate-100/90">
            @forelse($notifikasis as $notif)
                @php
                    $isUnread = ! (bool) $notif->is_read;
                    $style = $styleFor($notif);
                    $link = $notif->link && $notif->link !== '#' ? $notif->link : null;
                @endphp

                <article class="notif-item group flex gap-5 p-6 md:p-8 {{ $isUnread ? 'notif-unread ' . $style['border'] : 'bg-white/70' }}">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border text-xl transition duration-300 group-hover:scale-110 {{ $isUnread ? $style['iconWrap'] : 'border-slate-100 bg-slate-50 text-slate-400' }}">
                        <i class="{{ $style['icon'] }}"></i>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="mb-2 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-[10px] font-black tracking-[0.1em] {{ $style['badge'] }}">
                                        {{ $style['label'] }}
                                    </span>

                                    @if($isUnread)
                                        <span class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-[10px] font-black tracking-[0.1em] text-indigo-600">
                                            <span class="h-2 w-2 rounded-full bg-indigo-500 animate-pulse"></span>
                                            Baru
                                        </span>
                                    @endif
                                </div>

                                <h3 class="truncate text-lg font-black tracking-tight {{ $isUnread ? 'text-slate-950' : 'text-slate-500' }}">
                                    {{ $notif->judul ?? 'Informasi Posyandu' }}
                                </h3>
                            </div>

                            <span class="inline-flex w-max items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-1 text-[10px] font-black {{ $isUnread ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-400' }}">
                                <i class="far fa-clock"></i>
                                {{ optional($notif->created_at)->diffForHumans() ?? '-' }}
                            </span>
                        </div>

                        <p class="mb-5 max-w-3xl text-[14px] leading-relaxed {{ $isUnread ? 'font-medium text-slate-700' : 'font-medium text-slate-400' }}">
                            {{ $notif->pesan ?? 'Tidak ada isi pesan.' }}
                        </p>

                        <div class="notif-actions flex flex-wrap items-center gap-2">
                            @if($isUnread)
                                <form action="{{ route('kader.notifikasi.read', $notif->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-nexus-sm border border-indigo-100 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white">
                                        Tandai Dibaca
                                    </button>
                                </form>
                            @endif

                            @if($link)
                                <a href="{{ $link }}" class="btn-nexus-sm border border-emerald-100 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white">
                                    Lihat Detail
                                </a>
                            @endif

                            <button type="button" onclick="confirmDelete('{{ $notif->id }}')" class="btn-nexus-sm border border-rose-100 bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white">
                                <i class="fas fa-trash-alt mr-1.5"></i>
                                Hapus
                            </button>

                            <form id="delete-form-{{ $notif->id }}" action="{{ route('kader.notifikasi.destroy', $notif->id) }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="flex flex-col items-center justify-center px-6 py-24 text-center">
                    <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full border border-slate-100 bg-slate-50 text-4xl text-slate-300 shadow-inner">
                        <i class="fas fa-envelope-open"></i>
                    </div>

                    <h3 class="mb-1 text-2xl font-black tracking-tight text-slate-800">
                        Kotak Masuk Bersih
                    </h3>

                    <p class="mx-auto max-w-xs text-sm font-medium leading-relaxed text-slate-400">
                        Tidak ada notifikasi {{ $filter === 'belum_dibaca' ? 'yang belum dibaca' : 'untuk ditampilkan' }} saat ini.
                    </p>
                </div>
            @endforelse
        </div>

        @if(method_exists($notifikasis, 'hasPages') && $notifikasis->hasPages())
            <div class="border-t border-slate-100 bg-slate-50/55 p-7">
                {{ $notifikasis->withQueryString()->links() }}
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            iconColor: '#6366f1',
            title: '<span class="font-black text-xl text-slate-800 tracking-tight">Berhasil</span>',
            html: '<p class="text-sm text-slate-500 font-medium">{{ session("success") }}</p>',
            showConfirmButton: false,
            timer: 1900
        });
    @endif

    function confirmDelete(id) {
        Swal.fire({
            title: '<span class="font-black text-2xl text-slate-800 tracking-tight">Hapus Arsip?</span>',
            html: '<p class="mt-2 text-[13px] font-medium leading-relaxed text-slate-500">Notifikasi ini akan dihapus permanen dari riwayat sinyal sistem.</p>',
            icon: 'warning',
            iconColor: '#f43f5e',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batalkan',
            reverseButtons: true,
            buttonsStyling: false,
            customClass: {
                confirmButton: 'bg-rose-500 hover:bg-rose-600 text-white font-black text-[11px] tracking-widest px-8 py-3.5 rounded-full transition-all shadow-lg mx-2',
                cancelButton: 'bg-slate-100 hover:bg-slate-200 text-slate-600 font-black text-[11px] tracking-widest px-8 py-3.5 rounded-full transition-all mx-2'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('delete-form-' + id);
                if (form) form.submit();
            }
        });
    }
</script>
@endpush