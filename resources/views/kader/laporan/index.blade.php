@extends('layouts.kader')

@section('title', 'Laporan Kader')
@section('page-name', 'Laporan Kader')

@php
    $reportCards = [
        [
            'type' => 'balita',
            'title' => 'Laporan Balita',
            'desc' => 'Pemeriksaan Balita, status gizi, dan imunisasi terakhir.',
            'icon' => 'fa-child-reaching',
            'tone' => 'sky',
            'count' => $stats['balita'] ?? 0,
            'check' => $stats['pemeriksaan_balita_bulan_ini'] ?? 0,
        ],
        [
            'type' => 'remaja',
            'title' => 'Laporan Remaja',
            'desc' => 'Pemeriksaan Remaja, BB, TB, IMT, TD, dan GDS bila tersedia.',
            'icon' => 'fa-user-graduate',
            'tone' => 'emerald',
            'count' => $stats['remaja'] ?? 0,
            'check' => $stats['pemeriksaan_remaja_bulan_ini'] ?? 0,
        ],
        [
            'type' => 'lansia',
            'title' => 'Laporan Lansia',
            'desc' => 'Pemeriksaan Lansia, kemandirian, tensi, gula, kolesterol, dan asam urat.',
            'icon' => 'fa-person-cane',
            'tone' => 'amber',
            'count' => $stats['lansia'] ?? 0,
            'check' => $stats['pemeriksaan_lansia_bulan_ini'] ?? 0,
        ],
    ];

    $toneClass = function ($tone) {
        return match($tone) {
            'sky' => 'bg-sky-50 text-sky-700 border-sky-100',
            'amber' => 'bg-amber-50 text-amber-700 border-amber-100',
            default => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        };
    };
@endphp

@push('styles')
<style>
    .laporan-page {
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        position: relative;
        isolation: isolate;
    }

    .laporan-page::before {
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

    .input-premium {
        border: 1px solid rgba(226,232,240,.9);
        background: rgba(255,255,255,.72);
        outline: none;
        transition: all .3s ease-in-out;
    }

    .input-premium:focus {
        border-color: rgba(16,185,129,.42);
        box-shadow: 0 0 0 4px rgba(16,185,129,.08);
        background: rgba(255,255,255,.86);
    }

    .report-card {
        border: 1px solid rgba(226,232,240,.78);
        background: rgba(255,255,255,.58);
        backdrop-filter: blur(14px);
        transition: all .3s ease-in-out;
    }

    .report-card:hover {
        transform: translateY(-2px);
        border-color: rgba(16,185,129,.24);
        box-shadow: 0 18px 34px rgba(15,23,42,.055);
    }

    .report-card.active {
        border-color: rgba(16,185,129,.42);
        background: rgba(236,253,245,.86);
        box-shadow: 0 14px 32px rgba(5,150,105,.08);
    }

    .preview-frame-shell {
        border: 1px solid rgba(226,232,240,.86);
        background: rgba(248,250,252,.72);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.72);
    }

    .pdf-frame {
        width: 100%;
        height: 820px;
        border: 0;
        background: #f8fafc;
    }

    .preview-empty {
        min-height: 300px;
        border: 1px dashed rgba(148,163,184,.55);
        background:
            radial-gradient(circle at 20% 20%, rgba(16,185,129,.08), transparent 30%),
            radial-gradient(circle at 80% 10%, rgba(245,158,11,.08), transparent 28%),
            rgba(248,250,252,.72);
    }

    .fade-in {
        animation: fadeInUp .35s ease-in-out both;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .pdf-frame {
            height: 640px;
        }
    }
</style>
@endpush

@section('content')
<div class="laporan-page space-y-5">

    {{-- HERO --}}
    <section class="hero-panel rounded-[30px] p-5 sm:p-6">
        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/80 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-emerald-700">
                    <i class="fa-solid fa-file-lines"></i>
                    Laporan Kader
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Laporan Pemeriksaan Posyandu
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Buat laporan pemeriksaan berdasarkan sasaran utama: Balita, Remaja, dan Lansia. Laporan ditampilkan dalam format PDF agar siap dicek, dicetak, atau diunduh.
                </p>
            </div>

            <div class="rounded-[24px] border border-white/70 bg-white/50 p-4 backdrop-blur-md">
                <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Tanggal Hari Ini</p>
                <p class="mt-1 text-sm font-black text-slate-900">
                    {{ now('Asia/Jakarta')->translatedFormat('d F Y') }}
                </p>
            </div>
        </div>
    </section>

    {{-- STATS --}}
    <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
        @foreach($reportCards as $card)
            <div class="glass-panel rounded-[24px] p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">
                            {{ str_replace('Laporan ', '', $card['title']) }}
                        </p>

                        <h2 class="mt-2 text-3xl font-black text-slate-900">
                            {{ $card['count'] }}
                        </h2>

                        <p class="mt-1 text-xs font-bold text-slate-400">
                            {{ $card['check'] }} pemeriksaan bulan ini
                        </p>
                    </div>

                    <div class="grid h-12 w-12 place-items-center rounded-2xl border {{ $toneClass($card['tone']) }}">
                        <i class="fa-solid {{ $card['icon'] }}"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </section>

    {{-- FORM --}}
    <form method="GET" action="{{ route('kader.laporan.generate') }}" id="laporanForm" class="space-y-5">
        <section class="glass-panel rounded-[30px] p-4 sm:p-5">
            <div class="mb-5">
                <h2 class="text-lg font-black text-slate-900">Pilih Jenis Laporan</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Laporan dibagi menjadi 3 sasaran utama agar formatnya jelas dan tidak berlebihan.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">
                @foreach($reportCards as $card)
                    <label class="report-card cursor-pointer rounded-[24px] p-4" data-report-card="{{ $card['type'] }}">
                        <input
                            type="radio"
                            name="jenis_laporan"
                            value="{{ $card['type'] }}"
                            class="sr-only report-radio"
                            {{ $loop->first ? 'checked' : '' }}
                        >

                        <div class="flex items-start gap-3">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl border {{ $toneClass($card['tone']) }}">
                                <i class="fa-solid {{ $card['icon'] }}"></i>
                            </div>

                            <div class="min-w-0">
                                <h3 class="text-sm font-black text-slate-900">
                                    {{ $card['title'] }}
                                </h3>

                                <p class="mt-1 text-xs font-bold leading-5 text-slate-400">
                                    {{ $card['desc'] }}
                                </p>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="rounded-full border border-slate-100 bg-slate-50 px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] text-slate-500">
                                        {{ $card['count'] }} Sasaran
                                    </span>

                                    <span class="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] text-emerald-700">
                                        {{ $card['check'] }} Pemeriksaan
                                    </span>
                                </div>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
        </section>

        <section class="glass-panel rounded-[30px] p-4 sm:p-5">
            <div class="mb-5">
                <h2 class="text-lg font-black text-slate-900">Filter Periode Pemeriksaan</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Periode digunakan untuk menampilkan data pemeriksaan yang sudah berjalan pada rentang tanggal tertentu.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_1fr_auto]">
                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                        Tanggal Awal
                    </label>
                    <input
                        type="date"
                        name="tanggal_awal"
                        id="tanggal_awal"
                        value="{{ now('Asia/Jakarta')->startOfMonth()->toDateString() }}"
                        class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">
                        Tanggal Akhir
                    </label>
                    <input
                        type="date"
                        name="tanggal_akhir"
                        id="tanggal_akhir"
                        value="{{ now('Asia/Jakarta')->toDateString() }}"
                        class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700"
                    >
                </div>

                <div class="flex items-end">
                    <button type="submit"
                            id="previewSubmitBtn"
                            class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 text-sm font-black text-white shadow-[0_14px_28px_rgba(5,150,105,.18)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700 lg:w-auto">
                        <i class="fa-solid fa-eye"></i>
                        Preview Laporan
                    </button>
                </div>
            </div>
        </section>

        <section class="rounded-[26px] border border-amber-100 bg-amber-50/70 p-4">
            <div class="flex items-start gap-3">
                <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-white/70 text-amber-700">
                    <i class="fa-solid fa-circle-info"></i>
                </div>

                <div>
                    <h3 class="text-sm font-black text-amber-800">Catatan Format</h3>
                    <p class="mt-1 text-xs font-bold leading-5 text-amber-700">
                        Laporan ditampilkan sebagai preview PDF terlebih dahulu agar isi dan periode dapat dicek sebelum diunduh. Absensi tidak dibuat sebagai laporan terpisah. Imunisasi masuk ke laporan Balita.
                    </p>
                </div>
            </div>
        </section>
    </form>

    {{-- PREVIEW PDF --}}
    <section id="previewSection" class="glass-panel hidden rounded-[30px] p-4 sm:p-5">
        <div class="mb-4 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <div class="mb-2 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/80 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-emerald-700">
                    <i class="fa-solid fa-file-pdf"></i>
                    Preview PDF
                </div>

                <h2 class="text-lg font-black text-slate-900">
                    Preview Laporan
                </h2>

                <p id="previewInfoText" class="mt-1 text-xs font-bold text-slate-400">
                    Periksa isi laporan terlebih dahulu sebelum mengunduh.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:flex xl:items-center">
                <a href="#"
                   id="openPdfBtn"
                   target="_blank"
                   rel="noopener"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl border border-sky-100 bg-sky-50/80 px-5 py-3 text-sm font-black text-sky-700 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-sky-100/80">
                    <i class="fa-solid fa-up-right-from-square"></i>
                    Buka Tab Baru
                </a>

                <a href="#"
                   id="downloadPdfBtn"
                   target="_blank"
                   rel="noopener"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-[0_14px_28px_rgba(5,150,105,.18)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                    <i class="fa-solid fa-download"></i>
                    Unduh PDF
                </a>
            </div>
        </div>

        <div id="previewLoading" class="preview-empty grid place-items-center rounded-[24px] p-8 text-center">
            <div>
                <div class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-3xl bg-emerald-50 text-emerald-700">
                    <i class="fa-solid fa-file-pdf text-xl"></i>
                </div>

                <h3 class="text-base font-black text-slate-900">
                    Preview laporan belum dibuat
                </h3>

                <p class="mx-auto mt-2 max-w-md text-sm font-bold leading-6 text-slate-400">
                    Pilih jenis laporan dan periode, lalu klik Preview Laporan. PDF akan muncul di area ini.
                </p>
            </div>
        </div>

        <div id="previewFrameWrap" class="preview-frame-shell hidden overflow-hidden rounded-[24px]">
            <iframe
                id="pdfPreviewFrame"
                src=""
                class="pdf-frame"
                title="Preview PDF Laporan">
            </iframe>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var cards = document.querySelectorAll('[data-report-card]');
    var radios = document.querySelectorAll('.report-radio');

    var form = document.getElementById('laporanForm');
    var previewSection = document.getElementById('previewSection');
    var iframe = document.getElementById('pdfPreviewFrame');
    var downloadBtn = document.getElementById('downloadPdfBtn');
    var openPdfBtn = document.getElementById('openPdfBtn');
    var previewLoading = document.getElementById('previewLoading');
    var previewFrameWrap = document.getElementById('previewFrameWrap');
    var previewInfoText = document.getElementById('previewInfoText');
    var submitBtn = document.getElementById('previewSubmitBtn');

    function getSelectedReportLabel() {
        var selected = document.querySelector('.report-radio:checked');

        if (!selected) {
            return 'Laporan';
        }

        var selectedCard = document.querySelector('[data-report-card="' + selected.value + '"]');
        var title = selectedCard ? selectedCard.querySelector('h3') : null;

        return title ? title.textContent.trim() : 'Laporan';
    }

    function updateCards() {
        var selected = document.querySelector('.report-radio:checked');
        var selectedValue = selected ? selected.value : null;

        cards.forEach(function (card) {
            card.classList.toggle('active', card.dataset.reportCard === selectedValue);
        });
    }

    function buildUrl(mode) {
        var formData = new FormData(form);
        var params = new URLSearchParams(formData);

        params.set('mode', mode);

        return form.action + '?' + params.toString();
    }

    radios.forEach(function (radio) {
        radio.addEventListener('change', updateCards);
    });

    if (form && previewSection && iframe && downloadBtn && openPdfBtn) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            var tanggalAwal = document.getElementById('tanggal_awal');
            var tanggalAkhir = document.getElementById('tanggal_akhir');

            if (tanggalAwal && tanggalAkhir && tanggalAwal.value && tanggalAkhir.value && tanggalAkhir.value < tanggalAwal.value) {
                alert('Tanggal akhir tidak boleh lebih awal dari tanggal awal.');
                return;
            }

            var previewUrl = buildUrl('preview');
            var downloadUrl = buildUrl('download');
            var reportLabel = getSelectedReportLabel();

            iframe.src = previewUrl + '#toolbar=0&navpanes=0&scrollbar=0&view=FitH';
            downloadBtn.href = downloadUrl;
            openPdfBtn.href = previewUrl;

            previewInfoText.textContent = reportLabel + ' sedang ditampilkan dalam format PDF. Periksa isi laporan sebelum mengunduh.';

            previewSection.classList.remove('hidden');
            previewSection.classList.add('fade-in');

            previewLoading.classList.add('hidden');
            previewFrameWrap.classList.remove('hidden');

            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Membuat Preview...';

            setTimeout(function () {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                submitBtn.innerHTML = '<i class="fa-solid fa-eye"></i> Preview Laporan';
            }, 1200);

            setTimeout(function () {
                previewSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 150);
        });
    }

    updateCards();
});
</script>
@endpush