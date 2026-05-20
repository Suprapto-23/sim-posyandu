@extends('layouts.kader')

@section('title', 'Edit Pengukuran Fisik')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $pasien = $pemeriksaan->kunjungan->pasien ?? null;

    $namaPasien = $pasien->nama_lengkap
        ?? $pasien->nama
        ?? $pemeriksaan->nama_pasien
        ?? 'Data sasaran';

    $nikPasien = $pasien->nik
        ?? $pemeriksaan->nik_pasien
        ?? '-';

    $kategori = $pemeriksaan->kategori_pasien ?? 'balita';

    $kategoriLabel = match($kategori) {
        'balita' => 'Balita / Anak',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
        default => 'Sasaran',
    };

    $kategoriIcon = match($kategori) {
        'balita' => 'fa-child-reaching',
        'remaja' => 'fa-user-graduate',
        'lansia' => 'fa-person-cane',
        default => 'fa-users',
    };

    $statusRaw = strtolower($pemeriksaan->status_verifikasi ?? 'pending');

    $isReviewed = in_array($statusRaw, [
        'verified',
        'terverifikasi',
        'approved',
        'valid',
        'tervalidasi',
        'sudah_ditinjau',
    ]);

    $needFix = in_array($statusRaw, [
        'ditolak',
        'rejected',
        'direvisi',
        'perlu_perbaikan',
    ]);

    $statusLabel = match(true) {
        $isReviewed => 'Sudah Ditinjau',
        $needFix => 'Perlu Perbaikan',
        default => 'Menunggu Review',
    };

    $statusClass = match(true) {
        $isReviewed => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        $needFix => 'bg-rose-50 text-rose-700 border-rose-100',
        default => 'bg-amber-50 text-amber-700 border-amber-100',
    };

    $catatanBidan = $pemeriksaan->catatan_validasi
        ?? $pemeriksaan->catatan_bidan
        ?? $pemeriksaan->catatan_review
        ?? null;

    $routeHas = fn ($name) => Route::has($name);

    $tanggalValue = old(
        'tanggal_periksa',
        !empty($pemeriksaan->tanggal_periksa)
            ? Carbon::parse($pemeriksaan->tanggal_periksa)->format('Y-m-d')
            : now()->toDateString()
    );
@endphp

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap');

    .edit-page {
        position: relative;
        isolation: isolate;
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        animation: pageIn .18s ease-out both;
    }

    .edit-page::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background:
            radial-gradient(circle at 8% 6%, rgba(16,185,129,.12), transparent 26%),
            radial-gradient(circle at 92% 10%, rgba(245,158,11,.10), transparent 24%),
            linear-gradient(135deg, #f7fffc 0%, #f8fafc 55%, #fffaf1 100%);
    }

    @keyframes pageIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .hero {
        border: 1px solid rgba(167,243,208,.65);
        background:
            radial-gradient(circle at 12% 18%, rgba(16,185,129,.14), transparent 28%),
            radial-gradient(circle at 90% 16%, rgba(245,158,11,.12), transparent 30%),
            linear-gradient(135deg, rgba(255,255,255,.96), rgba(236,253,245,.78));
        box-shadow: 0 14px 34px rgba(15,23,42,.055);
    }

    .panel {
        border: 1px solid rgba(226,232,240,.88);
        background: rgba(255,255,255,.95);
        box-shadow: 0 10px 28px rgba(15,23,42,.045);
    }

    .badge-title {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        border-radius: 999px;
        border: 1px solid rgba(16,185,129,.18);
        background: rgba(236,253,245,.88);
        color: #047857;
        padding: .55rem .85rem;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .btn-soft {
        border-radius: 16px;
        font-weight: 900;
        transition: background .15s ease, transform .15s ease, border-color .15s ease;
    }

    .btn-soft:hover {
        transform: translateY(-1px);
    }

    .btn-primary {
        color: white;
        background: linear-gradient(135deg, #059669, #10b981);
        box-shadow: 0 10px 20px rgba(5,150,105,.14);
    }

    .btn-dark {
        color: white;
        background: linear-gradient(135deg, #0f172a, #1e293b);
        box-shadow: 0 10px 20px rgba(15,23,42,.12);
    }

    .btn-outline {
        border: 1px solid rgba(16,185,129,.18);
        color: #047857;
        background: white;
    }

    .btn-outline:hover {
        background: #ecfdf5;
    }

    .input-soft {
        border: 1px solid rgba(226,232,240,.9);
        background: white;
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .input-soft:focus {
        border-color: rgba(16,185,129,.28);
        box-shadow: 0 0 0 4px rgba(16,185,129,.08);
    }

    .field-group {
        border: 1px solid rgba(226,232,240,.82);
        background: #fff;
    }

    .identity-card {
        border: 1px solid rgba(16,185,129,.16);
        background: #ecfdf5;
    }

    .fix-alert {
        border: 1px solid rgba(244,63,94,.16);
        background: #fff1f2;
        color: #be123c;
    }

    .review-alert {
        border: 1px solid rgba(245,158,11,.18);
        background: #fff8eb;
        color: #92400e;
    }

    @media (max-width: 640px) {
        .btn-soft:hover { transform: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .edit-page,
        .btn-soft {
            animation: none !important;
            transition: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="edit-page space-y-5">

    {{-- HERO --}}
    <section class="hero rounded-[28px] p-5 sm:p-6">
        <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="badge-title mb-3">
                    <i class="fa-solid {{ $needFix ? 'fa-screwdriver-wrench' : 'fa-pen-to-square' }}"></i>
                    {{ $needFix ? 'Perbaiki Pengukuran' : 'Edit Pengukuran' }}
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    {{ $needFix ? 'Perbaiki Pengukuran Fisik' : 'Edit Pengukuran Fisik' }}
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Perbarui data pengukuran awal {{ $kategoriLabel }}. Setelah disimpan, data akan kembali masuk daftar <b>Menunggu Review</b> Bidan.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:flex">
                @if($routeHas('kader.pemeriksaan.show'))
                    <a href="{{ route('kader.pemeriksaan.show', $pemeriksaan->id) }}" class="btn-soft btn-outline inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                        <i class="fa-solid fa-arrow-left"></i>
                        Detail
                    </a>
                @endif

                @if($routeHas('kader.pemeriksaan.index'))
                    <a href="{{ route('kader.pemeriksaan.index') }}" class="btn-soft btn-dark inline-flex items-center justify-center gap-2 px-5 py-3 text-sm">
                        <i class="fa-solid fa-list"></i>
                        Daftar
                    </a>
                @endif
            </div>
        </div>
    </section>

    @if($errors->any())
        <section class="rounded-[22px] border border-rose-100 bg-rose-50 p-4 text-sm font-bold text-rose-700">
            <div class="mb-2 flex items-center gap-2 font-black">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Ada input yang perlu diperbaiki
            </div>
            <ul class="ml-5 list-disc space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    @if($needFix && !empty($catatanBidan))
        <section class="fix-alert rounded-[22px] p-4 text-sm font-bold leading-6">
            <div class="mb-2 flex items-center gap-2 font-black">
                <i class="fa-solid fa-note-sticky"></i>
                Catatan Bidan
            </div>
            <p>{{ $catatanBidan }}</p>
        </section>
    @elseif(!$needFix)
        <section class="review-alert rounded-[22px] p-4 text-sm font-bold leading-6">
            <div class="mb-2 flex items-center gap-2 font-black">
                <i class="fa-solid fa-circle-info"></i>
                Status Data
            </div>
            <p>Data saat ini berstatus {{ $statusLabel }}. Perubahan akan disimpan sebagai data yang menunggu review Bidan.</p>
        </section>
    @endif

    <form method="POST" action="{{ route('kader.pemeriksaan.update', $pemeriksaan->id) }}" id="formEditPengukuran" class="grid grid-cols-1 gap-5 xl:grid-cols-12">
        @csrf
        @method('PUT')

        {{-- LEFT --}}
        <section class="panel rounded-[26px] p-4 sm:p-5 xl:col-span-4">
            <div class="mb-4">
                <h2 class="text-lg font-black text-slate-900">Data Sasaran</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Identitas sasaran tidak diubah dari halaman ini.
                </p>
            </div>

            <div class="identity-card rounded-[24px] p-5">
                <div class="mb-4 flex items-start gap-4">
                    <div class="grid h-14 w-14 shrink-0 place-items-center rounded-3xl bg-white text-emerald-700">
                        <i class="fa-solid {{ $kategoriIcon }} text-lg"></i>
                    </div>

                    <div class="min-w-0">
                        <span class="rounded-full bg-white px-3 py-1 text-[10px] font-black uppercase tracking-[.1em] text-emerald-700">
                            {{ $kategoriLabel }}
                        </span>

                        <h3 class="mt-3 truncate text-lg font-black text-slate-900">
                            {{ $namaPasien }}
                        </h3>

                        <p class="mt-1 text-xs font-bold text-slate-500">
                            <i class="fa-solid fa-id-card mr-1"></i>
                            {{ $nikPasien }}
                        </p>
                    </div>
                </div>

                <div class="rounded-2xl bg-white/80 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Status Review</p>
                    <p class="mt-2 inline-flex items-center rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] {{ $statusClass }}">
                        {{ $statusLabel }}
                    </p>
                </div>
            </div>

            <div class="mt-4 rounded-[22px] border border-slate-200 bg-slate-50 p-4">
                <h3 class="text-sm font-black text-slate-900">Alur Perbaikan</h3>
                <p class="mt-2 text-xs font-bold leading-5 text-slate-500">
                    Jika data diperbaiki, status akan kembali menjadi Menunggu Review agar Bidan dapat meninjau ulang hasil pengukuran.
                </p>
            </div>
        </section>

        {{-- RIGHT --}}
        <section class="panel rounded-[26px] p-4 sm:p-5 xl:col-span-8">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Form Pengukuran</h2>
                    <p class="mt-1 text-xs font-bold leading-5 text-slate-500">
                        Edit parameter sesuai kategori {{ $kategoriLabel }}. Kolom yang tidak diperiksa dapat dikosongkan.
                    </p>
                </div>

                <span class="w-fit rounded-full bg-amber-50 px-3 py-1 text-[10px] font-black uppercase tracking-[.12em] text-amber-700">
                    Akan Menunggu Review
                </span>
            </div>

            <div class="space-y-5">

                {{-- TANGGAL --}}
                <div class="field-group rounded-[22px] p-4">
                    <h3 class="mb-3 text-sm font-black text-slate-900">Tanggal Pengukuran</h3>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                                Tanggal
                            </label>
                            <input
                                type="date"
                                name="tanggal_periksa"
                                value="{{ $tanggalValue }}"
                                max="{{ now()->toDateString() }}"
                                class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700"
                                required
                            >
                        </div>
                    </div>
                </div>

                {{-- PARAMETER DASAR --}}
                <div class="field-group rounded-[22px] p-4">
                    <h3 class="mb-3 text-sm font-black text-slate-900">Parameter Dasar</h3>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Berat Badan</label>
                            <div class="relative">
                                <input
                                    type="number"
                                    step="0.1"
                                    min="0.1"
                                    max="300"
                                    name="berat_badan"
                                    id="berat_badan"
                                    value="{{ old('berat_badan', $pemeriksaan->berat_badan) }}"
                                    placeholder="20"
                                    class="input-soft h-12 w-full rounded-2xl px-4 pr-12 text-sm font-bold text-slate-700"
                                >
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">kg</span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                                {{ $kategori === 'balita' ? 'Tinggi / Panjang Badan' : 'Tinggi Badan' }}
                            </label>
                            <div class="relative">
                                <input
                                    type="number"
                                    step="0.1"
                                    min="10"
                                    max="250"
                                    name="tinggi_badan"
                                    id="tinggi_badan"
                                    value="{{ old('tinggi_badan', $pemeriksaan->tinggi_badan) }}"
                                    placeholder="100"
                                    class="input-soft h-12 w-full rounded-2xl px-4 pr-12 text-sm font-bold text-slate-700"
                                >
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">cm</span>
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">IMT Otomatis</label>
                            <input
                                type="text"
                                id="imt_preview"
                                value="-"
                                class="h-12 w-full rounded-2xl border border-slate-100 bg-slate-50 px-4 text-sm font-black text-slate-700"
                                readonly
                            >
                        </div>
                    </div>
                </div>

                {{-- BALITA --}}
                @if($kategori === 'balita')
                    <div class="field-group rounded-[22px] p-4">
                        <h3 class="mb-3 text-sm font-black text-slate-900">Parameter Balita / Anak</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Suhu Tubuh</label>
                                <input type="number" step="0.1" min="30" max="45" name="suhu_tubuh" value="{{ old('suhu_tubuh', $pemeriksaan->suhu_tubuh) }}" placeholder="36.5" class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Lingkar Kepala</label>
                                <input type="number" step="0.1" min="10" max="100" name="lingkar_kepala" value="{{ old('lingkar_kepala', $pemeriksaan->lingkar_kepala) }}" placeholder="45" class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">LiLA</label>
                                <input type="number" step="0.1" min="5" max="100" name="lingkar_lengan" value="{{ old('lingkar_lengan', $pemeriksaan->lingkar_lengan) }}" placeholder="14" class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- REMAJA --}}
                @if($kategori === 'remaja')
                    <div class="field-group rounded-[22px] p-4">
                        <h3 class="mb-3 text-sm font-black text-slate-900">Parameter Remaja</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">LiLA</label>
                                <input type="number" step="0.1" min="5" max="100" name="lingkar_lengan" value="{{ old('lingkar_lengan', $pemeriksaan->lingkar_lengan) }}" placeholder="24" class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Lingkar Perut</label>
                                <input type="number" step="0.1" min="20" max="200" name="lingkar_perut" value="{{ old('lingkar_perut', $pemeriksaan->lingkar_perut) }}" placeholder="75" class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Tekanan Darah</label>
                                <input type="text" name="tekanan_darah" value="{{ old('tekanan_darah', $pemeriksaan->tekanan_darah) }}" placeholder="120/80" class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Hemoglobin / Hb</label>
                                <input type="number" step="0.1" min="1" max="30" name="hemoglobin" value="{{ old('hemoglobin', $pemeriksaan->hemoglobin) }}" placeholder="13.5" class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Suhu Tubuh</label>
                                <input type="number" step="0.1" min="30" max="45" name="suhu_tubuh" value="{{ old('suhu_tubuh', $pemeriksaan->suhu_tubuh) }}" placeholder="36.5" class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- LANSIA --}}
                @if($kategori === 'lansia')
                    <div class="field-group rounded-[22px] p-4">
                        <h3 class="mb-3 text-sm font-black text-slate-900">Parameter Lansia</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Lingkar Perut</label>
                                <input type="number" step="0.1" min="20" max="200" name="lingkar_perut" value="{{ old('lingkar_perut', $pemeriksaan->lingkar_perut) }}" placeholder="85" class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Tekanan Darah</label>
                                <input type="text" name="tekanan_darah" value="{{ old('tekanan_darah', $pemeriksaan->tekanan_darah) }}" placeholder="120/80" class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Gula Darah</label>
                                <input type="number" step="0.1" min="10" max="1000" name="gula_darah" value="{{ old('gula_darah', $pemeriksaan->gula_darah) }}" placeholder="120" class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Kolesterol</label>
                                <input type="number" min="10" max="1000" name="kolesterol" value="{{ old('kolesterol', $pemeriksaan->kolesterol) }}" placeholder="180" class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Asam Urat</label>
                                <input type="number" step="0.1" min="1" max="30" name="asam_urat" value="{{ old('asam_urat', $pemeriksaan->asam_urat) }}" placeholder="6.5" class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Hemoglobin / Hb</label>
                                <input type="number" step="0.1" min="1" max="30" name="hemoglobin" value="{{ old('hemoglobin', $pemeriksaan->hemoglobin) }}" placeholder="13.5" class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                            </div>

                            <div class="md:col-span-3">
                                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Tingkat Kemandirian</label>
                                <select name="tingkat_kemandirian" class="input-soft h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                                    <option value="">Pilih tingkat kemandirian</option>
                                    <option value="mandiri" {{ old('tingkat_kemandirian', $pemeriksaan->tingkat_kemandirian) === 'mandiri' ? 'selected' : '' }}>Mandiri</option>
                                    <option value="bantuan_sebagian" {{ old('tingkat_kemandirian', $pemeriksaan->tingkat_kemandirian) === 'bantuan_sebagian' ? 'selected' : '' }}>Perlu Bantuan Sebagian</option>
                                    <option value="bantuan_penuh" {{ old('tingkat_kemandirian', $pemeriksaan->tingkat_kemandirian) === 'bantuan_penuh' ? 'selected' : '' }}>Perlu Bantuan Penuh</option>
                                </select>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- CATATAN --}}
                <div class="field-group rounded-[22px] p-4">
                    <h3 class="mb-3 text-sm font-black text-slate-900">Catatan Kader</h3>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Keluhan</label>
                            <textarea name="keluhan" rows="4" placeholder="Contoh: pusing, demam, batuk, atau tidak ada keluhan..." class="input-soft w-full rounded-2xl px-4 py-3 text-sm font-bold text-slate-700">{{ old('keluhan', $pemeriksaan->keluhan) }}</textarea>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Catatan Tambahan</label>
                            <textarea name="catatan_kader" rows="4" placeholder="Catatan pengukuran atau informasi pendukung..." class="input-soft w-full rounded-2xl px-4 py-3 text-sm font-bold text-slate-700">{{ old('catatan_kader', $pemeriksaan->catatan_kader) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ACTION --}}
                <div class="sticky bottom-4 z-20 rounded-[24px] border border-emerald-100 bg-white/95 p-4 shadow-[0_14px_34px_rgba(15,23,42,.10)]">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-sm font-black text-slate-900">
                                {{ $needFix ? 'Simpan Perbaikan Data' : 'Simpan Perubahan Data' }}
                            </p>
                            <p class="mt-1 text-xs font-bold text-slate-400">
                                Setelah disimpan, data akan masuk daftar Menunggu Review Bidan.
                            </p>
                        </div>

                        <button type="submit" class="btn-soft btn-primary inline-flex items-center justify-center gap-2 px-6 py-3 text-sm">
                            <i class="fa-solid fa-floppy-disk"></i>
                            {{ $needFix ? 'Simpan Perbaikan' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const kategori = @json($kategori);
        const beratInput = document.getElementById('berat_badan');
        const tinggiInput = document.getElementById('tinggi_badan');
        const imtPreview = document.getElementById('imt_preview');

        const updateImt = () => {
            if (!imtPreview) return;

            if (kategori === 'balita') {
                imtPreview.value = 'Tidak dihitung';
                return;
            }

            const berat = parseFloat(beratInput?.value || 0);
            const tinggi = parseFloat(tinggiInput?.value || 0);

            if (!berat || !tinggi) {
                imtPreview.value = '-';
                return;
            }

            const meter = tinggi / 100;
            const imt = berat / (meter * meter);

            imtPreview.value = Number.isFinite(imt) ? imt.toFixed(2) : '-';
        };

        beratInput?.addEventListener('input', updateImt);
        tinggiInput?.addEventListener('input', updateImt);

        updateImt();
    });
</script>
@endpush