@extends('layouts.bidan')

@section('title', 'Validasi Pemeriksaan')
@section('page-name', 'Validasi Pemeriksaan')
@section('page-title', 'Validasi Pemeriksaan')

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

    $pasienNama = data_get($ringkasan, 'nama_pasien')
        ?? $getValue($pasien, ['nama_lengkap', 'nama', 'nama_balita', 'nama_remaja', 'nama_lansia'], 'Nama tidak tersedia');

    $pasienNik = data_get($ringkasan, 'nik_pasien')
        ?? $getValue($pasien, ['nik', 'nik_anak'], '-');

    $tanggalKunjungan = data_get($ringkasan, 'tanggal_kunjungan')
        ?? $formatDate(data_get($pemeriksaan, 'kunjungan.tanggal_kunjungan') ?? data_get($pemeriksaan, 'created_at'));

    $petugasInput = data_get($ringkasan, 'petugas_input')
        ?? data_get($pemeriksaan, 'pemeriksa.name')
        ?? data_get($pemeriksaan, 'pemeriksa.nama')
        ?? data_get($pemeriksaan, 'kunjungan.petugas.name')
        ?? data_get($pemeriksaan, 'kunjungan.petugas.nama')
        ?? '-';

    $statusRingkasLabel = $kategori === 'balita'
        ? 'Status Gizi'
        : 'Status Ringkas Pemeriksaan';

    $statusRingkasPlaceholder = $kategori === 'balita'
        ? 'Contoh: Gizi baik'
        : 'Contoh: Dalam batas pemantauan';

    $statusRingkas = old('status_gizi', data_get($pemeriksaan, 'status_gizi', ''));

    $kesimpulan = old(
        'kesimpulan_pemeriksaan',
        data_get($pemeriksaan, 'kesimpulan_pemeriksaan')
            ?? data_get($pemeriksaan, 'diagnosa')
            ?? ''
    );

    $tindakan = old(
        'tindakan',
        data_get($pemeriksaan, 'tindakan')
            ?? ''
    );

    $catatanEdukasi = old(
        'catatan_edukasi',
        data_get($pemeriksaan, 'catatan_edukasi')
            ?? data_get($pemeriksaan, 'edukasi')
            ?? ''
    );
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
                <a href="{{ route('bidan.pemeriksaan.index', ['tab' => 'pending']) }}"
                   class="mb-4 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-600 transition hover:bg-slate-50 hover:text-emerald-700">
                    <i class="ph ph-arrow-left"></i>
                    Kembali
                </a>

                <div class="inline-flex items-center gap-2 rounded-full border border-amber-100 bg-amber-50 px-3.5 py-1.5 text-[11px] font-black uppercase tracking-[0.14em] text-amber-700">
                    <i class="ph ph-clock-countdown text-sm"></i>
                    Menunggu Validasi
                </div>

                <h1 class="mt-3 text-[24px] font-black leading-tight tracking-[-0.025em] text-slate-900 md:text-[28px]">
                    Validasi Pemeriksaan
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Bidan meninjau data pemeriksaan dari Kader, lalu menyimpan hasil validasi sebagai arsip Rekam Medis.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-xs font-black ring-1 {{ $meta['badge'] }}">
                    <i class="ph {{ $meta['icon'] }}"></i>
                    {{ $meta['label'] }}
                </span>

                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3.5 py-2 text-xs font-black text-emerald-700">
                    <i class="ph ph-check-circle"></i>
                    Output: Rekam Medis
                </span>
            </div>
        </div>
    </section>

    {{-- DATA RINGKAS --}}
    <section class="nexus-panel-enter grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[20px] border border-white/80 bg-white/85 p-4 shadow-sm shadow-slate-200/60 backdrop-blur">
            <p class="text-[11px] font-black uppercase tracking-[0.12em] text-slate-400">
                Nama Sasaran
            </p>

            <p class="mt-2 truncate text-sm font-black text-slate-900">
                {{ $pasienNama }}
            </p>
        </div>

        <div class="rounded-[20px] border border-white/80 bg-white/85 p-4 shadow-sm shadow-slate-200/60 backdrop-blur">
            <p class="text-[11px] font-black uppercase tracking-[0.12em] text-slate-400">
                NIK
            </p>

            <p class="mt-2 truncate text-sm font-black text-slate-900">
                {{ $pasienNik }}
            </p>
        </div>

        <div class="rounded-[20px] border border-white/80 bg-white/85 p-4 shadow-sm shadow-slate-200/60 backdrop-blur">
            <p class="text-[11px] font-black uppercase tracking-[0.12em] text-slate-400">
                Tanggal Pemeriksaan
            </p>

            <p class="mt-2 truncate text-sm font-black text-slate-900">
                {{ $tanggalKunjungan }}
            </p>
        </div>

        <div class="rounded-[20px] border border-white/80 bg-white/85 p-4 shadow-sm shadow-slate-200/60 backdrop-blur">
            <p class="text-[11px] font-black uppercase tracking-[0.12em] text-slate-400">
                Petugas Input
            </p>

            <p class="mt-2 truncate text-sm font-black text-slate-900">
                {{ $petugasInput }}
            </p>
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
                    Parameter yang Ditinjau
                </h2>

                <p class="mt-1 text-xs font-semibold text-slate-500">
                    Data berikut berasal dari pemeriksaan awal yang dicatat oleh Kader.
                </p>
            </div>

            <div class="flex h-10 w-10 items-center justify-center rounded-2xl ring-1 {{ $meta['iconBox'] }}">
                <i class="ph {{ $meta['icon'] }} text-base"></i>
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

    {{-- FORM VALIDASI --}}
    <form method="POST"
          action="{{ route('bidan.pemeriksaan.simpan-validasi', $pemeriksaan->id) }}"
          class="space-y-4">
        @csrf
        @method('PUT')

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
                        Isi hasil validasi secara ringkas dan sesuai kebutuhan layanan.
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                    <i class="ph ph-note-pencil text-base"></i>
                </div>
            </div>

            <div class="grid gap-4">
                <div>
                    <label for="status_gizi" class="mb-2 block text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                        {{ $statusRingkasLabel }}
                    </label>

                    <input type="text"
                           id="status_gizi"
                           name="status_gizi"
                           value="{{ $statusRingkas }}"
                           maxlength="100"
                           placeholder="{{ $statusRingkasPlaceholder }}"
                           class="min-h-[46px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('status_gizi') border-red-300 ring-4 ring-red-100 @enderror">

                    @error('status_gizi')
                        <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kesimpulan_pemeriksaan" class="mb-2 block text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                        Kesimpulan Pemeriksaan
                    </label>

                    <textarea id="kesimpulan_pemeriksaan"
                              name="kesimpulan_pemeriksaan"
                              rows="4"
                              maxlength="1000"
                              required
                              placeholder="Tuliskan kesimpulan pemeriksaan berdasarkan hasil peninjauan Bidan."
                              class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold leading-6 text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('kesimpulan_pemeriksaan') border-red-300 ring-4 ring-red-100 @enderror">{{ $kesimpulan }}</textarea>

                    <div class="mt-2 flex items-center justify-between gap-3">
                        @error('kesimpulan_pemeriksaan')
                            <p class="text-sm font-semibold text-red-600">{{ $message }}</p>
                        @else
                            <p class="text-xs font-semibold text-slate-400">
                                Wajib diisi.
                            </p>
                        @enderror

                        <p id="kesimpulanCounter" class="text-xs font-black text-slate-400">
                            0/1000
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div>
                        <label for="tindakan" class="mb-2 block text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                            Tindakan / Layanan
                        </label>

                        <textarea id="tindakan"
                                  name="tindakan"
                                  rows="3"
                                  maxlength="1000"
                                  required
                                  placeholder="Tuliskan tindakan atau layanan yang diberikan."
                                  class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold leading-6 text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('tindakan') border-red-300 ring-4 ring-red-100 @enderror">{{ $tindakan }}</textarea>

                        <div class="mt-2 flex items-center justify-between gap-3">
                            @error('tindakan')
                                <p class="text-sm font-semibold text-red-600">{{ $message }}</p>
                            @else
                                <p class="text-xs font-semibold text-slate-400">
                                    Wajib diisi.
                                </p>
                            @enderror

                            <p id="tindakanCounter" class="text-xs font-black text-slate-400">
                                0/1000
                            </p>
                        </div>
                    </div>

                    <div>
                        <label for="catatan_edukasi" class="mb-2 block text-xs font-black uppercase tracking-[0.12em] text-slate-500">
                            Edukasi Kesehatan
                        </label>

                        <textarea id="catatan_edukasi"
                                  name="catatan_edukasi"
                                  rows="3"
                                  maxlength="1000"
                                  placeholder="Opsional, tuliskan edukasi kesehatan yang diberikan."
                                  class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold leading-6 text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100 @error('catatan_edukasi') border-red-300 ring-4 ring-red-100 @enderror">{{ $catatanEdukasi }}</textarea>

                        <div class="mt-2 flex items-center justify-between gap-3">
                            @error('catatan_edukasi')
                                <p class="text-sm font-semibold text-red-600">{{ $message }}</p>
                            @else
                                <p class="text-xs font-semibold text-slate-400">
                                    Opsional.
                                </p>
                            @enderror

                            <p id="edukasiCounter" class="text-xs font-black text-slate-400">
                                0/1000
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ACTION --}}
        <section class="nexus-panel-enter rounded-[24px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
            <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
                <a href="{{ route('bidan.pemeriksaan.show', $pemeriksaan->id) }}"
                   class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                    Batal
                </a>

                <button type="submit"
                        class="inline-flex min-h-[46px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                    <i class="ph ph-check-circle"></i>
                    Simpan Validasi
                </button>
            </div>

            <p class="mt-3 text-center text-xs font-semibold text-slate-400">
                Setelah disimpan, data menjadi pemeriksaan tervalidasi dan tampil pada Rekam Medis.
            </p>
        </section>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const counters = [
            ['kesimpulan_pemeriksaan', 'kesimpulanCounter', 1000],
            ['tindakan', 'tindakanCounter', 1000],
            ['catatan_edukasi', 'edukasiCounter', 1000],
        ];

        counters.forEach(([inputId, counterId, max]) => {
            const input = document.getElementById(inputId);
            const counter = document.getElementById(counterId);

            if (!input || !counter) {
                return;
            }

            const update = () => {
                counter.textContent = `${input.value.length}/${max}`;
            };

            input.addEventListener('input', update, { passive: true });
            update();
        });
    })();
</script>
@endpush