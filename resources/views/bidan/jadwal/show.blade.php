@extends('layouts.bidan')

@section('title', 'Detail Jadwal Posyandu')
@section('page-name', 'Detail Jadwal')
@section('page-title', 'Detail Jadwal Posyandu')

@php
    use Carbon\Carbon;

    Carbon::setLocale('id');

    $kategoriOptions = $kategoriOptions ?? [
        'posyandu' => [
            'label' => 'Posyandu Rutin',
            'desc' => 'Agenda pelayanan Posyandu umum, absensi, dan pengukuran dasar.',
            'icon' => 'ph ph-house-line',
        ],
        'imunisasi' => [
            'label' => 'Imunisasi Balita',
            'desc' => 'Agenda pelayanan imunisasi untuk sasaran Balita.',
            'icon' => 'ph ph-syringe',
        ],
        'pemeriksaan' => [
            'label' => 'Pemeriksaan Klinis',
            'desc' => 'Agenda pemeriksaan lanjutan oleh Bidan.',
            'icon' => 'ph ph-stethoscope',
        ],
        'lainnya' => [
            'label' => 'Kegiatan Lainnya',
            'desc' => 'Agenda tambahan Posyandu di luar layanan utama.',
            'icon' => 'ph ph-calendar-plus',
        ],
    ];

    $targetOptions = $targetOptions ?? [
        'semua' => [
            'label' => 'Semua Sasaran',
            'desc' => 'Balita, Remaja, Lansia, dan warga yang terdaftar.',
            'icon' => 'ph ph-users-three',
        ],
        'balita' => [
            'label' => 'Balita',
            'desc' => 'Sasaran Balita.',
            'icon' => 'ph ph-baby',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'desc' => 'Sasaran Remaja.',
            'icon' => 'ph ph-user-focus',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'desc' => 'Sasaran Lansia.',
            'icon' => 'ph ph-heartbeat',
        ],
    ];

    $statusOptions = $statusOptions ?? [
        'aktif' => [
            'label' => 'Aktif',
            'desc' => 'Jadwal masih berlaku.',
            'icon' => 'ph ph-check-circle',
        ],
        'selesai' => [
            'label' => 'Selesai',
            'desc' => 'Jadwal sudah dilaksanakan.',
            'icon' => 'ph ph-flag-checkered',
        ],
        'dibatalkan' => [
            'label' => 'Dibatalkan',
            'desc' => 'Jadwal dibatalkan atau ditunda.',
            'icon' => 'ph ph-x-circle',
        ],
    ];

    $judul = $jadwal->judul ?? 'Judul Jadwal Tidak Terdata';
    $kategori = strtolower((string) ($jadwal->kategori ?? 'posyandu'));
    $target = strtolower((string) ($jadwal->target_peserta ?? 'semua'));
    $status = strtolower((string) ($jadwal->status ?? 'aktif'));
    $lokasi = $jadwal->lokasi ?? '-';
    $deskripsi = trim((string) ($jadwal->deskripsi ?? ''));

    $kategoriMeta = $kategoriOptions[$kategori] ?? $kategoriOptions['posyandu'];
    $targetMeta = $targetOptions[$target] ?? $targetOptions['semua'];
    $statusMeta = $statusOptions[$status] ?? $statusOptions['aktif'];

    $formatTanggal = function ($date, bool $withDay = false) {
        if (! $date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat($withDay ? 'l, d F Y' : 'd F Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatTanggalPendek = function ($date) {
        if (! $date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d M Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatBulanPendek = function ($date) {
        if (! $date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('M');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatTanggalAngka = function ($date) {
        if (! $date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->format('d');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatWaktu = function ($mulai, $selesai) {
        try {
            $mulai = $mulai ? Carbon::parse($mulai)->format('H:i') : '-';
            $selesai = $selesai ? Carbon::parse($selesai)->format('H:i') : '-';

            return $mulai . ' - ' . $selesai . ' WIB';
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatMetaDate = function ($date) {
        if (! $date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d M Y, H:i');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $isToday = function ($date) {
        if (! $date) {
            return false;
        }

        try {
            return Carbon::parse($date)->isToday();
        } catch (\Throwable $e) {
            return false;
        }
    };

    $isPastDate = function ($date) {
        if (! $date) {
            return false;
        }

        try {
            return Carbon::parse($date)->startOfDay()->lt(now()->startOfDay());
        } catch (\Throwable $e) {
            return false;
        }
    };

    $canModifyFallback = function ($jadwal) {
        if (($jadwal->status ?? 'aktif') !== 'aktif') {
            return false;
        }

        if (empty($jadwal->tanggal)) {
            return false;
        }

        try {
            $tanggal = Carbon::parse($jadwal->tanggal)->format('Y-m-d');
            $waktuMulai = $jadwal->waktu_mulai
                ? Carbon::parse($jadwal->waktu_mulai)->format('H:i:s')
                : '00:00:00';

            $startDateTime = Carbon::parse($tanggal . ' ' . $waktuMulai);

            return now()->lt($startDateTime);
        } catch (\Throwable $e) {
            return false;
        }
    };

    $canEdit = isset($canEdit) ? (bool) $canEdit : $canModifyFallback($jadwal);
    $canDelete = isset($canDelete) ? (bool) $canDelete : $canEdit;

    $tanggalLabel = $formatTanggal($jadwal->tanggal ?? null, true);
    $tanggalPendek = $formatTanggalPendek($jadwal->tanggal ?? null);
    $bulanPendek = $formatBulanPendek($jadwal->tanggal ?? null);
    $tanggalAngka = $formatTanggalAngka($jadwal->tanggal ?? null);
    $waktuLabel = $formatWaktu($jadwal->waktu_mulai ?? null, $jadwal->waktu_selesai ?? null);

    $today = $isToday($jadwal->tanggal ?? null);
    $past = $isPastDate($jadwal->tanggal ?? null);

    $statusTheme = function ($value) {
        return match (strtolower((string) $value)) {
            'aktif' => [
                'label' => 'Aktif',
                'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'panel' => 'border-emerald-100 bg-emerald-50/75',
                'iconBox' => 'bg-white text-emerald-700 ring-emerald-100',
                'dot' => 'bg-emerald-500',
                'icon' => 'ph ph-check-circle',
            ],
            'selesai' => [
                'label' => 'Selesai',
                'badge' => 'bg-slate-50 text-slate-600 ring-slate-200',
                'panel' => 'border-slate-100 bg-slate-50/80',
                'iconBox' => 'bg-white text-slate-600 ring-slate-100',
                'dot' => 'bg-slate-400',
                'icon' => 'ph ph-flag-checkered',
            ],
            'dibatalkan' => [
                'label' => 'Dibatalkan',
                'badge' => 'bg-rose-50 text-rose-700 ring-rose-200',
                'panel' => 'border-rose-100 bg-rose-50/75',
                'iconBox' => 'bg-white text-rose-700 ring-rose-100',
                'dot' => 'bg-rose-500',
                'icon' => 'ph ph-x-circle',
            ],
            default => [
                'label' => ucfirst((string) $value),
                'badge' => 'bg-slate-50 text-slate-600 ring-slate-200',
                'panel' => 'border-slate-100 bg-slate-50/80',
                'iconBox' => 'bg-white text-slate-600 ring-slate-100',
                'dot' => 'bg-slate-400',
                'icon' => 'ph ph-info',
            ],
        };
    };

    $kategoriTheme = function ($value) {
        return match (strtolower((string) $value)) {
            'imunisasi' => [
                'badge' => 'bg-cyan-50 text-cyan-700 ring-cyan-200',
                'panel' => 'border-cyan-100 bg-cyan-50/75',
                'iconBox' => 'bg-white text-cyan-700 ring-cyan-100',
                'gradient' => 'from-cyan-500 to-sky-500',
                'icon' => 'ph ph-syringe',
            ],
            'pemeriksaan' => [
                'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'panel' => 'border-emerald-100 bg-emerald-50/75',
                'iconBox' => 'bg-white text-emerald-700 ring-emerald-100',
                'gradient' => 'from-emerald-500 to-teal-500',
                'icon' => 'ph ph-stethoscope',
            ],
            'lainnya' => [
                'badge' => 'bg-amber-50 text-amber-700 ring-amber-200',
                'panel' => 'border-amber-100 bg-amber-50/75',
                'iconBox' => 'bg-white text-amber-700 ring-amber-100',
                'gradient' => 'from-amber-500 to-orange-500',
                'icon' => 'ph ph-calendar-plus',
            ],
            default => [
                'badge' => 'bg-sky-50 text-sky-700 ring-sky-200',
                'panel' => 'border-sky-100 bg-sky-50/75',
                'iconBox' => 'bg-white text-sky-700 ring-sky-100',
                'gradient' => 'from-sky-500 to-cyan-500',
                'icon' => 'ph ph-house-line',
            ],
        };
    };

    $targetTheme = function ($value) {
        return match (strtolower((string) $value)) {
            'balita' => [
                'badge' => 'bg-sky-50 text-sky-700 ring-sky-200',
                'panel' => 'border-sky-100 bg-sky-50/75',
                'iconBox' => 'bg-white text-sky-700 ring-sky-100',
                'icon' => 'ph ph-baby',
            ],
            'remaja' => [
                'badge' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
                'panel' => 'border-indigo-100 bg-indigo-50/75',
                'iconBox' => 'bg-white text-indigo-700 ring-indigo-100',
                'icon' => 'ph ph-user-focus',
            ],
            'lansia' => [
                'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'panel' => 'border-emerald-100 bg-emerald-50/75',
                'iconBox' => 'bg-white text-emerald-700 ring-emerald-100',
                'icon' => 'ph ph-heartbeat',
            ],
            default => [
                'badge' => 'bg-slate-50 text-slate-700 ring-slate-200',
                'panel' => 'border-slate-100 bg-slate-50/80',
                'iconBox' => 'bg-white text-slate-700 ring-slate-100',
                'icon' => 'ph ph-users-three',
            ],
        };
    };

    $lockState = function () use ($canEdit, $status) {
        if ($canEdit) {
            return [
                'label' => 'Bisa Diedit',
                'desc' => 'Jadwal masih aktif dan belum melewati waktu mulai.',
                'icon' => 'ph ph-pencil-simple',
                'panel' => 'border-emerald-100 bg-emerald-50/75',
                'iconBox' => 'bg-white text-emerald-700 ring-emerald-100',
            ];
        }

        if ($status === 'dibatalkan') {
            return [
                'label' => 'Dibatalkan',
                'desc' => 'Jadwal dibatalkan dan tidak dapat diedit.',
                'icon' => 'ph ph-x-circle',
                'panel' => 'border-rose-100 bg-rose-50/75',
                'iconBox' => 'bg-white text-rose-700 ring-rose-100',
            ];
        }

        if ($status === 'selesai') {
            return [
                'label' => 'Terkunci',
                'desc' => 'Jadwal sudah selesai dan disimpan sebagai arsip.',
                'icon' => 'ph ph-lock-simple',
                'panel' => 'border-slate-100 bg-slate-50/80',
                'iconBox' => 'bg-white text-slate-600 ring-slate-100',
            ];
        }

        return [
            'label' => 'Terkunci',
            'desc' => 'Waktu mulai jadwal sudah terlewati.',
            'icon' => 'ph ph-lock-simple',
            'panel' => 'border-slate-100 bg-slate-50/80',
            'iconBox' => 'bg-white text-slate-600 ring-slate-100',
        ];
    };

    $statusData = $statusTheme($status);
    $kategoriData = $kategoriTheme($kategori);
    $targetData = $targetTheme($target);
    $lockData = $lockState();

    $summaryCards = [
        [
            'label' => 'Tanggal',
            'value' => $tanggalPendek,
            'icon' => 'ph ph-calendar-check',
            'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        ],
        [
            'label' => 'Waktu',
            'value' => $waktuLabel,
            'icon' => 'ph ph-clock',
            'class' => 'bg-cyan-50 text-cyan-700 ring-cyan-100',
        ],
        [
            'label' => 'Status',
            'value' => $statusData['label'],
            'icon' => $statusData['icon'],
            'class' => 'bg-slate-50 text-slate-700 ring-slate-100',
        ],
        [
            'label' => 'Akses',
            'value' => $lockData['label'],
            'icon' => $lockData['icon'],
            'class' => 'bg-amber-50 text-amber-700 ring-amber-100',
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
            radial-gradient(circle at 8% 6%, rgba(16, 185, 129, .14), transparent 28%),
            radial-gradient(circle at 92% 8%, rgba(14, 165, 233, .12), transparent 26%),
            radial-gradient(circle at 50% 100%, rgba(245, 158, 11, .09), transparent 30%),
            linear-gradient(135deg, #f4fff9 0%, #eef9ff 46%, #f8fafc 100%);
    }

    .pc-grid-bg {
        background-image:
            linear-gradient(rgba(15, 23, 42, .035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(15, 23, 42, .035) 1px, transparent 1px);
        background-size: 30px 30px;
    }

    .pc-glass {
        background: rgba(255, 255, 255, .84);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .065);
    }

    .pc-soft-card {
        border: 1px solid rgba(226, 232, 240, .88);
        background: rgba(255, 255, 255, .88);
        box-shadow: 0 12px 30px rgba(15, 23, 42, .045);
    }

    .pc-stat-card {
        min-height: 118px;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .pc-stat-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 36px rgba(15, 23, 42, .075);
    }

    .pc-label {
        display: block;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .13em;
        text-transform: uppercase;
        color: #94a3b8;
    }

    .pc-value {
        margin-top: 4px;
        font-size: 14px;
        font-weight: 950;
        line-height: 1.45;
        color: #0f172a;
    }

    .pc-help {
        margin-top: 2px;
        font-size: 12px;
        font-weight: 750;
        line-height: 1.5;
        color: #64748b;
    }

    .pc-action-btn {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        border-radius: 1rem;
        padding: .65rem 1rem;
        font-size: 13px;
        font-weight: 950;
        line-height: 1;
        transition: transform .16s ease, background .16s ease, border-color .16s ease, box-shadow .16s ease;
        white-space: nowrap;
    }

    .pc-action-btn:hover {
        transform: translateY(-1px);
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
        width: min(100%, 500px);
        transform: translateY(12px) scale(.97);
        opacity: 0;
        border-radius: 1.75rem;
        border: 1px solid rgba(255, 255, 255, .78);
        background:
            radial-gradient(circle at 0% 0%, rgba(244, 63, 94, .14), transparent 34%),
            radial-gradient(circle at 100% 0%, rgba(14, 165, 233, .12), transparent 34%),
            rgba(255, 255, 255, .96);
        box-shadow: 0 30px 90px rgba(15, 23, 42, .25);
        transition: transform .18s ease, opacity .18s ease;
    }

    .pc-modal-backdrop.is-open .pc-modal-card {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    @media (max-width: 640px) {
        .pc-action-btn {
            width: 100%;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
            scroll-behavior: auto !important;
        }
    }
</style>
@endpush

@section('content')
<div class="pc-page relative min-h-screen overflow-hidden px-4 py-5 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute inset-0 pc-grid-bg opacity-70"></div>

    <div class="relative z-10 mx-auto max-w-[1450px] space-y-5">
        <section class="pc-glass relative overflow-hidden rounded-[1.8rem] border border-white/80 p-5 sm:p-6">
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-emerald-300/20 blur-3xl"></div>
            <div class="absolute -bottom-24 left-24 h-72 w-72 rounded-full bg-sky-300/16 blur-3xl"></div>

            <div class="relative grid gap-5 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div>
                    <a href="{{ route('bidan.jadwal.index') }}"
                       class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/80 px-3 py-1.5 text-xs font-black text-slate-600 shadow-sm transition hover:bg-white">
                        <i class="ph ph-arrow-left"></i>
                        Kembali
                    </a>

                    <div class="mt-4 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50/80 px-3 py-1.5 text-xs font-black text-emerald-700">
                        <i class="ph ph-calendar-check"></i>
                        Detail Agenda Posyandu
                    </div>

                    <h1 class="mt-4 max-w-4xl text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                        {{ $judul }}
                    </h1>

                    <p class="mt-3 max-w-3xl text-sm font-semibold leading-6 text-slate-600">
                        Detail jadwal pelayanan Posyandu, target sasaran, status agenda, dan aturan akses perubahan.
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-2 text-xs font-black ring-1 {{ $statusData['badge'] }}">
                            <span class="h-2 w-2 rounded-full {{ $statusData['dot'] }}"></span>
                            {{ $statusData['label'] }}
                        </span>

                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-2 text-xs font-black ring-1 {{ $kategoriData['badge'] }}">
                            <i class="{{ $kategoriData['icon'] }}"></i>
                            {{ $kategoriMeta['label'] }}
                        </span>

                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-2 text-xs font-black ring-1 {{ $targetData['badge'] }}">
                            <i class="{{ $targetData['icon'] }}"></i>
                            {{ $targetMeta['label'] }}
                        </span>

                        @if($today)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-2 text-xs font-black text-emerald-700">
                                <i class="ph ph-sparkle"></i>
                                Hari Ini
                            </span>
                        @elseif($past)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-2 text-xs font-black text-amber-700">
                                <i class="ph ph-warning-circle"></i>
                                Tanggal Terlewat
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row xl:justify-end">
                    @if($canEdit)
                        <a href="{{ route('bidan.jadwal.edit', $jadwal) }}"
                           class="pc-action-btn bg-gradient-to-r from-sky-500 to-cyan-500 text-white shadow-lg shadow-sky-500/20">
                            <i class="ph ph-pencil-simple"></i>
                            Edit Jadwal
                        </a>
                    @else
                        <span class="pc-action-btn border border-slate-200 bg-slate-50 text-slate-500">
                            <i class="ph ph-lock-simple"></i>
                            Tidak Dapat Diedit
                        </span>
                    @endif

                    @if($canDelete)
                        <form action="{{ route('bidan.jadwal.destroy', $jadwal) }}"
                              method="POST"
                              data-delete-form
                              data-delete-title="Hapus jadwal ini?"
                              data-delete-message="Jadwal {{ $judul }} akan dihapus dan tidak tampil lagi pada daftar agenda.">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="pc-action-btn border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100">
                                <i class="ph ph-trash"></i>
                                Hapus
                            </button>
                        </form>
                    @else
                        <span class="pc-action-btn border border-slate-200 bg-slate-50 text-slate-500">
                            <i class="ph ph-lock-simple"></i>
                            Hapus Terkunci
                        </span>
                    @endif
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach($summaryCards as $card)
                <div class="pc-stat-card pc-glass rounded-[1.5rem] border border-white/80 p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-[11px] font-black uppercase tracking-[.15em] text-slate-400">
                                {{ $card['label'] }}
                            </p>

                            <p class="mt-2 truncate text-xl font-black tracking-tight text-slate-950">
                                {{ $card['value'] }}
                            </p>
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $card['class'] }}">
                            <i class="{{ $card['icon'] }} text-xl"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>

        <section class="grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(330px,.65fr)]">
            <div class="space-y-5">
                <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">
                                Informasi Agenda
                            </p>
                            <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                                Detail Pelaksanaan
                            </h2>
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $kategoriData['gradient'] }} text-white shadow-lg shadow-emerald-500/15">
                            <i class="{{ $kategoriData['icon'] }} text-xl"></i>
                        </div>
                    </div>

                    <div class="rounded-[1.5rem] border {{ $kategoriData['panel'] }} p-5">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-center">
                            <div class="flex min-h-[120px] w-full items-center justify-center rounded-[1.4rem] border border-white/70 bg-white/65 p-5 lg:w-[160px]">
                                <div class="text-center">
                                    <p class="text-xs font-black uppercase tracking-[.16em] text-slate-400">
                                        {{ $bulanPendek }}
                                    </p>
                                    <p class="text-5xl font-black leading-none text-slate-950">
                                        {{ $tanggalAngka }}
                                    </p>
                                    <p class="mt-2 text-xs font-black text-slate-500">
                                        {{ $tanggalPendek }}
                                    </p>
                                </div>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-black uppercase tracking-[.16em] text-slate-500">
                                    {{ $kategoriMeta['label'] }}
                                </p>

                                <h3 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                                    {{ $judul }}
                                </h3>

                                <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                                    {{ $tanggalLabel }}
                                </p>

                                <div class="mt-4 grid gap-3 md:grid-cols-3">
                                    <div class="rounded-2xl border border-white/70 bg-white/70 p-4">
                                        <span class="pc-label">Waktu</span>
                                        <p class="pc-value">{{ $waktuLabel }}</p>
                                    </div>

                                    <div class="rounded-2xl border border-white/70 bg-white/70 p-4">
                                        <span class="pc-label">Lokasi</span>
                                        <p class="pc-value">{{ $lokasi }}</p>
                                    </div>

                                    <div class="rounded-2xl border border-white/70 bg-white/70 p-4">
                                        <span class="pc-label">Sasaran</span>
                                        <p class="pc-value">{{ $targetMeta['label'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <div class="pc-soft-card rounded-[1.4rem] p-4">
                            <span class="pc-label">Tanggal</span>
                            <p class="pc-value">{{ $tanggalLabel }}</p>
                            <p class="pc-help">Tanggal pelaksanaan agenda Posyandu.</p>
                        </div>

                        <div class="pc-soft-card rounded-[1.4rem] p-4">
                            <span class="pc-label">Waktu</span>
                            <p class="pc-value">{{ $waktuLabel }}</p>
                            <p class="pc-help">Rentang waktu kegiatan pelayanan.</p>
                        </div>

                        <div class="pc-soft-card rounded-[1.4rem] p-4">
                            <span class="pc-label">Lokasi</span>
                            <p class="pc-value">{{ $lokasi }}</p>
                            <p class="pc-help">Tempat pelaksanaan kegiatan.</p>
                        </div>

                        <div class="pc-soft-card rounded-[1.4rem] p-4">
                            <span class="pc-label">Target Sasaran</span>
                            <p class="pc-value">{{ $targetMeta['label'] }}</p>
                            <p class="pc-help">{{ $targetMeta['desc'] }}</p>
                        </div>
                    </div>

                    <div class="mt-5 pc-soft-card rounded-[1.4rem] p-4">
                        <span class="pc-label">Deskripsi / Catatan</span>
                        <p class="mt-2 text-sm font-semibold leading-7 text-slate-600">
                            {{ $deskripsi !== '' ? $deskripsi : 'Tidak ada deskripsi tambahan.' }}
                        </p>
                    </div>
                </section>

                <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
                    <div class="mb-5">
                        <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">
                            Distribusi
                        </p>
                        <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                            Sasaran dan Kategori Layanan
                        </h2>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-[1.4rem] border {{ $kategoriData['panel'] }} p-4">
                            <div class="flex items-start gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $kategoriData['iconBox'] }}">
                                    <i class="{{ $kategoriData['icon'] }} text-lg"></i>
                                </div>

                                <div>
                                    <span class="pc-label">Kategori Layanan</span>
                                    <h3 class="mt-1 text-base font-black text-slate-950">
                                        {{ $kategoriMeta['label'] }}
                                    </h3>
                                    <p class="mt-1 text-sm font-semibold leading-6 text-slate-600">
                                        {{ $kategoriMeta['desc'] }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[1.4rem] border {{ $targetData['panel'] }} p-4">
                            <div class="flex items-start gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $targetData['iconBox'] }}">
                                    <i class="{{ $targetData['icon'] }} text-lg"></i>
                                </div>

                                <div>
                                    <span class="pc-label">Target Peserta</span>
                                    <h3 class="mt-1 text-base font-black text-slate-950">
                                        {{ $targetMeta['label'] }}
                                    </h3>
                                    <p class="mt-1 text-sm font-semibold leading-6 text-slate-600">
                                        {{ $targetMeta['desc'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 rounded-[1.4rem] border border-amber-100 bg-amber-50/80 p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white text-amber-700 ring-1 ring-amber-100">
                                <i class="ph ph-info"></i>
                            </div>

                            <div>
                                <h3 class="text-sm font-black text-amber-800">
                                    Jadwal hanya agenda layanan
                                </h3>

                                <p class="mt-1 text-sm font-semibold leading-6 text-amber-700">
                                    Jadwal digunakan untuk mengatur informasi waktu, lokasi, kategori layanan, dan target sasaran. Data pemeriksaan dan catatan medis tetap dikelola pada modul pemeriksaan atau rekam medis.
                                </p>

                                @if($kategori === 'imunisasi')
                                    <p class="mt-2 text-sm font-semibold leading-6 text-amber-700">
                                        Karena modul imunisasi difokuskan untuk Balita, jadwal kategori imunisasi ditujukan ke sasaran Balita.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="space-y-5">
                <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
                    <div class="mb-5">
                        <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">
                            Status
                        </p>
                        <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                            Kondisi Jadwal
                        </h2>
                    </div>

                    <div class="rounded-[1.4rem] border {{ $statusData['panel'] }} p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $statusData['iconBox'] }}">
                                <i class="{{ $statusData['icon'] }} text-lg"></i>
                            </div>

                            <div>
                                <span class="pc-label">Status Jadwal</span>
                                <h3 class="mt-1 text-base font-black text-slate-950">
                                    {{ $statusData['label'] }}
                                </h3>
                                <p class="mt-1 text-sm font-semibold leading-6 text-slate-600">
                                    {{ $statusMeta['desc'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
                    <div class="mb-5">
                        <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">
                            Akses
                        </p>
                        <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                            Perubahan Data
                        </h2>
                    </div>

                    <div class="rounded-[1.4rem] border {{ $lockData['panel'] }} p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $lockData['iconBox'] }}">
                                <i class="{{ $lockData['icon'] }} text-lg"></i>
                            </div>

                            <div>
                                <span class="pc-label">Hak Edit</span>
                                <h3 class="mt-1 text-base font-black text-slate-950">
                                    {{ $lockData['label'] }}
                                </h3>
                                <p class="mt-1 text-sm font-semibold leading-6 text-slate-600">
                                    {{ $lockData['desc'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
                    <div class="mb-5">
                        <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">
                            Riwayat
                        </p>
                        <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">
                            Data Sistem
                        </h2>
                    </div>

                    <div class="space-y-3">
                        <div class="pc-soft-card rounded-[1.4rem] p-4">
                            <span class="pc-label">Dibuat</span>
                            <p class="pc-value">{{ $formatMetaDate($jadwal->created_at ?? null) }}</p>
                        </div>

                        <div class="pc-soft-card rounded-[1.4rem] p-4">
                            <span class="pc-label">Diperbarui</span>
                            <p class="pc-value">{{ $formatMetaDate($jadwal->updated_at ?? null) }}</p>
                        </div>
                    </div>
                </section>
            </aside>
        </section>

        <section class="pc-glass rounded-[1.75rem] border border-white/80 p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('bidan.jadwal.index') }}"
                   class="pc-action-btn border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">
                    <i class="ph ph-arrow-left"></i>
                    Kembali ke Daftar
                </a>

                <div class="flex flex-col gap-3 sm:flex-row">
                    @if($canEdit)
                        <a href="{{ route('bidan.jadwal.edit', $jadwal) }}"
                           class="pc-action-btn border border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100">
                            <i class="ph ph-pencil-simple"></i>
                            Edit Jadwal
                        </a>
                    @else
                        <span class="pc-action-btn border border-slate-200 bg-slate-50 text-slate-500">
                            <i class="ph ph-lock-simple"></i>
                            Edit Terkunci
                        </span>
                    @endif

                    @if($canDelete)
                        <form action="{{ route('bidan.jadwal.destroy', $jadwal) }}"
                              method="POST"
                              data-delete-form
                              data-delete-title="Hapus jadwal ini?"
                              data-delete-message="Jadwal {{ $judul }} akan dihapus dan tidak tampil lagi pada daftar agenda.">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="pc-action-btn border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100">
                                <i class="ph ph-trash"></i>
                                Hapus
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('modals')
<div id="pcJadwalDeleteModal" class="pc-modal-backdrop" aria-hidden="true">
    <div class="pc-modal-card p-6">
        <div class="flex gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.25rem] bg-gradient-to-br from-rose-500 to-orange-500 text-white shadow-lg shadow-rose-500/20">
                <i class="ph ph-warning-circle text-2xl"></i>
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-[11px] font-black uppercase tracking-[.18em] text-rose-700">
                    Konfirmasi
                </p>

                <h3 id="pcJadwalDeleteTitle" class="mt-1 text-lg font-black text-slate-950">
                    Hapus jadwal ini?
                </h3>

                <p id="pcJadwalDeleteMessage" class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                    Jadwal yang dihapus tidak akan tampil lagi pada daftar agenda.
                </p>
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button"
                    id="pcJadwalDeleteCancel"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                Batal
            </button>

            <button type="button"
                    id="pcJadwalDeleteSubmit"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-rose-500 to-orange-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-rose-400/20 transition hover:-translate-y-0.5">
                <i class="ph ph-trash"></i>
                Hapus Jadwal
            </button>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    let modal = document.querySelector('#pcJadwalDeleteModal');
    const modalTitle = document.querySelector('#pcJadwalDeleteTitle');
    const modalMessage = document.querySelector('#pcJadwalDeleteMessage');
    const modalCancel = document.querySelector('#pcJadwalDeleteCancel');
    const modalSubmit = document.querySelector('#pcJadwalDeleteSubmit');

    let selectedDeleteForm = null;

    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    function lockBody() {
        document.documentElement.classList.add('pc-modal-open');
        document.body.classList.add('pc-modal-open');
    }

    function unlockBody() {
        document.documentElement.classList.remove('pc-modal-open');
        document.body.classList.remove('pc-modal-open');
    }

    function showDeleteDialog(form) {
        if (!modal) {
            HTMLFormElement.prototype.submit.call(form);
            return;
        }

        selectedDeleteForm = form;

        if (modalTitle) {
            modalTitle.textContent = form.dataset.deleteTitle || 'Hapus jadwal ini?';
        }

        if (modalMessage) {
            modalMessage.textContent = form.dataset.deleteMessage || 'Jadwal yang dihapus tidak akan tampil lagi pada daftar agenda.';
        }

        if (modalSubmit) {
            modalSubmit.disabled = false;
            modalSubmit.classList.remove('opacity-70', 'cursor-not-allowed');
            modalSubmit.innerHTML = '<i class="ph ph-trash"></i> Hapus Jadwal';
        }

        lockBody();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function hideDeleteDialog() {
        if (!modal) {
            return;
        }

        selectedDeleteForm = null;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        unlockBody();
    }

    document.addEventListener('submit', function (event) {
        const form = event.target.closest('[data-delete-form]');

        if (!form) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        showDeleteDialog(form);
    }, true);

    if (modalCancel) {
        modalCancel.addEventListener('click', hideDeleteDialog);
    }

    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                hideDeleteDialog();
            }
        });
    }

    if (modalSubmit) {
        modalSubmit.addEventListener('click', function () {
            if (!selectedDeleteForm) {
                hideDeleteDialog();
                return;
            }

            modalSubmit.disabled = true;
            modalSubmit.classList.add('opacity-70', 'cursor-not-allowed');
            modalSubmit.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> Menghapus...';

            HTMLFormElement.prototype.submit.call(selectedDeleteForm);
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal && modal.classList.contains('is-open')) {
            hideDeleteDialog();
        }
    });
})();
</script>
@endpush