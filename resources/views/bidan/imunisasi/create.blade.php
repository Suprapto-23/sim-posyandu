@extends('layouts.bidan')

@section('title', ($mode ?? 'create') === 'edit' ? 'Perbaiki Catatan Imunisasi' : 'Catat Imunisasi Balita')
@section('page-name', 'Imunisasi')
@section('page-title', ($mode ?? 'create') === 'edit' ? 'Perbaiki Catatan Imunisasi' : 'Catat Imunisasi Balita')

@php
    use Carbon\Carbon;

    $mode = $mode ?? 'create';
    $isEdit = $mode === 'edit' && !empty($imunisasi);
    $balitas = collect($balitas ?? []);

    $programOptions = $programOptions ?? [
        'BCG' => 'BCG',
        'Polio' => 'Polio',
        'DPT-HB-Hib' => 'DPT-HB-Hib',
        'Hepatitis B' => 'Hepatitis B',
        'Campak / MR' => 'Campak / MR',
        'IPV' => 'IPV',
        'PCV' => 'PCV',
        'Rotavirus' => 'Rotavirus',
        'Lainnya' => 'Lainnya',
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

    $formatInputDate = function ($date) {
        if (!$date || $date === '-') {
            return now()->format('Y-m-d');
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable $e) {
            return now()->format('Y-m-d');
        }
    };

    $getBalitaName = function ($balita) use ($getValue) {
        return $getValue($balita, ['nama_lengkap', 'nama', 'nama_balita'], 'Nama tidak tersedia');
    };

    $getBalitaNik = function ($balita) use ($getValue) {
        return $getValue($balita, ['nik', 'nik_anak'], '-');
    };

    $getBalitaWali = function ($balita) use ($getValue) {
        return $getValue($balita, ['nama_ibu', 'nama_ayah', 'nama_wali'], '-');
    };

    $getBalitaAlamat = function ($balita) use ($getValue) {
        return $getValue($balita, ['alamat', 'alamat_lengkap', 'dusun'], '-');
    };

    $formAction = $isEdit
        ? route('bidan.imunisasi.update', $imunisasi->id)
        : route('bidan.imunisasi.store');

    $selectedBalitaId = old(
        'balita_id',
        data_get($selectedBalita, 'id')
            ?? data_get($imunisasi, 'balita_id')
            ?? data_get($imunisasi, 'kunjungan.pasien_id')
            ?? ''
    );

    $selectedJenis = old(
        'jenis_imunisasi',
        data_get($imunisasi, 'jenis_imunisasi')
            ?? data_get($imunisasi, 'nama_imunisasi')
            ?? ''
    );

    $selectedVaksin = old(
        'vaksin',
        data_get($imunisasi, 'vaksin')
            ?? data_get($imunisasi, 'nama_vaksin')
            ?? ''
    );

    $selectedDosis = old(
        'dosis',
        data_get($imunisasi, 'dosis')
            ?? data_get($imunisasi, 'dosis_ke')
            ?? ''
    );

    $selectedBatch = old(
        'batch_number',
        data_get($imunisasi, 'batch_number')
            ?? data_get($imunisasi, 'no_batch')
            ?? data_get($imunisasi, 'nomor_batch')
            ?? ''
    );

    $selectedTanggal = old(
        'tanggal_imunisasi',
        $formatInputDate(
            data_get($imunisasi, 'tanggal_imunisasi')
                ?? data_get($imunisasi, 'tanggal')
                ?? null
        )
    );

    $selectedKeterangan = old(
        'keterangan',
        data_get($imunisasi, 'keterangan')
            ?? data_get($imunisasi, 'catatan')
            ?? ''
    );

    $selectedBalita = $selectedBalita ?? $balitas->firstWhere('id', $selectedBalitaId);

    $selectedBalitaName = $selectedBalita ? $getBalitaName($selectedBalita) : '-';
    $selectedBalitaNik = $selectedBalita ? $getBalitaNik($selectedBalita) : '-';
    $selectedBalitaWali = $selectedBalita ? $getBalitaWali($selectedBalita) : '-';
    $selectedBalitaAlamat = $selectedBalita ? $getBalitaAlamat($selectedBalita) : '-';

    $pageTitle = $isEdit ? 'Perbaiki Catatan Imunisasi' : 'Catat Imunisasi Balita';
    $pageDesc = $isEdit
        ? 'Perbaiki catatan imunisasi Balita yang sudah tersimpan.'
        : 'Catat layanan imunisasi untuk sasaran Balita.';

    $submitLabel = $isEdit ? 'Simpan Perbaikan Catatan' : 'Simpan Catatan Imunisasi';
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
        scrollbar-color: rgba(6, 182, 212, .35) transparent;
    }

    .nexus-scroll::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .nexus-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .nexus-scroll::-webkit-scrollbar-thumb {
        background: rgba(6, 182, 212, .35);
        border-radius: 999px;
    }

    .balita-picker-result.is-selected {
        border-color: rgba(6, 182, 212, .45);
        background: rgba(236, 254, 255, .9);
    }

    .balita-picker-result.is-hidden {
        display: none !important;
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
                    Layanan Imunisasi
                </div>

                <h1 class="mt-4 max-w-4xl text-[26px] font-black leading-tight tracking-[-0.025em] text-slate-900 md:text-[30px]">
                    {{ $pageTitle }}
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    {{ $pageDesc }} Data yang disimpan menjadi arsip layanan imunisasi Balita.
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3.5 py-2 text-xs font-black text-cyan-700">
                        <i class="ph ph-baby"></i>
                        Sasaran Balita
                    </span>

                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-black text-slate-500">
                        <i class="ph ph-check-circle"></i>
                        Output: catatan imunisasi
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- OUTPUT RINGKAS --}}
    <section class="nexus-panel-enter rounded-[22px] border border-cyan-100 bg-cyan-50/70 p-4">
        <div class="flex gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white text-cyan-700 ring-1 ring-cyan-100">
                <i class="ph ph-info text-lg"></i>
            </div>

            <div>
                <h2 class="text-sm font-black text-slate-900">
                    Output Halaman
                </h2>

                <p class="mt-1 text-sm leading-6 text-slate-600">
                    Form ini menghasilkan catatan imunisasi Balita yang berisi identitas Balita, jenis imunisasi, vaksin, dosis, nomor batch, tanggal layanan, dan catatan tambahan.
                </p>
            </div>
        </div>
    </section>

    {{-- FORM --}}
    <form method="POST" action="{{ $formAction }}" class="space-y-5">
        @csrf

        @if($isEdit)
            @method('PUT')
        @endif

        <input type="hidden" name="kategori" value="balita">
        <input type="hidden" id="balita_id" name="balita_id" value="{{ $selectedBalitaId }}">

        {{-- PILIH BALITA --}}
        <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
            <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-cyan-600">
                        Langkah 1
                    </p>

                    <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                        Pilih Balita
                    </h2>

                    <p class="mt-1 text-xs font-semibold text-slate-500">
                        Ketik nama atau NIK, lalu klik data Balita yang sesuai.
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                    <i class="ph ph-baby text-lg"></i>
                </div>
            </div>

            @if($balitas->count() > 0)
                <div class="space-y-4">
                    <div>
                        <label for="balitaSearchInput" class="mb-2 block text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                            Pencarian Balita
                        </label>

                        <div class="relative">
                            <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>

                            <input type="text"
                                   id="balitaSearchInput"
                                   autocomplete="off"
                                   spellcheck="false"
                                   inputmode="search"
                                   placeholder="Contoh: Salsa / 3201..."
                                   class="min-h-[48px] w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-11 text-sm font-semibold text-slate-700 outline-none transition focus:border-cyan-300 focus:bg-white focus:ring-4 focus:ring-cyan-100">

                            <button type="button"
                                    id="balitaSearchClear"
                                    class="absolute right-3 top-1/2 hidden h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                                <i class="ph ph-x text-sm"></i>
                            </button>
                        </div>

                        @error('balita_id')
                            <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 xl:grid-cols-[minmax(0,1.15fr)_minmax(320px,.85fr)]">
                        <div class="rounded-[22px] border border-slate-100 bg-slate-50/70 p-3">
                            <div class="mb-3 flex items-center justify-between gap-3 px-1">
                                <p class="text-xs font-black uppercase tracking-[0.12em] text-slate-400">
                                    Hasil Pencarian
                                </p>

                                <p class="text-xs font-black text-cyan-700">
                                    <span id="balitaResultCount">{{ $balitas->count() }}</span> data
                                </p>
                            </div>

                            <div id="balitaResults"
                                 class="nexus-scroll max-h-[360px] space-y-2 overflow-y-auto pr-1">
                                @foreach($balitas as $balita)
                                    @php
    $balitaId = data_get($balita, 'id');
    $balitaNama = $getBalitaName($balita);
    $balitaNik = $getBalitaNik($balita);
    $balitaWali = $getBalitaWali($balita);
    $balitaAlamat = $getBalitaAlamat($balita);

    $searchName = mb_strtolower(trim((string) $balitaNama), 'UTF-8');
    $searchNik = mb_strtolower(trim((string) $balitaNik), 'UTF-8');

    $isSelectedBalita = (string) $selectedBalitaId === (string) $balitaId;
@endphp

                                    <button type="button"
        class="balita-picker-result w-full rounded-2xl border p-3 text-left transition hover:border-cyan-200 hover:bg-cyan-50/80 {{ $isSelectedBalita ? 'is-selected' : 'border-white bg-white' }}"
        data-id="{{ $balitaId }}"
        data-name="{{ $balitaNama }}"
        data-nik="{{ $balitaNik }}"
        data-wali="{{ $balitaWali }}"
        data-alamat="{{ $balitaAlamat }}"
        data-search-name="{{ $searchName }}"
        data-search-nik="{{ $searchNik }}"
        data-order="{{ $loop->index }}">
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                                                <i class="ph ph-baby text-lg"></i>
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                                    <h3 class="truncate text-sm font-black text-slate-900">
                                                        {{ $balitaNama }}
                                                    </h3>

                                                    @if($isSelectedBalita)
                                                        <span class="selected-badge inline-flex w-fit rounded-full bg-cyan-50 px-2.5 py-1 text-[10px] font-black text-cyan-700 ring-1 ring-cyan-200">
                                                            Dipilih
                                                        </span>
                                                    @else
                                                        <span class="selected-badge hidden w-fit rounded-full bg-cyan-50 px-2.5 py-1 text-[10px] font-black text-cyan-700 ring-1 ring-cyan-200">
                                                            Dipilih
                                                        </span>
                                                    @endif
                                                </div>

                                                <p class="mt-1 truncate text-xs font-semibold text-slate-500">
                                                    NIK: {{ $balitaNik }}
                                                </p>

                                                <p class="mt-1 truncate text-xs font-semibold text-slate-500">
                                                    Wali: {{ $balitaWali }}
                                                </p>
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>

                            <div id="balitaEmptyResult"
                                 class="hidden rounded-2xl border border-dashed border-slate-200 bg-white px-5 py-8 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-400">
                                    <i class="ph ph-magnifying-glass text-xl"></i>
                                </div>

                                <h3 class="mt-3 text-sm font-black text-slate-800">
                                    Balita Tidak Ditemukan
                                </h3>

                                <p class="mt-1 text-xs font-semibold text-slate-500">
                                    Periksa kembali nama atau NIK yang diketik.
                                </p>
                            </div>
                        </div>

                        <div class="rounded-[22px] border border-cyan-100 bg-cyan-50/60 p-4">
                            <div class="mb-4 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.14em] text-cyan-700">
                                        Data Terpilih
                                    </p>

                                    <h3 class="mt-1 text-base font-black text-slate-900">
                                        Balita Imunisasi
                                    </h3>
                                </div>

                                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-cyan-700 ring-1 ring-cyan-100">
                                    <i class="ph ph-check-circle text-lg"></i>
                                </div>
                            </div>

                            <div class="grid gap-3">
                                <div class="rounded-2xl bg-white p-4 ring-1 ring-cyan-100">
                                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                                        Nama Balita
                                    </p>

                                    <p id="previewBalitaNama" class="mt-2 truncate text-sm font-black text-slate-900">
                                        {{ $selectedBalitaName }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-white p-4 ring-1 ring-cyan-100">
                                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                                        NIK
                                    </p>

                                    <p id="previewBalitaNik" class="mt-2 truncate text-sm font-black text-slate-900">
                                        {{ $selectedBalitaNik }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-white p-4 ring-1 ring-cyan-100">
                                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                                        Wali
                                    </p>

                                    <p id="previewBalitaWali" class="mt-2 truncate text-sm font-black text-slate-900">
                                        {{ $selectedBalitaWali }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-white p-4 ring-1 ring-cyan-100">
                                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                                        Alamat
                                    </p>

                                    <p id="previewBalitaAlamat" class="mt-2 line-clamp-2 text-sm font-black text-slate-900">
                                        {{ $selectedBalitaAlamat }}
                                    </p>
                                </div>
                            </div>

                            <p id="balitaSelectionStatus" class="mt-4 rounded-2xl bg-white px-4 py-3 text-xs font-black text-cyan-700 ring-1 ring-cyan-100">
                                {{ $selectedBalitaId ? 'Balita sudah dipilih. Lanjutkan mengisi data imunisasi.' : 'Pilih satu Balita dari hasil pencarian.' }}
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                        <i class="ph ph-baby text-2xl"></i>
                    </div>

                    <h3 class="mt-4 text-base font-black text-slate-800">
                        Data Balita Belum Tersedia
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
                        Catatan imunisasi baru dapat dibuat setelah data Balita tersedia.
                    </p>
                </div>
            @endif
        </section>

        {{-- DATA IMUNISASI --}}
        <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-cyan-600">
                        Langkah 2
                    </p>

                    <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                        Isi Data Imunisasi
                    </h2>

                    <p class="mt-1 text-xs font-semibold text-slate-500">
                        Isi berdasarkan layanan imunisasi yang diberikan kepada Balita.
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700 ring-1 ring-cyan-100">
                    <i class="ph ph-syringe text-lg"></i>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="jenis_imunisasi" class="mb-2 block text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                        Jenis Imunisasi
                    </label>

                    <select id="jenis_imunisasi"
                            name="jenis_imunisasi"
                            required
                            class="min-h-[48px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-700 outline-none transition focus:border-cyan-300 focus:bg-white focus:ring-4 focus:ring-cyan-100 @error('jenis_imunisasi') border-red-300 ring-4 ring-red-100 @enderror">
                        <option value="">Pilih jenis imunisasi</option>

                        @foreach($programOptions as $value => $label)
                            @php
                                $optionValue = is_string($value) ? $value : $label;
                            @endphp

                            <option value="{{ $optionValue }}" @selected($selectedJenis === $optionValue)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    @error('jenis_imunisasi')
                        <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="vaksin" class="mb-2 block text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                        Nama Vaksin
                    </label>

                    <input type="text"
                           id="vaksin"
                           name="vaksin"
                           value="{{ $selectedVaksin }}"
                           required
                           maxlength="100"
                           placeholder="Contoh: BCG / Polio / MR"
                           class="min-h-[48px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-700 outline-none transition focus:border-cyan-300 focus:bg-white focus:ring-4 focus:ring-cyan-100 @error('vaksin') border-red-300 ring-4 ring-red-100 @enderror">

                    @error('vaksin')
                        <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="dosis" class="mb-2 block text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                        Dosis
                    </label>

                    <input type="text"
                           id="dosis"
                           name="dosis"
                           value="{{ $selectedDosis }}"
                           required
                           maxlength="50"
                           placeholder="Contoh: Dosis 1 / 0,5 ml"
                           class="min-h-[48px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-700 outline-none transition focus:border-cyan-300 focus:bg-white focus:ring-4 focus:ring-cyan-100 @error('dosis') border-red-300 ring-4 ring-red-100 @enderror">

                    @error('dosis')
                        <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="batch_number" class="mb-2 block text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                        Nomor Batch
                    </label>

                    <input type="text"
                           id="batch_number"
                           name="batch_number"
                           value="{{ $selectedBatch }}"
                           maxlength="100"
                           placeholder="Opsional"
                           class="min-h-[48px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-700 outline-none transition focus:border-cyan-300 focus:bg-white focus:ring-4 focus:ring-cyan-100 @error('batch_number') border-red-300 ring-4 ring-red-100 @enderror">

                    @error('batch_number')
                        <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tanggal_imunisasi" class="mb-2 block text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                        Tanggal Imunisasi
                    </label>

                    <input type="date"
                           id="tanggal_imunisasi"
                           name="tanggal_imunisasi"
                           value="{{ $selectedTanggal }}"
                           max="{{ now()->format('Y-m-d') }}"
                           required
                           class="min-h-[48px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-700 outline-none transition focus:border-cyan-300 focus:bg-white focus:ring-4 focus:ring-cyan-100 @error('tanggal_imunisasi') border-red-300 ring-4 ring-red-100 @enderror">

                    @error('tanggal_imunisasi')
                        <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                        Kategori Sasaran
                    </label>

                    <div class="flex min-h-[48px] items-center rounded-2xl border border-cyan-100 bg-cyan-50 px-4 py-3 text-sm font-black text-cyan-700">
                        <i class="ph ph-baby mr-2"></i>
                        Balita
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label for="keterangan" class="mb-2 block text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                        Catatan Tambahan
                    </label>

                    <textarea id="keterangan"
                              name="keterangan"
                              rows="4"
                              maxlength="1000"
                              placeholder="Opsional, contoh: kondisi umum atau keterangan layanan."
                              class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold leading-6 text-slate-700 outline-none transition focus:border-cyan-300 focus:bg-white focus:ring-4 focus:ring-cyan-100 @error('keterangan') border-red-300 ring-4 ring-red-100 @enderror">{{ $selectedKeterangan }}</textarea>

                    <div class="mt-2 flex items-center justify-between gap-3">
                        @error('keterangan')
                            <p class="text-sm font-semibold text-red-600">{{ $message }}</p>
                        @else
                            <p class="text-xs font-semibold text-slate-400">
                                Catatan tambahan bersifat opsional.
                            </p>
                        @enderror

                        <p id="keteranganCounter" class="text-xs font-black text-slate-400">
                            0/1000
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- RINGKASAN --}}
        <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.16em] text-cyan-600">
                        Langkah 3
                    </p>

                    <h2 class="mt-1 text-base font-black tracking-[-0.02em] text-slate-900 md:text-lg">
                        Ringkasan Catatan
                    </h2>

                    <p class="mt-1 text-xs font-semibold text-slate-500">
                        Pastikan ringkasan sudah sesuai sebelum menyimpan catatan imunisasi.
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                    <i class="ph ph-check-circle text-lg"></i>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                        Balita
                    </p>

                    <p id="summaryBalita" class="mt-2 truncate text-sm font-black text-slate-900">
                        {{ $selectedBalitaName }}
                    </p>
                </div>

                <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                        Jenis Imunisasi
                    </p>

                    <p id="summaryJenis" class="mt-2 truncate text-sm font-black text-slate-900">
                        {{ $selectedJenis ?: '-' }}
                    </p>
                </div>

                <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                        Vaksin
                    </p>

                    <p id="summaryVaksin" class="mt-2 truncate text-sm font-black text-slate-900">
                        {{ $selectedVaksin ?: '-' }}
                    </p>
                </div>

                <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                    <p class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">
                        Tanggal
                    </p>

                    <p id="summaryTanggal" class="mt-2 truncate text-sm font-black text-slate-900">
                        {{ $selectedTanggal ?: '-' }}
                    </p>
                </div>
            </div>
        </section>

        {{-- ACTION --}}
        <section class="nexus-panel-enter rounded-[26px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
            <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
                <a href="{{ $isEdit ? route('bidan.imunisasi.show', $imunisasi->id) : route('bidan.imunisasi.index') }}"
                   class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                    Batal
                </a>

                <button type="submit"
                        id="submitButton"
                        @disabled($balitas->count() === 0 || !$selectedBalitaId)
                        class="inline-flex min-h-[46px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-300">
                    <i class="ph ph-floppy-disk"></i>
                    {{ $submitLabel }}
                </button>
            </div>
        </section>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const balitaIdInput = document.getElementById('balita_id');
        const searchInput = document.getElementById('balitaSearchInput');
        const clearButton = document.getElementById('balitaSearchClear');
        const resultCount = document.getElementById('balitaResultCount');
        const emptyResult = document.getElementById('balitaEmptyResult');
        const submitButton = document.getElementById('submitButton');

        const resultButtons = Array.from(document.querySelectorAll('.balita-picker-result'));

        const jenisInput = document.getElementById('jenis_imunisasi');
        const vaksinInput = document.getElementById('vaksin');
        const tanggalInput = document.getElementById('tanggal_imunisasi');
        const keteranganInput = document.getElementById('keterangan');

        const previewBalitaNama = document.getElementById('previewBalitaNama');
        const previewBalitaNik = document.getElementById('previewBalitaNik');
        const previewBalitaWali = document.getElementById('previewBalitaWali');
        const previewBalitaAlamat = document.getElementById('previewBalitaAlamat');
        const balitaSelectionStatus = document.getElementById('balitaSelectionStatus');

        const summaryBalita = document.getElementById('summaryBalita');
        const summaryJenis = document.getElementById('summaryJenis');
        const summaryVaksin = document.getElementById('summaryVaksin');
        const summaryTanggal = document.getElementById('summaryTanggal');
        const keteranganCounter = document.getElementById('keteranganCounter');

        const normalize = (value) => {
            return String(value || '')
                .toLowerCase()
                .trim();
        };

        const dash = (value) => {
            const text = String(value || '').trim();
            return text === '' ? '-' : text;
        };

        const enableSubmitIfReady = () => {
            if (!submitButton || !balitaIdInput) {
                return;
            }

            submitButton.disabled = !balitaIdInput.value;
        };

        const setSelectedBalita = (button) => {
            if (!button || !balitaIdInput) {
                return;
            }

            const id = button.dataset.id || '';
            const name = button.dataset.name || '';
            const nik = button.dataset.nik || '';
            const wali = button.dataset.wali || '';
            const alamat = button.dataset.alamat || '';

            balitaIdInput.value = id;

            resultButtons.forEach((item) => {
                item.classList.remove('is-selected');

                const badge = item.querySelector('.selected-badge');
                badge?.classList.add('hidden');
                badge?.classList.remove('inline-flex');
            });

            button.classList.add('is-selected');

            const activeBadge = button.querySelector('.selected-badge');
            activeBadge?.classList.remove('hidden');
            activeBadge?.classList.add('inline-flex');

            if (previewBalitaNama) {
                previewBalitaNama.textContent = dash(name);
            }

            if (previewBalitaNik) {
                previewBalitaNik.textContent = dash(nik);
            }

            if (previewBalitaWali) {
                previewBalitaWali.textContent = dash(wali);
            }

            if (previewBalitaAlamat) {
                previewBalitaAlamat.textContent = dash(alamat);
            }

            if (summaryBalita) {
                summaryBalita.textContent = dash(name);
            }

            if (balitaSelectionStatus) {
                balitaSelectionStatus.textContent = 'Balita sudah dipilih. Lanjutkan mengisi data imunisasi.';
            }

            enableSubmitIfReady();
        };

        const isNumericKeyword = (keyword) => {
    return /^[0-9]+$/.test(keyword);
};

const getMatchRank = (button, keyword) => {
    if (keyword === '') {
        return 10 + Number(button.dataset.order || 0);
    }

    const name = normalize(button.dataset.searchName);
    const nik = normalize(button.dataset.searchNik);

    if (isNumericKeyword(keyword)) {
        if (nik.startsWith(keyword)) {
            return 0;
        }

        if (nik.includes(keyword)) {
            return 1;
        }

        return 999;
    }

    if (name.startsWith(keyword)) {
        return 0;
    }

    if (name.includes(keyword)) {
        return 1;
    }

    return 999;
};

const filterResults = () => {
    const keyword = normalize(searchInput?.value);
    let visible = 0;

    const rankedButtons = resultButtons
        .map((button) => {
            return {
                button,
                rank: getMatchRank(button, keyword),
                name: normalize(button.dataset.searchName),
                order: Number(button.dataset.order || 0),
            };
        })
        .sort((a, b) => {
            if (a.rank !== b.rank) {
                return a.rank - b.rank;
            }

            if (a.name !== b.name) {
                return a.name.localeCompare(b.name);
            }

            return a.order - b.order;
        });

    rankedButtons.forEach((item) => {
        const matched = item.rank < 999;

        item.button.classList.toggle('is-hidden', !matched);

        if (matched) {
            visible += 1;
            item.button.parentElement?.appendChild(item.button);
        }
    });

    if (resultCount) {
        resultCount.textContent = String(visible);
    }

    if (emptyResult) {
        emptyResult.classList.toggle('hidden', visible > 0);
    }

    if (clearButton) {
        const hasKeyword = keyword !== '';
        clearButton.classList.toggle('hidden', !hasKeyword);
        clearButton.classList.toggle('inline-flex', hasKeyword);
    }
};

        const updateSummary = () => {
            if (summaryJenis) {
                summaryJenis.textContent = dash(jenisInput?.value);
            }

            if (summaryVaksin) {
                summaryVaksin.textContent = dash(vaksinInput?.value);
            }

            if (summaryTanggal) {
                summaryTanggal.textContent = dash(tanggalInput?.value);
            }
        };

        const updateCounter = () => {
            if (!keteranganInput || !keteranganCounter) {
                return;
            }

            keteranganCounter.textContent = `${keteranganInput.value.length}/1000`;
        };

        resultButtons.forEach((button) => {
            button.addEventListener('click', () => setSelectedBalita(button));
        });

        searchInput?.addEventListener('input', filterResults, { passive: true });

        clearButton?.addEventListener('click', () => {
            if (!searchInput) {
                return;
            }

            searchInput.value = '';
            searchInput.focus();
            filterResults();
        });

        jenisInput?.addEventListener('change', updateSummary, { passive: true });
        vaksinInput?.addEventListener('input', updateSummary, { passive: true });
        tanggalInput?.addEventListener('input', updateSummary, { passive: true });
        keteranganInput?.addEventListener('input', updateCounter, { passive: true });

        const selectedButton = resultButtons.find((button) => {
            return String(button.dataset.id || '') === String(balitaIdInput?.value || '');
        });

        if (selectedButton) {
            setSelectedBalita(selectedButton);
        }

        filterResults();
        updateSummary();
        updateCounter();
        enableSubmitIfReady();
    })();
</script>
@endpush