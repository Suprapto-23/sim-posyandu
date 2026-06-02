@extends('layouts.kader')

@section('title', 'Input Pengukuran Fisik')
@section('page-name', 'Input Pengukuran Fisik')
@section('page-title', 'Input Pengukuran Fisik')

@php
    use Carbon\Carbon;

    $kategori_awal = $kategori_awal ?? request('kategori', 'balita');
    $pasien_id_awal = $pasien_id_awal ?? request('pasien_id');

    if (! in_array($kategori_awal, ['balita', 'remaja', 'lansia'], true)) {
        $kategori_awal = 'balita';
    }

    $kategoriMenus = [
        'balita' => [
            'label' => 'Balita',
            'desc' => 'BB, TB, LK, dan LiLA',
            'icon' => 'fa-child-reaching',
            'solid' => 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white',
            'soft' => 'border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-white',
            'text' => 'text-emerald-700',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'desc' => 'BB, TB, LiLA, LP, dan tensi',
            'icon' => 'fa-user-graduate',
            'solid' => 'bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white',
            'soft' => 'border-violet-200 bg-gradient-to-br from-violet-50 via-fuchsia-50 to-white',
            'text' => 'text-violet-700',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'desc' => 'BB, TB, LP, tensi, dan kemandirian',
            'icon' => 'fa-person-cane',
            'solid' => 'bg-gradient-to-br from-sky-500 to-cyan-500 text-white',
            'soft' => 'border-sky-200 bg-gradient-to-br from-sky-50 via-cyan-50 to-white',
            'text' => 'text-sky-700',
        ],
    ];

    $today = now('Asia/Jakarta')->toDateString();

    $oldKategori = old('kategori_pasien', $kategori_awal);
    $oldPasienId = old('pasien_id', $pasien_id_awal);
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

    .pc-soft-hover {
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .pc-soft-hover:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 36px rgba(15, 23, 42, .07);
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

    .pc-help {
        margin-top: .4rem;
        font-size: .72rem;
        font-weight: 700;
        color: #94a3b8;
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

    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
            scroll-behavior: auto !important;
        }
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
                            Input Pengukuran Kader
                        </div>

                        <h1 class="mt-4 max-w-4xl text-2xl font-black leading-tight tracking-tight text-slate-950 sm:text-[1.85rem] lg:text-[2rem]">
                            Catat pengukuran fisik sasaran dengan data yang jelas.
                        </h1>

                        <p class="mt-3 max-w-3xl text-sm font-medium leading-6 text-slate-600">
                            Pilih kategori, pilih sasaran dari database, lalu isi hasil pengukuran. Data ini masuk antrean review Bidan, bukan langsung jadi vonis kesehatan. Kita bukan sedang bikin aplikasi dukun.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('kader.pemeriksaan.index') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-white/80 px-4 py-2.5 text-sm font-black text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-white">
                            <i class="fa-solid fa-arrow-left"></i>
                            Kembali
                        </a>

                        <a href="{{ route('kader.dashboard') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white/80 px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-white">
                            <i class="fa-solid fa-chart-line"></i>
                            Dashboard
                        </a>
                    </div>
                </div>

                <div class="pc-glass rounded-[1.55rem] border border-white/80 p-5">
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Status Output</p>
                    <h2 class="mt-2 text-xl font-black text-slate-950">Menunggu Review</h2>
                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                        Setelah disimpan, data akan masuk ke halaman Bidan untuk dicek sebelum dipakai sebagai riwayat warga.
                    </p>

                    <div class="mt-5 rounded-[1.2rem] border border-amber-200 bg-amber-50/80 p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-amber-600">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-950">Default status</p>
                                <p class="text-xs font-bold text-slate-500">Menunggu review Bidan</p>
                            </div>
                        </div>
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
                                <p>Form belum valid. Bereskan dulu sebelum disimpan.</p>
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

        <form id="measurementForm"
      method="POST"
      action="{{ route('kader.pemeriksaan.store') }}"
      data-selected-pasien="{{ $oldPasienId }}"
      data-api-url="{{ route('kader.pemeriksaan.api') }}"
      class="pc-balanced-grid grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(330px,.55fr)]">
            @csrf

            <section class="pc-left-panel pc-glass flex h-full flex-col rounded-[1.75rem] border border-white/80 p-5">
                <div class="mb-5">
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Kategori Sasaran</p>
                    <h2 class="mt-1 text-xl font-black tracking-tight text-slate-950">Pilih kelompok data</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">
                        Field wajib akan berubah sesuai kategori. Teknologi akhirnya melakukan sesuatu yang masuk akal.
                    </p>
                </div>

                <div class="grid gap-3 md:grid-cols-3">
                    @foreach($kategoriMenus as $key => $item)
                        <label class="pc-soft-hover cursor-pointer rounded-[1.35rem] border p-4 {{ $oldKategori === $key ? $item['soft'] : 'border-slate-200 bg-white/75' }}">
                            <input type="radio"
                                   name="kategori_pasien"
                                   value="{{ $key }}"
                                   class="sr-only"
                                   data-category-radio
                                   @checked($oldKategori === $key)>

                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $item['solid'] }}">
                                    <i class="fa-solid {{ $item['icon'] }}"></i>
                                </div>

                                <div class="min-w-0">
                                    <p class="text-sm font-black text-slate-950">{{ $item['label'] }}</p>
                                    <p class="mt-1 line-clamp-1 text-xs font-semibold text-slate-500">{{ $item['desc'] }}</p>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    <div class="lg:col-span-2">
                        <label class="pc-label pc-required">Pilih Sasaran</label>
                        <select name="pasien_id" id="pasienSelect" class="pc-field">
                            <option value="">Memuat data sasaran...</option>
                        </select>
                        <p id="pasienHelp" class="pc-help">Pilih sasaran dari database, jangan input manual seperti catatan warung.</p>
                    </div>

                    <div>
                        <label class="pc-label pc-required">Tanggal Pengukuran</label>
                        <input type="date"
                               name="tanggal_periksa"
                               value="{{ old('tanggal_periksa', $today) }}"
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
                               value="{{ old('suhu_tubuh') }}"
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
                               value="{{ old('berat_badan') }}"
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
                               value="{{ old('tinggi_badan') }}"
                               placeholder="Cm"
                               class="pc-field"
                               data-imt-source>
                    </div>

                    <div data-field-group="balita">
                        <label class="pc-label pc-required">Lingkar Kepala</label>
                        <input type="number"
                               step="0.1"
                               min="10"
                               max="100"
                               name="lingkar_kepala"
                               value="{{ old('lingkar_kepala') }}"
                               placeholder="Cm"
                               class="pc-field">
                    </div>

                    <div data-field-group="balita remaja">
                        <label class="pc-label pc-required">Lingkar Lengan Atas</label>
                        <input type="number"
                               step="0.1"
                               min="5"
                               max="100"
                               name="lingkar_lengan"
                               value="{{ old('lingkar_lengan') }}"
                               placeholder="Cm"
                               class="pc-field">
                    </div>

                    <div data-field-group="remaja lansia">
                        <label class="pc-label pc-required">Lingkar Perut</label>
                        <input type="number"
                               step="0.1"
                               min="20"
                               max="200"
                               name="lingkar_perut"
                               value="{{ old('lingkar_perut') }}"
                               placeholder="Cm"
                               class="pc-field">
                    </div>

                    <div data-field-group="remaja lansia">
                        <label class="pc-label pc-required">Tekanan Darah</label>
                        <input type="text"
                               name="tekanan_darah"
                               value="{{ old('tekanan_darah') }}"
                               placeholder="Contoh: 120/80"
                               class="pc-field">
                    </div>

                    <div data-field-group="lansia">
                        <label class="pc-label pc-required">Tingkat Kemandirian</label>
                        <select name="tingkat_kemandirian" class="pc-field">
                            <option value="">Pilih tingkat kemandirian</option>
                            <option value="mandiri" @selected(old('tingkat_kemandirian') === 'mandiri')>Mandiri</option>
                            <option value="bantuan_sebagian" @selected(old('tingkat_kemandirian') === 'bantuan_sebagian')>Bantuan Sebagian</option>
                            <option value="bantuan_penuh" @selected(old('tingkat_kemandirian') === 'bantuan_penuh')>Bantuan Penuh</option>
                        </select>
                    </div>

                    <div data-field-group="remaja lansia">
                        <label class="pc-label">Gula Darah</label>
                        <input type="number"
                               step="0.1"
                               min="10"
                               max="1000"
                               name="gula_darah"
                               value="{{ old('gula_darah') }}"
                               placeholder="mg/dL"
                               class="pc-field">
                    </div>

                    <div data-field-group="remaja lansia">
                        <label class="pc-label">Kolesterol</label>
                        <input type="number"
                               min="10"
                               max="1000"
                               name="kolesterol"
                               value="{{ old('kolesterol') }}"
                               placeholder="mg/dL"
                               class="pc-field">
                    </div>

                    <div data-field-group="remaja lansia">
                        <label class="pc-label">Asam Urat</label>
                        <input type="number"
                               step="0.1"
                               min="1"
                               max="30"
                               name="asam_urat"
                               value="{{ old('asam_urat') }}"
                               placeholder="mg/dL"
                               class="pc-field">
                    </div>

                    <div data-field-group="remaja">
                        <label class="pc-label">Hemoglobin</label>
                        <input type="number"
                               step="0.1"
                               min="1"
                               max="30"
                               name="hemoglobin"
                               value="{{ old('hemoglobin') }}"
                               placeholder="g/dL"
                               class="pc-field">
                    </div>

                    <div class="lg:col-span-2">
                        <label class="pc-label">Keluhan / Kondisi Saat Datang</label>
                        <textarea name="keluhan"
                                  rows="3"
                                  placeholder="Contoh: batuk ringan, pusing, tidak ada keluhan"
                                  class="pc-field resize-none">{{ old('keluhan') }}</textarea>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="pc-label">Catatan Kader</label>
                        <textarea name="catatan_kader"
                                  rows="3"
                                  placeholder="Catatan awal dari Kader sebelum direview Bidan"
                                  class="pc-field resize-none">{{ old('catatan_kader') }}</textarea>
                    </div>
                </div>
            </section>

            <aside class="pc-side-panel flex h-full flex-col space-y-5">
                <div class="pc-side-card pc-glass flex h-full flex-col rounded-[1.75rem] border border-white/80 p-5">
                    <p class="text-[11px] font-black uppercase tracking-[.18em] text-emerald-700">Ringkasan Input</p>
                    <h2 class="mt-1 text-xl font-black text-slate-950">Cek sebelum simpan</h2>
                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                        Pastikan kategori, sasaran, tanggal, BB, dan TB sudah benar. Salah data di sini bikin Bidan ikut menghela napas.
                    </p>

                    <div class="mt-5 space-y-3">
                        <div class="rounded-[1.2rem] border border-white/80 bg-white/75 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-black text-slate-600">Kategori</span>
                                <span id="summaryKategori" class="text-sm font-black text-slate-950">-</span>
                            </div>
                        </div>

                        <div class="rounded-[1.2rem] border border-white/80 bg-white/75 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-sm font-black text-slate-600">Sasaran</span>
                                <span id="summaryPasien" class="line-clamp-1 text-right text-sm font-black text-slate-950">-</span>
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
        Simpan Pengukuran
    </button>

    <p class="mt-3 text-center text-xs font-semibold leading-5 text-slate-500">
        Data tersimpan sebagai menunggu review Bidan.
    </p>
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
                    <h3 class="mt-1 text-lg font-black text-slate-950">Simpan pengukuran?</h3>
                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                        Data akan dikirim ke antrean review Bidan. Pastikan sasaran dan hasil ukur sudah benar.
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

    const form = document.querySelector('#measurementForm');
    const categoryRadios = Array.from(document.querySelectorAll('[data-category-radio]'));
    const pasienSelect = document.querySelector('#pasienSelect');
    const pasienHelp = document.querySelector('#pasienHelp');
    const summaryKategori = document.querySelector('#summaryKategori');
    const summaryPasien = document.querySelector('#summaryPasien');
    const summaryImt = document.querySelector('#summaryImt');
    const submitBtn = document.querySelector('#submitMeasurementBtn');

    let submitModal = document.querySelector('#pcSubmitModal');

    if (submitModal && submitModal.parentElement !== document.body) {
        document.body.appendChild(submitModal);
    }

    const cancelSubmit = document.querySelector('#pcCancelSubmit');
    const confirmSubmit = document.querySelector('#pcConfirmSubmit');

    const categoryLabels = {
        balita: 'Balita',
        remaja: 'Remaja',
        lansia: 'Lansia'
    };

    function lockBody() {
        document.documentElement.classList.add('pc-modal-open');
        document.body.classList.add('pc-modal-open');
    }

    function unlockBody() {
        document.documentElement.classList.remove('pc-modal-open');
        document.body.classList.remove('pc-modal-open');
    }

    function getSelectedCategory() {
        const selected = document.querySelector('[data-category-radio]:checked');
        return selected ? selected.value : 'balita';
    }

    function updateCategoryCards() {
        const selected = getSelectedCategory();

        categoryRadios.forEach(function (radio) {
            const card = radio.closest('label');

            if (!card) {
                return;
            }

            card.classList.remove(
                'border-emerald-200',
                'border-violet-200',
                'border-sky-200',
                'bg-gradient-to-br',
                'from-emerald-50',
                'from-violet-50',
                'from-sky-50',
                'via-teal-50',
                'via-fuchsia-50',
                'via-cyan-50',
                'to-white'
            );

            if (radio.checked) {
                if (radio.value === 'balita') {
                    card.classList.add('border-emerald-200', 'bg-gradient-to-br', 'from-emerald-50', 'via-teal-50', 'to-white');
                } else if (radio.value === 'remaja') {
                    card.classList.add('border-violet-200', 'bg-gradient-to-br', 'from-violet-50', 'via-fuchsia-50', 'to-white');
                } else {
                    card.classList.add('border-sky-200', 'bg-gradient-to-br', 'from-sky-50', 'via-cyan-50', 'to-white');
                }
            } else {
                card.classList.add('border-slate-200');
            }
        });

        if (summaryKategori) {
            summaryKategori.textContent = categoryLabels[selected] || '-';
        }
    }

    function updateVisibleFields() {
        const selected = getSelectedCategory();

        document.querySelectorAll('[data-field-group]').forEach(function (group) {
            const values = String(group.dataset.fieldGroup || '').split(' ');
            const visible = values.includes(selected);

            group.classList.toggle('pc-hidden', !visible);

            group.querySelectorAll('input, select, textarea').forEach(function (input) {
                input.disabled = !visible;
            });
        });
    }

    function loadPasien() {
        if (!form || !pasienSelect) {
            return;
        }

        const category = getSelectedCategory();
        const apiUrl = form.dataset.apiUrl;
        const selectedPasien = form.dataset.selectedPasien || '';

        pasienSelect.innerHTML = '<option value="">Memuat data sasaran...</option>';
        pasienSelect.disabled = true;

        fetch(apiUrl + '?kategori=' + encodeURIComponent(category), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            cache: 'no-store'
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Gagal memuat sasaran');
                }

                return response.json();
            })
            .then(function (payload) {
                const rows = Array.isArray(payload.data) ? payload.data : [];

                pasienSelect.innerHTML = '<option value="">Pilih sasaran</option>';

                rows.forEach(function (item) {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = `${item.nama} - NIK ${item.nik || '-'}`;

                    if (String(item.id) === String(selectedPasien)) {
                        option.selected = true;
                    }

                    pasienSelect.appendChild(option);
                });

                pasienSelect.disabled = false;

                if (pasienHelp) {
                    pasienHelp.textContent = rows.length
                        ? `${rows.length} sasaran tersedia untuk kategori ${categoryLabels[category]}.`
                        : 'Belum ada data sasaran pada kategori ini.';
                }

                updateSummaryPasien();
            })
            .catch(function () {
                pasienSelect.innerHTML = '<option value="">Gagal memuat data sasaran</option>';

                if (pasienHelp) {
                    pasienHelp.textContent = 'API sasaran gagal dimuat. Cek route kader.pemeriksaan.api.';
                }
            });
    }

    function updateSummaryPasien() {
        if (!summaryPasien || !pasienSelect) {
            return;
        }

        const selected = pasienSelect.options[pasienSelect.selectedIndex];
        summaryPasien.textContent = selected && selected.value ? selected.textContent : '-';
    }

    function calculateImt() {
        const category = getSelectedCategory();
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

    function refreshAll(reloadPasien = false) {
        updateCategoryCards();
        updateVisibleFields();
        calculateImt();

        if (reloadPasien) {
            form.dataset.selectedPasien = '';
            loadPasien();
        }
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

    categoryRadios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            refreshAll(true);
        });
    });

    if (pasienSelect) {
        pasienSelect.addEventListener('change', updateSummaryPasien);
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

    refreshAll(false);
    loadPasien();
})();
</script>
@endpush