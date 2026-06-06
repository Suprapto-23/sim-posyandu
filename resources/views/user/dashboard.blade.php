@extends('layouts.user')

@section('title', 'Beranda Saya')
@section('page_title', 'Beranda Saya')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    $user = Auth::user();
    $userName = $user->name ?? 'Warga';
    $firstName = Str::of($userName)->explode(' ')->first() ?: 'Warga';

    $routeTo = function ($names, $params = []) {
        foreach ((array) $names as $name) {
            if (Route::has($name)) {
                return route($name, $params);
            }
        }

        return '#';
    };

    $anakList = isset($dataAnak) ? collect($dataAnak) : collect();

    $remajaList = collect();
    if (isset($dataRemaja)) {
        $remajaList = $dataRemaja instanceof \Illuminate\Support\Collection
            ? $dataRemaja
            : collect([$dataRemaja])->filter();
    }

    $lansiaList = collect();
    if (isset($dataLansia)) {
        $lansiaList = $dataLansia instanceof \Illuminate\Support\Collection
            ? $dataLansia
            : collect([$dataLansia])->filter();
    }

    $jadwalList = isset($jadwalTerdekat) ? collect($jadwalTerdekat) : collect();
    $jadwalUtama = $jadwalList->first();

    $notifList = isset($notifikasiTerbaru) ? collect($notifikasiTerbaru)->take(4) : collect();
    $jumlahNotif = $totalNotifikasiBelumDibaca ?? $notifList->where('is_read', false)->count();

    $totalSasaran = $anakList->count() + $remajaList->count() + $lansiaList->count();

    $monitoringRoute = $routeTo('user.monitoring.index');
    $riwayatRoute = $routeTo('user.riwayat.index');
    $jadwalRoute = $routeTo('user.jadwal.index');
    $notifikasiRoute = $routeTo('user.notifikasi.index');
    $profileRoute = $routeTo('user.profile.edit');

    $balitaShowRoute = fn ($id) => $routeTo(['user.balita.show', 'user.monitoring.balita.show'], [$id]);
    $remajaShowRoute = fn ($id) => $routeTo(['user.remaja.show', 'user.monitoring.remaja.show'], [$id]);
    $lansiaShowRoute = fn ($id) => $routeTo(['user.lansia.show', 'user.monitoring.lansia.show'], [$id]);

    $formatDate = fn ($value, $format = 'd F Y') => $value
        ? Carbon::parse($value)->translatedFormat($format)
        : '-';

    $formatTime = fn ($value) => $value
        ? Carbon::parse($value)->format('H:i')
        : '-';

    $ageText = function ($tanggalLahir) {
        if (blank($tanggalLahir)) return '-';

        $diff = Carbon::parse($tanggalLahir)->diff(now());

        return $diff->y > 0 ? $diff->y . ' tahun' : $diff->m . ' bulan';
    };

    $genderText = fn ($value) => match ($value) {
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        default => 'Belum diisi',
    };

    $healthItems = collect();

    foreach ($anakList->take(2) as $anak) {
        $healthItems->push([
            'type' => 'Balita',
            'name' => $anak->nama_lengkap,
            'meta' => $ageText($anak->tanggal_lahir) . ' • ' . $genderText($anak->jenis_kelamin),
            'caption' => 'Lihat KMS',
            'href' => $balitaShowRoute($anak->id),
            'icon' => 'child',
            'tone' => 'rose',
        ]);
    }

    foreach ($remajaList->take(1) as $remaja) {
        $healthItems->push([
            'type' => 'Remaja',
            'name' => $remaja->nama_lengkap,
            'meta' => $ageText($remaja->tanggal_lahir) . ' • ' . ($remaja->sekolah ?: 'Sekolah belum diisi'),
            'caption' => 'Cek IMT',
            'href' => $remajaShowRoute($remaja->id),
            'icon' => 'user-graduate',
            'tone' => 'sky',
        ]);
    }

    foreach ($lansiaList->take(1) as $lansia) {
        $healthItems->push([
            'type' => 'Lansia',
            'name' => $lansia->nama_lengkap,
            'meta' => $ageText($lansia->tanggal_lahir) . ' • ' . ucwords(str_replace('_', ' ', $lansia->tingkat_kemandirian ?: 'Belum diisi')),
            'caption' => 'Cek Tensi',
            'href' => $lansiaShowRoute($lansia->id),
            'icon' => 'heart-pulse',
            'tone' => 'amber',
        ]);
    }

    $toneMap = [
        'emerald' => [
            'icon' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
            'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'hover' => 'group-hover:text-emerald-700',
            'circle' => 'bg-emerald-50 text-emerald-600',
        ],
        'rose' => [
            'icon' => 'bg-rose-50 text-rose-500 border-rose-100',
            'badge' => 'bg-rose-50 text-rose-700 border-rose-200',
            'hover' => 'group-hover:text-rose-600',
            'circle' => 'bg-rose-50 text-rose-500',
        ],
        'sky' => [
            'icon' => 'bg-sky-50 text-sky-500 border-sky-100',
            'badge' => 'bg-sky-50 text-sky-700 border-sky-200',
            'hover' => 'group-hover:text-sky-600',
            'circle' => 'bg-sky-50 text-sky-500',
        ],
        'amber' => [
            'icon' => 'bg-amber-50 text-amber-500 border-amber-100',
            'badge' => 'bg-amber-50 text-amber-700 border-amber-200',
            'hover' => 'group-hover:text-amber-600',
            'circle' => 'bg-amber-50 text-amber-500',
        ],
        'violet' => [
            'icon' => 'bg-violet-50 text-violet-500 border-violet-100',
            'badge' => 'bg-violet-50 text-violet-700 border-violet-200',
            'hover' => 'group-hover:text-violet-600',
            'circle' => 'bg-violet-50 text-violet-500',
        ],
    ];

    $menus = [
        ['icon' => 'heartbeat', 'label' => 'Pantau', 'caption' => 'Kesehatan', 'href' => $monitoringRoute, 'tone' => 'rose'],
        ['icon' => 'file-medical', 'label' => 'Riwayat', 'caption' => 'Rekam medis', 'href' => $riwayatRoute, 'tone' => 'sky'],
        ['icon' => 'calendar-check', 'label' => 'Jadwal', 'caption' => 'Agenda', 'href' => $jadwalRoute, 'tone' => 'emerald'],
        ['icon' => 'bell', 'label' => 'Pesan', 'caption' => 'Notifikasi', 'href' => $notifikasiRoute, 'tone' => 'amber'],
        ['icon' => 'user-cog', 'label' => 'Profil', 'caption' => 'Akun', 'href' => $profileRoute, 'tone' => 'violet'],
    ];
@endphp

@push('styles')
<style>
    .nexus-enter {
        opacity: 0;
        animation: nexusEnter .38s cubic-bezier(.16, 1, .3, 1) forwards;
    }

    .nexus-enter-2 {
        opacity: 0;
        animation: nexusEnter .38s cubic-bezier(.16, 1, .3, 1) .06s forwards;
    }

    .nexus-enter-3 {
        opacity: 0;
        animation: nexusEnter .38s cubic-bezier(.16, 1, .3, 1) .12s forwards;
    }

    @keyframes nexusEnter {
        from {
            opacity: 0;
            transform: translateY(12px) scale(.99);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .nexus-page {
        background:
            radial-gradient(circle at 8% 8%, rgba(16, 185, 129, .14), transparent 28%),
            radial-gradient(circle at 90% 12%, rgba(14, 165, 233, .11), transparent 26%),
            radial-gradient(circle at 74% 88%, rgba(245, 158, 11, .11), transparent 28%),
            linear-gradient(135deg, #f8fafc 0%, #ecfdf5 44%, #eff6ff 100%);
    }

    .nexus-card {
        border: 1px solid rgba(255,255,255,.76);
        background: rgba(255,255,255,.68);
        backdrop-filter: blur(20px);
        box-shadow: 0 16px 46px rgba(15,23,42,.055);
    }

    .nexus-soft {
        border: 1px solid rgba(226,232,240,.78);
        background: rgba(255,255,255,.70);
        backdrop-filter: blur(16px);
        box-shadow: 0 10px 28px rgba(15,23,42,.04);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .nexus-soft:hover {
        transform: translateY(-2px);
        border-color: rgba(16,185,129,.28);
        box-shadow: 0 16px 38px rgba(15,23,42,.065);
    }

    .nexus-scroll::-webkit-scrollbar {
        width: 4px;
    }

    .nexus-scroll::-webkit-scrollbar-thumb {
        background: rgba(148,163,184,.55);
        border-radius: 999px;
    }

    @media (prefers-reduced-motion: reduce) {
        .nexus-enter,
        .nexus-enter-2,
        .nexus-enter-3 {
            animation: none;
            opacity: 1;
        }

        .nexus-soft,
        .nexus-soft:hover {
            transition: none;
            transform: none;
        }
    }
</style>
@endpush

@section('content')
<div class="nexus-page -mx-4 -my-4 min-h-[calc(100vh-96px)] px-4 py-5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-5">

        <section class="nexus-enter grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">

            <div class="nexus-card relative overflow-hidden rounded-[30px] p-5 sm:p-6">
                <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-emerald-300/20 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-24 left-8 h-56 w-56 rounded-full bg-sky-300/14 blur-3xl"></div>

                <div class="relative">
                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50/90 px-3.5 py-2 text-[10px] font-black uppercase tracking-[0.18em] text-emerald-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Portal Warga Aktif
                    </span>

                    <h1 class="mt-4 text-3xl font-black tracking-tight text-slate-800 sm:text-4xl">
                        Halo, {{ ucwords($firstName) }}
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm font-semibold leading-7 text-slate-600">
                        Pantau jadwal, data kesehatan, dan pemberitahuan Posyandu dalam tampilan yang ringkas, segar, dan tidak terasa seperti tabel administrasi zaman batu.
                    </p>

                    <div class="mt-5 grid grid-cols-3 gap-3">
                        <div class="rounded-[20px] border border-emerald-100 bg-white/70 px-3 py-3">
                            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-400">Data</p>
                            <p class="mt-1 text-2xl font-black text-slate-800">{{ $totalSasaran }}</p>
                        </div>

                        <div class="rounded-[20px] border border-sky-100 bg-white/70 px-3 py-3">
                            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-400">Jadwal</p>
                            <p class="mt-1 text-2xl font-black text-sky-700">{{ $jadwalList->count() }}</p>
                        </div>

                        <div class="rounded-[20px] border border-amber-100 bg-white/70 px-3 py-3">
                            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-400">Pesan</p>
                            <p class="mt-1 text-2xl font-black text-amber-700">{{ $jumlahNotif }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nexus-card relative overflow-hidden rounded-[30px] p-5">
                <div class="absolute -right-12 -top-16 h-40 w-40 rounded-full bg-emerald-200/24 blur-3xl"></div>

                <div class="relative flex h-full flex-col justify-between">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-emerald-700">
                                Jadwal Terdekat
                            </p>

                            @if($jadwalUtama)
                                <h2 class="mt-3 line-clamp-2 text-xl font-black leading-tight text-slate-800">
                                    {{ $jadwalUtama->judul }}
                                </h2>

                                <p class="mt-2 text-sm font-bold leading-6 text-slate-500">
                                    {{ $formatDate($jadwalUtama->tanggal, 'l, d F Y') }}
                                    <br>
                                    {{ $formatTime($jadwalUtama->waktu_mulai) }} sampai {{ $formatTime($jadwalUtama->waktu_selesai) }} WIB
                                </p>
                            @else
                                <h2 class="mt-3 text-xl font-black text-slate-800">
                                    Belum Ada Agenda
                                </h2>

                                <p class="mt-2 text-sm font-bold leading-6 text-slate-500">
                                    Jadwal aktif belum tersedia.
                                </p>
                            @endif
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[18px] bg-emerald-100 text-base font-black text-emerald-700">
                            J
                        </div>
                    </div>

                    <a href="{{ $jadwalRoute }}"
                       class="smooth-route mt-4 inline-flex h-10 w-full items-center justify-center rounded-2xl bg-emerald-600 px-5 text-xs font-black uppercase tracking-[0.12em] text-white shadow-[0_12px_26px_rgba(16,185,129,.22)] transition hover:bg-emerald-700">
                        Lihat Jadwal
                    </a>
                </div>
            </div>
        </section>

        @if(isset($pesanError) && $pesanError)
            <section class="nexus-enter-2 relative overflow-hidden rounded-[24px] border border-amber-200 bg-amber-50/85 p-5 text-amber-800 shadow-sm backdrop-blur-xl">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white text-amber-600 shadow-sm">
                            <i class="fas fa-lock"></i>
                        </div>

                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.16em]">Akses Data Terbatas</p>
                            <p class="mt-1 text-sm font-semibold leading-6">{{ $pesanError }}</p>
                        </div>
                    </div>

                    <a href="{{ $profileRoute }}"
                       class="smooth-route inline-flex h-10 shrink-0 items-center justify-center rounded-2xl bg-amber-500 px-5 text-xs font-black uppercase tracking-[0.12em] text-amber-950 shadow-[0_12px_26px_rgba(245,158,11,.22)] transition hover:bg-amber-400">
                        Lengkapi Profil
                    </a>
                </div>
            </section>
        @endif

        <section class="nexus-enter-2 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
            @foreach($menus as $menu)
                @php $tone = $toneMap[$menu['tone']]; @endphp

                <a href="{{ $menu['href'] }}"
                   class="smooth-route nexus-soft group rounded-[22px] p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[16px] border text-lg {{ $tone['icon'] }}">
                            <i class="fas fa-{{ $menu['icon'] }}"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-slate-800">{{ $menu['label'] }}</p>
                            <p class="mt-0.5 truncate text-xs font-semibold text-slate-500">{{ $menu['caption'] }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </section>

        <section class="nexus-enter-3 grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_380px]">

            <div class="grid grid-cols-1 gap-5">

                <div class="nexus-card overflow-hidden rounded-[30px]">
                    <div class="border-b border-emerald-100/80 bg-gradient-to-r from-white/86 via-emerald-50/72 to-sky-50/62 px-5 py-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.20em] text-emerald-700">
                                    Agenda
                                </p>
                                <h3 class="mt-1 text-xl font-black text-slate-800">Jadwal Posyandu</h3>
                            </div>

                            <a href="{{ $jadwalRoute }}"
                               class="smooth-route inline-flex h-10 items-center rounded-2xl border border-emerald-100 bg-white/80 px-4 text-xs font-black uppercase tracking-[0.12em] text-emerald-700 hover:bg-emerald-50">
                                Semua
                            </a>
                        </div>
                    </div>

                    <div class="p-4">
                        @if($jadwalUtama)
                            <article class="relative overflow-hidden rounded-[26px] border border-emerald-100 bg-gradient-to-br from-white/82 via-emerald-50/48 to-sky-50/44 p-4">
                                <div class="absolute right-0 top-0 h-full w-1.5 bg-emerald-400/80"></div>

                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                    <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-[22px] border border-emerald-100 bg-white/78 text-emerald-700 shadow-sm">
                                        <span class="text-[10px] font-black uppercase tracking-[0.14em]">
                                            {{ $formatDate($jadwalUtama->tanggal, 'M') }}
                                        </span>
                                        <span class="text-2xl font-black leading-none">
                                            {{ $formatDate($jadwalUtama->tanggal, 'd') }}
                                        </span>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.10em] text-emerald-700">
                                                {{ ucwords(str_replace('_', ' ', $jadwalUtama->target_peserta ?? 'Semua')) }}
                                            </span>

                                            <span class="rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.10em] text-sky-700">
                                                {{ $formatTime($jadwalUtama->waktu_mulai) }} WIB
                                            </span>
                                        </div>

                                        <h4 class="mt-3 line-clamp-2 text-lg font-black leading-tight text-slate-800">
                                            {{ $jadwalUtama->judul }}
                                        </h4>

                                        <p class="mt-2 line-clamp-1 text-sm font-semibold text-slate-500">
                                            <i class="fas fa-map-marker-alt mr-1.5 text-emerald-400"></i>
                                            {{ $jadwalUtama->lokasi ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </article>
                        @else
                            <div class="rounded-[26px] border border-dashed border-emerald-300 bg-emerald-50/60 p-8 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-[22px] bg-white text-2xl text-emerald-500 shadow-sm">
                                    <i class="fas fa-calendar-times"></i>
                                </div>
                                <h4 class="mt-4 text-base font-black text-slate-800">Tidak Ada Jadwal</h4>
                                <p class="mt-1 text-sm font-semibold text-slate-500">Belum ada agenda Posyandu dalam waktu dekat.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="nexus-card overflow-hidden rounded-[30px]">
                    <div class="border-b border-emerald-100/80 bg-gradient-to-r from-white/86 via-emerald-50/72 to-amber-50/60 px-5 py-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.20em] text-emerald-700">
                                    Monitoring
                                </p>
                                <h3 class="mt-1 text-xl font-black text-slate-800">Data Keluarga</h3>
                            </div>

                            <a href="{{ $monitoringRoute }}"
                               class="smooth-route inline-flex h-10 items-center rounded-2xl border border-emerald-100 bg-white/80 px-4 text-xs font-black uppercase tracking-[0.12em] text-emerald-700 hover:bg-emerald-50">
                                Semua
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 p-4 md:grid-cols-2">
                        @forelse($healthItems as $item)
                            @php $tone = $toneMap[$item['tone']]; @endphp

                            <a href="{{ $item['href'] }}"
                               class="smooth-route nexus-soft group rounded-[24px] p-4">
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[18px] border text-lg {{ $tone['icon'] }}">
                                        <i class="fas fa-{{ $item['icon'] }}"></i>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <span class="rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[0.10em] {{ $tone['badge'] }}">
                                            {{ $item['type'] }}
                                        </span>

                                        <h4 class="mt-3 line-clamp-1 text-base font-black text-slate-800 {{ $tone['hover'] }}">
                                            {{ $item['name'] }}
                                        </h4>

                                        <p class="mt-1 line-clamp-1 text-sm font-semibold text-slate-500">
                                            {{ $item['meta'] }}
                                        </p>

                                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                                            <span class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-500">
                                                {{ $item['caption'] }}
                                            </span>
                                            <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $tone['circle'] }}">
                                                <i class="fas fa-chevron-right text-[10px]"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="col-span-full rounded-[26px] border border-dashed border-emerald-300 bg-emerald-50/60 p-8 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-[22px] bg-white text-xl text-emerald-500 shadow-sm">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <h4 class="mt-4 text-base font-black text-slate-800">Data Belum Terhubung</h4>
                                <p class="mx-auto mt-1 max-w-md text-sm font-semibold leading-6 text-slate-500">
                                    Lengkapi NIK atau hubungi Kader agar data kesehatan keluarga muncul.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <aside class="nexus-card flex max-h-[720px] min-h-[460px] flex-col overflow-hidden rounded-[30px]">
                <div class="border-b border-sky-100/80 bg-gradient-to-r from-white/86 via-sky-50/72 to-emerald-50/62 px-5 py-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.20em] text-sky-700">
                                Kotak Masuk
                            </p>
                            <h3 class="mt-1 text-xl font-black text-slate-800">Notifikasi</h3>
                        </div>

                        <a href="{{ $notifikasiRoute }}"
                           class="smooth-route inline-flex h-10 items-center rounded-2xl border border-sky-100 bg-white/80 px-4 text-xs font-black uppercase tracking-[0.12em] text-sky-700 hover:bg-sky-50">
                            Semua
                        </a>
                    </div>
                </div>

                <div class="nexus-scroll flex-1 space-y-3 overflow-y-auto p-4">
                    @forelse($notifList as $notif)
                        <a href="{{ $notifikasiRoute }}"
                           class="smooth-route block rounded-[22px] border p-4 shadow-sm transition hover:-translate-y-0.5
                           {{ !($notif['is_read'] ?? true)
                                ? 'border-sky-200 bg-sky-50/70'
                                : 'border-slate-100 bg-white/70 hover:border-sky-200 hover:bg-sky-50/45' }}">
                            <div class="flex gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[16px] border
                                {{ !($notif['is_read'] ?? true)
                                    ? 'border-sky-200 bg-white text-sky-600'
                                    : 'border-slate-200 bg-slate-50 text-slate-400' }}">
                                    <i class="fas {{ !($notif['is_read'] ?? true) ? 'fa-envelope' : 'fa-envelope-open' }}"></i>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <h4 class="line-clamp-1 text-sm font-black text-slate-800">
                                            {{ $notif['judul'] ?? 'Pemberitahuan' }}
                                        </h4>

                                        @if(!($notif['is_read'] ?? true))
                                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-rose-500"></span>
                                        @endif
                                    </div>

                                    <p class="mt-1 line-clamp-2 text-sm font-semibold leading-6 text-slate-500">
                                        {{ $notif['pesan'] ?? '-' }}
                                    </p>

                                    <p class="mt-3 text-[10px] font-black uppercase tracking-[0.12em] text-sky-600">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $notif['waktu'] ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="flex h-full min-h-[260px] flex-col items-center justify-center rounded-[24px] border border-dashed border-sky-300 bg-sky-50/55 p-8 text-center">
                            <div class="flex h-14 w-14 items-center justify-center rounded-[22px] bg-white text-xl text-sky-500 shadow-sm">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <h4 class="mt-4 text-base font-black text-slate-800">Kotak Masuk Bersih</h4>
                            <p class="mt-1 text-sm font-semibold leading-6 text-slate-500">Belum ada pemberitahuan baru.</p>
                        </div>
                    @endforelse
                </div>
            </aside>
        </section>
    </div>
</div>
@endsection