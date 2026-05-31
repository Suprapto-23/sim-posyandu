@extends('layouts.bidan')

@section('title', 'Detail Imunisasi Balita')
@section('page-name', 'Imunisasi')
@section('page-title', 'Detail Imunisasi Balita')

@php
    use Carbon\Carbon;

    $programOptions = $programOptions ?? [];

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

    $balita = $balita
        ?? data_get($imunisasi, 'kunjungan.pasien')
        ?? data_get($imunisasi, 'balita');

    $namaBalita = $balita
        ? $getValue($balita, ['nama_lengkap', 'nama', 'nama_balita'], 'Balita tidak terdata')
        : $getValue($imunisasi, ['nama_balita', 'nama_pasien'], 'Balita tidak terdata');

    $nikBalita = $balita
        ? $getValue($balita, ['nik', 'nik_anak'], '-')
        : $getValue($imunisasi, ['nik', 'nik_balita', 'nik_anak'], '-');

    $waliBalita = $balita
        ? $getValue($balita, ['nama_ibu', 'nama_ayah', 'nama_wali'], '-')
        : $getValue($imunisasi, ['nama_ibu', 'nama_ayah', 'nama_wali'], '-');

    $alamatBalita = $balita
        ? $getValue($balita, ['alamat', 'alamat_lengkap', 'dusun'], '-')
        : $getValue($imunisasi, ['alamat', 'alamat_balita'], '-');

    $jenisKelamin = $balita
        ? strtolower((string) $getValue($balita, ['jenis_kelamin', 'jk', 'gender'], '-'))
        : '-';

    $jenisKelamin = match ($jenisKelamin) {
        'l', 'laki-laki', 'laki laki', 'male' => 'Laki-laki',
        'p', 'perempuan', 'female' => 'Perempuan',
        default => $jenisKelamin === '-' ? '-' : ucfirst($jenisKelamin),
    };

    $tanggalLahir = $balita
        ? $formatDate($getValue($balita, ['tanggal_lahir', 'tgl_lahir', 'lahir'], null))
        : '-';

    $jenisImunisasi = $getValue($imunisasi, ['jenis_imunisasi', 'nama_imunisasi', 'jenis'], 'Imunisasi');
    $vaksin = $getValue($imunisasi, ['vaksin', 'nama_vaksin'], '-');
    $dosis = $getValue($imunisasi, ['dosis', 'dosis_ke'], '-');
    $batch = $getValue($imunisasi, ['batch_number', 'no_batch', 'nomor_batch'], '-');
    $tanggalImunisasiRaw = $getValue($imunisasi, ['tanggal_imunisasi', 'tanggal', 'created_at'], null);
    $tanggalImunisasi = $formatDate($tanggalImunisasiRaw);

    $catatan = $getValue($imunisasi, ['keterangan', 'catatan'], '-');

    $petugas = data_get($imunisasi, 'kunjungan.petugas.name')
        ?? data_get($imunisasi, 'kunjungan.petugas.nama')
        ?? data_get($imunisasi, 'kunjungan.petugas.nama_lengkap')
        ?? '-';

    $tanggalInput = $formatDateTime(
        data_get($imunisasi, 'created_at')
    );

    $tanggalPerbarui = $formatDateTime(
        data_get($imunisasi, 'updated_at')
    );

    $summaryCards = [
        [
            'label' => 'Jenis Imunisasi',
            'value' => $jenisImunisasi,
            'icon' => 'ph-syringe',
            'class' => 'bg-cyan-50 text-cyan-700 ring-cyan-100',
        ],
        [
            'label' => 'Nama Vaksin',
            'value' => $vaksin,
            'icon' => 'ph-first-aid-kit',
            'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        ],
        [
            'label' => 'Dosis',
            'value' => $dosis,
            'icon' => 'ph-drop',
            'class' => 'bg-sky-50 text-sky-700 ring-sky-100',
        ],
        [
            'label' => 'Tanggal Layanan',
            'value' => $tanggalImunisasi,
            'icon' => 'ph-calendar-check',
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
                <a href="{{ route('bidan.imunisasi.index') }}"
                   class="mb-4 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-600 transition hover:bg-slate-50 hover:text-emerald-700">
                    <i class="ph ph-arrow-left"></i>
                    Kembali
                </a>

                <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.16em] text-cyan-700">
                    <i class="ph ph-syringe text-base"></i>
                    Detail Catatan Imunisasi
                </div>

                <h1 class="mt-4 max-w-4xl text-[26px] font-black leading-tight tracking-[-0.025em] text-slate-900 md:text-[30px]">
                    {{ $namaBalita }}
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Halaman ini menampilkan detail catatan layanan imunisasi Balita yang sudah tersimpan di sistem.
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3.5 py-2 text-xs font-black text-cyan-700">
                        <i class="ph ph-baby"></i>
                        Sasaran Balita
                    </span>

                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-black text-slate-500">
                        <i class="ph ph-identification-card"></i>
                        {{ $nikBalita }}
                    </span>
                </div>
            </div>

            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row xl:pt-12">
                <a href="{{ route('bidan.imunisasi.edit', $imunisasi->id) }}"
                   class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-700">
                    <i class="ph ph-pencil-simple"></i>
                    Perbaiki Catatan
                </a>
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

    {{-- OUTPUT INFO --}}
    <section class="nexus-panel-enter rounded-[22px] border border-cyan-100 bg-cyan-50/70 p-4">
        <div class="flex gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-cyan-700 ring-1 ring-cyan-100">
                <i class="ph ph-info text-lg"></i>
            </div>

            <div>
                <h2 class="text-sm font-black text-slate-900">
                    Output Detail Imunisasi
                </h2>

                <p class="mt-1 text-sm leading-6 text-slate-600">
                    Detail ini berisi identitas Balita, data layanan imunisasi, tanggal layanan, petugas, nomor batch, dan catatan tambahan.
                </p>
            </div>
        </div>
    </section>

    {{-- IDENTITAS BALITA --}}
    <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="mb-5 flex items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-cyan-600">
                    Identitas Sasaran
                </p>

                <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                    Data Balita
                </h2>
            </div>

            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                <i class="ph ph-baby text-lg"></i>
            </div>
        </div>

        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Nama Balita
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $namaBalita }}
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    NIK
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $nikBalita }}
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Jenis Kelamin
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $jenisKelamin }}
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Tanggal Lahir
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $tanggalLahir }}
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Wali
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $waliBalita }}
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100 md:col-span-3">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Alamat
                </p>

                <p class="mt-2 line-clamp-2 text-sm font-black text-slate-900">
                    {{ $alamatBalita }}
                </p>
            </div>
        </div>
    </section>

    {{-- DETAIL LAYANAN --}}
    <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="mb-5 flex items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-cyan-600">
                    Data Layanan
                </p>

                <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                    Informasi Imunisasi
                </h2>
            </div>

            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700 ring-1 ring-cyan-100">
                <i class="ph ph-syringe text-lg"></i>
            </div>
        </div>

        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Jenis Imunisasi
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $jenisImunisasi }}
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Nama Vaksin
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $vaksin }}
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Dosis
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $dosis }}
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Nomor Batch
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $batch }}
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Tanggal Imunisasi
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $tanggalImunisasi }}
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Petugas
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $petugas }}
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Waktu Input
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $tanggalInput }}
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                    Diperbarui
                </p>

                <p class="mt-2 truncate text-sm font-black text-slate-900">
                    {{ $tanggalPerbarui }}
                </p>
            </div>
        </div>
    </section>

    {{-- CATATAN --}}
    <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="mb-5 flex items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.16em] text-cyan-600">
                    Catatan
                </p>

                <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                    Catatan Tambahan
                </h2>
            </div>

            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                <i class="ph ph-note-pencil text-lg"></i>
            </div>
        </div>

        <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
            <p class="text-sm font-semibold leading-7 text-slate-600">
                {{ $catatan }}
            </p>
        </div>
    </section>

    {{-- ACTION --}}
    <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
        <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
            <a href="{{ route('bidan.imunisasi.index') }}"
               class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                Kembali ke Daftar Imunisasi
            </a>

            <a href="{{ route('bidan.imunisasi.edit', $imunisasi->id) }}"
               class="inline-flex min-h-[46px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                <i class="ph ph-pencil-simple"></i>
                Perbaiki Catatan
            </a>
        </div>
    </section>
</div>
@endsection