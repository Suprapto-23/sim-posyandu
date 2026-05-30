@extends('layouts.bidan')

@section('title', 'Laporan Masuk')
@section('page-name', 'Laporan Masuk')
@section('page-title', 'Laporan Masuk')

@php
    use Illuminate\Support\Facades\Route;

    $jenisOptions = [
        'semua' => 'Semua Sasaran',
        'balita' => 'Balita',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
    ];

    $statusOptions = [
        'semua' => 'Semua Status',
        'menunggu_review' => 'Menunggu Review',
        'disetujui' => 'Disetujui',
        'perlu_revisi' => 'Perlu Revisi',
    ];

    $jenisLabel = fn ($value) => $jenisOptions[$value] ?? ucfirst($value ?? '-');

    $statusMeta = function ($item) {
        if (($item->menunggu_review ?? 0) > 0) {
            return [
                'label' => 'Menunggu Review',
                'class' => 'bg-amber-50 text-amber-700 ring-amber-200',
                'dot' => 'bg-amber-500',
            ];
        }

        if (($item->perlu_revisi ?? 0) > 0) {
            return [
                'label' => 'Perlu Revisi',
                'class' => 'bg-rose-50 text-rose-700 ring-rose-200',
                'dot' => 'bg-rose-500',
            ];
        }

        return [
            'label' => 'Disetujui',
            'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'dot' => 'bg-emerald-500',
        ];
    };

    $summaryCards = [
        [
            'label' => 'Total Data',
            'value' => $ringkasan['total'] ?? 0,
            'icon' => 'ph-files',
            'class' => 'bg-slate-50 text-slate-700 ring-slate-100',
        ],
        [
            'label' => 'Menunggu Review',
            'value' => $ringkasan['menunggu_review'] ?? 0,
            'icon' => 'ph-clock-countdown',
            'class' => 'bg-amber-50 text-amber-700 ring-amber-100',
        ],
        [
            'label' => 'Disetujui',
            'value' => $ringkasan['disetujui'] ?? 0,
            'icon' => 'ph-check-circle',
            'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        ],
        [
            'label' => 'Perlu Revisi',
            'value' => $ringkasan['perlu_revisi'] ?? 0,
            'icon' => 'ph-warning-circle',
            'class' => 'bg-rose-50 text-rose-700 ring-rose-100',
        ],
    ];
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
        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-emerald-100/80 blur-3xl"></div>
        <div class="absolute -bottom-24 -left-20 h-64 w-64 rounded-full bg-cyan-100/80 blur-3xl"></div>

        <div class="relative grid gap-5 xl:grid-cols-[1.2fr_.8fr] xl:items-center">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-4 py-2 text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700">
                    <i class="ph ph-file-text text-base"></i>
                    Laporan Masuk Bidan
                </div>

                <h1 class="mt-4 max-w-2xl text-[28px] font-black leading-tight tracking-[-0.03em] text-slate-900 md:text-[34px]">
                    Kotak Masuk Validasi Laporan Posyandu
                </h1>

                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
                    Halaman ini menampilkan rekap data pemeriksaan dari Kader berdasarkan periode dan kategori sasaran.
                    Dibuat ringkas, presisi, dan tidak berat di HP, karena loading lama itu bukan fitur, itu penderitaan.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">Balita</p>
                    <h3 class="mt-2 text-3xl font-black tracking-tight text-slate-900">{{ $ringkasan['balita'] ?? 0 }}</h3>
                    <p class="mt-1 text-xs font-medium text-slate-500">Data pemeriksaan</p>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">Remaja</p>
                    <h3 class="mt-2 text-3xl font-black tracking-tight text-slate-900">{{ $ringkasan['remaja'] ?? 0 }}</h3>
                    <p class="mt-1 text-xs font-medium text-slate-500">Data pemeriksaan</p>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4 sm:col-span-2">
                    <p class="text-[11px] font-black uppercase tracking-[0.15em] text-slate-400">Lansia</p>
                    <h3 class="mt-2 text-3xl font-black tracking-tight text-slate-900">{{ $ringkasan['lansia'] ?? 0 }}</h3>
                    <p class="mt-1 text-xs font-medium text-slate-500">Termasuk data tensi, gula darah, kolesterol, asam urat, dan indikator lansia lainnya.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- SUMMARY --}}
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach($summaryCards as $card)
            <div class="rounded-[24px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold text-slate-500">{{ $card['label'] }}</p>
                        <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900">{{ $card['value'] }}</h2>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl ring-1 {{ $card['class'] }}">
                        <i class="ph {{ $card['icon'] }} text-xl"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </section>

    {{-- FILTER --}}
    <section class="rounded-[28px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
        <form method="GET" action="{{ route('bidan.laporan.index') }}" class="grid gap-3 md:grid-cols-5 md:items-end">
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[0.14em] text-slate-400">Bulan</label>
                <select name="bulan" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    <option value="semua" @selected($bulan === 'semua')>Semua Bulan</option>
                    @foreach($bulanOptions as $key => $label)
                        <option value="{{ $key }}" @selected((string) $bulan === (string) $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[0.14em] text-slate-400">Tahun</label>
                <select name="tahun" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    @foreach($tahunOptions as $option)
                        <option value="{{ $option }}" @selected((int) $tahun === (int) $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[0.14em] text-slate-400">Kategori</label>
                <select name="jenis" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    @foreach($jenisOptions as $key => $label)
                        <option value="{{ $key }}" @selected($jenis === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[0.14em] text-slate-400">Status</label>
                <select name="status" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100">
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="inline-flex min-h-[46px] flex-1 items-center justify-center gap-2 rounded-2xl bg-slate-900 px-4 py-3 text-sm font-black text-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-emerald-700">
                    <i class="ph ph-funnel"></i>
                    Filter
                </button>

                <a href="{{ route('bidan.laporan.index') }}" class="inline-flex min-h-[46px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                    <i class="ph ph-arrow-counter-clockwise"></i>
                </a>
            </div>
        </form>
    </section>

    {{-- LIST --}}
    <section class="rounded-[28px] border border-white/80 bg-white/85 p-5 shadow-sm shadow-slate-200/70 backdrop-blur">
        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-600">Daftar Laporan</p>
                <h2 class="mt-1 text-lg font-black tracking-[-0.02em] text-slate-900">Laporan Masuk Per Periode</h2>
            </div>

            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-500">
                {{ $laporanMasuk->total() }} kelompok laporan
            </div>
        </div>

        <div class="nexus-scroll overflow-x-auto">
            <table class="min-w-[900px] w-full border-separate border-spacing-y-3">
                <thead>
                    <tr class="text-left text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">
                        <th class="px-4 py-2">Periode</th>
                        <th class="px-4 py-2">Kategori</th>
                        <th class="px-4 py-2 text-center">Total</th>
                        <th class="px-4 py-2 text-center">Menunggu</th>
                        <th class="px-4 py-2 text-center">Disetujui</th>
                        <th class="px-4 py-2 text-center">Revisi</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($laporanMasuk as $item)
                        @php
                            $meta = $statusMeta($item);
                            $namaBulan = $bulanOptions[(int) $item->bulan] ?? '-';
                        @endphp

                        <tr>
                            <td class="rounded-l-2xl border-y border-l border-slate-100 bg-slate-50/80 px-4 py-4">
                                <p class="font-black text-slate-900">{{ $namaBulan }} {{ $item->tahun }}</p>
                                <p class="mt-1 text-xs font-medium text-slate-500">
                                    Update terakhir:
                                    {{ $item->terakhir_update ? \Carbon\Carbon::parse($item->terakhir_update)->format('d/m/Y H:i') : '-' }}
                                </p>
                            </td>

                            <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-black text-slate-700 ring-1 ring-slate-200">
                                    {{ $jenisLabel($item->jenis) }}
                                </span>
                            </td>

                            <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4 text-center">
                                <span class="text-base font-black text-slate-900">{{ $item->total_data }}</span>
                            </td>

                            <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4 text-center">
                                <span class="text-base font-black text-amber-600">{{ $item->menunggu_review }}</span>
                            </td>

                            <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4 text-center">
                                <span class="text-base font-black text-emerald-600">{{ $item->disetujui }}</span>
                            </td>

                            <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4 text-center">
                                <span class="text-base font-black text-rose-600">{{ $item->perlu_revisi }}</span>
                            </td>

                            <td class="border-y border-slate-100 bg-slate-50/80 px-4 py-4">
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black ring-1 {{ $meta['class'] }}">
                                    <span class="h-2 w-2 rounded-full {{ $meta['dot'] }}"></span>
                                    {{ $meta['label'] }}
                                </span>
                            </td>

                            <td class="rounded-r-2xl border-y border-r border-slate-100 bg-slate-50/80 px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    @if(Route::has('bidan.laporan.show'))
                                        <a href="{{ route('bidan.laporan.show', [
                                            'tahun' => $item->tahun,
                                            'bulan' => $item->bulan,
                                            'jenis' => $item->jenis,
                                        ]) }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-black text-emerald-700 ring-1 ring-emerald-100 transition hover:bg-emerald-600 hover:text-white">
                                            Detail
                                            <i class="ph ph-caret-right"></i>
                                        </a>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-black text-slate-400 ring-1 ring-slate-100">
                                            Detail
                                        </span>
                                    @endif

                                    @if(($item->disetujui ?? 0) > 0)
                                        <a href="{{ route('bidan.laporan.cetak', [
                                            'bulan' => $item->bulan,
                                            'tahun' => $item->tahun,
                                            'jenis' => $item->jenis,
                                        ]) }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-700">
                                            PDF
                                            <i class="ph ph-download-simple"></i>
                                        </a>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-black text-slate-400">
                                            PDF
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-slate-400 shadow-sm">
                                    <i class="ph ph-folder-simple-dashed text-3xl"></i>
                                </div>
                                <h3 class="mt-4 text-lg font-black text-slate-800">Belum Ada Laporan Masuk</h3>
                                <p class="mt-2 text-sm text-slate-500">
                                    Data akan muncul setelah ada pemeriksaan dari Kader pada periode yang dipilih.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($laporanMasuk->hasPages())
            <div class="mt-5">
                {{ $laporanMasuk->links() }}
            </div>
        @endif
    </section>
</div>
@endsection