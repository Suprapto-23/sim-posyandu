@extends('layouts.kader')

@section('title', 'Detail Data Remaja')
@section('page-name', 'Detail Data Remaja')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $tanggalLahir = $remaja->tanggal_lahir ? Carbon::parse($remaja->tanggal_lahir) : null;

    $usiaText = '-';
    if ($tanggalLahir) {
        $diff = $tanggalLahir->diff(now('Asia/Jakarta'));
        $usiaText = $diff->y . ' tahun ' . $diff->m . ' bulan';
    }

    $genderLabel = $remaja->jenis_kelamin === 'L'
        ? 'Laki-laki'
        : ($remaja->jenis_kelamin === 'P' ? 'Perempuan' : '-');

    $genderClass = $remaja->jenis_kelamin === 'L'
        ? 'border-sky-100 bg-sky-50/80 text-sky-700'
        : 'border-pink-100 bg-pink-50/80 text-pink-700';

    $kunjungans = $remaja->kunjungans ?? collect();

    $totalLayanan = $kunjungans->count();

    $totalPemeriksaan = $kunjungans->filter(function ($kunjungan) {
        return filled($kunjungan->pemeriksaan ?? null);
    })->count();

    $kunjunganTerakhir = $kunjungans->first();

    $pemeriksaanTerakhir = optional($kunjunganTerakhir)->pemeriksaan;

    $akunTerhubung = $remaja->user ?? null;

    $updatedLabel = $remaja->updated_at
        ? Carbon::parse($remaja->updated_at)->translatedFormat('d F Y')
        : '-';
@endphp

@push('styles')
<style>
    .remaja-show-page {
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        position: relative;
        isolation: isolate;
    }

    .remaja-show-page::before {
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
        max-height: 560px;
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
<div class="remaja-show-page space-y-5">

    {{-- HERO --}}
    <section class="hero-panel rounded-[30px] p-5 sm:p-6">
        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/80 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-emerald-700">
                    <i class="fa-solid fa-user-graduate"></i>
                    Detail Remaja
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Detail Data Remaja
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Halaman ini menampilkan identitas Remaja, data pendidikan, orang tua/wali, status akun warga, dan riwayat pemeriksaan Posyandu.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @if($routeHas('kader.data.remaja.index'))
                    <a href="{{ route('kader.data.remaja.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-white/60 px-5 py-3 text-sm font-black text-emerald-700 backdrop-blur-md transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-50">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali
                    </a>
                @endif

                @if($routeHas('kader.data.remaja.edit'))
                    <a href="{{ route('kader.data.remaja.edit', $remaja->id) }}"
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
                        {{ strtoupper(substr($remaja->nama_lengkap ?? 'R', 0, 1)) }}
                    </span>
                </div>

                <div class="min-w-0">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/80 px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] text-emerald-700">
                            <i class="fa-solid fa-user-graduate"></i>
                            Remaja
                        </span>

                        <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] {{ $genderClass }}">
                            <i class="fa-solid {{ $remaja->jenis_kelamin === 'L' ? 'fa-mars' : 'fa-venus' }}"></i>
                            {{ $genderLabel }}
                        </span>
                    </div>

                    <h2 class="break-words text-2xl font-black tracking-[-.04em] text-slate-900">
                        {{ $remaja->nama_lengkap }}
                    </h2>

                    <p class="mt-2 text-sm font-bold leading-6 text-slate-500">
                        NIK {{ $remaja->nik ?? '-' }}
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
                        {{ $akunTerhubung ? 'Data Remaja Terhubung dengan Akun Warga' : 'Data Remaja Belum Terhubung dengan Akun Warga' }}
                    </h3>

                    <p class="mt-1 text-xs font-bold leading-5">
                        @if($akunTerhubung)
                            Akun warga yang terhubung: {{ $akunTerhubung->name ?? $akunTerhubung->nama ?? '-' }}.
                        @else
                            Gunakan sinkron akun untuk mencocokkan NIK Remaja dengan akun user/warga yang sudah dibuat oleh Admin.
                        @endif
                    </p>
                </div>
            </div>

            @if(!$akunTerhubung && $routeHas('kader.data.remaja.sync'))
                <form method="POST" action="{{ route('kader.data.remaja.sync', $remaja->id) }}">
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
                <h3 class="text-lg font-black text-slate-900">Identitas Remaja</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Data utama sasaran yang digunakan pada layanan Posyandu.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Nama Lengkap</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $remaja->nama_lengkap ?? '-' }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">NIK Remaja</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $remaja->nik ?? '-' }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Jenis Kelamin</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $genderLabel }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Tempat Lahir</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $remaja->tempat_lahir ?? '-' }}</p>
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

        {{-- PENDIDIKAN --}}
        <div class="glass-panel rounded-[30px] p-5 xl:col-span-5">
            <div class="mb-4">
                <h3 class="text-lg font-black text-slate-900">Data Pendidikan</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Data pendukung untuk identifikasi sasaran Remaja.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3">
                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Nama Sekolah</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $remaja->sekolah ?? '-' }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Kelas</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $remaja->kelas ?? '-' }}</p>
                </div>

                <div class="rounded-[24px] border border-sky-100 bg-sky-50/70 p-4">
                    <div class="flex items-start gap-3">
                        <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white/70 text-sky-700">
                            <i class="fa-solid fa-school"></i>
                        </div>
                        <div>
                            <p class="text-sm font-black text-sky-800">Informasi Pendidikan</p>
                            <p class="mt-1 text-xs font-bold leading-5 text-sky-700">
                                Data sekolah bersifat pendukung dan dapat diperbarui saat edit data Remaja.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ORANG TUA --}}
        <div class="glass-panel rounded-[30px] p-5 xl:col-span-6">
            <div class="mb-4">
                <h3 class="text-lg font-black text-slate-900">Data Orang Tua / Wali</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Informasi keluarga untuk identifikasi dan kontak.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3">
                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Nama Orang Tua / Wali</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $remaja->nama_ortu ?? '-' }}</p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">No. HP Orang Tua</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $remaja->telepon_ortu ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- ALAMAT --}}
        <div class="glass-panel rounded-[30px] p-5 xl:col-span-6">
            <div class="mb-4">
                <h3 class="text-lg font-black text-slate-900">Alamat Domisili</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Lokasi tinggal Remaja berdasarkan data yang tercatat.
                </p>
            </div>

            <div class="rounded-[24px] border border-white/70 bg-white/56 p-5 backdrop-blur-md">
                <div class="flex items-start gap-3">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-emerald-50/90 text-emerald-700">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>

                    <div>
                        <p class="text-sm font-black text-slate-900">
                            {{ $remaja->alamat ?? '-' }}
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
                <i class="fa-solid fa-stethoscope"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Pemeriksaan</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $totalPemeriksaan }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Data pengukuran tercatat</p>
        </div>

        <div class="glass-panel card-hover rounded-[26px] p-5">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-sky-50/90 text-sky-700">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Update Profil</p>
            <h2 class="mt-2 text-lg font-black text-slate-900">{{ $updatedLabel }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Pembaruan data terakhir</p>
        </div>
    </section>

    {{-- PEMERIKSAAN TERAKHIR --}}
    <section class="glass-panel rounded-[30px] p-5">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900">Ringkasan Pemeriksaan Terakhir</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Menampilkan data pengukuran terbaru jika Remaja sudah pernah diperiksa.
                </p>
            </div>

            @if($routeHas('kader.pemeriksaan.create'))
                <a href="{{ route('kader.pemeriksaan.create', ['kategori' => 'remaja', 'id' => $remaja->id]) }}"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-[0_12px_24px_rgba(5,150,105,.16)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                    <i class="fa-solid fa-plus"></i>
                    Input Pengukuran
                </a>
            @endif
        </div>

        @if($pemeriksaanTerakhir)
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Berat Badan</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">
                        {{ $pemeriksaanTerakhir->berat_badan ?? '-' }}
                        <span class="text-sm text-slate-400">kg</span>
                    </p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Tinggi Badan</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">
                        {{ $pemeriksaanTerakhir->tinggi_badan ?? '-' }}
                        <span class="text-sm text-slate-400">cm</span>
                    </p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">IMT</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">
                        {{ $pemeriksaanTerakhir->imt ?? '-' }}
                    </p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Tekanan Darah</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">
                        {{ $pemeriksaanTerakhir->tekanan_darah ?? '-' }}
                    </p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">LiLA</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">
                        {{ $pemeriksaanTerakhir->lingkar_lengan ?? '-' }}
                        <span class="text-sm text-slate-400">cm</span>
                    </p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Lingkar Perut</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">
                        {{ $pemeriksaanTerakhir->lingkar_perut ?? '-' }}
                        <span class="text-sm text-slate-400">cm</span>
                    </p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Hb</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">
                        {{ $pemeriksaanTerakhir->hemoglobin ?? $pemeriksaanTerakhir->hb ?? '-' }}
                    </p>
                </div>

                <div class="info-card rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Tanggal</p>
                    <p class="mt-2 text-sm font-black text-slate-900">
                        @if($kunjunganTerakhir && $kunjunganTerakhir->tanggal_kunjungan)
                            {{ Carbon::parse($kunjunganTerakhir->tanggal_kunjungan)->translatedFormat('d F Y') }}
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
        @else
            <div class="rounded-[28px] border border-dashed border-slate-200 bg-slate-50/70 p-10 text-center">
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-white/60 text-slate-400 backdrop-blur-md">
                    <i class="fa-solid fa-stethoscope text-xl"></i>
                </div>

                <h3 class="mt-4 text-lg font-black text-slate-900">Belum Ada Pemeriksaan</h3>
                <p class="mx-auto mt-2 max-w-md text-sm font-bold leading-6 text-slate-400">
                    Data pengukuran akan muncul setelah Remaja mengikuti pemeriksaan atau pengukuran fisik di Posyandu.
                </p>
            </div>
        @endif
    </section>

    {{-- RIWAYAT LAYANAN --}}
    <section class="glass-panel rounded-[30px] p-4 sm:p-5">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900">Riwayat Layanan Posyandu</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Menampilkan riwayat kunjungan dan pemeriksaan Remaja.
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
                            $tanggalKunjungan = $kunjungan->tanggal_kunjungan
                                ? Carbon::parse($kunjungan->tanggal_kunjungan)
                                : null;

                            $pemeriksaan = $kunjungan->pemeriksaan ?? null;

                            $jenis = $kunjungan->jenis_kunjungan ?? 'pemeriksaan';

                            $jenisLabel = match($jenis) {
                                'imunisasi' => 'Imunisasi',
                                'absensi' => 'Absensi Posyandu',
                                'pemeriksaan' => 'Pemeriksaan Fisik',
                                default => 'Layanan Posyandu',
                            };

                            $jenisClass = match($jenis) {
                                'imunisasi' => 'border-amber-100 bg-amber-50/80 text-amber-700',
                                'absensi' => 'border-sky-100 bg-sky-50/80 text-sky-700',
                                'pemeriksaan' => 'border-emerald-100 bg-emerald-50/80 text-emerald-700',
                                default => 'border-slate-100 bg-slate-50/80 text-slate-700',
                            };
                        @endphp

                        <article class="card-hover rounded-[26px] border border-white/70 bg-white/56 p-4 backdrop-blur-md">
                            <div class="grid gap-4 xl:grid-cols-[1fr_1fr_1.3fr_auto] xl:items-center">
                                <div>
                                    <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] {{ $jenisClass }}">
                                        <i class="fa-solid {{ $jenis === 'absensi' ? 'fa-user-check' : 'fa-stethoscope' }}"></i>
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
                                    <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Ringkasan Pemeriksaan</p>

                                    @if($pemeriksaan)
                                        <p class="mt-1 text-sm font-black text-slate-900">
                                            BB {{ $pemeriksaan->berat_badan ?? '-' }} kg,
                                            TB {{ $pemeriksaan->tinggi_badan ?? '-' }} cm,
                                            IMT {{ $pemeriksaan->imt ?? '-' }}
                                        </p>

                                        <p class="mt-1 text-xs font-bold text-slate-400">
                                            Tensi {{ $pemeriksaan->tekanan_darah ?? '-' }},
                                            LiLA {{ $pemeriksaan->lingkar_lengan ?? '-' }}
                                        </p>
                                    @else
                                        <p class="mt-1 text-sm font-black text-slate-900">
                                            Belum ada data pengukuran
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
                    Riwayat akan muncul setelah Remaja mengikuti absensi atau pengukuran fisik yang tercatat di sistem.
                </p>
            </div>
        @endif
    </section>

    {{-- ACTION BOTTOM --}}
    <section class="glass-panel rounded-[26px] p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-black text-slate-900">Detail data Remaja selesai ditampilkan</h3>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Gunakan tombol edit jika ada data identitas, pendidikan, atau kontak wali yang perlu diperbarui.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                @if($routeHas('kader.data.remaja.index'))
                    <a href="{{ route('kader.data.remaja.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-white/60 px-5 py-3 text-sm font-black text-emerald-700 backdrop-blur-md transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-50">
                        <i class="fa-solid fa-list"></i>
                        Daftar Remaja
                    </a>
                @endif

                @if($routeHas('kader.data.remaja.edit'))
                    <a href="{{ route('kader.data.remaja.edit', $remaja->id) }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-[0_12px_24px_rgba(5,150,105,.16)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                        <i class="fa-solid fa-pen"></i>
                        Edit Remaja
                    </a>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection