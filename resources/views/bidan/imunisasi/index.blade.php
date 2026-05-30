@extends('layouts.bidan')

@section('title', 'Validasi Pemeriksaan Klinis')
@section('page-name', 'Validasi Pemeriksaan')
@section('page-title', 'Validasi Pemeriksaan')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    $kategori = strtolower($pemeriksaan->kategori_pasien ?? '-');

    $namaPasien = $pasien->nama_lengkap ?? $pemeriksaan->nama_pasien ?? 'Pasien Tidak Terdata';
    $nikPasien = $pasien->nik ?? $pemeriksaan->nik_pasien ?? '-';
    $jkPasien = $pasien->jenis_kelamin ?? '-';

    $tanggalInput = $pemeriksaan->tanggal_kunjungan
        ?? $pemeriksaan->tanggal_periksa
        ?? optional($pemeriksaan->kunjungan)->tanggal_kunjungan
        ?? $pemeriksaan->created_at;

    $tanggalFormatted = $tanggalInput ? Carbon::parse($tanggalInput)->translatedFormat('d M Y') : '-';

    $firstLetter = strtoupper(substr($namaPasien, 0, 1));

    $kategoriMeta = match ($kategori) {
        'balita' => [
            'label' => 'Balita',
            'icon' => 'ph-baby',
            'badge' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'soft' => 'bg-sky-50 text-sky-700 ring-sky-100',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'icon' => 'ph-user-focus',
            'badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'soft' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'icon' => 'ph-heartbeat',
            'badge' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'soft' => 'bg-amber-50 text-amber-700 ring-amber-100',
        ],
        default => [
            'label' => ucfirst($kategori),
            'icon' => 'ph-user',
            'badge' => 'bg-slate-50 text-slate-700 ring-slate-200',
            'soft' => 'bg-slate-50 text-slate-700 ring-slate-100',
        ],
    };

    $metric = function ($label, $value, $unit = '', $icon = 'ph-activity') {
        return [
            'label' => $label,
            'value' => ($value !== null && $value !== '') ? trim($value . ' ' . $unit) : '-',
            'icon' => $icon,
        ];
    };

    $baseMetrics = [
        $metric('Berat Badan', $pemeriksaan->berat_badan ?? null, 'kg', 'ph-scales'),
        $metric('Tinggi Badan', $pemeriksaan->tinggi_badan ?? null, 'cm', 'ph-ruler'),
        $metric('IMT', $pemeriksaan->imt ?? null, '', 'ph-chart-line-up'),
        $metric('Suhu Tubuh', $pemeriksaan->suhu_tubuh ?? null, '°C', 'ph-thermometer'),
    ];

    $categoryMetrics = [];

    if ($kategori === 'balita') {
        $categoryMetrics = [
            $metric('Lingkar Kepala', $pemeriksaan->lingkar_kepala ?? null, 'cm', 'ph-circle'),
            $metric('Lingkar Lengan', $pemeriksaan->lingkar_lengan ?? $pemeriksaan->lila ?? null, 'cm', 'ph-armchair'),
        ];
    }

    if ($kategori === 'remaja') {
        $categoryMetrics = [
            $metric('Tekanan Darah', $pemeriksaan->tekanan_darah ?? null, 'mmHg', 'ph-heartbeat'),
            $metric('Hemoglobin', $pemeriksaan->hb ?? $pemeriksaan->hemoglobin ?? null, 'g/dL', 'ph-drop'),
        ];
    }

    if ($kategori === 'lansia') {
        $categoryMetrics = [
            $metric('Tekanan Darah', $pemeriksaan->tekanan_darah ?? null, 'mmHg', 'ph-heartbeat'),
            $metric('Gula Darah', $pemeriksaan->gula_darah ?? null, 'mg/dL', 'ph-drop-half'),
            $metric('Kolesterol', $pemeriksaan->kolesterol ?? null, 'mg/dL', 'ph-wave-sine'),
            $metric('Asam Urat', $pemeriksaan->asam_urat ?? null, 'mg/dL', 'ph-flask'),
            $metric('Lingkar Perut', $pemeriksaan->lingkar_perut ?? null, 'cm', 'ph-circle-dashed'),
            $metric('Kemandirian', $pemeriksaan->tingkat_kemandirian ?? null, '', 'ph-person-simple-walk'),
        ];
    }

    $allMetrics = array_merge($baseMetrics, $categoryMetrics);

    $analisisStatus = data_get($analisis, 'kesimpulan.status')
        ?? data_get($analisis, 'kategori')
        ?? data_get($analisis, 'status')
        ?? 'Perlu Tinjauan Bidan';

    $analisisPesan = data_get($analisis, 'kesimpulan.pesan')
        ?? data_get($analisis, 'pesan')
        ?? data_get($analisis, 'kesimpulan.rekomendasi')
        ?? '';

    $analisisRekomendasi = data_get($analisis, 'kesimpulan.rekomendasi')
        ?? data_get($analisis, 'rekomendasi')
        ?? data_get($analisis, 'kesimpulan.tindakan')
        ?? '';

    $defaultDiagnosa = old('diagnosa', $analisisPesan);
    $defaultStatusGizi = old('status_gizi', $analisisStatus);
    $defaultTindakan = old('tindakan', data_get($analisis, 'kesimpulan.tindakan') ?? '');
    $defaultEdukasi = old('catatan_edukasi', $analisisRekomendasi);
@endphp

@section('content')
<style>
    .nexus-font {
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .nexus-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(16, 185, 129, 0.35) transparent;
    }

    .nexus-scroll::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .nexus-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .nexus-scroll::-webkit-scrollbar-thumb {
        background: rgba(16, 185, 129, 0.35);
        border-radius: 999px;
    }
</style>

<div class="nexus-font space-y-5 pb-8 text-slate-800">

    {{-- HEADER --}}
    <section class="relative overflow-hidden rounded-[28px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-emerald-100/80 blur-3xl"></div>
        <div class="absolute -bottom-28 -left-24 h-72 w-72 rounded-full bg-cyan-100/80 blur-3xl"></div>

        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <a href="{{ route('bidan.pemeriksaan.index') }}"
                   class="mb-4 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-600 transition hover:bg-slate-50 hover:text-emerald-700">
                    <i class="ph ph-arrow-left"></i>
                    Kembali
                </a>

                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700">
                    <i class="ph ph-stethoscope text-base"></i>
                    Validasi Klinis Bidan
                </div>

                <h1 class="mt-4 max-w-2xl text-[28px] font-black leading-tight tracking-[-0.03em] text-slate-900 md:text-[34px]">
                    Tinjauan Pemeriksaan Meja 5
                </h1>

                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
                    Cek data dari Kader, baca saran sistem, lalu sahkan diagnosa resmi. Jangan asal klik, ini rekam medis, bukan tombol skip iklan.
                </p>
            </div>

            <div class="rounded-3xl border border-slate-100 bg-slate-50/80 p-4 lg:min-w-[320px]">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-xl font-black text-white shadow-sm">
                        {{ $firstLetter }}
                    </div>

                    <div class="min-w-0">
                        <h2 class="truncate text-lg font-black text-slate-900">
                            {{ $namaPasien }}
                        </h2>
                        <p class="mt-1 truncate text-sm font-semibold text-slate-500">
                            NIK {{ $nikPasien }}
                        </p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-[11px] font-black ring-1 {{ $kategoriMeta['badge'] }}">
                                <i class="ph {{ $kategoriMeta['icon'] }}"></i>
                                {{ $kategoriMeta['label'] }}
                            </span>
                            <span class="inline-flex rounded-full bg-white px-3 py-1 text-[11px] font-black text-slate-600 ring-1 ring-slate-200">
                                {{ $tanggalFormatted }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- MAIN GRID --}}
    <section class="grid gap-5 xl:grid-cols-12">

        {{-- LEFT PANEL --}}
        <div class="space-y-5 xl:col-span-7">

            {{-- BIODATA --}}
            <div class="rounded-[28px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-600">
                            Data Pasien
                        </p>
                        <h2 class="mt-1 text-lg font-black tracking-[-0.02em] text-slate-900">
                            Identitas Pemeriksaan
                        </h2>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl ring-1 {{ $kategoriMeta['soft'] }}">
                        <i class="ph {{ $kategoriMeta['icon'] }} text-xl"></i>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                        <p class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">Nama Lengkap</p>
                        <p class="mt-2 truncate text-sm font-black text-slate-900">{{ $namaPasien }}</p>
                    </div>

                    <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                        <p class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">NIK</p>
                        <p class="mt-2 truncate text-sm font-black text-slate-900">{{ $nikPasien }}</p>
                    </div>

                    <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                        <p class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">Jenis Kelamin</p>
                        <p class="mt-2 truncate text-sm font-black text-slate-900">{{ $jkPasien }}</p>
                    </div>

                    <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                        <p class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">Input Kader</p>
                        <p class="mt-2 truncate text-sm font-black text-slate-900">
                            {{ $pemeriksaan->pemeriksa->name ?? $pemeriksaan->createdBy->name ?? 'Kader Posyandu' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- HASIL UKUR --}}
            <div class="rounded-[28px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-cyan-600">
                            Data Pengukuran
                        </p>
                        <h2 class="mt-1 text-lg font-black tracking-[-0.02em] text-slate-900">
                            Hasil Ukur dari Kader
                        </h2>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700 ring-1 ring-cyan-100">
                        <i class="ph ph-chart-line-up text-xl"></i>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($allMetrics as $item)
                        <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-white text-emerald-700 ring-1 ring-emerald-100">
                                <i class="ph {{ $item['icon'] }} text-lg"></i>
                            </div>

                            <p class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">
                                {{ $item['label'] }}
                            </p>
                            <p class="mt-2 truncate text-base font-black text-slate-900">
                                {{ $item['value'] }}
                            </p>
                        </div>
                    @endforeach
                </div>

                @if(!empty($pemeriksaan->keluhan) || !empty($pemeriksaan->catatan))
                    <div class="mt-4 rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                        <p class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">
                            Catatan Awal Kader
                        </p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $pemeriksaan->keluhan ?? $pemeriksaan->catatan ?? '-' }}
                        </p>
                    </div>
                @endif
            </div>

            {{-- ANALISIS OTOMATIS --}}
            <div class="rounded-[28px] border border-emerald-100 bg-gradient-to-br from-emerald-50 via-white to-cyan-50 p-5 shadow-sm shadow-slate-200/70">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-600">
                            Decision Support
                        </p>
                        <h2 class="mt-1 text-lg font-black tracking-[-0.02em] text-slate-900">
                            Saran Analisis Sistem
                        </h2>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-emerald-700 ring-1 ring-emerald-100">
                        <i class="ph ph-brain text-xl"></i>
                    </div>
                </div>

                @if($analisis)
                    <div class="rounded-2xl bg-white/80 p-4 ring-1 ring-emerald-100">
                        <p class="text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">
                            Kesimpulan Sistem
                        </p>

                        <h3 class="mt-2 text-xl font-black tracking-[-0.02em] text-emerald-700">
                            {{ $analisisStatus }}
                        </h3>

                        @if($analisisPesan)
                            <p class="mt-3 text-sm leading-6 text-slate-600">
                                {{ $analisisPesan }}
                            </p>
                        @endif

                        @if($kategori === 'balita')
                            <div class="mt-4 grid gap-3 md:grid-cols-3">
                                <div class="rounded-2xl bg-slate-50 p-3 ring-1 ring-slate-100">
                                    <p class="text-[10px] font-black uppercase text-slate-400">BB/U</p>
                                    <p class="mt-1 text-sm font-black text-slate-800">
                                        {{ data_get($analisis, 'bbu.status', '-') }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-3 ring-1 ring-slate-100">
                                    <p class="text-[10px] font-black uppercase text-slate-400">TB/U</p>
                                    <p class="mt-1 text-sm font-black text-slate-800">
                                        {{ data_get($analisis, 'tbu.status', '-') }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-3 ring-1 ring-slate-100">
                                    <p class="text-[10px] font-black uppercase text-slate-400">BB/TB</p>
                                    <p class="mt-1 text-sm font-black text-slate-800">
                                        {{ data_get($analisis, 'bbtb.status', '-') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        @if($analisisRekomendasi)
                            <div class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50/80 p-4">
                                <p class="text-[11px] font-black uppercase tracking-[0.14em] text-emerald-700">
                                    Rekomendasi
                                </p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">
                                    {{ $analisisRekomendasi }}
                                </p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-white/70 p-6 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-400">
                            <i class="ph ph-warning-circle text-2xl"></i>
                        </div>
                        <h3 class="mt-4 text-base font-black text-slate-800">
                            Analisis Otomatis Belum Tersedia
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Bidan tetap bisa mengisi validasi manual berdasarkan hasil pemeriksaan.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT FORM --}}
        <div class="xl:col-span-5">
            <form method="POST"
                  action="{{ route('bidan.pemeriksaan.simpan-validasi', $pemeriksaan->id) }}"
                  class="rounded-[28px] border border-white/80 bg-white/90 p-5 shadow-sm shadow-slate-200/70 backdrop-blur xl:sticky xl:top-24">
                @csrf
                @method('PUT')

                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-600">
                            Pengesahan Bidan
                        </p>
                        <h2 class="mt-1 text-lg font-black tracking-[-0.02em] text-slate-900">
                            Form Validasi Klinis
                        </h2>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                        <i class="ph ph-seal-check text-xl"></i>
                    </div>
                </div>

                <div class="space-y-4">

                    {{-- STATUS --}}
                    <div>
                        <label for="status_gizi" class="mb-2 block text-xs font-black uppercase tracking-[0.14em] text-slate-400">
                            Status Ringkas Pasien
                        </label>

                        <input type="text"
                               id="status_gizi"
                               name="status_gizi"
                               value="{{ $defaultStatusGizi }}"
                               maxlength="100"
                               placeholder="Contoh: Normal, Gizi Baik, Risiko PTM, Perlu Pemantauan"
                               class="min-h-[46px] w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">

                        @error('status_gizi')
                            <p class="mt-2 text-sm font-bold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- DIAGNOSA --}}
                    <div>
                        <label for="diagnosa" class="mb-2 block text-xs font-black uppercase tracking-[0.14em] text-slate-400">
                            Kesimpulan / Diagnosa Klinis <span class="text-rose-500">*</span>
                        </label>

                        <textarea id="diagnosa"
                                  name="diagnosa"
                                  rows="4"
                                  maxlength="255"
                                  required
                                  placeholder="Tuliskan kesimpulan klinis hasil pemeriksaan."
                                  class="nexus-scroll w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold leading-6 text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">{{ $defaultDiagnosa }}</textarea>

                        <div class="mt-2 flex justify-between gap-3">
                            @error('diagnosa')
                                <p class="text-sm font-bold text-rose-600">{{ $message }}</p>
                            @else
                                <p class="text-xs font-semibold text-slate-400">
                                    Wajib diisi, maksimal 255 karakter.
                                </p>
                            @enderror
                        </div>
                    </div>

                    {{-- TINDAKAN --}}
                    <div>
                        <label for="tindakan" class="mb-2 block text-xs font-black uppercase tracking-[0.14em] text-slate-400">
                            Tindakan Medis / Vitamin / Rujukan
                        </label>

                        <textarea id="tindakan"
                                  name="tindakan"
                                  rows="3"
                                  maxlength="255"
                                  placeholder="Contoh: Edukasi gizi, pemberian vitamin, kontrol ulang, atau rujukan."
                                  class="nexus-scroll w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold leading-6 text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">{{ $defaultTindakan }}</textarea>

                        @error('tindakan')
                            <p class="mt-2 text-sm font-bold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- EDUKASI --}}
                    <div>
                        <label for="catatan_edukasi" class="mb-2 block text-xs font-black uppercase tracking-[0.14em] text-slate-400">
                            Catatan Edukasi untuk Warga
                        </label>

                        <textarea id="catatan_edukasi"
                                  name="catatan_edukasi"
                                  rows="4"
                                  placeholder="Tuliskan saran mandiri yang bisa dibaca warga pada riwayat rekam medis."
                                  class="nexus-scroll w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold leading-6 text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">{{ $defaultEdukasi }}</textarea>

                        @error('catatan_edukasi')
                            <p class="mt-2 text-sm font-bold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- INFO --}}
                    <div class="rounded-2xl border border-amber-100 bg-amber-50/80 p-4">
                        <div class="flex gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-amber-600 ring-1 ring-amber-100">
                                <i class="ph ph-warning-circle text-lg"></i>
                            </div>

                            <div>
                                <h3 class="text-sm font-black text-slate-900">
                                    Data akan dikunci sebagai rekam medis
                                </h3>
                                <p class="mt-1 text-xs leading-5 text-slate-600">
                                    Setelah disahkan, status pemeriksaan berubah menjadi terverifikasi dan tampil pada riwayat kesehatan warga.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- ACTION --}}
                    <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
                        <button type="submit"
                                class="inline-flex min-h-[50px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-emerald-700">
                            <i class="ph ph-seal-check"></i>
                            Sahkan Rekam Medis
                        </button>

                        <a href="{{ route('bidan.pemeriksaan.index') }}"
                           class="inline-flex min-h-[50px] items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                            Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection