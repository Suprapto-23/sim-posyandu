@extends('layouts.bidan')

@section('title', 'Detail Pemeriksaan Klinis')
@section('page-name', 'Detail Pemeriksaan')
@section('page-title', 'Detail Pemeriksaan Klinis')

@php
    use Carbon\Carbon;

    $kategori = $kategori ?? data_get($ringkasan ?? [], 'kategori', 'balita');
    $parameter = collect($parameter ?? []);
    $ringkasan = $ringkasan ?? [];

    $kategoriMeta = [
        'balita' => [
            'label' => 'Balita',
            'icon' => 'ph-baby',
            'badge' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'iconBox' => 'bg-sky-50 text-sky-700 ring-sky-100',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'icon' => 'ph-user-focus',
            'badge' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
            'iconBox' => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'icon' => 'ph-heartbeat',
            'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'iconBox' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        ],
    ];

    $meta = $kategoriMeta[$kategori] ?? $kategoriMeta['balita'];

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

    $formatParameterValue = function ($value) {
        if ($value === null || $value === '' || $value === '-') {
            return 'Belum diisi';
        }

        return $value;
    };

    $isEmptyParameter = function ($value) {
        return $value === null || $value === '' || $value === '-' || $value === 'Belum diisi';
    };

    $statusRaw = strtolower((string) data_get($pemeriksaan, 'status_verifikasi', ''));
    $isVerified = in_array($statusRaw, ['verified', 'tervalidasi', 'approved'], true);

    $statusLabel = $isVerified ? 'Tervalidasi' : 'Menunggu Validasi';

    $statusTheme = $isVerified
        ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
        : 'bg-amber-50 text-amber-700 ring-amber-200';

    $statusIcon = $isVerified ? 'ph-check-circle' : 'ph-clock-countdown';

    $backTab = $isVerified ? 'verified' : 'pending';

    $pasienNama = data_get($ringkasan, 'nama_pasien')
        ?? $getValue($pasien, ['nama_lengkap', 'nama', 'nama_balita', 'nama_remaja', 'nama_lansia'], 'Nama tidak tersedia');

    $pasienNik = data_get($ringkasan, 'nik_pasien')
        ?? $getValue($pasien, ['nik', 'nik_anak'], '-');

    $jenisKelamin = strtolower((string) $getValue($pasien, ['jenis_kelamin', 'jk', 'gender'], '-'));

    $jenisKelamin = match ($jenisKelamin) {
        'l', 'laki-laki', 'laki laki', 'male' => 'Laki-laki',
        'p', 'perempuan', 'female' => 'Perempuan',
        default => $jenisKelamin === '-' ? '-' : ucfirst($jenisKelamin),
    };

    $tanggalLahir = $formatDate($getValue($pasien, ['tanggal_lahir', 'tgl_lahir', 'lahir'], null));

    $alamat = $getValue($pasien, ['alamat', 'alamat_lengkap', 'dusun'], '-');

    $tanggalKunjungan = data_get($ringkasan, 'tanggal_kunjungan')
        ?? $formatDate(data_get($pemeriksaan, 'kunjungan.tanggal_kunjungan') ?? data_get($pemeriksaan, 'created_at'));

    $tanggalPeriksa = data_get($ringkasan, 'tanggal_periksa')
        ?? $formatDate(data_get($pemeriksaan, 'tanggal_periksa') ?? data_get($pemeriksaan, 'updated_at') ?? data_get($pemeriksaan, 'created_at'));

    $waktuInput = $formatDateTime(data_get($pemeriksaan, 'created_at'));
    $waktuUpdate = $formatDateTime(data_get($pemeriksaan, 'updated_at'));

    $petugasInput = data_get($ringkasan, 'petugas_input')
        ?? data_get($pemeriksaan, 'pemeriksa.name')
        ?? data_get($pemeriksaan, 'pemeriksa.nama')
        ?? data_get($pemeriksaan, 'kunjungan.petugas.name')
        ?? data_get($pemeriksaan, 'kunjungan.petugas.nama')
        ?? '-';

    $bidanValidasi = data_get($ringkasan, 'bidan_validasi')
        ?? data_get($pemeriksaan, 'verifikator.name')
        ?? data_get($pemeriksaan, 'verifikator.nama')
        ?? data_get($pemeriksaan, 'verifikatorLegacy.name')
        ?? data_get($pemeriksaan, 'verifikatorLegacy.nama')
        ?? '-';

    $statusRingkasLabel = $kategori === 'balita'
        ? 'Status Gizi'
        : 'Status Ringkas Pemeriksaan';

    $statusRingkas = $getValue($pemeriksaan, ['status_gizi'], '-');

    $kesimpulan = $getValue($pemeriksaan, [
        'kesimpulan_pemeriksaan',
        'diagnosa',
        'catatan_bidan',
    ], '-');

    $tindakan = $getValue($pemeriksaan, [
        'tindakan',
        'tindakan_lanjut',
        'layanan',
    ], '-');

    $edukasi = $getValue($pemeriksaan, [
        'catatan_edukasi',
        'edukasi',
        'edukasi_kesehatan',
    ], '-');

    $summaryCards = [
        [
            'label' => 'Kategori',
            'value' => $meta['label'],
            'icon' => $meta['icon'],
            'class' => $meta['iconBox'],
        ],
        [
            'label' => 'Status',
            'value' => $statusLabel,
            'icon' => $statusIcon,
            'class' => $statusTheme,
        ],
        [
            'label' => 'Tanggal Pemeriksaan',
            'value' => $tanggalKunjungan,
            'icon' => 'ph-calendar-check',
            'class' => 'bg-sky-50 text-sky-700 ring-sky-100',
        ],
        [
            'label' => 'Petugas Input',
            'value' => $petugasInput,
            'icon' => 'ph-user-circle',
            'class' => 'bg-slate-50 text-slate-700 ring-slate-100',
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

    .parameter-card {
        min-height: 76px;
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
<div class="nexus-font nexus-page-enter space-y-4 pb-8 text-slate-800">

    {{-- HEADER --}}
    <section class="nexus-panel-enter rounded-[24px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0">
                <a href="{{ route('bidan.pemeriksaan.index', ['tab' => $backTab]) }}"
                   class="mb-4 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-600 transition hover:bg-slate-50 hover:text-emerald-700">
                    <i class="ph ph-arrow-left"></i>
                    Kembali
                </a>

                <div class="inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-[11px] font-black uppercase tracking-[0.14em] ring-1 {{ $statusTheme }}">
                    <i class="ph {{ $statusIcon }} text-sm"></i>
                    {{ $statusLabel }}
                </div>

                <h1 class="mt-3 text-[24px] font-black leading-tight tracking-[-0.025em] text-slate-900 md:text-[28px]">
                    Detail Pemeriksaan Klinis
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Halaman ini menampilkan detail pemeriksaan kesehatan sasaran Posyandu secara read-only.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-xs font-black ring-1 {{ $meta['badge'] }}">
                    <i class="ph {{ $meta['icon'] }}"></i>
                    {{ $meta['label'] }}
                </span>

                @unless($isVerified)
                    <a href="{{ route('bidan.pemeriksaan.validasi', $pemeriksaan->id) }}"
                       class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-black text-white transition hover:bg-emerald-700">
                        <i class="ph ph-check-circle"></i>
                        Validasi Pemeriksaan
                    </a>
                @endunless
            </div>
        </div>
    </section>

    {{-- SUMMARY --}}
    <section class="nexus-panel-enter grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        @foreach($summaryCards as $card)
            <div class="rounded-[20px] border border-white/80 bg-white/85 p-4 shadow-sm shadow-slate-200/60 backdrop-blur">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-[11px] font-black uppercase tracking-[0.12em] text-slate-400">
                            {{ $card['label'] }}
                        </p>

                        <p class="mt-2 truncate text-sm font-black text-slate-900">
                            {{ $card['value'] }}
                        </p>
                    </div>

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $card['class'] }}">
                        <i class="ph {{ $card['icon'] }} text-base"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </section>

    {{-- IDENTITAS SASARAN --}}
    <section class="nexus-panel-enter rounded-[24px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.14em] text-emerald-600">
                    Identitas Sasaran
                </p>

                <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                    Data Pasien
                </h2>
            </div>

            <div class="flex h-10 w-10 items-center justify-center rounded-2xl ring-1 {{ $meta['iconBox'] }}">
                <i class="ph {{ $meta['icon'] }} text-base"></i>
            </div>
        </div>

        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Nama
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $pasienNama }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    NIK
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $pasienNik }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Jenis Kelamin
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $jenisKelamin }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Tanggal Lahir
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $tanggalLahir }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4 xl:col-span-4">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Alamat
                </p>

                <p class="mt-2 line-clamp-2 text-sm font-black text-slate-900">
                    {{ $alamat }}
                </p>
            </div>
        </div>
    </section>

    {{-- PARAMETER --}}
    <section class="nexus-panel-enter rounded-[24px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.14em] text-emerald-600">
                    Data Pemeriksaan
                </p>

                <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                    Parameter Pemeriksaan
                </h2>

                <p class="mt-1 text-xs font-semibold text-slate-500">
                    Parameter berikut berasal dari data pemeriksaan yang dicatat oleh Kader.
                </p>
            </div>

            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                <i class="ph ph-clipboard-text text-base"></i>
            </div>
        </div>

        @if($parameter->count() > 0)
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach($parameter as $item)
                    @php
                        $value = $formatParameterValue(data_get($item, 'value', '-'));
                        $empty = $isEmptyParameter($value);
                    @endphp

                    <div class="parameter-card rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-slate-500 ring-1 ring-slate-100">
                                <i class="ph {{ data_get($item, 'icon', 'ph-dot') }} text-base"></i>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                                    {{ data_get($item, 'label', '-') }}
                                </p>

                                <p class="mt-1.5 truncate text-sm font-black {{ $empty ? 'text-slate-400' : 'text-slate-900' }}">
                                    {{ $value }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                    <i class="ph ph-folder-simple-dashed text-2xl"></i>
                </div>

                <h3 class="mt-4 text-base font-black text-slate-800">
                    Parameter Belum Tersedia
                </h3>

                <p class="mt-2 text-sm text-slate-500">
                    Data parameter pemeriksaan belum tersedia pada catatan ini.
                </p>
            </div>
        @endif
    </section>

    {{-- HASIL VALIDASI --}}
    <section class="nexus-panel-enter rounded-[24px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.14em] text-emerald-600">
                    Hasil Validasi
                </p>

                <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                    Catatan Bidan
                </h2>

                <p class="mt-1 text-xs font-semibold text-slate-500">
                    Bagian ini menampilkan hasil validasi Bidan setelah pemeriksaan disahkan.
                </p>
            </div>

            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                <i class="ph ph-note-pencil text-base"></i>
            </div>
        </div>

        @if($isVerified)
            <div class="grid gap-3">
                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                        {{ $statusRingkasLabel }}
                    </p>

                    <p class="mt-2 text-sm font-black text-slate-900">
                        {{ $statusRingkas }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                        Kesimpulan Pemeriksaan
                    </p>

                    <p class="mt-2 text-sm font-semibold leading-7 text-slate-700">
                        {{ $kesimpulan }}
                    </p>
                </div>

                <div class="grid gap-3 lg:grid-cols-2">
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                            Tindakan / Layanan
                        </p>

                        <p class="mt-2 text-sm font-semibold leading-7 text-slate-700">
                            {{ $tindakan }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                            Edukasi Kesehatan
                        </p>

                        <p class="mt-2 text-sm font-semibold leading-7 text-slate-700">
                            {{ $edukasi }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.12em] text-emerald-700">
                            Bidan Validasi
                        </p>

                        <p class="mt-2 text-sm font-black text-slate-900">
                            {{ $bidanValidasi }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.12em] text-emerald-700">
                            Waktu Pembaruan
                        </p>

                        <p class="mt-2 text-sm font-black text-slate-900">
                            {{ $waktuUpdate }}
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-2xl border border-amber-100 bg-amber-50/70 p-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="flex gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white text-amber-700 ring-1 ring-amber-100">
                            <i class="ph ph-clock-countdown text-lg"></i>
                        </div>

                        <div>
                            <h3 class="text-base font-black text-slate-900">
                                Pemeriksaan Menunggu Validasi
                            </h3>

                            <p class="mt-1 text-sm leading-6 text-slate-600">
                                Data ini belum disahkan oleh Bidan. Lakukan validasi agar hasil pemeriksaan dapat masuk ke arsip Rekam Medis.
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('bidan.pemeriksaan.validasi', $pemeriksaan->id) }}"
                       class="inline-flex min-h-[44px] shrink-0 items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                        <i class="ph ph-check-circle"></i>
                        Validasi Pemeriksaan
                    </a>
                </div>
            </div>
        @endif
    </section>

    {{-- INFORMASI SISTEM --}}
    <section class="nexus-panel-enter rounded-[24px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="grid gap-3 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Waktu Input
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $waktuInput }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Tanggal Periksa
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $tanggalPeriksa }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Status Data
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $statusLabel }}
                </p>
            </div>
        </div>
    </section>

    {{-- ACTION --}}
    <section class="nexus-panel-enter rounded-[24px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
        <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
            <a href="{{ route('bidan.pemeriksaan.index', ['tab' => $backTab]) }}"
               class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                Kembali ke Daftar Pemeriksaan
            </a>

            @unless($isVerified)
                <a href="{{ route('bidan.pemeriksaan.validasi', $pemeriksaan->id) }}"
                   class="inline-flex min-h-[46px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                    <i class="ph ph-check-circle"></i>
                    Validasi Pemeriksaan
                </a>
            @endunless
        </div>
    </section>
</div>
@endsection