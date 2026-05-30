@extends('layouts.kader')

@section('title', 'Detail Data Balita')
@section('page-name', 'Detail Data Balita')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $kunjungans = collect($balita->kunjungans ?? []);
    $totalKunjungan = $kunjungans->count();

    $pemeriksaanTerakhir = $kunjungans
        ->filter(fn ($item) => filled(data_get($item, 'pemeriksaan')))
        ->first();

    $kunjunganTerakhir = $kunjungans->first();

    $tanggalLahir = $balita->tanggal_lahir ? Carbon::parse($balita->tanggal_lahir) : null;

    $usiaText = '-';
    if ($tanggalLahir) {
        $diff = $tanggalLahir->diff(now('Asia/Jakarta'));
        $usiaText = $diff->y > 0
            ? $diff->y . ' tahun ' . $diff->m . ' bulan'
            : $diff->m . ' bulan ' . $diff->d . ' hari';
    }

    $genderLabel = match ($balita->jenis_kelamin ?? null) {
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        default => '-',
    };

    $genderClass = match ($balita->jenis_kelamin ?? null) {
        'L' => 'border-sky-100 bg-sky-50 text-sky-700',
        'P' => 'border-pink-100 bg-pink-50 text-pink-700',
        default => 'border-slate-100 bg-slate-50 text-slate-600',
    };

    $akunTerhubung = filled($balita->user_id ?? null) || filled($userTerhubung ?? null);

    $sessionType = session('success') ? 'success' : (session('warning') ? 'warning' : (session('error') ? 'error' : null));
    $sessionMessage = session('success') ?? session('warning') ?? session('error');

    $initial = strtoupper(substr($balita->nama_lengkap ?? 'B', 0, 1));
@endphp

@section('content')
    <style>
        .nexus-toast-show {
            animation: nexusToastShow .22s ease-out both;
        }

        .nexus-toast-hide {
            animation: nexusToastHide .18s ease-in both;
        }

        @keyframes nexusToastShow {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(.985);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes nexusToastHide {
            from {
                opacity: 1;
                transform: translateY(0) scale(1);
            }

            to {
                opacity: 0;
                transform: translateY(-10px) scale(.985);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .nexus-toast-show,
            .nexus-toast-hide {
                animation: none !important;
            }
        }
    </style>

    <div class="w-full space-y-5">

        {{-- TOAST SESSION --}}
        @if($sessionType && $sessionMessage)
            <div
                id="nexusSessionToast"
                class="fixed right-4 top-4 z-[100000] w-[calc(100%-2rem)] max-w-md nexus-toast-show"
            >
                <div
                    class="overflow-hidden rounded-3xl border bg-white shadow-2xl shadow-slate-900/10
                    {{ $sessionType === 'success' ? 'border-emerald-100' : ($sessionType === 'warning' ? 'border-amber-100' : 'border-rose-100') }}"
                >
                    <div class="flex items-start gap-3 p-4">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border
                            {{ $sessionType === 'success' ? 'border-emerald-100 bg-emerald-50 text-emerald-700' : ($sessionType === 'warning' ? 'border-amber-100 bg-amber-50 text-amber-700' : 'border-rose-100 bg-rose-50 text-rose-700') }}"
                        >
                            <i class="ph-fill {{ $sessionType === 'success' ? 'ph-check-circle' : ($sessionType === 'warning' ? 'ph-warning-circle' : 'ph-x-circle') }} text-2xl"></i>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p
                                class="text-sm font-black
                                {{ $sessionType === 'success' ? 'text-emerald-800' : ($sessionType === 'warning' ? 'text-amber-800' : 'text-rose-800') }}"
                            >
                                {{ $sessionType === 'success' ? 'Berhasil Diproses' : ($sessionType === 'warning' ? 'Perhatian Sistem' : 'Aksi Gagal') }}
                            </p>

                            <p class="mt-1 text-sm font-semibold leading-6 text-slate-600">
                                {{ $sessionMessage }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="nexus-toast-close flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                            aria-label="Tutup notifikasi"
                        >
                            <i class="ph-bold ph-x text-lg"></i>
                        </button>
                    </div>

                    <div
                        class="h-1 w-full
                        {{ $sessionType === 'success' ? 'bg-emerald-500' : ($sessionType === 'warning' ? 'bg-amber-500' : 'bg-rose-500') }}"
                    ></div>
                </div>
            </div>
        @endif

        {{-- HERO --}}
        <section class="relative overflow-hidden rounded-[1.75rem] border border-emerald-100 bg-gradient-to-br from-emerald-50 via-teal-50 to-slate-50 p-5 shadow-sm sm:p-6">
            <div class="pointer-events-none absolute -right-16 -top-20 h-48 w-48 rounded-full bg-emerald-200/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 left-10 h-56 w-56 rounded-full bg-amber-200/20 blur-3xl"></div>

            <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 flex-col gap-4 sm:flex-row sm:items-center">
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-[1.5rem] bg-emerald-700 text-3xl font-black text-white shadow-sm">
                        {{ $initial }}
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-2 rounded-2xl border border-emerald-100 bg-white/70 px-4 py-2 text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700">
                                <i class="ph-fill ph-baby text-base"></i>
                                Detail Balita
                            </span>

                            <span class="inline-flex items-center rounded-2xl border px-3 py-2 text-[11px] font-black uppercase tracking-[0.14em] {{ $genderClass }}">
                                {{ $genderLabel }}
                            </span>

                            @if($akunTerhubung)
                                <span class="inline-flex items-center gap-1 rounded-2xl border border-teal-100 bg-teal-50 px-3 py-2 text-[11px] font-black uppercase tracking-[0.14em] text-teal-700">
                                    <i class="ph-fill ph-link-simple"></i>
                                    Akun Terhubung
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-2xl border border-amber-100 bg-amber-50 px-3 py-2 text-[11px] font-black uppercase tracking-[0.14em] text-amber-700">
                                    <i class="ph-fill ph-warning-circle"></i>
                                    Belum Terhubung
                                </span>
                            @endif
                        </div>

                        <h1 class="mt-3 truncate text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                            {{ $balita->nama_lengkap }}
                        </h1>

                        <p class="mt-2 text-sm font-bold leading-6 text-slate-600">
                            NIK Balita:
                            <span class="font-black text-slate-800">{{ $balita->nik ?? '-' }}</span>
                        </p>

                        <p class="mt-1 max-w-2xl text-sm font-semibold leading-7 text-slate-600">
                            Detail data sasaran Balita untuk pemantauan layanan Posyandu, riwayat kunjungan, pengukuran fisik, dan sinkronisasi akun warga.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row lg:items-center">
                    @if($routeHas('kader.data.balita.index'))
                        <a
                            href="{{ route('kader.data.balita.index') }}"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 shadow-sm transition-all duration-150 ease-out hover:bg-slate-50"
                        >
                            <i class="ph-bold ph-arrow-left text-lg"></i>
                            Kembali
                        </a>
                    @endif

                    @if($routeHas('kader.data.balita.edit'))
                        <a
                            href="{{ route('kader.data.balita.edit', $balita->id) }}"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-4 text-sm font-black text-white shadow-sm transition-all duration-150 ease-out hover:bg-emerald-800"
                        >
                            <i class="ph-fill ph-pencil-simple text-lg"></i>
                            Edit Data
                        </a>
                    @endif
                </div>
            </div>
        </section>

        {{-- STATISTIK --}}
        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-slate-100 bg-white/85 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                        <i class="ph-fill ph-calendar-heart text-xl"></i>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-black text-emerald-700">
                        Usia
                    </span>
                </div>
                <p class="mt-4 text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">
                    Usia Saat Ini
                </p>
                <h2 class="mt-1 text-2xl font-black text-slate-900">
                    {{ $usiaText }}
                </h2>
                <p class="mt-1 text-xs font-semibold text-slate-500">
                    Berdasarkan tanggal lahir
                </p>
            </div>

            <div class="rounded-3xl border border-slate-100 bg-white/85 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-teal-50 text-teal-700">
                        <i class="ph-fill ph-stethoscope text-xl"></i>
                    </div>
                    <span class="rounded-full bg-teal-50 px-3 py-1 text-[11px] font-black text-teal-700">
                        Layanan
                    </span>
                </div>
                <p class="mt-4 text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">
                    Total Kunjungan
                </p>
                <h2 class="mt-1 text-3xl font-black text-slate-900">
                    {{ $totalKunjungan }}
                </h2>
                <p class="mt-1 text-xs font-semibold text-slate-500">
                    Riwayat layanan tercatat
                </p>
            </div>

            <div class="rounded-3xl border border-slate-100 bg-white/85 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-50 text-sky-700">
                        <i class="ph-fill ph-ruler text-xl"></i>
                    </div>
                    <span class="rounded-full bg-sky-50 px-3 py-1 text-[11px] font-black text-sky-700">
                        Fisik
                    </span>
                </div>
                <p class="mt-4 text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">
                    Pemeriksaan Terakhir
                </p>
                <h2 class="mt-1 text-2xl font-black text-slate-900">
                    {{ $pemeriksaanTerakhir ? 'Ada' : 'Belum Ada' }}
                </h2>
                <p class="mt-1 text-xs font-semibold text-slate-500">
                    Data pengukuran fisik
                </p>
            </div>

            <div class="rounded-3xl border border-slate-100 bg-white/85 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-2xl
                        {{ $akunTerhubung ? 'bg-teal-50 text-teal-700' : 'bg-amber-50 text-amber-700' }}"
                    >
                        <i class="ph-fill {{ $akunTerhubung ? 'ph-link-simple' : 'ph-warning-circle' }} text-xl"></i>
                    </div>
                    <span
                        class="rounded-full px-3 py-1 text-[11px] font-black
                        {{ $akunTerhubung ? 'bg-teal-50 text-teal-700' : 'bg-amber-50 text-amber-700' }}"
                    >
                        Akun
                    </span>
                </div>
                <p class="mt-4 text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">
                    Status Akun
                </p>
                <h2 class="mt-1 text-2xl font-black text-slate-900">
                    {{ $akunTerhubung ? 'Terhubung' : 'Belum' }}
                </h2>
                <p class="mt-1 text-xs font-semibold text-slate-500">
                    Akses warga/orang tua
                </p>
            </div>
        </section>

        <div class="grid gap-5 xl:grid-cols-[1fr_360px]">

            {{-- KONTEN KIRI --}}
            <div class="space-y-5">

                {{-- IDENTITAS --}}
                <section class="rounded-[1.75rem] border border-slate-100 bg-white/85 p-5 shadow-sm">
                    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-2xl bg-emerald-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.16em] text-emerald-700">
                                <i class="ph-fill ph-identification-card"></i>
                                Identitas
                            </div>

                            <h2 class="mt-3 text-2xl font-black text-slate-900">
                                Profil Balita
                            </h2>

                            <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                                Data dasar Balita dan keluarga.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Nama Lengkap
                            </p>
                            <p class="mt-2 text-sm font-black text-slate-800">
                                {{ $balita->nama_lengkap ?? '-' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                NIK Balita
                            </p>
                            <p class="mt-2 text-sm font-black text-slate-800">
                                {{ $balita->nik ?? '-' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Tempat, Tanggal Lahir
                            </p>
                            <p class="mt-2 text-sm font-black text-slate-800">
                                {{ $balita->tempat_lahir ?? '-' }},
                                {{ $tanggalLahir ? $tanggalLahir->translatedFormat('d F Y') : '-' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Jenis Kelamin
                            </p>
                            <p class="mt-2 text-sm font-black text-slate-800">
                                {{ $genderLabel }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Nama Ibu
                            </p>
                            <p class="mt-2 text-sm font-black text-slate-800">
                                {{ $balita->nama_ibu ?? '-' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Nama Ayah
                            </p>
                            <p class="mt-2 text-sm font-black text-slate-800">
                                {{ $balita->nama_ayah ?? '-' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Berat Lahir
                            </p>
                            <p class="mt-2 text-sm font-black text-slate-800">
                                {{ $balita->berat_lahir ?? '-' }} kg
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Panjang Lahir
                            </p>
                            <p class="mt-2 text-sm font-black text-slate-800">
                                {{ $balita->panjang_lahir ?? '-' }} cm
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 md:col-span-2">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Alamat
                            </p>
                            <p class="mt-2 text-sm font-semibold leading-7 text-slate-700">
                                {{ $balita->alamat ?? '-' }}
                            </p>
                        </div>
                    </div>
                </section>

                {{-- PEMERIKSAAN TERAKHIR --}}
                <section class="rounded-[1.75rem] border border-slate-100 bg-white/85 p-5 shadow-sm">
                    <div class="mb-5">
                        <div class="inline-flex items-center gap-2 rounded-2xl bg-sky-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.16em] text-sky-700">
                            <i class="ph-fill ph-ruler"></i>
                            Pengukuran
                        </div>

                        <h2 class="mt-3 text-2xl font-black text-slate-900">
                            Pemeriksaan Terakhir
                        </h2>

                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                            Ringkasan pengukuran fisik terakhir jika sudah tersedia.
                        </p>
                    </div>

                    @if($pemeriksaanTerakhir && $pemeriksaanTerakhir->pemeriksaan)
                        @php
                            $pemeriksaan = $pemeriksaanTerakhir->pemeriksaan;
                        @endphp

                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                    Berat Badan
                                </p>
                                <p class="mt-2 text-2xl font-black text-slate-900">
                                    {{ $pemeriksaan->berat_badan ?? '-' }}
                                    <span class="text-sm text-slate-500">kg</span>
                                </p>
                            </div>

                            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                    Tinggi Badan
                                </p>
                                <p class="mt-2 text-2xl font-black text-slate-900">
                                    {{ $pemeriksaan->tinggi_badan ?? '-' }}
                                    <span class="text-sm text-slate-500">cm</span>
                                </p>
                            </div>

                            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                    Lingkar Kepala
                                </p>
                                <p class="mt-2 text-2xl font-black text-slate-900">
                                    {{ $pemeriksaan->lingkar_kepala ?? '-' }}
                                    <span class="text-sm text-slate-500">cm</span>
                                </p>
                            </div>

                            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                    LILA
                                </p>
                                <p class="mt-2 text-2xl font-black text-slate-900">
                                    {{ $pemeriksaan->lila ?? '-' }}
                                    <span class="text-sm text-slate-500">cm</span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3 text-sm font-semibold leading-6 text-sky-700">
                            <i class="ph-fill ph-calendar-check mr-1"></i>
                            Kunjungan terakhir tercatat pada
                            <span class="font-black">
                                {{ filled($pemeriksaanTerakhir->tanggal_kunjungan ?? null) ? Carbon::parse($pemeriksaanTerakhir->tanggal_kunjungan)->translatedFormat('d F Y') : '-' }}
                            </span>
                        </div>
                    @else
                        <div class="rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50/70 px-5 py-10 text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                                <i class="ph-fill ph-ruler text-2xl"></i>
                            </div>

                            <h3 class="mt-4 text-lg font-black text-slate-900">
                                Belum Ada Pemeriksaan
                            </h3>

                            <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-7 text-slate-500">
                                Data pemeriksaan fisik Balita belum tersedia. Input pemeriksaan dapat dilakukan melalui menu pemeriksaan Kader.
                            </p>

                            @if($routeHas('kader.pemeriksaan.create'))
                                <a
                                    href="{{ route('kader.pemeriksaan.create') }}"
                                    class="mt-5 inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-5 text-sm font-black text-white shadow-sm transition-all duration-150 ease-out hover:bg-emerald-800"
                                >
                                    <i class="ph-fill ph-plus-circle text-lg"></i>
                                    Input Pemeriksaan
                                </a>
                            @endif
                        </div>
                    @endif
                </section>

                {{-- RIWAYAT KUNJUNGAN --}}
                <section class="rounded-[1.75rem] border border-slate-100 bg-white/85 p-5 shadow-sm">
                    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-2xl bg-amber-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.16em] text-amber-700">
                                <i class="ph-fill ph-clock-counter-clockwise"></i>
                                Riwayat
                            </div>

                            <h2 class="mt-3 text-2xl font-black text-slate-900">
                                Riwayat Kunjungan
                            </h2>

                            <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                                Maksimal 10 kunjungan terakhir yang terhubung dengan data Balita.
                            </p>
                        </div>
                    </div>

                    @if($kunjungans->count())
                        <div class="overflow-hidden rounded-2xl border border-slate-100">
                            <div class="hidden grid-cols-[150px_1fr_160px_110px] gap-3 border-b border-slate-100 bg-slate-50 px-4 py-3 text-xs font-black uppercase tracking-[0.14em] text-slate-400 lg:grid">
                                <span>Tanggal</span>
                                <span>Jenis Layanan</span>
                                <span>Petugas</span>
                                <span>Aksi</span>
                            </div>

                            <div class="divide-y divide-slate-100">
                                @foreach($kunjungans as $kunjungan)
                                    @php
                                        $tanggalKunjungan = filled($kunjungan->tanggal_kunjungan ?? null)
                                            ? Carbon::parse($kunjungan->tanggal_kunjungan)
                                            : null;

                                        $jenisKunjungan = $kunjungan->jenis_kunjungan ?? 'kunjungan';

                                        $badgeClass = match ($jenisKunjungan) {
                                            'imunisasi' => 'border-sky-100 bg-sky-50 text-sky-700',
                                            'pemeriksaan' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
                                            default => 'border-slate-100 bg-slate-50 text-slate-600',
                                        };

                                        $jenisLabel = match ($jenisKunjungan) {
                                            'imunisasi' => 'Imunisasi',
                                            'pemeriksaan' => 'Pemeriksaan Fisik',
                                            default => 'Kunjungan Posyandu',
                                        };
                                    @endphp

                                    <div class="grid gap-3 px-4 py-4 lg:grid-cols-[150px_1fr_160px_110px] lg:items-center">
                                        <div>
                                            <p class="text-sm font-black text-slate-900">
                                                {{ $tanggalKunjungan ? $tanggalKunjungan->translatedFormat('d M Y') : '-' }}
                                            </p>
                                            <p class="mt-0.5 text-xs font-semibold text-slate-500">
                                                {{ $tanggalKunjungan ? $tanggalKunjungan->format('H:i') . ' WIB' : 'Waktu tidak tercatat' }}
                                            </p>
                                        </div>

                                        <div>
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-[11px] font-black uppercase tracking-[0.12em] {{ $badgeClass }}">
                                                {{ $jenisLabel }}
                                            </span>

                                            @if($kunjungan->pemeriksaan)
                                                <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                                                    BB {{ $kunjungan->pemeriksaan->berat_badan ?? '-' }} kg,
                                                    TB {{ $kunjungan->pemeriksaan->tinggi_badan ?? '-' }} cm
                                                </p>
                                            @else
                                                <p class="mt-2 text-xs font-semibold leading-5 text-slate-500">
                                                    Tidak ada data antropometri pada kunjungan ini.
                                                </p>
                                            @endif
                                        </div>

                                        <div>
                                            <p class="text-sm font-black text-slate-800">
                                                {{ data_get($kunjungan, 'petugas.name') ?? data_get($kunjungan, 'petugas.nama_lengkap') ?? '-' }}
                                            </p>
                                            <p class="mt-0.5 text-xs font-semibold text-slate-500">
                                                Petugas layanan
                                            </p>
                                        </div>

                                        <div>
                                            @if($routeHas('kader.kunjungan.show'))
                                                <a
                                                    href="{{ route('kader.kunjungan.show', $kunjungan->id) }}"
                                                    class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 text-xs font-black text-slate-600 transition-all duration-150 ease-out hover:bg-slate-50"
                                                >
                                                    <i class="ph-fill ph-eye text-base"></i>
                                                    Detail
                                                </a>
                                            @else
                                                <span class="inline-flex h-10 w-full items-center justify-center rounded-2xl border border-slate-100 bg-slate-50 px-3 text-xs font-black text-slate-400">
                                                    Tidak Ada Aksi
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50/70 px-5 py-10 text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                                <i class="ph-fill ph-clipboard-text text-2xl"></i>
                            </div>

                            <h3 class="mt-4 text-lg font-black text-slate-900">
                                Riwayat Masih Kosong
                            </h3>

                            <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-7 text-slate-500">
                                Belum ada riwayat kunjungan atau pemeriksaan yang tercatat untuk Balita ini.
                            </p>
                        </div>
                    @endif
                </section>
            </div>

            {{-- SIDEBAR KANAN --}}
            <aside class="space-y-5">
                <section class="sticky top-5 rounded-[1.75rem] border border-slate-100 bg-white/90 p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl border
                            {{ $akunTerhubung ? 'border-teal-100 bg-teal-50 text-teal-700' : 'border-amber-100 bg-amber-50 text-amber-700' }}"
                        >
                            <i class="ph-fill {{ $akunTerhubung ? 'ph-link-simple' : 'ph-warning-circle' }} text-2xl"></i>
                        </div>

                        <div>
                            <h3 class="text-lg font-black text-slate-900">
                                Akun Warga
                            </h3>
                            <p class="text-sm font-semibold text-slate-500">
                                Akses orang tua/wali.
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-5 rounded-2xl border px-4 py-3
                        {{ $akunTerhubung ? 'border-teal-100 bg-teal-50 text-teal-800' : 'border-amber-100 bg-amber-50 text-amber-800' }}"
                    >
                        <p class="text-sm font-black">
                            {{ $akunTerhubung ? 'Akun Terhubung' : 'Belum Terhubung' }}
                        </p>

                        <p class="mt-1 text-xs font-semibold leading-5">
                            {{ $akunTerhubung ? 'Data Balita sudah bisa diakses oleh akun warga terkait.' : 'Akun warga belum tersinkron dengan data Balita ini.' }}
                        </p>
                    </div>

                    @if($userTerhubung)
                        <div class="mt-4 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Nama Akun
                            </p>
                            <p class="mt-2 text-sm font-black text-slate-800">
                                {{ $userTerhubung->name ?? '-' }}
                            </p>

                            <p class="mt-3 text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Email
                            </p>
                            <p class="mt-2 break-all text-sm font-semibold text-slate-700">
                                {{ $userTerhubung->email ?? '-' }}
                            </p>

                            <p class="mt-3 text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Status
                            </p>
                            <p class="mt-2 text-sm font-black text-slate-800">
                                {{ ucfirst($userTerhubung->status ?? 'aktif') }}
                            </p>
                        </div>
                    @else
                        <div class="mt-4 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3">
                            <p class="text-sm font-black text-amber-800">
                                Akun belum ditemukan
                            </p>
                            <p class="mt-1 text-xs font-semibold leading-5 text-amber-700">
                                Admin perlu membuat akun warga dengan NIK Balita yang sama, lalu Kader dapat melakukan sinkronisasi.
                            </p>
                        </div>

                        @if($routeHas('kader.data.balita.sync'))
                            <form
                                action="{{ route('kader.data.balita.sync', $balita->id) }}"
                                method="POST"
                                class="mt-4"
                            >
                                @csrf
                                <button
                                    type="submit"
                                    class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 text-sm font-black text-amber-700 transition-all duration-150 ease-out hover:bg-amber-100"
                                >
                                    <i class="ph-fill ph-link-simple text-lg"></i>
                                    Coba Sinkron Akun
                                </button>
                            </form>
                        @endif
                    @endif

                    <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <h4 class="text-sm font-black text-slate-800">
                            Ringkasan Sistem
                        </h4>

                        <ul class="mt-3 space-y-2 text-xs font-semibold leading-5 text-slate-600">
                            <li class="flex gap-2">
                                <i class="ph-fill ph-check-circle mt-0.5 text-emerald-600"></i>
                                <span>NIK utama yang digunakan adalah NIK Balita.</span>
                            </li>
                            <li class="flex gap-2">
                                <i class="ph-fill ph-check-circle mt-0.5 text-emerald-600"></i>
                                <span>Orang tua/wali memakai akun warga untuk melihat data.</span>
                            </li>
                            <li class="flex gap-2">
                                <i class="ph-fill ph-check-circle mt-0.5 text-emerald-600"></i>
                                <span>Riwayat kunjungan tidak boleh hilang saat data digunakan.</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-5 grid gap-2">
                        @if($routeHas('kader.data.balita.edit'))
                            <a
                                href="{{ route('kader.data.balita.edit', $balita->id) }}"
                                class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-4 text-sm font-black text-white shadow-sm transition-all duration-150 ease-out hover:bg-emerald-800"
                            >
                                <i class="ph-fill ph-pencil-simple text-lg"></i>
                                Edit Data Balita
                            </a>
                        @endif

                        @if($routeHas('kader.data.balita.index'))
                            <a
                                href="{{ route('kader.data.balita.index') }}"
                                class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 transition-all duration-150 ease-out hover:bg-slate-50"
                            >
                                <i class="ph-bold ph-arrow-left text-lg"></i>
                                Kembali ke Daftar
                            </a>
                        @endif
                    </div>
                </section>
            </aside>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sessionToast = document.getElementById('nexusSessionToast');

            function closeSessionToast() {
                if (!sessionToast) {
                    return;
                }

                sessionToast.classList.remove('nexus-toast-show');
                sessionToast.classList.add('nexus-toast-hide');

                setTimeout(function () {
                    sessionToast.remove();
                }, 240);
            }

            if (sessionToast) {
                setTimeout(closeSessionToast, 3800);

                const closeButton = sessionToast.querySelector('.nexus-toast-close');

                if (closeButton) {
                    closeButton.addEventListener('click', closeSessionToast);
                }
            }
        });
    </script>
@endsection