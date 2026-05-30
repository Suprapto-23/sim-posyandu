@extends('layouts.kader')

@section('title', 'Detail Data Lansia')
@section('page-name', 'Detail Data Lansia')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $kunjungans = collect($lansia->kunjungans ?? []);
    $totalKunjungan = $kunjungans->count();

    $pemeriksaanTerakhir = data_get($lansia, 'pemeriksaan_terakhir');

    if (!$pemeriksaanTerakhir) {
        $pemeriksaanTerakhir = $kunjungans
            ->filter(fn ($item) => filled(data_get($item, 'pemeriksaan')))
            ->first();
    }

    $tanggalLahir = filled($lansia->tanggal_lahir ?? null)
        ? Carbon::parse($lansia->tanggal_lahir)
        : null;

    $usiaText = '-';

    if ($tanggalLahir) {
        $diff = $tanggalLahir->diff(now('Asia/Jakarta'));
        $usiaText = $diff->y . ' tahun ' . $diff->m . ' bulan';
    }

    $genderLabel = match ($lansia->jenis_kelamin ?? null) {
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        default => '-',
    };

    $genderClass = match ($lansia->jenis_kelamin ?? null) {
        'L' => 'border-sky-100 bg-sky-50 text-sky-700',
        'P' => 'border-pink-100 bg-pink-50 text-pink-700',
        default => 'border-slate-100 bg-slate-50 text-slate-600',
    };

    $kemandirianLabel = match ($lansia->tingkat_kemandirian ?? null) {
        'mandiri' => 'Mandiri',
        'bantuan_sebagian' => 'Bantuan Sebagian',
        'ketergantungan_penuh' => 'Ketergantungan Penuh',
        default => 'Belum Diisi',
    };

    $kemandirianClass = match ($lansia->tingkat_kemandirian ?? null) {
        'mandiri' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
        'bantuan_sebagian' => 'border-amber-100 bg-amber-50 text-amber-700',
        'ketergantungan_penuh' => 'border-rose-100 bg-rose-50 text-rose-700',
        default => 'border-slate-100 bg-slate-50 text-slate-600',
    };

    $akunTerhubung = filled($lansia->user_id ?? null) || filled($userTerhubung ?? null);

    $imt = $lansia->imt ?? null;

    $imtLabel = 'Belum dihitung';
    $imtClass = 'border-slate-100 bg-slate-50 text-slate-600';

    if (filled($imt)) {
        if ($imt < 18.5) {
            $imtLabel = 'Berat Badan Kurang';
            $imtClass = 'border-amber-100 bg-amber-50 text-amber-700';
        } elseif ($imt >= 18.5 && $imt < 25) {
            $imtLabel = 'Normal';
            $imtClass = 'border-emerald-100 bg-emerald-50 text-emerald-700';
        } elseif ($imt >= 25 && $imt < 30) {
            $imtLabel = 'Berat Badan Berlebih';
            $imtClass = 'border-amber-100 bg-amber-50 text-amber-700';
        } else {
            $imtLabel = 'Obesitas';
            $imtClass = 'border-rose-100 bg-rose-50 text-rose-700';
        }
    }

    $tensi = $lansia->tekanan_darah ?? null;
    $tensiLabel = filled($tensi) ? 'Tercatat' : 'Belum Diisi';
    $tensiClass = filled($tensi)
        ? 'border-sky-100 bg-sky-50 text-sky-700'
        : 'border-slate-100 bg-slate-50 text-slate-600';

    if (filled($tensi) && str_contains($tensi, '/')) {
        [$sistolik, $diastolik] = array_pad(explode('/', $tensi), 2, null);

        if ((int) $sistolik >= 140 || (int) $diastolik >= 90) {
            $tensiLabel = 'Perlu Perhatian';
            $tensiClass = 'border-rose-100 bg-rose-50 text-rose-700';
        }
    }

    $sessionType = session('success') ? 'success' : (session('warning') ? 'warning' : (session('error') ? 'error' : null));
    $sessionMessage = session('success') ?? session('warning') ?? session('error');

    $initial = strtoupper(substr($lansia->nama_lengkap ?? 'L', 0, 1));
@endphp

@section('content')
    <style>
        .nexus-toast-show {
            animation: nexusToastShow .22s ease-out both;
        }

        .nexus-toast-hide {
            animation: nexusToastHide .18s ease-in both;
        }

        .nexus-modal-show {
            animation: nexusModalShow .16s ease-out both;
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

        @keyframes nexusModalShow {
            from {
                opacity: 0;
                transform: translateY(10px) scale(.985);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        body.nexus-modal-lock {
            overflow: hidden !important;
        }

        #nexusConfirmOverlay {
            position: fixed !important;
            inset: 0 !important;
            z-index: 999999 !important;
            display: none;
            place-items: center;
            min-height: 100dvh;
            padding: 18px;
            background: rgba(15, 23, 42, .48);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        #nexusConfirmOverlay.is-open {
            display: grid !important;
        }

        #nexusConfirmBox {
            width: min(100%, 440px);
            max-height: calc(100dvh - 36px);
            overflow-y: auto;
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, .8);
            background: rgba(255, 255, 255, .98);
            box-shadow: 0 26px 80px rgba(15, 23, 42, .22);
        }

        @media (max-width: 640px) {
            #nexusConfirmOverlay {
                padding: 14px;
                backdrop-filter: blur(4px);
                -webkit-backdrop-filter: blur(4px);
            }

            #nexusConfirmBox {
                border-radius: 24px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .nexus-toast-show,
            .nexus-toast-hide,
            .nexus-modal-show {
                animation: none !important;
            }
        }
    </style>

    <div class="w-full space-y-5">

        {{-- SESSION TOAST --}}
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
                                <i class="ph-fill ph-person-simple-walk text-base"></i>
                                Detail Lansia
                            </span>

                            <span class="inline-flex items-center rounded-2xl border px-3 py-2 text-[11px] font-black uppercase tracking-[0.14em] {{ $genderClass }}">
                                {{ $genderLabel }}
                            </span>

                            <span class="inline-flex items-center rounded-2xl border px-3 py-2 text-[11px] font-black uppercase tracking-[0.14em] {{ $kemandirianClass }}">
                                {{ $kemandirianLabel }}
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
                            {{ $lansia->nama_lengkap }}
                        </h1>

                        <p class="mt-2 text-sm font-bold leading-6 text-slate-600">
                            NIK Lansia:
                            <span class="font-black text-slate-800">{{ $lansia->nik ?? '-' }}</span>
                        </p>

                        <p class="mt-1 max-w-2xl text-sm font-semibold leading-7 text-slate-600">
                            Detail data Lansia untuk pemantauan kesehatan dasar, tingkat kemandirian, riwayat penyakit, dan sinkronisasi akun warga.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row lg:items-center">
                    @if($routeHas('kader.data.lansia.index'))
                        <a
                            href="{{ route('kader.data.lansia.index') }}"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 shadow-sm transition-all duration-150 ease-out hover:bg-slate-50"
                        >
                            <i class="ph-bold ph-arrow-left text-lg"></i>
                            Kembali
                        </a>
                    @endif

                    @if($routeHas('kader.data.lansia.edit'))
                        <a
                            href="{{ route('kader.data.lansia.edit', $lansia->id) }}"
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
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-50 text-sky-700">
                        <i class="ph-fill ph-heartbeat text-xl"></i>
                    </div>
                    <span class="rounded-full bg-sky-50 px-3 py-1 text-[11px] font-black text-sky-700">
                        Tensi
                    </span>
                </div>

                <p class="mt-4 text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">
                    Tekanan Darah
                </p>

                <h2 class="mt-1 text-2xl font-black text-slate-900">
                    {{ $lansia->tekanan_darah ?? '-' }}
                </h2>

                <p class="mt-1 text-xs font-semibold text-slate-500">
                    {{ $tensiLabel }}
                </p>
            </div>

            <div class="rounded-3xl border border-slate-100 bg-white/85 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-50 text-amber-700">
                        <i class="ph-fill ph-ruler text-xl"></i>
                    </div>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-[11px] font-black text-amber-700">
                        IMT
                    </span>
                </div>

                <p class="mt-4 text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">
                    Indeks Massa Tubuh
                </p>

                <h2 class="mt-1 text-2xl font-black text-slate-900">
                    {{ $imt ?? '-' }}
                </h2>

                <p class="mt-1 text-xs font-semibold text-slate-500">
                    {{ $imtLabel }}
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
                    Akses warga Lansia
                </p>
            </div>
        </section>

        <div class="grid gap-5 xl:grid-cols-[1fr_360px]">

            {{-- KONTEN KIRI --}}
            <div class="space-y-5">

                {{-- PROFIL --}}
                <section class="rounded-[1.75rem] border border-slate-100 bg-white/85 p-5 shadow-sm">
                    <div class="mb-5">
                        <div class="inline-flex items-center gap-2 rounded-2xl bg-emerald-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.16em] text-emerald-700">
                            <i class="ph-fill ph-identification-card"></i>
                            Identitas
                        </div>

                        <h2 class="mt-3 text-2xl font-black text-slate-900">
                            Profil Lansia
                        </h2>

                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                            Data dasar Lansia, alamat, dan identitas layanan.
                        </p>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Nama Lengkap
                            </p>
                            <p class="mt-2 text-sm font-black text-slate-800">
                                {{ $lansia->nama_lengkap ?? '-' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                NIK Lansia
                            </p>
                            <p class="mt-2 text-sm font-black text-slate-800">
                                {{ $lansia->nik ?? '-' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Tempat, Tanggal Lahir
                            </p>
                            <p class="mt-2 text-sm font-black text-slate-800">
                                {{ $lansia->tempat_lahir ?? '-' }},
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

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 md:col-span-2">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Alamat
                            </p>
                            <p class="mt-2 text-sm font-semibold leading-7 text-slate-700">
                                {{ $lansia->alamat ?? '-' }}
                            </p>
                        </div>
                    </div>
                </section>

                {{-- PEMERIKSAAN DASAR --}}
                <section class="rounded-[1.75rem] border border-slate-100 bg-white/85 p-5 shadow-sm">
                    <div class="mb-5">
                        <div class="inline-flex items-center gap-2 rounded-2xl bg-sky-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.16em] text-sky-700">
                            <i class="ph-fill ph-heartbeat"></i>
                            Pemeriksaan Dasar
                        </div>

                        <h2 class="mt-3 text-2xl font-black text-slate-900">
                            Ringkasan Kesehatan Lansia
                        </h2>

                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                            Data pengukuran fisik dan indikator kesehatan dasar.
                        </p>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Berat Badan
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-900">
                                {{ $lansia->berat_badan ?? '-' }}
                                <span class="text-sm text-slate-500">kg</span>
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Tinggi Badan
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-900">
                                {{ $lansia->tinggi_badan ?? '-' }}
                                <span class="text-sm text-slate-500">cm</span>
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                IMT
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-900">
                                {{ $imt ?? '-' }}
                            </p>
                            <span class="mt-2 inline-flex rounded-full border px-3 py-1 text-[11px] font-black {{ $imtClass }}">
                                {{ $imtLabel }}
                            </span>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Lingkar Perut
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-900">
                                {{ $lansia->lingkar_perut ?? '-' }}
                                <span class="text-sm text-slate-500">cm</span>
                            </p>
                        </div>
                    </div>

                    <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-2xl border border-slate-100 bg-white/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Tekanan Darah
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-900">
                                {{ $lansia->tekanan_darah ?? '-' }}
                            </p>
                            <span class="mt-2 inline-flex rounded-full border px-3 py-1 text-[11px] font-black {{ $tensiClass }}">
                                {{ $tensiLabel }}
                            </span>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-white/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Gula Darah
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-900">
                                {{ $lansia->gula_darah ?? '-' }}
                                <span class="text-sm text-slate-500">mg/dL</span>
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-white/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Kolesterol
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-900">
                                {{ $lansia->kolesterol ?? '-' }}
                                <span class="text-sm text-slate-500">mg/dL</span>
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-white/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Asam Urat
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-900">
                                {{ $lansia->asam_urat ?? '-' }}
                                <span class="text-sm text-slate-500">mg/dL</span>
                            </p>
                        </div>
                    </div>
                </section>

                {{-- KONDISI LANSIA --}}
                <section class="rounded-[1.75rem] border border-slate-100 bg-white/85 p-5 shadow-sm">
                    <div class="mb-5">
                        <div class="inline-flex items-center gap-2 rounded-2xl bg-amber-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.16em] text-amber-700">
                            <i class="ph-fill ph-hand-heart"></i>
                            Kondisi Lansia
                        </div>

                        <h2 class="mt-3 text-2xl font-black text-slate-900">
                            Kemandirian, Riwayat Penyakit, dan Keluhan
                        </h2>

                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                            Catatan kondisi fungsional dan kesehatan Lansia.
                        </p>
                    </div>

                    <div class="grid gap-3">
                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Tingkat Kemandirian
                            </p>
                            <span class="mt-3 inline-flex rounded-full border px-3 py-1 text-xs font-black {{ $kemandirianClass }}">
                                {{ $kemandirianLabel }}
                            </span>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Riwayat Penyakit Bawaan
                            </p>
                            <p class="mt-2 text-sm font-semibold leading-7 text-slate-700">
                                {{ $lansia->penyakit_bawaan ?? 'Belum ada catatan penyakit bawaan.' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">
                                Keluhan Saat Ini
                            </p>
                            <p class="mt-2 text-sm font-semibold leading-7 text-slate-700">
                                {{ $lansia->keluhan ?? 'Belum ada keluhan yang dicatat.' }}
                            </p>
                        </div>
                    </div>
                </section>

                {{-- RIWAYAT KUNJUNGAN --}}
                <section class="rounded-[1.75rem] border border-slate-100 bg-white/85 p-5 shadow-sm">
                    <div class="mb-5">
                        <div class="inline-flex items-center gap-2 rounded-2xl bg-teal-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.16em] text-teal-700">
                            <i class="ph-fill ph-clock-counter-clockwise"></i>
                            Riwayat
                        </div>

                        <h2 class="mt-3 text-2xl font-black text-slate-900">
                            Riwayat Kunjungan
                        </h2>

                        <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">
                            Maksimal 10 kunjungan terakhir yang terhubung dengan data Lansia.
                        </p>
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
                                            'pemeriksaan' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
                                            'edukasi' => 'border-sky-100 bg-sky-50 text-sky-700',
                                            default => 'border-slate-100 bg-slate-50 text-slate-600',
                                        };

                                        $jenisLabel = match ($jenisKunjungan) {
                                            'pemeriksaan' => 'Pemeriksaan Lansia',
                                            'edukasi' => 'Edukasi Kesehatan',
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
                                                    Tidak ada data pemeriksaan fisik pada kunjungan ini.
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
                                Belum ada riwayat kunjungan atau pemeriksaan yang tercatat untuk Lansia ini.
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
                                Akses Lansia.
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
                            {{ $akunTerhubung ? 'Data Lansia sudah bisa diakses oleh akun warga terkait.' : 'Akun warga belum tersinkron dengan data Lansia ini.' }}
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
                                Admin perlu membuat akun warga dengan NIK Lansia yang sama, lalu Kader dapat melakukan sinkronisasi.
                            </p>
                        </div>

                        @if($routeHas('kader.data.lansia.sync'))
                            <form
                                action="{{ route('kader.data.lansia.sync', $lansia->id) }}"
                                method="POST"
                                class="mt-4"
                                data-confirm="true"
                                data-confirm-variant="warning"
                                data-confirm-title="Sinkronkan Akun?"
                                data-confirm-message="Pastikan akun warga sudah dibuat Admin memakai NIK Lansia yang sama."
                                data-confirm-button="Ya, Sinkronkan"
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
                                <span>NIK utama yang digunakan adalah NIK Lansia.</span>
                            </li>
                            <li class="flex gap-2">
                                <i class="ph-fill ph-check-circle mt-0.5 text-emerald-600"></i>
                                <span>Data Lansia mencakup pengukuran fisik dan pemeriksaan kesehatan dasar.</span>
                            </li>
                            <li class="flex gap-2">
                                <i class="ph-fill ph-check-circle mt-0.5 text-emerald-600"></i>
                                <span>Riwayat pemeriksaan tidak boleh hilang saat data digunakan.</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-5 grid gap-2">
                        @if($routeHas('kader.data.lansia.edit'))
                            <a
                                href="{{ route('kader.data.lansia.edit', $lansia->id) }}"
                                class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-2xl bg-emerald-700 px-4 text-sm font-black text-white shadow-sm transition-all duration-150 ease-out hover:bg-emerald-800"
                            >
                                <i class="ph-fill ph-pencil-simple text-lg"></i>
                                Edit Data Lansia
                            </a>
                        @endif

                        @if($routeHas('kader.data.lansia.index'))
                            <a
                                href="{{ route('kader.data.lansia.index') }}"
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
            let pendingForm = null;
            let isSubmitting = false;

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

            function createConfirmLayer() {
                document.getElementById('nexusConfirmOverlay')?.remove();

                document.body.insertAdjacentHTML('beforeend', `
                    <div id="nexusConfirmOverlay" aria-hidden="true">
                        <div id="nexusConfirmBox" class="nexus-modal-show p-5" role="dialog" aria-modal="true" aria-labelledby="nexusConfirmTitle">
                            <div class="flex items-start gap-3.5">
                                <div id="nexusConfirmIconWrap" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-amber-100 bg-amber-50 text-amber-700">
                                    <i id="nexusConfirmIcon" class="ph-fill ph-warning-circle text-2xl"></i>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <h3 id="nexusConfirmTitle" class="text-lg font-black leading-6 text-slate-900">
                                        Konfirmasi Aksi
                                    </h3>

                                    <p id="nexusConfirmMessage" class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                                        Pastikan data sudah benar sebelum diproses.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 grid grid-cols-2 gap-2">
                                <button
                                    id="nexusConfirmCancel"
                                    type="button"
                                    class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-600 transition-all duration-150 ease-out hover:bg-slate-50"
                                >
                                    Batal
                                </button>

                                <button
                                    id="nexusConfirmSubmit"
                                    type="button"
                                    class="inline-flex h-11 items-center justify-center rounded-2xl bg-amber-600 px-4 text-sm font-black text-white shadow-sm transition-all duration-150 ease-out hover:bg-amber-700"
                                >
                                    Ya, Lanjutkan
                                </button>
                            </div>
                        </div>
                    </div>
                `);
            }

            createConfirmLayer();

            const confirmOverlay = document.getElementById('nexusConfirmOverlay');
            const confirmTitle = document.getElementById('nexusConfirmTitle');
            const confirmMessage = document.getElementById('nexusConfirmMessage');
            const confirmSubmit = document.getElementById('nexusConfirmSubmit');
            const confirmCancel = document.getElementById('nexusConfirmCancel');

            function openConfirm(form) {
                pendingForm = form;

                confirmTitle.textContent = form.getAttribute('data-confirm-title') || 'Konfirmasi Aksi';
                confirmMessage.textContent = form.getAttribute('data-confirm-message') || 'Pastikan data sudah benar sebelum diproses.';
                confirmSubmit.textContent = form.getAttribute('data-confirm-button') || 'Ya, Lanjutkan';

                confirmOverlay.classList.add('is-open');
                confirmOverlay.setAttribute('aria-hidden', 'false');
                document.body.classList.add('nexus-modal-lock');
            }

            function closeConfirm() {
                pendingForm = null;
                confirmOverlay.classList.remove('is-open');
                confirmOverlay.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('nexus-modal-lock');
            }

            document.querySelectorAll('form[data-confirm="true"]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (isSubmitting) {
                        return;
                    }

                    if (!form.dataset.confirmed) {
                        event.preventDefault();
                        openConfirm(form);
                    }
                });
            });

            if (confirmCancel) {
                confirmCancel.addEventListener('click', closeConfirm);
            }

            if (confirmOverlay) {
                confirmOverlay.addEventListener('click', function (event) {
                    if (event.target === confirmOverlay) {
                        closeConfirm();
                    }
                });
            }

            if (confirmSubmit) {
                confirmSubmit.addEventListener('click', function () {
                    if (!pendingForm || isSubmitting) {
                        return;
                    }

                    isSubmitting = true;
                    pendingForm.dataset.confirmed = 'true';
                    confirmSubmit.disabled = true;
                    confirmSubmit.classList.add('opacity-70', 'cursor-wait');
                    confirmSubmit.textContent = 'Memproses...';

                    if (pendingForm.requestSubmit) {
                        pendingForm.requestSubmit();
                    } else {
                        pendingForm.submit();
                    }
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && confirmOverlay && confirmOverlay.classList.contains('is-open')) {
                    closeConfirm();
                }
            });
        });
    </script>
@endsection