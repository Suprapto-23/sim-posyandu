@extends('layouts.bidan')

@section('title', 'Detail Jadwal Posyandu')
@section('page-name', 'Detail Jadwal')
@section('page-title', 'Detail Jadwal Posyandu')

@php
    use Carbon\Carbon;

    $kategoriOptions = $kategoriOptions ?? [
        'posyandu' => [
            'label' => 'Posyandu Rutin',
            'desc' => 'Agenda pelayanan Posyandu umum, absensi, dan pengukuran dasar.',
            'icon' => 'ph-house-line',
        ],
        'imunisasi' => [
            'label' => 'Imunisasi Balita',
            'desc' => 'Agenda pelayanan imunisasi untuk sasaran Balita.',
            'icon' => 'ph-syringe',
        ],
        'pemeriksaan' => [
            'label' => 'Pemeriksaan Klinis',
            'desc' => 'Agenda pemeriksaan lanjutan oleh Bidan.',
            'icon' => 'ph-stethoscope',
        ],
        'lainnya' => [
            'label' => 'Kegiatan Lainnya',
            'desc' => 'Agenda tambahan Posyandu di luar layanan utama.',
            'icon' => 'ph-calendar-plus',
        ],
    ];

    $targetOptions = $targetOptions ?? [
        'semua' => [
            'label' => 'Semua Sasaran',
            'desc' => 'Balita, Remaja, Lansia, dan warga yang terdaftar.',
            'icon' => 'ph-users-three',
        ],
        'balita' => [
            'label' => 'Balita',
            'desc' => 'Sasaran Balita.',
            'icon' => 'ph-baby',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'desc' => 'Sasaran Remaja.',
            'icon' => 'ph-user-focus',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'desc' => 'Sasaran Lansia.',
            'icon' => 'ph-heartbeat',
        ],
    ];

    $statusOptions = $statusOptions ?? [
        'aktif' => [
            'label' => 'Aktif',
            'desc' => 'Jadwal masih berlaku.',
            'icon' => 'ph-check-circle',
        ],
        'selesai' => [
            'label' => 'Selesai',
            'desc' => 'Jadwal sudah dilaksanakan.',
            'icon' => 'ph-flag-checkered',
        ],
        'dibatalkan' => [
            'label' => 'Dibatalkan',
            'desc' => 'Jadwal dibatalkan atau ditunda.',
            'icon' => 'ph-x-circle',
        ],
    ];

    $judul = $jadwal->judul ?? 'Judul Jadwal Tidak Terdata';
    $kategori = $jadwal->kategori ?? 'posyandu';
    $target = $jadwal->target_peserta ?? 'semua';
    $status = $jadwal->status ?? 'aktif';
    $lokasi = $jadwal->lokasi ?? '-';
    $deskripsi = trim((string) ($jadwal->deskripsi ?? ''));

    $kategoriMeta = $kategoriOptions[$kategori] ?? $kategoriOptions['posyandu'];
    $targetMeta = $targetOptions[$target] ?? $targetOptions['semua'];
    $statusMeta = $statusOptions[$status] ?? $statusOptions['aktif'];

    $formatTanggal = function ($date, bool $withDay = false) {
        if (!$date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat($withDay ? 'l, d F Y' : 'd F Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatTanggalPendek = function ($date) {
        if (!$date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d M Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatBulanPendek = function ($date) {
        if (!$date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('M');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatTanggalAngka = function ($date) {
        if (!$date) {
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

            return "{$mulai} - {$selesai} WIB";
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatMetaDate = function ($date) {
        if (!$date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d M Y, H:i');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $isToday = function ($date) {
        if (!$date) {
            return false;
        }

        try {
            return Carbon::parse($date)->isToday();
        } catch (\Throwable $e) {
            return false;
        }
    };

    $isPastDate = function ($date) {
        if (!$date) {
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
                'panel' => 'border-emerald-100 bg-emerald-50/70',
                'iconBox' => 'bg-white text-emerald-700 ring-emerald-100',
                'dot' => 'bg-emerald-500',
                'icon' => 'ph-check-circle',
            ],
            'selesai' => [
                'label' => 'Selesai',
                'badge' => 'bg-slate-50 text-slate-600 ring-slate-200',
                'panel' => 'border-slate-100 bg-slate-50/80',
                'iconBox' => 'bg-white text-slate-600 ring-slate-100',
                'dot' => 'bg-slate-400',
                'icon' => 'ph-flag-checkered',
            ],
            'dibatalkan' => [
                'label' => 'Dibatalkan',
                'badge' => 'bg-rose-50 text-rose-700 ring-rose-200',
                'panel' => 'border-rose-100 bg-rose-50/70',
                'iconBox' => 'bg-white text-rose-700 ring-rose-100',
                'dot' => 'bg-rose-500',
                'icon' => 'ph-x-circle',
            ],
            default => [
                'label' => ucfirst((string) $value),
                'badge' => 'bg-slate-50 text-slate-600 ring-slate-200',
                'panel' => 'border-slate-100 bg-slate-50/80',
                'iconBox' => 'bg-white text-slate-600 ring-slate-100',
                'dot' => 'bg-slate-400',
                'icon' => 'ph-info',
            ],
        };
    };

    $kategoriTheme = function ($value) {
        return match (strtolower((string) $value)) {
            'imunisasi' => [
                'badge' => 'bg-cyan-50 text-cyan-700 ring-cyan-200',
                'panel' => 'border-cyan-100 bg-cyan-50/70',
                'iconBox' => 'bg-white text-cyan-700 ring-cyan-100',
                'gradient' => 'from-cyan-500 to-sky-500',
                'icon' => 'ph-syringe',
            ],
            'pemeriksaan' => [
                'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'panel' => 'border-emerald-100 bg-emerald-50/70',
                'iconBox' => 'bg-white text-emerald-700 ring-emerald-100',
                'gradient' => 'from-emerald-500 to-teal-500',
                'icon' => 'ph-stethoscope',
            ],
            'lainnya' => [
                'badge' => 'bg-amber-50 text-amber-700 ring-amber-200',
                'panel' => 'border-amber-100 bg-amber-50/70',
                'iconBox' => 'bg-white text-amber-700 ring-amber-100',
                'gradient' => 'from-amber-500 to-orange-500',
                'icon' => 'ph-calendar-plus',
            ],
            default => [
                'badge' => 'bg-sky-50 text-sky-700 ring-sky-200',
                'panel' => 'border-sky-100 bg-sky-50/70',
                'iconBox' => 'bg-white text-sky-700 ring-sky-100',
                'gradient' => 'from-sky-500 to-cyan-500',
                'icon' => 'ph-house-line',
            ],
        };
    };

    $targetTheme = function ($value) {
        return match (strtolower((string) $value)) {
            'balita' => [
                'badge' => 'bg-sky-50 text-sky-700 ring-sky-200',
                'panel' => 'border-sky-100 bg-sky-50/70',
                'iconBox' => 'bg-white text-sky-700 ring-sky-100',
                'icon' => 'ph-baby',
            ],
            'remaja' => [
                'badge' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
                'panel' => 'border-indigo-100 bg-indigo-50/70',
                'iconBox' => 'bg-white text-indigo-700 ring-indigo-100',
                'icon' => 'ph-user-focus',
            ],
            'lansia' => [
                'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'panel' => 'border-emerald-100 bg-emerald-50/70',
                'iconBox' => 'bg-white text-emerald-700 ring-emerald-100',
                'icon' => 'ph-heartbeat',
            ],
            default => [
                'badge' => 'bg-slate-50 text-slate-700 ring-slate-200',
                'panel' => 'border-slate-100 bg-slate-50/80',
                'iconBox' => 'bg-white text-slate-700 ring-slate-100',
                'icon' => 'ph-users-three',
            ],
        };
    };

    $lockState = function () use ($canEdit, $status) {
        if ($canEdit) {
            return [
                'label' => 'Bisa Diedit',
                'desc' => 'Jadwal masih aktif dan belum melewati waktu mulai.',
                'icon' => 'ph-pencil-simple',
                'panel' => 'border-emerald-100 bg-emerald-50/70',
                'iconBox' => 'bg-white text-emerald-700 ring-emerald-100',
            ];
        }

        if ($status === 'dibatalkan') {
            return [
                'label' => 'Dibatalkan',
                'desc' => 'Jadwal dibatalkan dan tidak dapat diedit.',
                'icon' => 'ph-x-circle',
                'panel' => 'border-rose-100 bg-rose-50/70',
                'iconBox' => 'bg-white text-rose-700 ring-rose-100',
            ];
        }

        if ($status === 'selesai') {
            return [
                'label' => 'Terkunci',
                'desc' => 'Jadwal sudah selesai dan disimpan sebagai arsip.',
                'icon' => 'ph-lock-simple',
                'panel' => 'border-slate-100 bg-slate-50/80',
                'iconBox' => 'bg-white text-slate-600 ring-slate-100',
            ];
        }

        return [
            'label' => 'Terkunci',
            'desc' => 'Waktu mulai jadwal sudah terlewati.',
            'icon' => 'ph-lock-simple',
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
            'icon' => 'ph-calendar-check',
            'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        ],
        [
            'label' => 'Waktu',
            'value' => $waktuLabel,
            'icon' => 'ph-clock',
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
    .nexus-font {
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .nexus-page-enter {
        animation: nexusMainIn .12s cubic-bezier(.22, 1, .36, 1) both;
        will-change: transform, opacity;
    }

    .nexus-panel-enter {
        animation: nexusPanelIn .12s cubic-bezier(.22, 1, .36, 1) both;
        will-change: transform, opacity;
    }

    @keyframes nexusMainIn {
        from {
            opacity: 0;
            transform: translate3d(0, 3px, 0);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @keyframes nexusPanelIn {
        from {
            opacity: 0;
            transform: translate3d(0, 2px, 0);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @media (max-width: 768px) {
        .nexus-page-enter,
        .nexus-panel-enter {
            animation-duration: .08s;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .nexus-page-enter,
        .nexus-panel-enter {
            animation: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="nexus-font nexus-page-enter space-y-5 pb-8 text-slate-800">

    {{-- HEADER --}}
    <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div class="min-w-0">
                <a href="{{ route('bidan.jadwal.index') }}"
                   class="mb-4 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-600 transition hover:bg-slate-50 hover:text-emerald-700">
                    <i class="ph ph-arrow-left"></i>
                    Kembali
                </a>

                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.16em] text-emerald-700">
                    <i class="ph ph-calendar-check text-base"></i>
                    Detail Agenda Posyandu
                </div>

                <h1 class="mt-4 max-w-4xl text-[26px] font-black leading-tight tracking-[-0.025em] text-slate-900 md:text-[30px]">
                    {{ $judul }}
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Detail jadwal pelayanan Posyandu, target sasaran, status agenda, dan aturan akses perubahan.
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-xs font-black ring-1 {{ $statusData['badge'] }}">
                        <span class="h-2 w-2 rounded-full {{ $statusData['dot'] }}"></span>
                        {{ $statusData['label'] }}
                    </span>

                    <span class="inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-xs font-black ring-1 {{ $kategoriData['badge'] }}">
                        <i class="ph {{ $kategoriMeta['icon'] }}"></i>
                        {{ $kategoriMeta['label'] }}
                    </span>

                    <span class="inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-xs font-black ring-1 {{ $targetData['badge'] }}">
                        <i class="ph {{ $targetMeta['icon'] }}"></i>
                        {{ $targetMeta['label'] }}
                    </span>

                    @if($today)
                        <span class="inline-flex items-center gap-2 rounded-full border border-amber-100 bg-amber-50 px-3.5 py-2 text-xs font-black text-amber-700">
                            <i class="ph ph-clock-countdown"></i>
                            Hari Ini
                        </span>
                    @elseif($past)
                        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-black text-slate-500">
                            <i class="ph ph-clock-counter-clockwise"></i>
                            Tanggal Terlewat
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row xl:pt-12">
                @if($canEdit)
                    <a href="{{ route('bidan.jadwal.edit', $jadwal->id) }}"
                       class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-700">
                        <i class="ph ph-pencil-simple"></i>
                        Edit Jadwal
                    </a>
                @else
                    <div class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-100 px-5 py-3 text-sm font-black text-slate-400">
                        <i class="ph ph-lock-simple"></i>
                        Tidak Dapat Diedit
                    </div>
                @endif

                @if($canDelete)
                    <form action="{{ route('bidan.jadwal.destroy', $jadwal->id) }}"
                          method="POST"
                          class="js-delete-jadwal">
                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="inline-flex min-h-[48px] w-full items-center justify-center gap-2 rounded-2xl border border-rose-100 bg-rose-50 px-5 py-3 text-sm font-black text-rose-700 transition hover:bg-rose-100 sm:w-auto">
                            <i class="ph ph-trash"></i>
                            Hapus
                        </button>
                    </form>
                @else
                    <div class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-100 px-5 py-3 text-sm font-black text-slate-400">
                        <i class="ph ph-trash"></i>
                        Hapus Terkunci
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- SUMMARY --}}
    <section class="nexus-panel-enter grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach($summaryCards as $card)
            <div class="rounded-[22px] border border-white/80 bg-white/85 p-4 shadow-sm shadow-slate-200/70 backdrop-blur">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-xs font-black uppercase tracking-[0.12em] text-slate-400">
                            {{ $card['label'] }}
                        </p>

                        <h2 class="mt-2 line-clamp-1 text-base font-black tracking-tight text-slate-900">
                            {{ $card['value'] }}
                        </h2>
                    </div>

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $card['class'] }}">
                        <i class="ph {{ $card['icon'] }} text-lg"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </section>

    {{-- AGENDA UTAMA --}}
    <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="mb-5 flex items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                    Informasi Agenda
                </p>

                <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                    Detail Pelaksanaan
                </h2>
            </div>

            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                <i class="ph ph-calendar-dots text-lg"></i>
            </div>
        </div>

        <div class="rounded-[22px] bg-gradient-to-br {{ $kategoriData['gradient'] }} p-5 text-white">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div class="min-w-0">
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-white/80">
                        {{ $kategoriMeta['label'] }}
                    </p>

                    <h3 class="mt-3 text-xl font-black tracking-[-0.025em] md:text-2xl">
                        {{ $judul }}
                    </h3>

                    <p class="mt-2 text-sm font-semibold leading-6 text-white/85">
                        {{ $tanggalLabel }}
                    </p>
                </div>

                <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/25">
                    <span class="text-[10px] font-black uppercase text-white/80">
                        {{ $bulanPendek }}
                    </span>

                    <span class="text-xl font-black leading-none text-white">
                        {{ $tanggalAngka }}
                    </span>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-[11px] font-black text-white ring-1 ring-white/25">
                    <i class="ph ph-clock mr-1"></i>
                    {{ $waktuLabel }}
                </span>

                <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-[11px] font-black text-white ring-1 ring-white/25">
                    <i class="ph ph-map-pin mr-1"></i>
                    {{ $lokasi }}
                </span>

                <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-[11px] font-black text-white ring-1 ring-white/25">
                    <i class="ph {{ $targetMeta['icon'] }} mr-1"></i>
                    {{ $targetMeta['label'] }}
                </span>
            </div>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Tanggal
                </p>

                <p class="mt-2 text-sm font-black text-slate-900">
                    {{ $tanggalLabel }}
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Waktu
                </p>

                <p class="mt-2 text-sm font-black text-slate-900">
                    {{ $waktuLabel }}
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Lokasi
                </p>

                <p class="mt-2 flex items-center gap-2 text-sm font-black text-slate-900">
                    <i class="ph ph-map-pin text-emerald-600"></i>
                    {{ $lokasi }}
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Target Sasaran
                </p>

                <p class="mt-2 flex items-center gap-2 text-sm font-black text-slate-900">
                    <i class="ph {{ $targetMeta['icon'] }} text-emerald-600"></i>
                    {{ $targetMeta['label'] }}
                </p>
            </div>
        </div>

        <div class="mt-3 rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
            <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                Deskripsi / Catatan
            </p>

            <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                {{ $deskripsi !== '' ? $deskripsi : 'Tidak ada deskripsi tambahan.' }}
            </p>
        </div>
    </section>

    {{-- GRID INFORMASI PRESISI --}}
    <section class="nexus-panel-enter grid gap-5 xl:grid-cols-3 xl:items-stretch">

        <div class="flex h-full flex-col rounded-[26px] border border-white/80 bg-white/90 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
            <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                        Status
                    </p>

                    <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900">
                        Kondisi Jadwal
                    </h2>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl ring-1 {{ $statusData['iconBox'] }}">
                    <i class="ph {{ $statusData['icon'] }} text-lg"></i>
                </div>
            </div>

            <div class="flex flex-1 rounded-[22px] border p-4 {{ $statusData['panel'] }}">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full {{ $statusData['dot'] }}"></span>

                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">
                            Status Jadwal
                        </p>
                    </div>

                    <h3 class="mt-3 text-2xl font-black tracking-[-0.025em] text-slate-900">
                        {{ $statusData['label'] }}
                    </h3>

                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                        {{ $statusMeta['desc'] }}
                    </p>
                </div>
            </div>
        </div>

        <div class="flex h-full flex-col rounded-[26px] border border-white/80 bg-white/90 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
            <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                        Akses
                    </p>

                    <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900">
                        Perubahan Data
                    </h2>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl ring-1 {{ $lockData['iconBox'] }}">
                    <i class="ph {{ $lockData['icon'] }} text-lg"></i>
                </div>
            </div>

            <div class="flex flex-1 rounded-[22px] border p-4 {{ $lockData['panel'] }}">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">
                        Hak Edit
                    </p>

                    <h3 class="mt-3 text-2xl font-black tracking-[-0.025em] text-slate-900">
                        {{ $lockData['label'] }}
                    </h3>

                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                        {{ $lockData['desc'] }}
                    </p>
                </div>
            </div>
        </div>

        <div class="flex h-full flex-col rounded-[26px] border border-white/80 bg-white/90 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
            <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                        Riwayat
                    </p>

                    <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900">
                        Data Sistem
                    </h2>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-50 text-slate-600 ring-1 ring-slate-100">
                    <i class="ph ph-clock-counter-clockwise text-lg"></i>
                </div>
            </div>

            <div class="grid flex-1 gap-3">
                <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                    <p class="text-[10px] font-black uppercase text-slate-400">
                        Dibuat
                    </p>

                    <p class="mt-1 text-sm font-black text-slate-900">
                        {{ $formatMetaDate($jadwal->created_at ?? null) }}
                    </p>
                </div>

                <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                    <p class="text-[10px] font-black uppercase text-slate-400">
                        Diperbarui
                    </p>

                    <p class="mt-1 text-sm font-black text-slate-900">
                        {{ $formatMetaDate($jadwal->updated_at ?? null) }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- DISTRIBUSI DAN CATATAN --}}
    <section class="nexus-panel-enter grid gap-5 xl:grid-cols-2 xl:items-stretch">

        <div class="flex h-full flex-col rounded-[26px] border border-white/80 bg-white/90 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
            <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                        Distribusi
                    </p>

                    <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900">
                        Sasaran & Kategori
                    </h2>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                    <i class="ph ph-users-three text-lg"></i>
                </div>
            </div>

            <div class="grid flex-1 gap-3">
                <div class="rounded-2xl border p-4 {{ $kategoriData['panel'] }}">
                    <div class="flex gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ring-1 {{ $kategoriData['iconBox'] }}">
                            <i class="ph {{ $kategoriMeta['icon'] }} text-lg"></i>
                        </div>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                                Kategori Layanan
                            </p>

                            <h3 class="mt-1 text-sm font-black text-slate-900">
                                {{ $kategoriMeta['label'] }}
                            </h3>

                            <p class="mt-1 text-xs leading-5 text-slate-600">
                                {{ $kategoriMeta['desc'] }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border p-4 {{ $targetData['panel'] }}">
                    <div class="flex gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ring-1 {{ $targetData['iconBox'] }}">
                            <i class="ph {{ $targetMeta['icon'] }} text-lg"></i>
                        </div>

                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                                Target Peserta
                            </p>

                            <h3 class="mt-1 text-sm font-black text-slate-900">
                                {{ $targetMeta['label'] }}
                            </h3>

                            <p class="mt-1 text-xs leading-5 text-slate-600">
                                {{ $targetMeta['desc'] }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex h-full flex-col rounded-[26px] border border-amber-100 bg-amber-50/75 p-5 shadow-sm shadow-slate-200/70">
            <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-amber-700">
                        Catatan Sistem
                    </p>

                    <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900">
                        Jadwal hanya agenda layanan
                    </h2>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-amber-600 ring-1 ring-amber-100">
                    <i class="ph ph-info text-lg"></i>
                </div>
            </div>

            <p class="flex-1 text-sm leading-6 text-slate-600">
                Jadwal digunakan untuk mengatur informasi waktu, lokasi, kategori layanan, dan target sasaran. Data pemeriksaan dan catatan medis tetap dikelola pada modul pemeriksaan atau rekam medis.
            </p>

            @if($kategori === 'imunisasi')
                <div class="mt-4 rounded-2xl border border-cyan-100 bg-cyan-50/80 p-4">
                    <div class="flex gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-cyan-700 ring-1 ring-cyan-100">
                            <i class="ph ph-syringe text-lg"></i>
                        </div>

                        <div>
                            <h3 class="text-sm font-black text-slate-900">
                                Agenda imunisasi untuk Balita
                            </h3>

                            <p class="mt-1 text-xs leading-5 text-slate-600">
                                Karena modul imunisasi difokuskan untuk Balita, jadwal kategori imunisasi ditujukan ke sasaran Balita.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- ACTION BAWAH --}}
    <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
        <div class="grid gap-3 sm:grid-cols-[1fr_auto_auto]">
            <a href="{{ route('bidan.jadwal.index') }}"
               class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                Kembali ke Daftar
            </a>

            @if($canEdit)
                <a href="{{ route('bidan.jadwal.edit', $jadwal->id) }}"
                   class="inline-flex min-h-[46px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                    <i class="ph ph-pencil-simple"></i>
                    Edit Jadwal
                </a>
            @else
                <div class="inline-flex min-h-[46px] items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-100 px-5 py-3 text-sm font-black text-slate-400">
                    <i class="ph ph-lock-simple"></i>
                    Edit Terkunci
                </div>
            @endif

            @if($canDelete)
                <form action="{{ route('bidan.jadwal.destroy', $jadwal->id) }}"
                      method="POST"
                      class="js-delete-jadwal">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="inline-flex min-h-[46px] w-full items-center justify-center gap-2 rounded-2xl border border-rose-100 bg-rose-50 px-5 py-3 text-sm font-black text-rose-700 transition hover:bg-rose-100">
                        <i class="ph ph-trash"></i>
                        Hapus
                    </button>
                </form>
            @endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        document.addEventListener('submit', (event) => {
            const form = event.target.closest('.js-delete-jadwal');

            if (!form) {
                return;
            }

            const confirmed = confirm('Hapus jadwal ini? Jadwal yang dihapus tidak akan tampil lagi pada daftar agenda.');

            if (!confirmed) {
                event.preventDefault();
            }
        });
    })();
</script>
@endpush