@extends('layouts.kader')

@section('title', 'Detail Log Imunisasi')
@section('page-name', 'Detail Log Imunisasi')
@section('page-title', 'Detail Log Imunisasi')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    $kategoriTheme = $imunisasi->kategori_theme;
    $badgeTheme = $imunisasi->badge_theme;

    $nama = $imunisasi->nama_penerima;
    $nik = $imunisasi->nik_penerima;

    $detailMetrics = [
        [
            'label' => 'Tanggal Imunisasi',
            'value' => $imunisasi->tanggal_lengkap_label,
            'icon' => 'fa-calendar-check',
        ],
        [
            'label' => 'Waktu Catat',
            'value' => $imunisasi->jam_label,
            'icon' => 'fa-clock',
        ],
        [
            'label' => 'Dosis',
            'value' => $imunisasi->dosis_label,
            'icon' => 'fa-prescription-bottle-medical',
        ],
        [
            'label' => 'No. Batch',
            'value' => $imunisasi->batch_label,
            'icon' => 'fa-barcode',
        ],
        [
            'label' => 'Tanggal Kedaluwarsa',
            'value' => $imunisasi->expiry_label,
            'icon' => 'fa-hourglass-end',
        ],
        [
            'label' => 'Penyelenggara',
            'value' => $imunisasi->penyelenggara_label,
            'icon' => 'fa-hospital',
        ],
    ];
@endphp

@push('styles')
<style>
    html {
        scroll-behavior: auto !important;
    }

    html.pc-modal-open,
    body.pc-modal-open {
        overflow: hidden !important;
    }

    .pc-page {
        background:
            radial-gradient(circle at 8% 5%, rgba(16, 185, 129, .14), transparent 28%),
            radial-gradient(circle at 95% 8%, rgba(14, 165, 233, .13), transparent 26%),
            radial-gradient(circle at 50% 96%, rgba(251, 191, 36, .10), transparent 30%),
            linear-gradient(135deg, #f3fff9 0%, #eef9ff 48%, #f8fafc 100%);
    }

    .pc-grid {
        background-image:
            linear-gradient(rgba(15, 23, 42, .035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, .035) 1px, transparent 1px);
        background-size: 30px 30px;
    }

    .pc-glass {
        background: rgba(255, 255, 255, .86);
        backdrop-filter: blur(9px);
        -webkit-backdrop-filter: blur(9px);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .065);
    }

    .pc-soft-hover {
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .pc-soft-hover:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 36px rgba(15, 23, 42, .07);
    }

    .pc-balanced-grid {
        align-items: stretch !important;
    }

    .pc-left-panel {
        min-height: 100%;
        height: 100%;
    }

    .pc-side-panel {
        min-height: 100%;
        height: 100%;
        align-self: stretch;
    }

    .pc-side-fill {
        flex: 1 1 auto;
        min-height: 0;
    }

    .pc-note-card {
        min-height: 132px;
    }

    .pc-modal-backdrop {
        position: fixed !important;
        inset: 0 !important;
        z-index: 2147483647 !important;
        display: none;
        align-items: center;
        justify-content: center;
        width: 100vw !important;
        height: 100vh !important;
        height: 100dvh !important;
        margin: 0 !important;
        padding: 1rem;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }

    .pc-modal-backdrop.is-open {
        display: flex !important;
    }

    .pc-modal-card {
        width: min(100%, 470px);
        transform: translateY(12px) scale(.97);
        opacity: 0;
        border-radius: 1.75rem;
        border: 1px solid rgba(255, 255, 255, .78);
        background:
            radial-gradient(circle at 0% 0%, rgba(16, 185, 129, .14), transparent 34%),
            radial-gradient(circle at 100% 0%, rgba(14, 165, 233, .12), transparent 34%),
            rgba(255, 255, 255, .95);
        box-shadow: 0 30px 90px rgba(15, 23, 42, .25);
        transition: transform .18s ease, opacity .18s ease;
    }

    .pc-modal-backdrop.is-open .pc-modal-card {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    @media (max-width: 1279px) {
        .pc-balanced-grid {
            align-items: start !important;
        }

        .pc-left-panel,
        .pc-side-panel {
            min-height: auto;
            height: auto;
        }

        .pc-side-fill {
            flex: none;
        }
    }
</style>
@endpush

@section('content')
<div class="pc-page relative min-h-screen overflow-hidden px-4 py-5 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute inset-0 pc-grid opacity-70"></div>

    <div class="relative z-10 mx-auto max-w-[1400px] space-y-5">

        <section class="relative overflow-hidden rounded-[1.75rem] border border-white/80 bg-gradient-to-br from-emerald-50/95 via-cyan-50/90 to-white/95 pc-glass">
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-emerald-300/20 blur-3xl"></div>
            <div class="absolute -bottom-24 left-24 h-72 w-72 rounded-full bg-sky-300/16 blur-3xl"></div>

            <div class="relative grid gap-6 p-5 sm:p-6 xl:grid-cols-[minmax(0,1.28fr)_350px] xl:items-stretch">
                <div class="flex min-w-0 flex-col justify-between gap-6">
                    <div>
                        <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-white/75 px-3 py-1.5 text-xs font-black text-emerald-700 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Detail Log Imunisasi
                        </div>

                        <h1 class="mt-4 max-w-4xl text-2xl font-black leading-tight tracking-tight text-slate-950 sm:text-[1.85rem] lg:text-[2rem]">
                            Detail imunisasi yang dicatat oleh Bidan.
                        </h1>

                        <p class="mt-3 max-w-3xl text-sm font-medium leading-6 text-slate-600">
                            Informasi ini bersifat read-only untuk Kader. Data bersumber dari pencatatan Bidan supaya alur sistem tetap rapi dan tidak jadi ajang rebutan tombol.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('kader.imunisasi.index') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white/80 px-4 py-2.5 text-sm font-black text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-white">
                            <i class="fa-solid fa-arrow-left"></i>
                            Kembali
                        </a>

                        <a href="{{ route('kader.dashboard') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-white">
                            <i class="fa-solid fa-chart-line"></i>
                            Dashboard
                        </a>
                    </div>
                </div>

                <div class="rounded-[1.55rem] border p-5 {{ $badgeTheme['soft'] }}">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[.18em] text-slate-500">Status Arsip</p>
                            <h2 class="mt-2 text-xl font-black text-slate-950">Tercatat</h2>
                            <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                                Data imunisasi tersimpan sebagai log pemantauan Kader.
                            </p>
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $badgeTheme['solid'] }}">
                            <i class="fa-solid {{ $badgeTheme['icon'] }}"></i>
                        </div>
                    </div>

                    <div class="mt-5 rounded-[1.2rem] border border-white/80 bg-white/75 p-4">
                        <p class="text-xs font-black uppercase tracking-[.14em] text-slate-500">Jenis Program</p>
                        <p class="mt-1 text-sm font-black text-slate-950">{{ $badgeTheme['label'] }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="pc-balanced-grid grid gap-5 xl:grid-cols-[minmax(0,1.25fr)_minmax(330px,.55fr)]">
            <div class="pc-left-panel flex h-full flex-col gap-5">
                <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
                    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Identitas Penerima</p>
                            <h2 class="mt-1 text-xl font-black text-slate-950">{{ $nama }}</h2>
                            <p class="mt-1 text-sm font-bold text-slate-500">NIK {{ $nik }}</p>
                        </div>

                        <span class="inline-flex w-fit items-center gap-2 rounded-full border px-3 py-1 text-xs font-black {{ $kategoriTheme['badge'] }}">
                            <i class="fa-solid {{ $kategoriTheme['icon'] }}"></i>
                            {{ $kategoriTheme['label'] }}
                        </span>
                    </div>

                    <div class="rounded-[1.45rem] border {{ $badgeTheme['soft'] }} p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl {{ $badgeTheme['solid'] }}">
                                    <i class="fa-solid {{ $badgeTheme['icon'] }} text-xl"></i>
                                </div>

                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[.16em] text-slate-500">Vaksin</p>
                                    <h3 class="mt-1 text-2xl font-black text-slate-950">{{ $imunisasi->vaksin_label }}</h3>
                                    <p class="mt-1 text-sm font-bold text-slate-500">{{ $imunisasi->jenis_label }}</p>
                                </div>
                            </div>

                            <span class="inline-flex w-fit items-center justify-center rounded-2xl border border-white/80 bg-white/85 px-4 py-3 text-sm font-black text-slate-700">
                                {{ $imunisasi->dosis_label }}
                            </span>
                        </div>
                    </div>
                </section>

                <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
                    <div class="mb-5">
                        <p class="text-[11px] font-black uppercase tracking-[.18em] text-sky-700">Detail Pelaksanaan</p>
                        <h2 class="mt-1 text-xl font-black text-slate-950">Informasi imunisasi</h2>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($detailMetrics as $metric)
                            <div class="pc-soft-hover rounded-[1.25rem] border border-slate-200 bg-white/75 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-[11px] font-black uppercase tracking-[.14em] text-slate-400">{{ $metric['label'] }}</p>
                                        <p class="mt-2 text-sm font-black text-slate-950">{{ $metric['value'] }}</p>
                                    </div>

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                        <i class="fa-solid {{ $metric['icon'] }}"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
                    <div class="grid items-stretch gap-4 lg:grid-cols-2">
                        <div class="pc-note-card rounded-[1.25rem] border border-slate-200 bg-white/75 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[.18em] text-slate-500">Catatan</p>
                            <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                                {{ $imunisasi->catatan_label }}
                            </p>
                        </div>

                        <div class="pc-note-card rounded-[1.25rem] border border-slate-200 bg-white/75 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[.18em] text-slate-500">Keterangan Sistem</p>
                            <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                                Data ini hanya dapat diubah melalui akun Bidan. Kader melihat log untuk pemantauan kegiatan.
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="pc-side-panel flex h-full flex-col gap-5">
                <section class="pc-glass pc-side-fill rounded-[1.75rem] border border-white/80 p-5">
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Otoritas Medis</p>
                    <h2 class="mt-1 text-xl font-black text-slate-950">{{ $imunisasi->nama_petugas }}</h2>
                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                        Petugas yang mencatat atau bertanggung jawab pada data imunisasi ini.
                    </p>

                    <div class="mt-5 space-y-3">
                        <div class="rounded-[1.2rem] border border-white/80 bg-white/75 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-black text-slate-600">Petugas</span>
                                <span class="line-clamp-1 text-right text-sm font-black text-slate-950">{{ $imunisasi->nama_petugas }}</span>
                            </div>
                        </div>

                        <div class="rounded-[1.2rem] border border-white/80 bg-white/75 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-black text-slate-600">Tanggal</span>
                                <span class="text-right text-sm font-black text-slate-950">{{ $imunisasi->tanggal_label }}</span>
                            </div>
                        </div>

                        <div class="rounded-[1.2rem] border border-white/80 bg-white/75 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-black text-slate-600">Sumber Data</span>
                                <span class="text-right text-sm font-black text-slate-950">Bidan</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-slate-500">Aksi Data</p>

                    <div class="mt-4 space-y-3">
                        <div class="rounded-[1.25rem] border border-emerald-200 bg-emerald-50 p-4">
                            <div class="flex gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-emerald-600">
                                    <i class="fa-solid fa-lock"></i>
                                </div>

                                <div>
                                    <p class="text-sm font-black text-emerald-800">Mode baca saja</p>
                                    <p class="mt-1 text-xs font-semibold leading-5 text-slate-600">
                                        Kader tidak dapat mengubah log imunisasi. Jadi tidak ada tombol edit yang tiba-tiba bikin sidangmu jadi debat kusir.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('kader.imunisasi.index') }}"
                           class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50">
                            <i class="fa-solid fa-list"></i>
                            Kembali ke Daftar
                        </a>
                    </div>
                </section>
            </aside>
        </section>
    </div>

    @if(session('success') || session('error'))
        <div id="pcInfoModal" class="pc-modal-backdrop" aria-hidden="true">
            <div class="pc-modal-card p-6">
                <div class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.25rem] {{ session('error') ? 'bg-gradient-to-br from-rose-500 to-orange-500 shadow-rose-500/20' : 'bg-gradient-to-br from-emerald-500 to-teal-500 shadow-emerald-500/20' }} text-white shadow-lg">
                        <i class="fa-solid {{ session('error') ? 'fa-triangle-exclamation' : 'fa-circle-check' }} text-xl"></i>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-black uppercase tracking-[.18em] {{ session('error') ? 'text-rose-700' : 'text-emerald-700' }}">
                            Informasi
                        </p>
                        <h3 class="mt-1 text-lg font-black text-slate-950">
                            {{ session('error') ? 'Ada kendala' : 'Berhasil' }}
                        </h3>
                        <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                            {{ session('error') ?: session('success') }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button"
                            id="pcCloseInfo"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-950 px-4 py-3 text-sm font-black text-white shadow-lg shadow-slate-900/10">
                        <i class="fa-solid fa-check"></i>
                        Mengerti
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    let modal = document.querySelector('#pcInfoModal');

    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    const closeBtn = document.querySelector('#pcCloseInfo');

    function lockBody() {
        document.documentElement.classList.add('pc-modal-open');
        document.body.classList.add('pc-modal-open');
    }

    function unlockBody() {
        document.documentElement.classList.remove('pc-modal-open');
        document.body.classList.remove('pc-modal-open');
    }

    function openInfo() {
        if (!modal) return;

        lockBody();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeInfo() {
        if (!modal) return;

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        unlockBody();
    }

    if (modal) {
        requestAnimationFrame(openInfo);

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeInfo();
            }
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeInfo);
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeInfo();
        }
    });
})();
</script>
@endpush