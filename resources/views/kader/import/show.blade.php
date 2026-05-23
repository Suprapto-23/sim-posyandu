@extends('layouts.kader')

@section('title', 'Detail Riwayat Import')
@section('page-name', 'Detail Riwayat Import')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Storage;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $typeMeta = [
        'balita' => [
            'label' => 'Balita / Anak',
            'icon' => 'fa-child-reaching',
            'class' => 'border-emerald-100 bg-emerald-50/80 text-emerald-700',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'icon' => 'fa-user-graduate',
            'class' => 'border-sky-100 bg-sky-50/80 text-sky-700',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'icon' => 'fa-person-cane',
            'class' => 'border-amber-100 bg-amber-50/80 text-amber-700',
        ],
    ];

    $statusMeta = [
        'completed' => [
            'label' => 'Berhasil',
            'icon' => 'fa-circle-check',
            'class' => 'border-emerald-100 bg-emerald-50/80 text-emerald-700',
            'soft' => 'border-emerald-100 bg-emerald-50/70 text-emerald-700',
        ],
        'processing' => [
            'label' => 'Diproses',
            'icon' => 'fa-clock',
            'class' => 'border-amber-100 bg-amber-50/80 text-amber-700',
            'soft' => 'border-amber-100 bg-amber-50/70 text-amber-700',
        ],
        'failed' => [
            'label' => 'Gagal',
            'icon' => 'fa-circle-xmark',
            'class' => 'border-rose-100 bg-rose-50/80 text-rose-700',
            'soft' => 'border-rose-100 bg-rose-50/70 text-rose-700',
        ],
    ];

    $statusData = $statusMeta[$import->status] ?? $statusMeta['processing'];

    $typeData = $typeMeta[$import->jenis_data] ?? [
        'label' => ucfirst($import->jenis_data ?? '-'),
        'icon' => 'fa-database',
        'class' => 'border-slate-100 bg-slate-50/80 text-slate-700',
    ];

    $creatorName = $import->creator?->name
        ?? $import->creator?->nama
        ?? 'Kader';

    $createdLabel = $import->created_at
        ? $import->created_at->translatedFormat('l, d F Y') . ' pukul ' . $import->created_at->format('H:i') . ' WIB'
        : '-';

    $updatedLabel = $import->updated_at
        ? $import->updated_at->translatedFormat('l, d F Y') . ' pukul ' . $import->updated_at->format('H:i') . ' WIB'
        : '-';

    $catatan = trim((string) ($import->catatan ?? 'Tidak ada catatan sistem.'));
    $filePath = $import->file_path ?? null;
    $fileExists = $filePath && Storage::exists($filePath);
@endphp

@push('styles')
<style>
    .import-show-page {
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        position: relative;
        isolation: isolate;
    }

    .import-show-page::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background:
            radial-gradient(circle at 8% 8%, rgba(16,185,129,.13), transparent 28%),
            radial-gradient(circle at 92% 12%, rgba(245,158,11,.10), transparent 26%),
            radial-gradient(circle at 50% 100%, rgba(14,165,233,.08), transparent 32%),
            linear-gradient(135deg, #f8fffc 0%, #f8fafc 58%, #fffaf0 100%);
    }

    .glass-panel {
        border: 1px solid rgba(255,255,255,.78);
        background: rgba(255,255,255,.64);
        backdrop-filter: blur(18px);
        box-shadow: 0 18px 42px rgba(15,23,42,.06);
    }

    .hero-panel {
        border: 1px solid rgba(167,243,208,.72);
        background:
            radial-gradient(circle at 12% 18%, rgba(16,185,129,.16), transparent 32%),
            radial-gradient(circle at 88% 16%, rgba(245,158,11,.13), transparent 32%),
            linear-gradient(135deg, rgba(255,255,255,.72), rgba(236,253,245,.70));
        backdrop-filter: blur(18px);
        box-shadow: 0 18px 42px rgba(15,23,42,.06);
    }

    .info-card {
        border: 1px solid rgba(226,232,240,.78);
        background: rgba(255,255,255,.58);
        backdrop-filter: blur(14px);
        transition: all .3s ease-in-out;
    }

    .info-card:hover {
        transform: translateY(-2px);
        border-color: rgba(16,185,129,.24);
        box-shadow: 0 18px 34px rgba(15,23,42,.055);
    }

    .timeline-card {
        border: 1px solid rgba(255,255,255,.74);
        background: rgba(255,255,255,.56);
        backdrop-filter: blur(14px);
        transition: all .3s ease-in-out;
    }

    .timeline-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 34px rgba(15,23,42,.055);
    }

    .log-box {
        border: 1px solid rgba(15,23,42,.10);
        background: rgba(15,23,42,.92);
        color: #d1fae5;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.06), 0 18px 42px rgba(15,23,42,.12);
    }
</style>
@endpush

@section('content')
<div class="import-show-page space-y-5">

    {{-- HERO --}}
    <section class="hero-panel rounded-[30px] p-5 sm:p-6">
        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/80 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-emerald-700">
                    <i class="fa-solid fa-file-circle-check"></i>
                    Detail Import
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Detail Riwayat Import
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Detail ini menjelaskan file yang diunggah Kader, kategori database tujuan, jumlah data tersimpan, dan catatan hasil validasi sistem.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @if($routeHas('kader.import.history'))
                    <a href="{{ route('kader.import.history') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-white/60 px-5 py-3 text-sm font-black text-emerald-700 backdrop-blur-md transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-50">
                        <i class="fa-solid fa-arrow-left"></i>
                        Riwayat
                    </a>
                @endif

                @if($routeHas('kader.import.create'))
                    <a href="{{ route('kader.import.create', ['type' => $import->jenis_data]) }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-[0_14px_28px_rgba(5,150,105,.18)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                        <i class="fa-solid fa-upload"></i>
                        Import Lagi
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- SUMMARY --}}
    <section class="glass-panel rounded-[30px] p-5 sm:p-6">
        <div class="grid gap-5 xl:grid-cols-[1.15fr_.85fr] xl:items-center">
            <div class="flex items-start gap-4">
                <div class="grid h-16 w-16 shrink-0 place-items-center rounded-3xl bg-emerald-50/90 text-emerald-700">
                    <i class="fa-solid fa-file-excel text-xl"></i>
                </div>

                <div class="min-w-0">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] {{ $typeData['class'] }}">
                            <i class="fa-solid {{ $typeData['icon'] }}"></i>
                            {{ $typeData['label'] }}
                        </span>

                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] {{ $statusData['class'] }}">
                            <i class="fa-solid {{ $statusData['icon'] }}"></i>
                            {{ $statusData['label'] }}
                        </span>
                    </div>

                    <h2 class="break-words text-2xl font-black tracking-[-.04em] text-slate-900">
                        {{ $import->nama_file }}
                    </h2>

                    <p class="mt-2 text-sm font-bold leading-6 text-slate-500">
                        ID Log #{{ str_pad($import->id, 5, '0', STR_PAD_LEFT) }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl bg-emerald-50/80 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-emerald-700">Data Tersimpan</p>
                    <p class="mt-2 text-3xl font-black text-slate-900">{{ $import->data_tersimpan ?? 0 }}</p>
                    <p class="mt-1 text-xs font-bold text-slate-400">Baris</p>
                </div>

                <div class="rounded-2xl bg-amber-50/80 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-amber-700">Status</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $statusData['label'] }}</p>
                    <p class="mt-1 text-xs font-bold text-slate-400">Validasi sistem</p>
                </div>
            </div>
        </div>
    </section>

    {{-- STATUS INFO --}}
    <section class="rounded-[24px] border p-4 {{ $statusData['soft'] }}">
        <div class="flex items-start gap-3">
            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white/70">
                <i class="fa-solid {{ $statusData['icon'] }}"></i>
            </div>

            <div>
                <h3 class="text-sm font-black">
                    @if($import->status === 'completed')
                        Import Berhasil Diproses
                    @elseif($import->status === 'failed')
                        Import Gagal Diproses
                    @else
                        Import Sedang Diproses
                    @endif
                </h3>

                <p class="mt-1 text-xs font-bold leading-5">
                    @if($import->status === 'completed')
                        Data valid sudah masuk ke database sasaran sesuai kategori. Data dengan NIK duplikat akan dilewati agar tidak menggandakan warga.
                    @elseif($import->status === 'failed')
                        File belum berhasil disimpan ke database. Periksa catatan sistem di bawah, lalu perbaiki file Excel dan upload ulang.
                    @else
                        File sudah diterima dan masih berada dalam status proses.
                    @endif
                </p>
            </div>
        </div>
    </section>

    {{-- DETAIL GRID --}}
    <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">

        {{-- DETAIL FILE --}}
        <div class="glass-panel rounded-[30px] p-5 xl:col-span-7">
            <div class="mb-4">
                <h3 class="text-lg font-black text-slate-900">Informasi File</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Metadata import dan target database.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Nama File</p>
                    <p class="mt-2 break-words text-sm font-black text-slate-900">{{ $import->nama_file }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Jenis Data</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $typeData['label'] }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Diunggah Oleh</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $creatorName }}</p>
                    <p class="mt-1 text-xs font-bold text-slate-400">Kader</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">File Arsip</p>
                    <p class="mt-2 text-sm font-black {{ $fileExists ? 'text-emerald-700' : 'text-slate-500' }}">
                        {{ $fileExists ? 'Tersimpan' : 'Tidak tersedia / sudah dihapus' }}
                    </p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Waktu Upload</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $createdLabel }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Update Terakhir</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $updatedLabel }}</p>
                </div>
            </div>
        </div>

        {{-- AKSI DAN TEMPLATE --}}
        <div class="glass-panel rounded-[30px] p-5 xl:col-span-5">
            <div class="mb-4">
                <h3 class="text-lg font-black text-slate-900">Aksi Lanjutan</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Gunakan template resmi jika perlu upload ulang.
                </p>
            </div>

            <div class="space-y-3">
                @if($routeHas('kader.import.template'))
                    <a href="{{ route('kader.import.template', $import->jenis_data) }}"
                       class="flex items-center justify-between gap-3 rounded-2xl border border-amber-100 bg-amber-50/70 p-4 text-amber-700 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-amber-100/70">
                        <div>
                            <p class="text-sm font-black">Unduh Template {{ $typeData['label'] }}</p>
                            <p class="mt-1 text-xs font-bold">Gunakan format resmi sebelum upload ulang.</p>
                        </div>
                        <i class="fa-solid fa-download"></i>
                    </a>
                @endif

                @if($routeHas('kader.import.create'))
                    <a href="{{ route('kader.import.create', ['type' => $import->jenis_data]) }}"
                       class="flex items-center justify-between gap-3 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 text-emerald-700 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-100/70">
                        <div>
                            <p class="text-sm font-black">Import Ulang</p>
                            <p class="mt-1 text-xs font-bold">Upload file hasil perbaikan.</p>
                        </div>
                        <i class="fa-solid fa-upload"></i>
                    </a>
                @endif

                @if($routeHas('kader.import.destroy'))
                    <form method="POST"
                          action="{{ route('kader.import.destroy', $import->id) }}"
                          onsubmit="return confirm('Hapus riwayat import ini? Data warga yang sudah masuk tidak ikut terhapus.');">
                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="flex w-full items-center justify-between gap-3 rounded-2xl border border-rose-100 bg-rose-50/70 p-4 text-left text-rose-700 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-rose-100/70">
                            <div>
                                <p class="text-sm font-black">Hapus Log Import</p>
                                <p class="mt-1 text-xs font-bold">Hanya menghapus riwayat dan arsip file.</p>
                            </div>
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    {{-- ALUR --}}
    <section class="glass-panel rounded-[30px] p-5">
        <div class="mb-4">
            <h3 class="text-lg font-black text-slate-900">Alur Import Data</h3>
            <p class="mt-1 text-xs font-bold text-slate-400">
                Pembagian proses dari upload sampai data masuk database.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="timeline-card rounded-2xl border-emerald-100 bg-emerald-50/70 p-4">
                <div class="mb-3 grid h-11 w-11 place-items-center rounded-2xl bg-white/70 text-emerald-700">
                    <i class="fa-solid fa-upload"></i>
                </div>
                <h4 class="text-sm font-black text-slate-900">Diunggah Kader</h4>
                <p class="mt-1 text-xs font-bold leading-5 text-slate-500">
                    Kader mengunggah file Excel sesuai kategori data sasaran.
                </p>
            </div>

            <div class="timeline-card rounded-2xl border-amber-100 bg-amber-50/70 p-4">
                <div class="mb-3 grid h-11 w-11 place-items-center rounded-2xl bg-white/70 text-amber-700">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h4 class="text-sm font-black text-slate-900">Divalidasi Sistem</h4>
                <p class="mt-1 text-xs font-bold leading-5 text-slate-500">
                    Sistem mengecek NIK, tanggal lahir, jenis kelamin, dan duplikasi data.
                </p>
            </div>

            <div class="timeline-card rounded-2xl border-sky-100 bg-sky-50/70 p-4">
                <div class="mb-3 grid h-11 w-11 place-items-center rounded-2xl bg-white/70 text-sky-700">
                    <i class="fa-solid fa-database"></i>
                </div>
                <h4 class="text-sm font-black text-slate-900">Masuk Database</h4>
                <p class="mt-1 text-xs font-bold leading-5 text-slate-500">
                    Data valid disimpan ke database warga sesuai kategori sasaran.
                </p>
            </div>
        </div>
    </section>

    {{-- CATATAN SISTEM --}}
    <section class="glass-panel rounded-[30px] p-5">
        <div class="mb-4">
            <h3 class="text-lg font-black text-slate-900">Catatan Sistem</h3>
            <p class="mt-1 text-xs font-bold text-slate-400">
                Pesan hasil proses import. Bagian ini penting untuk debugging, sayangnya debugging memang olahraga batin.
            </p>
        </div>

        <div class="log-box rounded-[24px] p-4">
            <div class="mb-4 flex items-center gap-2 border-b border-white/10 pb-3">
                <span class="h-3 w-3 rounded-full bg-rose-400"></span>
                <span class="h-3 w-3 rounded-full bg-amber-400"></span>
                <span class="h-3 w-3 rounded-full bg-emerald-400"></span>
                <span class="ml-2 text-xs font-black uppercase tracking-[.12em] text-emerald-200">Import Log</span>
            </div>

            <pre class="whitespace-pre-wrap break-words text-xs font-bold leading-6 text-emerald-100">{{ $catatan }}</pre>
        </div>
    </section>

    {{-- ACTION BOTTOM --}}
    <section class="glass-panel rounded-[26px] p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-black text-slate-900">Detail import selesai ditampilkan</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Jika gagal, perbaiki file Excel sesuai catatan sistem lalu import ulang.
                </p>
            </div>

            @if($routeHas('kader.import.history'))
                <a href="{{ route('kader.import.history') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-[0_12px_24px_rgba(5,150,105,.16)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                    <i class="fa-solid fa-list"></i>
                    Kembali ke Riwayat
                </a>
            @endif
        </div>
    </section>
</div>
@endsection