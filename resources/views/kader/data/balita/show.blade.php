@extends('layouts.kader')

@section('title', 'Detail Data Balita')
@section('page-name', 'Detail Data Balita')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $tanggalLahir = $balita->tanggal_lahir ? Carbon::parse($balita->tanggal_lahir) : null;

    $usiaText = '-';
    if ($tanggalLahir) {
        $diff = $tanggalLahir->diff(now('Asia/Jakarta'));
        $usiaText = $diff->y > 0
            ? $diff->y . ' tahun ' . $diff->m . ' bulan'
            : $diff->m . ' bulan ' . $diff->d . ' hari';
    }

    $genderLabel = $balita->jenis_kelamin === 'L'
        ? 'Laki-laki'
        : ($balita->jenis_kelamin === 'P' ? 'Perempuan' : '-');

    $genderClass = $balita->jenis_kelamin === 'L'
        ? 'border-sky-100 bg-sky-50/80 text-sky-700'
        : 'border-pink-100 bg-pink-50/80 text-pink-700';

    $kunjungans = $balita->kunjungans ?? collect();

    $totalLayanan = $kunjungans->count();

    $kunjunganTerakhir = $kunjungans->first();

    $riwayatImunisasi = $kunjungans->filter(function ($kunjungan) {
        return ($kunjungan->jenis_kunjungan ?? null) === 'imunisasi';
    });

    $totalVaksin = 0;
    foreach ($riwayatImunisasi as $kunjungan) {
        $totalVaksin += count($kunjungan->imunisasis ?? []);
    }

    $akunTerhubung = $userTerhubung ?? $balita->user ?? null;
@endphp

@push('styles')
<style>
    .balita-show-page {
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        position: relative;
        isolation: isolate;
    }

    .balita-show-page::before {
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

    .card-hover {
        transition: all .3s ease-in-out;
    }

    .card-hover:hover {
        transform: translateY(-2px);
        border-color: rgba(16,185,129,.24);
        box-shadow: 0 20px 46px rgba(15,23,42,.075);
    }

    .scroll-soft {
        max-height: 520px;
        overflow: auto;
        overscroll-behavior: contain;
    }

    .scroll-soft::-webkit-scrollbar {
        width: 7px;
        height: 7px;
    }

    .scroll-soft::-webkit-scrollbar-track {
        background: rgba(241,245,249,.8);
        border-radius: 999px;
    }

    .scroll-soft::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #10b981, #f59e0b);
        border-radius: 999px;
    }
</style>
@endpush

@section('content')
<div class="balita-show-page space-y-5">

    {{-- HERO --}}
    <section class="hero-panel rounded-[30px] p-5 sm:p-6">
        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/80 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-emerald-700">
                    <i class="fa-solid fa-child-reaching"></i>
                    Detail Balita
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Detail Data Balita
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Halaman ini menampilkan identitas Balita, status akun warga, data lahir, dan riwayat layanan Posyandu yang sudah tercatat.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @if($routeHas('kader.data.balita.index'))
                    <a href="{{ route('kader.data.balita.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-white/60 px-5 py-3 text-sm font-black text-emerald-700 backdrop-blur-md transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-50">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali
                    </a>
                @endif

                @if($routeHas('kader.data.balita.edit'))
                    <a href="{{ route('kader.data.balita.edit', $balita->id) }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-[0_14px_28px_rgba(5,150,105,.18)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                        <i class="fa-solid fa-pen"></i>
                        Edit Data
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- PROFILE SUMMARY --}}
    <section class="glass-panel rounded-[30px] p-5 sm:p-6">
        <div class="grid gap-5 xl:grid-cols-[1.15fr_.85fr] xl:items-center">
            <div class="flex items-start gap-4">
                <div class="grid h-20 w-20 shrink-0 place-items-center rounded-[28px] bg-emerald-50/90 text-emerald-700 shadow-[0_14px_28px_rgba(5,150,105,.10)]">
                    <span class="text-2xl font-black">
                        {{ strtoupper(substr($balita->nama_lengkap ?? 'B', 0, 1)) }}
                    </span>
                </div>

                <div class="min-w-0">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/80 px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] text-emerald-700">
                            <i class="fa-solid fa-child-reaching"></i>
                            Balita
                        </span>

                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] {{ $genderClass }}">
                            <i class="fa-solid {{ $balita->jenis_kelamin === 'L' ? 'fa-mars' : 'fa-venus' }}"></i>
                            {{ $genderLabel }}
                        </span>
                    </div>

                    <h2 class="break-words text-2xl font-black tracking-[-.04em] text-slate-900">
                        {{ $balita->nama_lengkap }}
                    </h2>

                    <p class="mt-2 text-sm font-bold leading-6 text-slate-500">
                        NIK {{ $balita->nik ?? '-' }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl bg-emerald-50/80 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-emerald-700">Usia</p>
                    <p class="mt-2 text-lg font-black text-slate-900">{{ $usiaText }}</p>
                    <p class="mt-1 text-xs font-bold text-slate-400">Berdasarkan tanggal lahir</p>
                </div>

                <div class="rounded-2xl bg-amber-50/80 p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-amber-700">Akun Warga</p>
                    <p class="mt-2 text-lg font-black text-slate-900">
                        {{ $akunTerhubung ? 'Terhubung' : 'Belum' }}
                    </p>
                    <p class="mt-1 text-xs font-bold text-slate-400">
                        {{ $akunTerhubung ? 'User tersedia' : 'Perlu sinkron' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- STATUS AKUN --}}
    <section class="rounded-[24px] border {{ $akunTerhubung ? 'border-emerald-100 bg-emerald-50/70 text-emerald-700' : 'border-amber-100 bg-amber-50/70 text-amber-700' }} p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3">
                <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white/70">
                    <i class="fa-solid {{ $akunTerhubung ? 'fa-circle-check' : 'fa-link-slash' }}"></i>
                </div>

                <div>
                    <h3 class="text-sm font-black">
                        {{ $akunTerhubung ? 'Data Balita Terhubung dengan Akun Warga' : 'Data Balita Belum Terhubung dengan Akun Warga' }}
                    </h3>

                    <p class="mt-1 text-xs font-bold leading-5">
                        @if($akunTerhubung)
                            Akun warga yang terhubung: {{ $akunTerhubung->name ?? $akunTerhubung->nama ?? '-' }}.
                        @else
                            Gunakan sinkron akun untuk mencocokkan NIK Balita dengan akun user/warga yang sudah dibuat oleh Admin.
                        @endif
                    </p>
                </div>
            </div>

            @if(!$akunTerhubung && $routeHas('kader.data.balita.sync'))
                <form method="POST" action="{{ route('kader.data.balita.sync', $balita->id) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-amber-500 px-5 py-3 text-sm font-black text-white shadow-[0_12px_24px_rgba(245,158,11,.16)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-amber-600">
                        <i class="fa-solid fa-rotate"></i>
                        Sinkron Akun
                    </button>
                </form>
            @endif
        </div>
    </section>

    {{-- CONTENT GRID --}}
    <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">

        {{-- IDENTITAS --}}
        <div class="glass-panel rounded-[30px] p-5 xl:col-span-7">
            <div class="mb-4">
                <h3 class="text-lg font-black text-slate-900">Identitas Balita</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Data utama sasaran yang digunakan pada layanan Posyandu.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Nama Lengkap</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $balita->nama_lengkap ?? '-' }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">NIK Balita</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $balita->nik ?? '-' }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Jenis Kelamin</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $genderLabel }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Tempat Lahir</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $balita->tempat_lahir ?? '-' }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Tanggal Lahir</p>
                    <p class="mt-2 text-sm font-black text-slate-900">
                        {{ $tanggalLahir ? $tanggalLahir->translatedFormat('d F Y') : '-' }}
                    </p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Usia Saat Ini</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $usiaText }}</p>
                </div>
            </div>
        </div>

        {{-- DATA LAHIR --}}
        <div class="glass-panel rounded-[30px] p-5 xl:col-span-5">
            <div class="mb-4">
                <h3 class="text-lg font-black text-slate-900">Data Lahir</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Data awal yang digunakan sebagai informasi dasar pertumbuhan.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3">
                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Berat Lahir</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">
                        {{ $balita->berat_lahir ?? '-' }}
                        <span class="text-sm text-slate-400">kg</span>
                    </p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Panjang Lahir</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">
                        {{ $balita->panjang_lahir ?? '-' }}
                        <span class="text-sm text-slate-400">cm</span>
                    </p>
                </div>

                <div class="rounded-[24px] border border-emerald-100 bg-emerald-50/70 p-4">
                    <div class="flex items-start gap-3">
                        <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white/70 text-emerald-700">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <div>
                            <p class="text-sm font-black text-emerald-800">Pemantauan Lanjutan</p>
                            <p class="mt-1 text-xs font-bold leading-5 text-emerald-700">
                                Pengukuran terbaru akan tercatat melalui fitur Pengukuran Fisik.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ORANG TUA --}}
        <div class="glass-panel rounded-[30px] p-5 xl:col-span-6">
            <div class="mb-4">
                <h3 class="text-lg font-black text-slate-900">Data Orang Tua</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Informasi keluarga untuk identifikasi dan pencarian sasaran.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3">
                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Nama Ibu</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $balita->nama_ibu ?? '-' }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Nama Ayah</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $balita->nama_ayah ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- ALAMAT --}}
        <div class="glass-panel rounded-[30px] p-5 xl:col-span-6">
            <div class="mb-4">
                <h3 class="text-lg font-black text-slate-900">Alamat Domisili</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Lokasi tinggal Balita berdasarkan data yang tercatat.
                </p>
            </div>

            <div class="rounded-[24px] border border-white/70 bg-white/56 p-5 backdrop-blur-md">
                <div class="flex items-start gap-3">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-emerald-50/90 text-emerald-700">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>

                    <div>
                        <p class="text-sm font-black text-slate-900">
                            {{ $balita->alamat ?? '-' }}
                        </p>
                        <p class="mt-2 text-xs font-bold leading-5 text-slate-400">
                            Alamat ini digunakan untuk pendataan sasaran dan kebutuhan laporan wilayah Posyandu.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- STATS --}}
    <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="glass-panel card-hover rounded-[26px] p-5">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50/90 text-emerald-700">
                <i class="fa-solid fa-notes-medical"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Total Layanan</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $totalLayanan }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Riwayat layanan tercatat</p>
        </div>

        <div class="glass-panel card-hover rounded-[26px] p-5">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-amber-50/90 text-amber-700">
                <i class="fa-solid fa-syringe"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Vaksin Tercatat</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $totalVaksin }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Dosis imunisasi</p>
        </div>

        <div class="glass-panel card-hover rounded-[26px] p-5">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-sky-50/90 text-sky-700">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Update Terakhir</p>
            <h2 class="mt-2 text-lg font-black text-slate-900">
                @if($kunjunganTerakhir && $kunjunganTerakhir->tanggal_kunjungan)
                    {{ Carbon::parse($kunjunganTerakhir->tanggal_kunjungan)->translatedFormat('d M Y') }}
                @else
                    Belum Ada
                @endif
            </h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Riwayat layanan terbaru</p>
        </div>
    </section>

    {{-- RIWAYAT LAYANAN --}}
    <section class="glass-panel rounded-[30px] p-4 sm:p-5">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900">Riwayat Layanan Posyandu</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Menampilkan riwayat kunjungan, pemeriksaan, atau imunisasi yang tercatat.
                </p>
            </div>

            <span class="w-fit rounded-full border border-emerald-100 bg-emerald-50/80 px-3 py-1 text-[10px] font-black uppercase tracking-[.12em] text-emerald-700">
                {{ $totalLayanan }} Riwayat
            </span>
        </div>

        @if($kunjungans->count())
            <div class="scroll-soft">
                <div class="space-y-3">
                    @foreach($kunjungans as $kunjungan)
                        @php
                            $jenis = $kunjungan->jenis_kunjungan ?? 'kunjungan';

                            $jenisLabel = match($jenis) {
                                'imunisasi' => 'Imunisasi',
                                'pemeriksaan' => 'Pemeriksaan Fisik',
                                default => 'Kunjungan Posyandu',
                            };

                            $jenisClass = match($jenis) {
                                'imunisasi' => 'border-amber-100 bg-amber-50/80 text-amber-700',
                                'pemeriksaan' => 'border-emerald-100 bg-emerald-50/80 text-emerald-700',
                                default => 'border-sky-100 bg-sky-50/80 text-sky-700',
                            };

                            $tanggalKunjungan = $kunjungan->tanggal_kunjungan
                                ? Carbon::parse($kunjungan->tanggal_kunjungan)
                                : null;

                            $pemeriksaan = $kunjungan->pemeriksaan ?? null;
                        @endphp

                        <article class="card-hover rounded-[26px] border border-white/70 bg-white/56 p-4 backdrop-blur-md">
                            <div class="grid gap-4 xl:grid-cols-[1fr_1fr_1fr_auto] xl:items-center">
                                <div>
                                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] {{ $jenisClass }}">
                                        <i class="fa-solid {{ $jenis === 'imunisasi' ? 'fa-syringe' : 'fa-stethoscope' }}"></i>
                                        {{ $jenisLabel }}
                                    </span>

                                    <h3 class="mt-3 text-sm font-black text-slate-900">
                                        {{ $tanggalKunjungan ? $tanggalKunjungan->translatedFormat('d F Y') : '-' }}
                                    </h3>

                                    <p class="mt-1 text-xs font-bold text-slate-400">
                                        {{ $tanggalKunjungan ? $tanggalKunjungan->format('H:i') . ' WIB' : 'Waktu belum tercatat' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Petugas</p>
                                    <p class="mt-1 text-sm font-black text-slate-900">
                                        {{ $kunjungan->petugas?->name ?? $kunjungan->petugas?->nama ?? '-' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Hasil Fisik</p>

                                    @if($pemeriksaan)
                                        <p class="mt-1 text-sm font-black text-slate-900">
                                            BB {{ $pemeriksaan->berat_badan ?? '-' }} kg,
                                            TB {{ $pemeriksaan->tinggi_badan ?? '-' }} cm
                                        </p>
                                    @else
                                        <p class="mt-1 text-sm font-black text-slate-900">
                                            Belum ada antropometri
                                        </p>
                                    @endif
                                </div>

                                <div class="flex justify-start xl:justify-end">
                                    <span class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-2 text-xs font-black text-slate-600">
                                        <i class="fa-solid fa-lock"></i>
                                        Arsip
                                    </span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @else
            <div class="rounded-[28px] border border-dashed border-slate-200 bg-slate-50/70 p-10 text-center">
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-white/60 text-slate-400 backdrop-blur-md">
                    <i class="fa-solid fa-folder-open text-xl"></i>
                </div>

                <h3 class="mt-4 text-lg font-black text-slate-900">Belum Ada Riwayat Layanan</h3>
                <p class="mx-auto mt-2 max-w-md text-sm font-bold leading-6 text-slate-400">
                    Riwayat akan muncul setelah Balita mengikuti absensi, pengukuran fisik, atau layanan Posyandu yang tercatat di sistem.
                </p>
            </div>
        @endif
    </section>

    {{-- IMUNISASI --}}
    @if($riwayatImunisasi->count())
        <section class="glass-panel rounded-[30px] p-4 sm:p-5">
            <div class="mb-4">
                <h2 class="text-lg font-black text-slate-900">Riwayat Imunisasi</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Arsip imunisasi yang sudah tercatat pada layanan Posyandu.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach($riwayatImunisasi as $kunjungan)
                    @foreach($kunjungan->imunisasis ?? [] as $imunisasi)
                        <article class="card-hover rounded-[24px] border border-amber-100 bg-amber-50/60 p-4">
                            <div class="mb-3 grid h-11 w-11 place-items-center rounded-2xl bg-white/70 text-amber-700">
                                <i class="fa-solid fa-syringe"></i>
                            </div>

                            <h3 class="text-sm font-black text-slate-900">
                                {{ $imunisasi->vaksin ?? $imunisasi->nama_vaksin ?? '-' }}
                            </h3>

                            <p class="mt-1 text-xs font-bold text-slate-500">
                                {{ $imunisasi->jenis_imunisasi ?? '-' }}
                            </p>

                            <div class="mt-4 rounded-2xl bg-white/60 p-3">
                                <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Tanggal</p>
                                <p class="mt-1 text-sm font-black text-slate-900">
                                    @if($imunisasi->tanggal_imunisasi ?? null)
                                        {{ Carbon::parse($imunisasi->tanggal_imunisasi)->translatedFormat('d F Y') }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </article>
                    @endforeach
                @endforeach
            </div>
        </section>
    @endif

    {{-- ACTION BOTTOM --}}
    <section class="glass-panel rounded-[26px] p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-black text-slate-900">Detail data Balita selesai ditampilkan</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Gunakan tombol edit jika ada data identitas yang perlu diperbarui.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                @if($routeHas('kader.data.balita.index'))
                    <a href="{{ route('kader.data.balita.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-white/60 px-5 py-3 text-sm font-black text-emerald-700 backdrop-blur-md transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-50">
                        <i class="fa-solid fa-list"></i>
                        Daftar Balita
                    </a>
                @endif

                @if($routeHas('kader.data.balita.edit'))
                    <a href="{{ route('kader.data.balita.edit', $balita->id) }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-[0_12px_24px_rgba(5,150,105,.16)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                        <i class="fa-solid fa-pen"></i>
                        Edit Balita
                    </a>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection