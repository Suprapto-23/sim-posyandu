@extends('layouts.kader')

@section('title', 'Edit Pengukuran Fisik')
@section('page-name', 'Edit Pengukuran Fisik')
@section('page-title', 'Edit Pengukuran Fisik')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    $pasien = optional($pemeriksaan->kunjungan)->pasien;

    $kategori = strtolower((string) ($pemeriksaan->kategori_pasien ?? 'balita'));

    if (! in_array($kategori, ['balita', 'remaja', 'lansia'], true)) {
        $kategori = 'balita';
    }

    $kategoriMeta = match ($kategori) {
        'remaja' => [
            'label' => 'Remaja',
            'desc' => 'LiLA, lingkar perut, tensi, dan skrining remaja',
            'icon' => 'fa-user-graduate',
            'solid' => 'bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white',
            'soft' => 'border-violet-200 bg-gradient-to-br from-violet-50 via-fuchsia-50 to-white',
            'text' => 'text-violet-700',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'desc' => 'Lingkar perut, tensi, kemandirian, dan pemeriksaan dasar',
            'icon' => 'fa-person-cane',
            'solid' => 'bg-gradient-to-br from-sky-500 to-cyan-500 text-white',
            'soft' => 'border-sky-200 bg-gradient-to-br from-sky-50 via-cyan-50 to-white',
            'text' => 'text-sky-700',
        ],
        default => [
            'label' => 'Balita',
            'desc' => 'BB, TB, lingkar kepala, dan LiLA',
            'icon' => 'fa-child-reaching',
            'solid' => 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white',
            'soft' => 'border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-white',
            'text' => 'text-emerald-700',
        ],
    };

    $namaPasien = $pasien->nama_lengkap
        ?? $pasien->nama
        ?? $pemeriksaan->nama_pasien
        ?? $pemeriksaan->nama_pasien
        ?? 'Tanpa Nama';

    $nikPasien = $pasien->nik
        ?? $pemeriksaan->nik_pasien
        ?? '-';

    $tanggalValue = old('tanggal_periksa', optional($pemeriksaan->tanggal_periksa)->format('Y-m-d') ?: Carbon::parse($pemeriksaan->tanggal_periksa ?? now())->format('Y-m-d'));

    $today = now('Asia/Jakarta')->toDateString();

    $status = strtolower((string) ($pemeriksaan->status_verifikasi ?? 'pending'));
    $isRevisi = in_array($status, ['ditolak', 'revisi', 'perlu_revisi', 'needs_revision', 'rejected', 'dikembalikan'], true);

    $catatanBidan = $pemeriksaan->catatan_validasi
        ?? $pemeriksaan->catatan_bidan
        ?? $pemeriksaan->catatan_review
        ?? null;
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

    .pc-field {
        width: 100%;
        border-radius: 1rem;
        border: 1px solid rgba(203, 213, 225, .95);
        background: rgba(255, 255, 255, .86);
        padding: .85rem 1rem;
        font-size: .875rem;
        font-weight: 800;
        color: #334155;
        outline: none;
        box-shadow: 0 6px 18px rgba(15, 23, 42, .035);
        transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .pc-field:focus {
        border-color: rgba(16, 185, 129, .55);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, .10);
        background: rgba(255, 255, 255, .96);
    }

    .pc-label {
        display: block;
        margin-bottom: .45rem;
        font-size: .72rem;
        font-weight: 950;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: #64748b;
    }

    .pc-required::after {
        content: " *";
        color: #f43f5e;
    }

    .pc-hidden {
        display: none !important;
    }

    .pc-modal-backdrop {
        position: fixed !important;
        top: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        left: 0 !important;
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
    .pc-balanced-grid {
    align-items: stretch !important;
}

.pc-left-panel {
    min-height: 100%;
}

.pc-side-panel {
    min-height: 100%;
}

.pc-side-card {
    min-height: 100%;
}

.pc-side-fill {
    flex: 1 1 auto;
}

.pc-side-bottom {
    margin-top: auto;
}

.pc-note-card {
    min-height: 132px;
}

@media (max-width: 1279px) {
    .pc-balanced-grid {
        align-items: start !important;
    }

    .pc-left-panel,
    .pc-side-panel,
    .pc-side-card {
        min-height: auto;
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

            <div class="relative grid gap-6 p-5 sm:p-6 xl:grid-cols-[minmax(0,1.3fr)_340px] xl:items-stretch">
                <div class="flex min-w-0 flex-col justify-between gap-6">
                    <div>
                        <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-white/75 px-3 py-1.5 text-xs font-black text-emerald-700 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Edit Pengukuran Kader
                        </div>

                        <h1 class="mt-4 max-w-4xl text-2xl font-black leading-tight tracking-tight text-slate-950 sm:text-[1.85rem] lg:text-[2rem]">
                            Perbaiki data pengukuran sebelum dikunci Bidan.
                        </h1>

                        <p class="mt-3 max-w-3xl text-sm font-medium leading-6 text-slate-600">
                            Edit hanya tersedia saat data belum direview atau dikembalikan untuk revisi. Kalau sudah divalidasi, Kader tidak boleh mengubah data. Begitulah data medis bekerja, bukan papan tulis warung.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('kader.pemeriksaan.index') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white/80 px-4 py-2.5 text-sm font-black text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-white">
                            <i class="fa-solid fa-arrow-left"></i>
                            Kembali
                        </a>

                        <a href="{{ route('kader.pemeriksaan.show', $pemeriksaan->id) }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-white">
                            <i class="fa-solid fa-eye"></i>
                            Lihat Detail
                        </a>
                    </div>
                </div>

                <div class="pc-glass rounded-[1.55rem] border border-white/80 p-5">
                    <p class="text-[11px] font-black uppercase tracking-[.18em] {{ $kategoriMeta['text'] }}">Sasaran</p>

                    <div class="mt-4 flex items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $kategoriMeta['solid'] }}">
                            <i class="fa-solid {{ $kategoriMeta['icon'] }}"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="line-clamp-1 text-base font-black text-slate-950">{{ $namaPasien }}</p>
                            <p class="mt-1 text-xs font-bold text-slate-500">NIK {{ $nikPasien }}</p>
                        </div>
                    </div>

                    <div class="mt-5 rounded-[1.2rem] border {{ $kategoriMeta['soft'] }} p-4">
                        <p class="text-sm font-black text-slate-950">{{ $kategoriMeta['label'] }}</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">{{ $kategoriMeta['desc'] }}</p>
                    </div>
                </div>
            </div>
        </section>

        @if(session('success') || session('error') || $errors->any())
            <section class="space-y-3">
                @if(session('success'))
                    <div class="rounded-[1.35rem] border border-emerald-200 bg-emerald-50/90 p-4 text-sm font-bold text-emerald-800 shadow-sm">
                        <i class="fa-solid fa-circle-check mr-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="rounded-[1.35rem] border border-rose-200 bg-rose-50/90 p-4 text-sm font-bold text-rose-800 shadow-sm">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="rounded-[1.35rem] border border-amber-200 bg-amber-50/90 p-4 text-sm font-bold text-amber-800 shadow-sm">
                        <div class="flex gap-2">
                            <i class="fa-solid fa-circle-info mt-0.5"></i>
                            <div>
                                <p>Form belum valid. Perbaiki bagian yang masih salah.</p>
                                <ul class="mt-2 list-disc space-y-1 pl-5 text-xs font-semibold">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </section>
        @endif

        @if($isRevisi && $catatanBidan)
            <section class="rounded-[1.55rem] border border-rose-200 bg-rose-50/90 p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[.18em] text-rose-700">Catatan Bidan</p>
                <h2 class="mt-1 text-lg font-black text-slate-950">Data dikembalikan untuk revisi</h2>
                <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">{{ $catatanBidan }}</p>
            </section>
        @endif

        <form id="measurementEditForm"
      method="POST"
      action="{{ route('kader.pemeriksaan.update', $pemeriksaan->id) }}"
      class="pc-balanced-grid grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(330px,.55fr)]">
            @csrf
            @method('PUT')

            <section class="pc-left-panel pc-glass flex h-full flex-col rounded-[1.75rem] border border-white/80 p-5">
                <div class="mb-5">
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Form Pengukuran</p>
                    <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">Perbarui hasil pengukuran</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">
                        Kategori dan sasaran dikunci agar data tidak pindah identitas seenaknya.
                    </p>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div>
                        <label class="pc-label pc-required">Tanggal Pengukuran</label>
                        <input type="date"
                               name="tanggal_periksa"
                               value="{{ $tanggalValue }}"
                               max="{{ $today }}"
                               class="pc-field">
                    </div>

                    <div>
                        <label class="pc-label">Suhu Tubuh</label>
                        <input type="number"
                               step="0.1"
                               min="30"
                               max="45"
                               name="suhu_tubuh"
                               value="{{ old('suhu_tubuh', $pemeriksaan->suhu_tubuh) }}"
                               placeholder="Contoh: 36.5"
                               class="pc-field">
                    </div>

                    <div>
                        <label class="pc-label pc-required">Berat Badan</label>
                        <input type="number"
                               step="0.1"
                               min="0.1"
                               max="300"
                               name="berat_badan"
                               value="{{ old('berat_badan', $pemeriksaan->berat_badan) }}"
                               placeholder="Kg"
                               class="pc-field"
                               data-imt-source>
                    </div>

                    <div>
                        <label class="pc-label pc-required">Tinggi / Panjang Badan</label>
                        <input type="number"
                               step="0.1"
                               min="10"
                               max="250"
                               name="tinggi_badan"
                               value="{{ old('tinggi_badan', $pemeriksaan->tinggi_badan) }}"
                               placeholder="Cm"
                               class="pc-field"
                               data-imt-source>
                    </div>

                    <div class="{{ $kategori !== 'balita' ? 'pc-hidden' : '' }}">
                        <label class="pc-label pc-required">Lingkar Kepala</label>
                        <input type="number"
                               step="0.1"
                               min="10"
                               max="100"
                               name="lingkar_kepala"
                               value="{{ old('lingkar_kepala', $pemeriksaan->lingkar_kepala) }}"
                               placeholder="Cm"
                               class="pc-field"
                               @disabled($kategori !== 'balita')>
                    </div>

                    <div class="{{ ! in_array($kategori, ['balita', 'remaja'], true) ? 'pc-hidden' : '' }}">
                        <label class="pc-label pc-required">Lingkar Lengan Atas</label>
                        <input type="number"
                               step="0.1"
                               min="5"
                               max="100"
                               name="lingkar_lengan"
                               value="{{ old('lingkar_lengan', $pemeriksaan->lingkar_lengan) }}"
                               placeholder="Cm"
                               class="pc-field"
                               @disabled(! in_array($kategori, ['balita', 'remaja'], true))>
                    </div>

                    <div class="{{ ! in_array($kategori, ['remaja', 'lansia'], true) ? 'pc-hidden' : '' }}">
                        <label class="pc-label pc-required">Lingkar Perut</label>
                        <input type="number"
                               step="0.1"
                               min="20"
                               max="200"
                               name="lingkar_perut"
                               value="{{ old('lingkar_perut', $pemeriksaan->lingkar_perut) }}"
                               placeholder="Cm"
                               class="pc-field"
                               @disabled(! in_array($kategori, ['remaja', 'lansia'], true))>
                    </div>

                    <div class="{{ ! in_array($kategori, ['remaja', 'lansia'], true) ? 'pc-hidden' : '' }}">
                        <label class="pc-label pc-required">Tekanan Darah</label>
                        <input type="text"
                               name="tekanan_darah"
                               value="{{ old('tekanan_darah', $pemeriksaan->tekanan_darah) }}"
                               placeholder="Contoh: 120/80"
                               class="pc-field"
                               @disabled(! in_array($kategori, ['remaja', 'lansia'], true))>
                    </div>

                    <div class="{{ $kategori !== 'lansia' ? 'pc-hidden' : '' }}">
                        <label class="pc-label pc-required">Tingkat Kemandirian</label>
                        <select name="tingkat_kemandirian"
                                class="pc-field"
                                @disabled($kategori !== 'lansia')>
                            <option value="">Pilih tingkat kemandirian</option>
                            <option value="mandiri" @selected(old('tingkat_kemandirian', $pemeriksaan->tingkat_kemandirian) === 'mandiri')>Mandiri</option>
                            <option value="bantuan_sebagian" @selected(old('tingkat_kemandirian', $pemeriksaan->tingkat_kemandirian) === 'bantuan_sebagian')>Bantuan Sebagian</option>
                            <option value="bantuan_penuh" @selected(old('tingkat_kemandirian', $pemeriksaan->tingkat_kemandirian) === 'bantuan_penuh')>Bantuan Penuh</option>
                        </select>
                    </div>

                    <div class="{{ ! in_array($kategori, ['remaja', 'lansia'], true) ? 'pc-hidden' : '' }}">
                        <label class="pc-label">Gula Darah</label>
                        <input type="number"
                               step="0.1"
                               min="10"
                               max="1000"
                               name="gula_darah"
                               value="{{ old('gula_darah', $pemeriksaan->gula_darah) }}"
                               placeholder="mg/dL"
                               class="pc-field"
                               @disabled(! in_array($kategori, ['remaja', 'lansia'], true))>
                    </div>

                    <div class="{{ ! in_array($kategori, ['remaja', 'lansia'], true) ? 'pc-hidden' : '' }}">
                        <label class="pc-label">Kolesterol</label>
                        <input type="number"
                               min="10"
                               max="1000"
                               name="kolesterol"
                               value="{{ old('kolesterol', $pemeriksaan->kolesterol) }}"
                               placeholder="mg/dL"
                               class="pc-field"
                               @disabled(! in_array($kategori, ['remaja', 'lansia'], true))>
                    </div>

                    <div class="{{ ! in_array($kategori, ['remaja', 'lansia'], true) ? 'pc-hidden' : '' }}">
                        <label class="pc-label">Asam Urat</label>
                        <input type="number"
                               step="0.1"
                               min="1"
                               max="30"
                               name="asam_urat"
                               value="{{ old('asam_urat', $pemeriksaan->asam_urat) }}"
                               placeholder="mg/dL"
                               class="pc-field"
                               @disabled(! in_array($kategori, ['remaja', 'lansia'], true))>
                    </div>

                    <div class="{{ $kategori !== 'remaja' ? 'pc-hidden' : '' }}">
                        <label class="pc-label">Hemoglobin</label>
                        <input type="number"
                               step="0.1"
                               min="1"
                               max="30"
                               name="hemoglobin"
                               value="{{ old('hemoglobin', $pemeriksaan->hemoglobin) }}"
                               placeholder="g/dL"
                               class="pc-field"
                               @disabled($kategori !== 'remaja')>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="pc-label">Keluhan / Kondisi Saat Datang</label>
                        <textarea name="keluhan"
                                  rows="3"
                                  placeholder="Contoh: batuk ringan, pusing, tidak ada keluhan"
                                  class="pc-field resize-none">{{ old('keluhan', $pemeriksaan->keluhan) }}</textarea>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="pc-label">Catatan Kader</label>
                        <textarea name="catatan_kader"
                                  rows="3"
                                  placeholder="Catatan awal dari Kader sebelum direview Bidan"
                                  class="pc-field resize-none">{{ old('catatan_kader', $pemeriksaan->catatan_kader) }}</textarea>
                    </div>
                </div>
            </section>

            <aside class="pc-side-panel flex h-full flex-col space-y-5">
                <div class="pc-side-card pc-glass flex h-full flex-col rounded-[1.75rem] border border-white/80 p-5">
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Ringkasan Revisi</p>
                    <h2 class="mt-1 text-xl font-black text-slate-950">Siap diperbarui</h2>
                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                        Jika data sebelumnya revisi, setelah disimpan status akan kembali ke menunggu review Bidan.
                    </p>

                    <div class="mt-5 space-y-3">
                        <div class="rounded-[1.2rem] border border-white/80 bg-white/75 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-black text-slate-600">Kategori</span>
                                <span class="text-sm font-black text-slate-950">{{ $kategoriMeta['label'] }}</span>
                            </div>
                        </div>

                        <div class="rounded-[1.2rem] border border-white/80 bg-white/75 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-black text-slate-600">Sasaran</span>
                                <span class="line-clamp-1 text-right text-sm font-black text-slate-950">{{ $namaPasien }}</span>
                            </div>
                        </div>

                        <div class="rounded-[1.2rem] border border-white/80 bg-white/75 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-black text-slate-600">Estimasi IMT</span>
                                <span id="summaryImt" class="text-sm font-black text-slate-950">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="pc-side-bottom pt-5">
    <button type="submit"
            id="submitMeasurementBtn"
            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-400/20 transition hover:-translate-y-0.5">
        <i class="fa-solid fa-floppy-disk"></i>
        Simpan Perubahan
    </button>
</div>
                </div>
            </aside>
        </form>
    </div>

    <div id="pcSubmitModal" class="pc-modal-backdrop" aria-hidden="true">
        <div class="pc-modal-card p-6">
            <div class="flex gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.25rem] bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-lg shadow-emerald-500/20">
                    <i class="fa-solid fa-circle-check text-xl"></i>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Konfirmasi</p>
                    <h3 class="mt-1 text-lg font-black text-slate-950">Simpan perubahan?</h3>
                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                        Data akan diperbarui dan dikirim kembali ke antrean review Bidan bila sebelumnya berstatus revisi.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button type="button"
                        id="pcCancelSubmit"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                    Batal
                </button>

                <button type="button"
                        id="pcConfirmSubmit"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-3 text-sm font-black text-white shadow-lg shadow-emerald-400/20 transition hover:-translate-y-0.5">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const form = document.querySelector('#measurementEditForm');
    const summaryImt = document.querySelector('#summaryImt');
    const submitBtn = document.querySelector('#submitMeasurementBtn');
    const category = @json($kategori);

    let submitModal = document.querySelector('#pcSubmitModal');

    if (submitModal && submitModal.parentElement !== document.body) {
        document.body.appendChild(submitModal);
    }

    const cancelSubmit = document.querySelector('#pcCancelSubmit');
    const confirmSubmit = document.querySelector('#pcConfirmSubmit');

    function lockBody() {
        document.documentElement.classList.add('pc-modal-open');
        document.body.classList.add('pc-modal-open');
    }

    function unlockBody() {
        document.documentElement.classList.remove('pc-modal-open');
        document.body.classList.remove('pc-modal-open');
    }

    function calculateImt() {
        const bb = parseFloat(document.querySelector('[name="berat_badan"]')?.value || 0);
        const tb = parseFloat(document.querySelector('[name="tinggi_badan"]')?.value || 0);

        if (!summaryImt) {
            return;
        }

        if (category === 'balita') {
            summaryImt.textContent = 'Tidak dihitung';
            return;
        }

        if (!bb || !tb) {
            summaryImt.textContent = '-';
            return;
        }

        const meter = tb / 100;
        const imt = bb / (meter * meter);

        if (!isFinite(imt)) {
            summaryImt.textContent = '-';
            return;
        }

        summaryImt.textContent = imt.toFixed(2);
    }

    function openSubmitModal() {
        if (!submitModal) {
            HTMLFormElement.prototype.submit.call(form);
            return;
        }

        lockBody();
        submitModal.classList.add('is-open');
        submitModal.setAttribute('aria-hidden', 'false');
    }

    function closeSubmitModal() {
        if (!submitModal) {
            return;
        }

        submitModal.classList.remove('is-open');
        submitModal.setAttribute('aria-hidden', 'true');
        unlockBody();
    }

    document.querySelectorAll('[data-imt-source]').forEach(function (input) {
        input.addEventListener('input', calculateImt);
    });

    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            openSubmitModal();
        });
    }

    if (cancelSubmit) {
        cancelSubmit.addEventListener('click', closeSubmitModal);
    }

    if (submitModal) {
        submitModal.addEventListener('click', function (event) {
            if (event.target === submitModal) {
                closeSubmitModal();
            }
        });
    }

    if (confirmSubmit) {
        confirmSubmit.addEventListener('click', function () {
            confirmSubmit.disabled = true;
            confirmSubmit.classList.add('opacity-70', 'cursor-not-allowed');
            confirmSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
            }

            HTMLFormElement.prototype.submit.call(form);
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeSubmitModal();
        }
    });

    calculateImt();
})();
</script>
@endpush