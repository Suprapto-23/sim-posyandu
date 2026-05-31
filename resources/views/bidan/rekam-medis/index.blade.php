@extends('layouts.bidan')

@section('title', 'Rekam Medis')
@section('page-name', 'Rekam Medis')
@section('page-title', 'Rekam Medis')

@php
    $data = $data ?? collect();
    $type = $type ?? request('type', 'balita');
    $search = $search ?? request('search', '');

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

    $stats = $stats ?? [
        'total' => [
            'balita' => 0,
            'remaja' => 0,
            'lansia' => 0,
        ],
        'verified' => [
            'balita' => 0,
            'remaja' => 0,
            'lansia' => 0,
        ],
        'total_semua' => 0,
        'verified_semua' => 0,
    ];

    $getValue = function ($item, array $keys, mixed $default = '-') {
        foreach ($keys as $key) {
            $value = data_get($item, $key);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $default;
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

    $getWali = function ($item) use ($getValue, $type) {
        if ($type === 'balita') {
            return $getValue($item, ['nama_ibu', 'nama_ayah', 'nama_wali'], '-');
        }

        if ($type === 'remaja') {
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

    $formatDate = function ($date) {
        if (!$date) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($date)->translatedFormat('d M Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $getTanggalLahir = function ($item) use ($getValue, $formatDate) {
        return $formatDate($getValue($item, ['tanggal_lahir', 'tgl_lahir', 'lahir'], null));
    };

    $themeMeta = function ($key) {
        return match ($key) {
            'remaja' => [
                'badge' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
                'iconBox' => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
                'active' => 'border-indigo-200 bg-indigo-50/80 text-indigo-700',
            ],
            'lansia' => [
                'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'iconBox' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                'active' => 'border-emerald-200 bg-emerald-50/80 text-emerald-700',
            ],
            default => [
                'badge' => 'bg-sky-50 text-sky-700 ring-sky-200',
                'iconBox' => 'bg-sky-50 text-sky-700 ring-sky-100',
                'active' => 'border-sky-200 bg-sky-50/80 text-sky-700',
            ],
        };
    };

    $currentTypeMeta = $typeOptions[$type] ?? $typeOptions['balita'];
    $currentTheme = $themeMeta($type);

    $totalCurrent = (int) data_get($stats, "total.$type", 0);
    $verifiedCurrent = (int) data_get($stats, "verified.$type", 0);

    $visibleCount = method_exists($data, 'count') ? $data->count() : count($data);

    $summaryCards = [
        [
            'label' => 'Total Sasaran',
            'value' => $totalCurrent,
            'icon' => $currentTypeMeta['icon'] ?? 'ph-users-three',
            'class' => $currentTheme['iconBox'],
        ],
        [
            'label' => 'Rekam Medis',
            'value' => $verifiedCurrent,
            'icon' => 'ph-folder-simple-user',
            'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        ],
        [
            'label' => 'Semua Sasaran',
            'value' => data_get($stats, 'total_semua', 0),
            'icon' => 'ph-users-three',
            'class' => 'bg-slate-50 text-slate-700 ring-slate-100',
        ],
        [
            'label' => 'Total Tervalidasi',
            'value' => data_get($stats, 'verified_semua', 0),
            'icon' => 'ph-check-circle',
            'class' => 'bg-cyan-50 text-cyan-700 ring-cyan-100',
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

    .nexus-live-hidden {
        display: none !important;
    }

    .nexus-list-stable {
        min-height: 420px;
        contain: layout paint;
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

        .nexus-list-stable {
            min-height: 320px;
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
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.16em] text-emerald-700">
                    <i class="ph ph-folder-simple-user text-base"></i>
                    Arsip Kesehatan
                </div>

                <h1 class="mt-4 max-w-4xl text-[26px] font-black leading-tight tracking-[-0.025em] text-slate-900 md:text-[30px]">
                    Rekam Medis {{ $currentTypeMeta['label'] ?? ucfirst($type) }}
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Akses ringkas riwayat pemeriksaan tervalidasi untuk Balita, Remaja, dan Lansia. Data imunisasi hanya ditampilkan pada sasaran Balita.
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-xs font-black ring-1 {{ $currentTheme['badge'] }}">
                        <i class="ph {{ $currentTypeMeta['icon'] ?? 'ph-users-three' }}"></i>
                        {{ $currentTypeMeta['label'] ?? ucfirst($type) }}
                    </span>

                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-black text-slate-500">
                        <i class="ph ph-database"></i>
                        <span id="rekamMedisVisibleCount">{{ $visibleCount }}</span>
                        data tampil
                    </span>

                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-black text-slate-500">
                        <i class="ph ph-magnifying-glass"></i>
                        Live search: nama / NIK
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

                        <h2 class="mt-2 line-clamp-1 text-xl font-black tracking-tight text-slate-900">
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

    {{-- TYPE SELECTOR --}}
    <section class="nexus-panel-enter grid gap-3 md:grid-cols-3">
        @foreach($typeOptions as $key => $option)
            @php
                $theme = $themeMeta($key);
                $active = $type === $key;
            @endphp

            <a href="{{ route('bidan.rekam-medis.index', ['type' => $key]) }}"
               class="rounded-[22px] border p-4 shadow-sm shadow-slate-200/60 transition hover:-translate-y-0.5 hover:shadow-md {{ $active ? $theme['active'] : 'border-white/80 bg-white/85 text-slate-600 hover:bg-slate-50' }}">
                <div class="flex gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $theme['iconBox'] }}">
                        <i class="ph {{ $option['icon'] }} text-lg"></i>
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-black text-slate-900">
                                {{ $option['label'] }}
                            </h3>

                            <span class="rounded-full bg-white/70 px-2 py-0.5 text-[10px] font-black text-slate-500 ring-1 ring-slate-100">
                                {{ data_get($stats, "total.$key", 0) }}
                            </span>
                        </div>

                        <p class="mt-1 line-clamp-2 text-xs font-semibold leading-5 text-slate-500">
                            {{ $option['desc'] }}
                        </p>
                    </div>
                </div>
            </a>
        @endforeach
    </section>

    {{-- CONTENT --}}
    <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="mb-5 grid gap-4 xl:grid-cols-[170px_minmax(0,1fr)] xl:items-center">
            <div class="min-w-0">
                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-emerald-600">
                    Direktori
                </p>

                <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                    Data {{ $currentTypeMeta['label'] ?? ucfirst($type) }}
                </h2>
            </div>

            <form method="GET"
                  action="{{ route('bidan.rekam-medis.index') }}"
                  class="grid w-full gap-2 md:grid-cols-[1fr_auto_auto]">
                <input type="hidden" name="type" value="{{ $type }}">

                <div class="relative">
                    <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>

                    <input type="text"
                           id="rekamMedisLiveSearch"
                           name="search"
                           value="{{ $search }}"
                           autocomplete="off"
                           spellcheck="false"
                           inputmode="search"
                           placeholder="Cari nama atau NIK..."
                           class="min-h-[46px] w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-11 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">

                    <button type="button"
                            id="rekamMedisClearSearch"
                            class="absolute right-3 top-1/2 hidden h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                        <i class="ph ph-x text-sm"></i>
                    </button>
                </div>

                <button type="submit"
                        class="inline-flex min-h-[46px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                    <i class="ph ph-funnel"></i>
                    Filter
                </button>

                @if($search)
                    <a href="{{ route('bidan.rekam-medis.index', ['type' => $type]) }}"
                       class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="nexus-list-stable">

            {{-- DESKTOP TABLE --}}
            <div class="nexus-scroll hidden max-h-[620px] overflow-auto lg:block">
                <table class="min-w-[1100px] w-full border-separate border-spacing-y-3">
                    <thead class="sticky top-0 z-10 bg-white/95 backdrop-blur">
                        <tr class="text-left text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">
                            <th class="px-4 py-3">Pasien</th>
                            <th class="px-4 py-3">NIK</th>
                            <th class="px-4 py-3">Jenis Kelamin</th>
                            <th class="px-4 py-3">Tanggal Lahir</th>
                            <th class="px-4 py-3">Wali / Kontak</th>
                            <th class="px-4 py-3">Alamat</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($data as $pasien)
                            @php
                                $nama = $getNama($pasien);
                                $nik = $getNik($pasien);
                                $alamat = $getAlamat($pasien);
                                $kontak = $getKontak($pasien);
                                $wali = $getWali($pasien);
                                $gender = $getGender($pasien);
                                $tanggalLahir = $getTanggalLahir($pasien);

                                $searchName = mb_strtolower(trim((string) $nama), 'UTF-8');
                                $searchNik = mb_strtolower(trim((string) $nik), 'UTF-8');
                            @endphp

                            <tr class="js-rekam-row"
                                data-name="{{ $searchName }}"
                                data-nik="{{ $searchNik }}">
                                <td class="rounded-l-2xl border-y border-l border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $currentTheme['iconBox'] }}">
                                            <i class="ph {{ $currentTypeMeta['icon'] ?? 'ph-user' }} text-lg"></i>
                                        </div>

                                        <div class="min-w-0">
                                            <p class="max-w-[240px] truncate text-sm font-black text-slate-900">
                                                {{ $nama }}
                                            </p>

                                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                                {{ $currentTypeMeta['label'] ?? ucfirst($type) }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <p class="whitespace-nowrap text-sm font-black text-slate-700">
                                        {{ $nik }}
                                    </p>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-black text-slate-600 ring-1 ring-slate-200">
                                        {{ $gender }}
                                    </span>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <p class="whitespace-nowrap text-sm font-bold text-slate-600">
                                        {{ $tanggalLahir }}
                                    </p>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <div class="min-w-0">
                                        <p class="max-w-[180px] truncate text-sm font-black text-slate-700">
                                            {{ $wali }}
                                        </p>

                                        <p class="mt-1 max-w-[180px] truncate text-xs font-semibold text-slate-500">
                                            {{ $kontak }}
                                        </p>
                                    </div>
                                </td>

                                <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <p class="max-w-[250px] truncate text-sm font-semibold text-slate-500">
                                        {{ $alamat }}
                                    </p>
                                </td>

                                <td class="rounded-r-2xl border-y border-r border-slate-100 bg-slate-50/80 px-4 py-4">
                                    <div class="flex justify-end">
                                        <a href="{{ route('bidan.rekam-medis.show', [$type, $pasien->id]) }}"
                                           class="inline-flex min-h-[40px] items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-700">
                                            Detail
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                                        <i class="ph ph-folder-simple-dashed text-3xl"></i>
                                    </div>

                                    <h3 class="mt-4 text-base font-black text-slate-800">
                                        Data Tidak Ditemukan
                                    </h3>

                                    <p class="mt-2 text-sm text-slate-500">
                                        Belum ada data {{ strtolower($currentTypeMeta['label'] ?? $type) }} yang cocok dengan pencarian.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MOBILE CARD --}}
            <div class="space-y-3 lg:hidden">
                @forelse($data as $pasien)
                    @php
                        $nama = $getNama($pasien);
                        $nik = $getNik($pasien);
                        $alamat = $getAlamat($pasien);
                        $kontak = $getKontak($pasien);
                        $wali = $getWali($pasien);
                        $gender = $getGender($pasien);
                        $tanggalLahir = $getTanggalLahir($pasien);

                        $searchName = mb_strtolower(trim((string) $nama), 'UTF-8');
                        $searchNik = mb_strtolower(trim((string) $nik), 'UTF-8');
                    @endphp

                    <article class="js-rekam-card rounded-2xl border border-slate-100 bg-slate-50/80 p-4"
                             data-name="{{ $searchName }}"
                             data-nik="{{ $searchNik }}">
                        <div class="flex items-start gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $currentTheme['iconBox'] }}">
                                <i class="ph {{ $currentTypeMeta['icon'] ?? 'ph-user' }} text-lg"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="line-clamp-2 text-base font-black text-slate-900">
                                            {{ $nama }}
                                        </h3>

                                        <p class="mt-1 text-xs font-semibold text-slate-500">
                                            NIK: {{ $nik }}
                                        </p>
                                    </div>

                                    <span class="shrink-0 rounded-full px-3 py-1 text-[11px] font-black ring-1 {{ $currentTheme['badge'] }}">
                                        {{ $currentTypeMeta['label'] ?? ucfirst($type) }}
                                    </span>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    <div class="rounded-xl bg-white p-3 ring-1 ring-slate-100">
                                        <p class="text-[10px] font-black uppercase text-slate-400">
                                            Gender
                                        </p>

                                        <p class="mt-1 truncate text-sm font-black text-slate-900">
                                            {{ $gender }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl bg-white p-3 ring-1 ring-slate-100">
                                        <p class="text-[10px] font-black uppercase text-slate-400">
                                            Lahir
                                        </p>

                                        <p class="mt-1 truncate text-sm font-black text-slate-900">
                                            {{ $tanggalLahir }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3 rounded-xl bg-white p-3 ring-1 ring-slate-100">
                                    <p class="text-[10px] font-black uppercase text-slate-400">
                                        Wali / Kontak
                                    </p>

                                    <p class="mt-1 truncate text-sm font-black text-slate-900">
                                        {{ $wali }}
                                    </p>

                                    <p class="mt-1 truncate text-xs font-semibold text-slate-500">
                                        {{ $kontak }}
                                    </p>
                                </div>

                                <p class="mt-3 flex items-center gap-1 truncate text-xs font-semibold text-slate-500">
                                    <i class="ph ph-map-pin"></i>
                                    {{ $alamat }}
                                </p>

                                <a href="{{ route('bidan.rekam-medis.show', [$type, $pasien->id]) }}"
                                   class="mt-4 inline-flex min-h-[42px] w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-700">
                                    Detail Rekam Medis
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                            <i class="ph ph-folder-simple-dashed text-3xl"></i>
                        </div>

                        <h3 class="mt-4 text-base font-black text-slate-800">
                            Data Tidak Ditemukan
                        </h3>

                        <p class="mt-2 text-sm text-slate-500">
                            Belum ada data {{ strtolower($currentTypeMeta['label'] ?? $type) }} yang cocok dengan pencarian.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- LIVE EMPTY --}}
            <div id="rekamMedisLiveEmpty"
                 class="hidden rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                    <i class="ph ph-magnifying-glass text-3xl"></i>
                </div>

                <h3 class="mt-4 text-base font-black text-slate-800">
                    Data Tidak Cocok
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Tidak ada data pada halaman ini yang sesuai dengan nama atau NIK.
                </p>
            </div>

            {{-- PAGINATION --}}
            @if(method_exists($data, 'hasPages') && $data->hasPages())
                <div id="rekamMedisPagination" class="mt-5 flex flex-col gap-3 border-t border-slate-100 pt-5 md:flex-row md:items-center md:justify-between">
                    <p class="text-sm font-semibold text-slate-500">
                        Menampilkan {{ $data->firstItem() }} sampai {{ $data->lastItem() }} dari {{ $data->total() }} data
                    </p>

                    <div>
                        {{ $data->links() }}
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const searchInput = document.getElementById('rekamMedisLiveSearch');
        const clearButton = document.getElementById('rekamMedisClearSearch');
        const visibleCountText = document.getElementById('rekamMedisVisibleCount');
        const liveEmpty = document.getElementById('rekamMedisLiveEmpty');

        const rows = Array.from(document.querySelectorAll('.js-rekam-row'));
        const cards = Array.from(document.querySelectorAll('.js-rekam-card'));

        const normalize = (value) => {
            return String(value || '')
                .toLowerCase()
                .trim();
        };

        const isNumericKeyword = (keyword) => {
            return /^[0-9]+$/.test(keyword);
        };

        const itemMatches = (item, keyword) => {
            const name = normalize(item.dataset.name);
            const nik = normalize(item.dataset.nik);

            if (keyword === '') {
                return true;
            }

            if (isNumericKeyword(keyword)) {
                return nik.includes(keyword);
            }

            return name.includes(keyword);
        };

        const setHidden = (element, hidden) => {
            if (!element) {
                return;
            }

            element.classList.toggle('nexus-live-hidden', hidden);
        };

        let frameId = null;

        const applyFilter = () => {
            if (frameId) {
                cancelAnimationFrame(frameId);
            }

            frameId = requestAnimationFrame(() => {
                const keyword = normalize(searchInput?.value);
                let visibleCount = 0;

                rows.forEach((row) => {
                    const visible = itemMatches(row, keyword);
                    setHidden(row, !visible);

                    if (visible) {
                        visibleCount += 1;
                    }
                });

                cards.forEach((card) => {
                    const visible = itemMatches(card, keyword);
                    setHidden(card, !visible);
                });

                if (visibleCountText) {
                    visibleCountText.textContent = String(visibleCount);
                }

                if (liveEmpty) {
                    const hasData = rows.length > 0 || cards.length > 0;
                    liveEmpty.classList.toggle('hidden', !hasData || visibleCount > 0);
                }

                if (clearButton) {
                    const hasKeyword = keyword !== '';
                    clearButton.classList.toggle('hidden', !hasKeyword);
                    clearButton.classList.toggle('inline-flex', hasKeyword);
                }
            });
        };

        searchInput?.addEventListener('input', applyFilter, { passive: true });
        searchInput?.addEventListener('keyup', applyFilter, { passive: true });
        searchInput?.addEventListener('search', applyFilter, { passive: true });

        clearButton?.addEventListener('click', () => {
            if (!searchInput) {
                return;
            }

            searchInput.value = '';
            searchInput.focus();
            applyFilter();
        });

        applyFilter();
    })();
</script>
@endpush