@extends('layouts.bidan')

@section('title', 'Detail Rekam Medis')
@section('page-name', 'Detail Rekam Medis')
@section('page-title', 'Detail Rekam Medis')

@php
    use Carbon\Carbon;

    $pasienType = $pasienType ?? $pasien_type ?? request('pasien_type', 'balita');
    $pasien_type = $pasien_type ?? $pasienType;

    $riwayatMedis = collect($riwayatMedis ?? []);
    $riwayatImunisasi = collect($riwayatImunisasi ?? []);
    $summary = $summary ?? [];

    $typeOptions = $typeOptions ?? [
        'balita' => [
            'label' => 'Balita',
            'desc' => 'Riwayat pertumbuhan, pemeriksaan dasar, dan imunisasi.',
            'icon' => 'ph-baby',
            'theme' => 'sky',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'desc' => 'Riwayat pemeriksaan kesehatan remaja.',
            'icon' => 'ph-user-focus',
            'theme' => 'indigo',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'desc' => 'Riwayat pemeriksaan dasar dan pemantauan kesehatan lansia.',
            'icon' => 'ph-heartbeat',
            'theme' => 'emerald',
        ],
    ];

    $currentTypeMeta = $typeOptions[$pasienType] ?? $typeOptions['balita'];
    $pasienTypeLabel = $pasienTypeLabel ?? ($currentTypeMeta['label'] ?? ucfirst($pasienType));

    $getValue = function ($item, array $keys, mixed $default = '-') {
        foreach ($keys as $key) {
            $value = data_get($item, $key);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $default;
    };

    $formatDate = function ($date) {
        if (!$date || $date === '-') {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d M Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $formatDateTime = function ($date) {
        if (!$date || $date === '-') {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d M Y, H:i') . ' WIB';
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $displayValue = function (mixed $value, string $suffix = '') {
        if ($value === null || $value === '' || $value === '-') {
            return '-';
        }

        return trim((string) $value . ' ' . $suffix);
    };

    $getNama = function ($item) use ($getValue) {
        return $getValue($item, ['nama_lengkap', 'nama', 'nama_balita', 'nama_remaja', 'nama_lansia'], 'Nama tidak tersedia');
    };

    $getNik = function ($item) use ($getValue) {
        return $getValue($item, ['nik', 'nik_anak', 'nik_remaja', 'nik_lansia'], '-');
    };

    $getAlamat = function ($item) use ($getValue) {
        return $getValue($item, ['alamat', 'alamat_lengkap', 'dusun'], '-');
    };

    $getKontak = function ($item) use ($getValue) {
        return $getValue($item, ['no_hp', 'nomor_hp', 'telepon', 'no_telepon'], '-');
    };

    $getWali = function ($item) use ($getValue, $pasienType) {
        if ($pasienType === 'balita') {
            return $getValue($item, ['nama_ibu', 'nama_ayah', 'nama_wali'], '-');
        }

        if ($pasienType === 'remaja') {
            return $getValue($item, ['nama_orang_tua', 'nama_wali', 'sekolah'], '-');
        }

        return $getValue($item, ['kontak_keluarga', 'nama_keluarga', 'nama_wali'], '-');
    };

    $getGender = function ($item) use ($getValue) {
        $gender = strtolower((string) $getValue($item, ['jenis_kelamin', 'jk', 'gender'], '-'));

        return match ($gender) {
            'l', 'laki-laki', 'laki laki', 'male' => 'Laki-laki',
            'p', 'perempuan', 'female' => 'Perempuan',
            default => $gender === '-' ? '-' : ucfirst($gender),
        };
    };

    $getTanggalLahir = function ($item) use ($getValue, $formatDate) {
        return $formatDate($getValue($item, ['tanggal_lahir', 'tgl_lahir', 'lahir'], null));
    };

    $getPersonName = function ($item, array $paths) {
        foreach ($paths as $path) {
            $value = data_get($item, $path . '.name')
                ?? data_get($item, $path . '.nama')
                ?? data_get($item, $path . '.nama_lengkap');

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return '-';
    };

    $themeMeta = function ($key) {
        return match ($key) {
            'remaja' => [
                'badge' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
                'panel' => 'border-indigo-100 bg-indigo-50/70',
                'iconBox' => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
                'gradient' => 'from-indigo-500 to-violet-500',
            ],
            'lansia' => [
                'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'panel' => 'border-emerald-100 bg-emerald-50/70',
                'iconBox' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                'gradient' => 'from-emerald-500 to-teal-500',
            ],
            default => [
                'badge' => 'bg-sky-50 text-sky-700 ring-sky-200',
                'panel' => 'border-sky-100 bg-sky-50/70',
                'iconBox' => 'bg-sky-50 text-sky-700 ring-sky-100',
                'gradient' => 'from-sky-500 to-cyan-500',
            ],
        };
    };

    $currentTheme = $themeMeta($pasienType);

    $pasienNama = $getNama($pasien);
    $pasienNik = $getNik($pasien);
    $pasienAlamat = $getAlamat($pasien);
    $pasienKontak = $getKontak($pasien);
    $pasienWali = $getWali($pasien);
    $pasienGender = $getGender($pasien);
    $pasienTanggalLahir = $getTanggalLahir($pasien);

    $latestParams = data_get($summary, 'parameter_terakhir', []);
    $latestParams = is_array($latestParams) ? $latestParams : [];

    $summaryCards = [
        [
            'label' => 'Pemeriksaan',
            'value' => data_get($summary, 'total_medis', $riwayatMedis->count()),
            'icon' => 'ph-stethoscope',
            'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        ],
        [
            'label' => $pasienType === 'balita' ? 'Imunisasi' : 'Ruang Lingkup',
            'value' => $pasienType === 'balita' ? data_get($summary, 'total_imunisasi', $riwayatImunisasi->count()) : 'Klinis',
            'icon' => $pasienType === 'balita' ? 'ph-syringe' : 'ph-folder-simple-user',
            'class' => $pasienType === 'balita'
                ? 'bg-cyan-50 text-cyan-700 ring-cyan-100'
                : 'bg-slate-50 text-slate-700 ring-slate-100',
        ],
        [
            'label' => 'Terakhir',
            'value' => data_get($summary, 'pemeriksaan_terakhir', '-'),
            'icon' => 'ph-clock-counter-clockwise',
            'class' => 'bg-amber-50 text-amber-700 ring-amber-100',
        ],
        [
            'label' => 'Kategori',
            'value' => $pasienTypeLabel,
            'icon' => $currentTypeMeta['icon'] ?? 'ph-user',
            'class' => $currentTheme['iconBox'],
        ],
    ];

    $medicalFields = function ($item) use ($pasienType, $displayValue) {
        if ($pasienType === 'lansia') {
            return [
                [
                    'label' => 'Tekanan Darah',
                    'value' => $displayValue(data_get($item, 'tekanan_darah')),
                    'icon' => 'ph-heartbeat',
                ],
                [
                    'label' => 'Gula Darah',
                    'value' => $displayValue(data_get($item, 'gula_darah'), 'mg/dL'),
                    'icon' => 'ph-drop',
                ],
                [
                    'label' => 'Kolesterol',
                    'value' => $displayValue(data_get($item, 'kolesterol'), 'mg/dL'),
                    'icon' => 'ph-waveform',
                ],
                [
                    'label' => 'Asam Urat',
                    'value' => $displayValue(data_get($item, 'asam_urat'), 'mg/dL'),
                    'icon' => 'ph-flask',
                ],
                [
                    'label' => 'Lingkar Perut',
                    'value' => $displayValue(data_get($item, 'lingkar_perut'), 'cm'),
                    'icon' => 'ph-ruler',
                ],
                [
                    'label' => 'Kemandirian',
                    'value' => $displayValue(data_get($item, 'tingkat_kemandirian')),
                    'icon' => 'ph-person-simple-walk',
                ],
            ];
        }

        if ($pasienType === 'remaja') {
            return [
                [
                    'label' => 'Berat Badan',
                    'value' => $displayValue(data_get($item, 'berat_badan'), 'kg'),
                    'icon' => 'ph-scales',
                ],
                [
                    'label' => 'Tinggi Badan',
                    'value' => $displayValue(data_get($item, 'tinggi_badan'), 'cm'),
                    'icon' => 'ph-ruler',
                ],
                [
                    'label' => 'IMT',
                    'value' => $displayValue(data_get($item, 'imt')),
                    'icon' => 'ph-chart-line-up',
                ],
                [
                    'label' => 'Tekanan Darah',
                    'value' => $displayValue(data_get($item, 'tekanan_darah')),
                    'icon' => 'ph-heartbeat',
                ],
                [
                    'label' => 'LILA',
                    'value' => $displayValue(data_get($item, 'lingkar_lengan'), 'cm'),
                    'icon' => 'ph-ruler',
                ],
            ];
        }

        return [
            [
                'label' => 'Berat Badan',
                'value' => $displayValue(data_get($item, 'berat_badan'), 'kg'),
                'icon' => 'ph-scales',
            ],
            [
                'label' => 'Tinggi Badan',
                'value' => $displayValue(data_get($item, 'tinggi_badan'), 'cm'),
                'icon' => 'ph-ruler',
            ],
            [
                'label' => 'Lingkar Kepala',
                'value' => $displayValue(data_get($item, 'lingkar_kepala'), 'cm'),
                'icon' => 'ph-circle',
            ],
            [
                'label' => 'LILA',
                'value' => $displayValue(data_get($item, 'lingkar_lengan'), 'cm'),
                'icon' => 'ph-ruler',
            ],
            [
                'label' => 'Status Gizi',
                'value' => $displayValue(data_get($item, 'status_gizi')),
                'icon' => 'ph-bowl-food',
            ],
        ];
    };
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

    .nexus-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(16, 185, 129, .35) transparent;
    }

    .nexus-scroll::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .nexus-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .nexus-scroll::-webkit-scrollbar-thumb {
        background: rgba(16, 185, 129, .35);
        border-radius: 999px;
    }

    .rekam-medis-scroll-box {
        max-height: 560px;
        overflow-y: auto;
        padding-right: 6px;
        scroll-behavior: smooth;
    }

    @media (max-width: 768px) {
        .nexus-page-enter,
        .nexus-panel-enter {
            animation-duration: .08s;
        }

        .rekam-medis-scroll-box {
            max-height: 480px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .nexus-page-enter,
        .nexus-panel-enter {
            animation: none !important;
        }

        .rekam-medis-scroll-box {
            scroll-behavior: auto;
        }
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
</style>
@endpush

@section('content')
<div class="nexus-font nexus-page-enter space-y-5 pb-8 text-slate-800">

    {{-- HEADER --}}
    <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div class="min-w-0">
                <a href="{{ route('bidan.rekam-medis.index', ['type' => $pasienType]) }}"
                   class="mb-4 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-600 transition hover:bg-slate-50 hover:text-emerald-700">
                    <i class="ph ph-arrow-left"></i>
                    Kembali
                </a>

                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.16em] text-emerald-700">
                    <i class="ph ph-folder-simple-user text-base"></i>
                    Detail Rekam Medis
                </div>

                <h1 class="mt-4 max-w-4xl text-[26px] font-black leading-tight tracking-[-0.025em] text-slate-900 md:text-[30px]">
                    {{ $pasienNama }}
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Riwayat pemeriksaan tervalidasi dan ringkasan kesehatan {{ strtolower($pasienTypeLabel) }}.
                    @if($pasienType === 'balita')
                        Termasuk riwayat imunisasi yang telah dicatat pada layanan Balita.
                    @endif
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-xs font-black ring-1 {{ $currentTheme['badge'] }}">
                        <i class="ph {{ $currentTypeMeta['icon'] ?? 'ph-user' }}"></i>
                        {{ $pasienTypeLabel }}
                    </span>

                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-black text-slate-500">
                        <i class="ph ph-identification-card"></i>
                        {{ $pasienNik }}
                    </span>
                </div>
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

    {{-- PENJELASAN OUTPUT --}}
    <section class="nexus-panel-enter rounded-[22px] border border-emerald-100 bg-emerald-50/70 p-4">
        <div class="flex gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-emerald-700 ring-1 ring-emerald-100">
                <i class="ph ph-info text-lg"></i>
            </div>

            <div>
                <h2 class="text-sm font-black text-slate-900">
                    Output Rekam Medis
                </h2>

                <p class="mt-1 text-sm leading-6 text-slate-600">
                    Halaman ini menampilkan identitas pasien, parameter pemeriksaan terakhir, riwayat pemeriksaan tervalidasi, catatan klinis, tindakan layanan, dan edukasi yang tercatat oleh Bidan.
                </p>
            </div>
        </div>
    </section>

    {{-- IDENTITAS --}}
    <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="mb-5 flex items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                    Identitas
                </p>

                <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                    Data Pasien
                </h2>
            </div>

            <div class="flex h-10 w-10 items-center justify-center rounded-2xl ring-1 {{ $currentTheme['iconBox'] }}">
                <i class="ph {{ $currentTypeMeta['icon'] ?? 'ph-user' }} text-lg"></i>
            </div>
        </div>

        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">Nama</p>
                <p class="mt-2 truncate text-sm font-black text-slate-900">{{ $pasienNama }}</p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">NIK</p>
                <p class="mt-2 truncate text-sm font-black text-slate-900">{{ $pasienNik }}</p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">Jenis Kelamin</p>
                <p class="mt-2 truncate text-sm font-black text-slate-900">{{ $pasienGender }}</p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">Tanggal Lahir</p>
                <p class="mt-2 truncate text-sm font-black text-slate-900">{{ $pasienTanggalLahir }}</p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">Wali / Keluarga</p>
                <p class="mt-2 truncate text-sm font-black text-slate-900">{{ $pasienWali }}</p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">Kontak</p>
                <p class="mt-2 truncate text-sm font-black text-slate-900">{{ $pasienKontak }}</p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100 md:col-span-2">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">Alamat</p>
                <p class="mt-2 line-clamp-2 text-sm font-black text-slate-900">{{ $pasienAlamat }}</p>
            </div>
        </div>
    </section>

    {{-- PARAMETER TERAKHIR --}}
    <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="mb-5 flex items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                    Parameter
                </p>

                <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                    Pemeriksaan Terakhir
                </h2>
            </div>

            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                <i class="ph ph-chart-line-up text-lg"></i>
            </div>
        </div>

        @if(count($latestParams) > 0)
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                @foreach($latestParams as $label => $value)
                    <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                        <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                            {{ $label }}
                        </p>

                        <p class="mt-2 truncate text-sm font-black text-slate-900">
                            {{ $value ?: '-' }}
                        </p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                    <i class="ph ph-chart-line-up text-2xl"></i>
                </div>

                <h3 class="mt-4 text-base font-black text-slate-800">
                    Parameter Belum Tersedia
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Belum ada pemeriksaan tervalidasi yang dapat ditampilkan.
                </p>
            </div>
        @endif
    </section>

    {{-- RIWAYAT PEMERIKSAAN --}}
    <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                    Riwayat Pemeriksaan
                </p>

                <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                    Pemeriksaan Tervalidasi
                </h2>

                <p class="mt-1 text-xs font-semibold text-slate-500">
                    Daftar dibatasi dalam area scroll agar halaman tetap ringkas dan mudah dibaca.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-[11px] font-black text-emerald-700">
                    <i class="ph ph-check-circle"></i>
                    {{ $riwayatMedis->count() }} data tervalidasi
                </span>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700 ring-1 ring-cyan-100">
                    <i class="ph ph-stethoscope text-lg"></i>
                </div>
            </div>
        </div>

        @if($riwayatMedis->count() > 0)
            <div class="rekam-medis-scroll-box nexus-scroll">
                @foreach($riwayatMedis as $pemeriksaan)
                    @php
                        $tanggalPemeriksaan = $formatDateTime(
                            data_get($pemeriksaan, 'tanggal_periksa')
                            ?? data_get($pemeriksaan, 'created_at')
                        );

                        $petugas = $getPersonName($pemeriksaan, ['pemeriksa', 'verifikator', 'verifikatorLegacy']);
                        $fields = $medicalFields($pemeriksaan);

                        $catatan = $getValue($pemeriksaan, [
                            'catatan_bidan',
                            'catatan',
                            'keterangan',
                            'hasil_pemeriksaan',
                            'keluhan',
                        ], '-');

                        $tindakan = $getValue($pemeriksaan, [
                            'tindakan',
                            'tindakan_lanjut',
                            'layanan',
                            'rekomendasi_tindakan',
                        ], '-');

                        $edukasi = $getValue($pemeriksaan, [
                            'catatan_edukasi',
                            'edukasi',
                            'edukasi_kesehatan',
                        ], '-');
                    @endphp

                    <article class="mb-3 rounded-[22px] border border-slate-100 bg-slate-50/80 p-4">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-[11px] font-black text-emerald-700">
                                        <i class="ph ph-check-circle"></i>
                                        Tervalidasi
                                    </span>

                                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-black text-slate-500">
                                        <i class="ph ph-clock"></i>
                                        {{ $tanggalPemeriksaan }}
                                    </span>
                                </div>

                                <h3 class="mt-3 text-base font-black text-slate-900">
                                    Pemeriksaan {{ $pasienTypeLabel }}
                                </h3>

                                <p class="mt-1 text-sm font-semibold text-slate-500">
                                    Petugas: {{ $petugas }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                            @foreach($fields as $field)
                                <div class="rounded-2xl bg-white p-3 ring-1 ring-slate-100">
                                    <p class="flex items-center gap-1 text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                                        <i class="ph {{ $field['icon'] }}"></i>
                                        {{ $field['label'] }}
                                    </p>

                                    <p class="mt-2 truncate text-sm font-black text-slate-900">
                                        {{ $field['value'] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-3 grid gap-3 lg:grid-cols-3">
                            <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-100">
                                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                                    Catatan Klinis
                                </p>

                                <p class="mt-2 line-clamp-3 text-sm font-semibold leading-6 text-slate-600">
                                    {{ $catatan }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-100">
                                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                                    Tindakan / Layanan
                                </p>

                                <p class="mt-2 line-clamp-3 text-sm font-semibold leading-6 text-slate-600">
                                    {{ $tindakan }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white p-4 ring-1 ring-slate-100">
                                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                                    Edukasi
                                </p>

                                <p class="mt-2 line-clamp-3 text-sm font-semibold leading-6 text-slate-600">
                                    {{ $edukasi }}
                                </p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                    <i class="ph ph-folder-simple-dashed text-2xl"></i>
                </div>

                <h3 class="mt-4 text-base font-black text-slate-800">
                    Belum Ada Rekam Medis Tervalidasi
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Data akan muncul setelah pemeriksaan divalidasi oleh Bidan.
                </p>
            </div>
        @endif
    </section>

    {{-- IMUNISASI KHUSUS BALITA --}}
    @if($pasienType === 'balita')
        <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                        Imunisasi
                    </p>

                    <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                        Riwayat Imunisasi Balita
                    </h2>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700 ring-1 ring-cyan-100">
                    <i class="ph ph-syringe text-lg"></i>
                </div>
            </div>

            @forelse($riwayatImunisasi as $imunisasi)
                @php
                    $tanggalImunisasi = $formatDate($getValue($imunisasi, ['tanggal_imunisasi', 'tanggal', 'created_at'], null));
                    $jenisImunisasi = $getValue($imunisasi, ['jenis_imunisasi', 'jenis', 'nama_imunisasi'], 'Imunisasi');
                    $vaksin = $getValue($imunisasi, ['vaksin', 'nama_vaksin'], '-');
                    $dosis = $getValue($imunisasi, ['dosis', 'dosis_ke'], '-');
                    $batch = $getValue($imunisasi, ['batch_number', 'no_batch', 'nomor_batch'], '-');
                    $keterangan = $getValue($imunisasi, ['keterangan', 'catatan'], '-');
                @endphp

                <article class="mb-3 rounded-[22px] border border-cyan-100 bg-cyan-50/60 p-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div class="min-w-0">
                            <h3 class="text-base font-black text-slate-900">
                                {{ $jenisImunisasi }}
                            </h3>

                            <p class="mt-1 text-sm font-semibold text-slate-500">
                                {{ $tanggalImunisasi }}
                            </p>
                        </div>

                        <span class="inline-flex w-fit items-center gap-2 rounded-full bg-white px-3 py-1 text-[11px] font-black text-cyan-700 ring-1 ring-cyan-100">
                            <i class="ph ph-syringe"></i>
                            {{ $vaksin }}
                        </span>
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-3">
                        <div class="rounded-2xl bg-white p-3 ring-1 ring-cyan-100">
                            <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                                Dosis
                            </p>

                            <p class="mt-2 text-sm font-black text-slate-900">
                                {{ $dosis }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-white p-3 ring-1 ring-cyan-100">
                            <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                                Batch
                            </p>

                            <p class="mt-2 text-sm font-black text-slate-900">
                                {{ $batch }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-white p-3 ring-1 ring-cyan-100">
                            <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                                Catatan
                            </p>

                            <p class="mt-2 truncate text-sm font-black text-slate-900">
                                {{ $keterangan }}
                            </p>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-cyan-200 bg-cyan-50/60 px-6 py-10 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-3xl bg-white text-cyan-600 shadow-sm">
                        <i class="ph ph-syringe text-2xl"></i>
                    </div>

                    <h3 class="mt-4 text-base font-black text-slate-800">
                        Riwayat Imunisasi Belum Ada
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
                        Data imunisasi akan tampil setelah layanan imunisasi Balita dicatat.
                    </p>
                </div>
            @endforelse
        </section>
    @endif

    {{-- ACTION --}}
    <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
        <a href="{{ route('bidan.rekam-medis.index', ['type' => $pasienType]) }}"
           class="inline-flex min-h-[46px] w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
            Kembali ke Direktori Rekam Medis
        </a>
    </section>
</div>
@endsection