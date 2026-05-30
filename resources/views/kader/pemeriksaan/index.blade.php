@extends('layouts.bidan')

@section('title', 'Pemeriksaan Klinis')
@section('page-name', 'Pemeriksaan Klinis')
@section('page-title', 'Pemeriksaan Klinis')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    $tab = $tab ?? request('tab', 'pending');
    $pendingCount = $pendingCount ?? 0;

    $verifiedCount = \App\Models\Pemeriksaan::where('status_verifikasi', 'verified')->count();

    $statusMeta = function ($status) {
        return $status === 'verified'
            ? [
                'label' => 'Selesai Diperiksa',
                'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'dot' => 'bg-emerald-500',
            ]
            : [
                'label' => 'Menunggu Validasi',
                'class' => 'bg-amber-50 text-amber-700 ring-amber-200',
                'dot' => 'bg-amber-500',
            ];
    };

    $kategoriMeta = function ($kategori) {
        $kategori = strtolower($kategori ?? '-');

        return match ($kategori) {
            'balita' => [
                'label' => 'Balita',
                'class' => 'bg-sky-50 text-sky-700 ring-sky-200',
                'icon' => 'ph-baby',
            ],
            'remaja' => [
                'label' => 'Remaja',
                'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'icon' => 'ph-user-focus',
            ],
            'lansia' => [
                'label' => 'Lansia',
                'class' => 'bg-amber-50 text-amber-700 ring-amber-200',
                'icon' => 'ph-heartbeat',
            ],
            default => [
                'label' => ucfirst($kategori),
                'class' => 'bg-slate-50 text-slate-700 ring-slate-200',
                'icon' => 'ph-user',
            ],
        };
    };

    $formatTanggal = function ($item) {
        $tanggal = $item->tanggal_kunjungan
            ?? $item->tanggal_periksa
            ?? optional($item->kunjungan)->tanggal_kunjungan
            ?? $item->created_at;

        return $tanggal ? Carbon::parse($tanggal)->translatedFormat('d M Y') : '-';
    };

    $metricValue = function ($value, $unit = '') {
        if ($value === null || $value === '') {
            return '-';
        }

        return trim($value . ' ' . $unit);
    };
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

    {{-- HERO --}}
    <section class="relative overflow-hidden rounded-[28px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur md:p-6">
        <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-emerald-100/80 blur-3xl"></div>
        <div class="absolute -bottom-28 -left-24 h-72 w-72 rounded-full bg-cyan-100/80 blur-3xl"></div>

        <div class="relative grid gap-5 xl:grid-cols-[1.25fr_.75fr] xl:items-center">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700">
                    <i class="ph ph-stethoscope text-base"></i>
                    Triase Meja 5
                </div>

                <h1 class="mt-4 max-w-2xl text-[28px] font-black leading-tight tracking-[-0.03em] text-slate-900 md:text-[34px]">
                    Pemeriksaan Klinis Bidan
                </h1>

                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
                    Validasi data pengukuran dari Kader, berikan diagnosa, tindakan, dan edukasi sebelum hasil diterbitkan ke rekam medis warga.
                    Halaman ini dibuat compact biar HP tidak ngos-ngosan seperti manusia buka 47 tab Chrome.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border border-amber-100 bg-amber-50/80 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-amber-600">
                                Menunggu
                            </p>
                            <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                                {{ $pendingCount }}
                            </h2>
                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                Perlu validasi
                            </p>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-amber-600 ring-1 ring-amber-100">
                            <i class="ph ph-clock-countdown text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/80 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-emerald-600">
                                Selesai
                            </p>
                            <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                                {{ $verifiedCount }}
                            </h2>
                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                Terverifikasi
                            </p>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-emerald-600 ring-1 ring-emerald-100">
                            <i class="ph ph-check-circle text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- TOOLBAR --}}
    <section class="rounded-[28px] border border-white/80 bg-white/85 p-4 shadow-sm shadow-slate-200/70 backdrop-blur">
        <div class="grid gap-3 lg:grid-cols-[auto_1fr] lg:items-center">

            {{-- TAB --}}
            <div class="grid grid-cols-2 gap-2 rounded-2xl bg-slate-50 p-1 ring-1 ring-slate-100">
                <a href="{{ route('bidan.pemeriksaan.index', ['tab' => 'pending']) }}"
                   class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-black transition-all duration-300
                   {{ $tab === 'pending' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:bg-white hover:text-slate-900' }}">
                    <i class="ph ph-hourglass-medium"></i>
                    Belum Diperiksa
                </a>

                <a href="{{ route('bidan.pemeriksaan.index', ['tab' => 'verified']) }}"
                   class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-black transition-all duration-300
                   {{ $tab === 'verified' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:bg-white hover:text-slate-900' }}">
                    <i class="ph ph-check-fat"></i>
                    Selesai
                </a>
            </div>

            {{-- SEARCH --}}
            <form method="GET" action="{{ route('bidan.pemeriksaan.index') }}" class="flex flex-col gap-2 sm:flex-row">
                <input type="hidden" name="tab" value="{{ $tab }}">

                <div class="relative flex-1">
                    <i class="ph ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Cari nama pasien atau NIK..."
                           class="min-h-[46px] w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                </div>

                <button type="submit"
                        class="inline-flex min-h-[46px] items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition-all duration-300 hover:-translate-y-0.5 hover:bg-emerald-700">
                    <i class="ph ph-funnel"></i>
                    Filter
                </button>

                @if(request('search'))
                    <a href="{{ route('bidan.pemeriksaan.index', ['tab' => $tab]) }}"
                       class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                        <i class="ph ph-arrow-counter-clockwise"></i>
                    </a>
                @endif
            </form>
        </div>
    </section>

    {{-- LIST --}}
    <section class="rounded-[28px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-600">
                    Daftar Pemeriksaan
                </p>
                <h2 class="mt-1 text-lg font-black tracking-[-0.02em] text-slate-900">
                    {{ $tab === 'verified' ? 'Arsip Pemeriksaan Tervalidasi' : 'Antrian Validasi Klinis' }}
                </h2>
            </div>

            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-500 ring-1 ring-slate-100">
                {{ $pemeriksaans->total() }} data
            </div>
        </div>

        {{-- DESKTOP TABLE --}}
        <div class="nexus-scroll hidden overflow-x-auto lg:block">
            <table class="min-w-[980px] w-full border-separate border-spacing-y-3">
                <thead>
                    <tr class="text-left text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">
                        <th class="px-4 py-2">Pasien</th>
                        <th class="px-4 py-2">Tanggal</th>
                        <th class="px-4 py-2">Kategori</th>
                        <th class="px-4 py-2 text-center">BB</th>
                        <th class="px-4 py-2 text-center">TB</th>
                        <th class="px-4 py-2 text-center">Tensi</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($pemeriksaans as $item)
                        @php
                            $pasien = optional($item->kunjungan)->pasien;
                            $kategori = $kategoriMeta($item->kategori_pasien);
                            $status = $statusMeta($item->status_verifikasi);
                        @endphp

                        <tr>
                            <td class="rounded-l-2xl border-y border-l border-slate-100 bg-slate-50/80 px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white text-emerald-700 ring-1 ring-emerald-100">
                                        <span class="text-sm font-black">
                                            {{ strtoupper(substr($pasien->nama_lengkap ?? 'P', 0, 1)) }}
                                        </span>
                                    </div>

                                    <div class="min-w-0">
                                        <p class="max-w-[240px] truncate font-black text-slate-900">
                                            {{ $pasien->nama_lengkap ?? 'Nama Tidak Terdata' }}
                                        </p>
                                        <p class="mt-1 max-w-[240px] truncate text-xs font-semibold text-slate-500">
                                            NIK {{ $pasien->nik ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                <p class="text-sm font-black text-slate-800">
                                    {{ $formatTanggal($item) }}
                                </p>
                            </td>

                            <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black ring-1 {{ $kategori['class'] }}">
                                    <i class="ph {{ $kategori['icon'] }}"></i>
                                    {{ $kategori['label'] }}
                                </span>
                            </td>

                            <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4 text-center">
                                <span class="font-black text-slate-900">
                                    {{ $metricValue($item->berat_badan, 'kg') }}
                                </span>
                            </td>

                            <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4 text-center">
                                <span class="font-black text-slate-900">
                                    {{ $metricValue($item->tinggi_badan, 'cm') }}
                                </span>
                            </td>

                            <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4 text-center">
                                <span class="font-black text-slate-900">
                                    {{ $item->tekanan_darah ?: '-' }}
                                </span>
                            </td>

                            <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black ring-1 {{ $status['class'] }}">
                                    <span class="h-2 w-2 rounded-full {{ $status['dot'] }}"></span>
                                    {{ $status['label'] }}
                                </span>
                            </td>

                            <td class="rounded-r-2xl border-y border-r border-slate-100 bg-slate-50/80 px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    @if($item->status_verifikasi === 'verified')
                                        <a href="{{ route('bidan.pemeriksaan.show', $item->id) }}"
                                           class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-black text-emerald-700 ring-1 ring-emerald-100 transition hover:bg-emerald-600 hover:text-white">
                                            Lihat
                                            <i class="ph ph-caret-right"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('bidan.pemeriksaan.validasi', $item->id) }}"
                                           class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-700">
                                            Periksa
                                            <i class="ph ph-stethoscope"></i>
                                        </a>

                                        <form action="{{ route('bidan.pemeriksaan.destroy', $item->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Hapus data antrian pemeriksaan ini?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="inline-flex items-center gap-2 rounded-xl bg-rose-50 px-4 py-2 text-sm font-black text-rose-700 ring-1 ring-rose-100 transition hover:bg-rose-600 hover:text-white">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                                    <i class="ph ph-clipboard-text text-3xl"></i>
                                </div>
                                <h3 class="mt-4 text-lg font-black text-slate-800">
                                    Data Pemeriksaan Kosong
                                </h3>
                                <p class="mt-2 text-sm text-slate-500">
                                    Tidak ada data yang cocok dengan filter saat ini.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- MOBILE CARD --}}
        <div class="space-y-3 lg:hidden">
            @forelse($pemeriksaans as $item)
                @php
                    $pasien = optional($item->kunjungan)->pasien;
                    $kategori = $kategoriMeta($item->kategori_pasien);
                    $status = $statusMeta($item->status_verifikasi);
                @endphp

                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-base font-black text-slate-900">
                                {{ $pasien->nama_lengkap ?? 'Nama Tidak Terdata' }}
                            </p>
                            <p class="mt-1 truncate text-xs font-semibold text-slate-500">
                                NIK {{ $pasien->nik ?? '-' }}
                            </p>
                        </div>

                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-3 py-1 text-[11px] font-black ring-1 {{ $kategori['class'] }}">
                            <i class="ph {{ $kategori['icon'] }}"></i>
                            {{ $kategori['label'] }}
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-2">
                        <div class="rounded-xl bg-white p-3 text-center ring-1 ring-slate-100">
                            <p class="text-[10px] font-black uppercase text-slate-400">BB</p>
                            <p class="mt-1 truncate text-sm font-black text-slate-900">
                                {{ $metricValue($item->berat_badan, 'kg') }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-white p-3 text-center ring-1 ring-slate-100">
                            <p class="text-[10px] font-black uppercase text-slate-400">TB</p>
                            <p class="mt-1 truncate text-sm font-black text-slate-900">
                                {{ $metricValue($item->tinggi_badan, 'cm') }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-white p-3 text-center ring-1 ring-slate-100">
                            <p class="text-[10px] font-black uppercase text-slate-400">Tensi</p>
                            <p class="mt-1 truncate text-sm font-black text-slate-900">
                                {{ $item->tekanan_darah ?: '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold text-slate-400">
                                {{ $formatTanggal($item) }}
                            </p>
                            <span class="mt-2 inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-black ring-1 {{ $status['class'] }}">
                                <span class="h-2 w-2 rounded-full {{ $status['dot'] }}"></span>
                                {{ $status['label'] }}
                            </span>
                        </div>

                        <div class="flex shrink-0 gap-2">
                            @if($item->status_verifikasi === 'verified')
                                <a href="{{ route('bidan.pemeriksaan.show', $item->id) }}"
                                   class="inline-flex min-h-[42px] items-center justify-center rounded-xl bg-white px-4 py-2 text-sm font-black text-emerald-700 ring-1 ring-emerald-100">
                                    Lihat
                                </a>
                            @else
                                <a href="{{ route('bidan.pemeriksaan.validasi', $item->id) }}"
                                   class="inline-flex min-h-[42px] items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-black text-white">
                                    Periksa
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                        <i class="ph ph-clipboard-text text-3xl"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-black text-slate-800">
                        Data Pemeriksaan Kosong
                    </h3>
                    <p class="mt-2 text-sm text-slate-500">
                        Tidak ada data yang cocok dengan filter saat ini.
                    </p>
                </div>
            @endforelse
        </div>

        @if($pemeriksaans->hasPages())
            <div class="mt-5 flex flex-col gap-3 border-t border-slate-100 pt-5 md:flex-row md:items-center md:justify-between">
                <p class="text-sm font-semibold text-slate-500">
                    Menampilkan {{ $pemeriksaans->firstItem() }} sampai {{ $pemeriksaans->lastItem() }} dari {{ $pemeriksaans->total() }} data
                </p>

                <div>
                    {{ $pemeriksaans->links() }}
                </div>
            </div>
        @endif
    </section>
</div>
@endsection