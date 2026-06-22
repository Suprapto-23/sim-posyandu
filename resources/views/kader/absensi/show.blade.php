@extends('layouts.kader')

@section('title', 'Detail Absensi')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $absensi = $absensi ?? null;
    $details = $details ?? collect();
    $semuaSesi = $semuaSesi ?? collect();

    $totalPasien = (int) ($totalPasien ?? $details->count());
    $totalHadir = (int) ($totalHadir ?? $details->where('hadir', true)->count());
    $totalAbsen = (int) ($totalAbsen ?? max(0, $totalPasien - $totalHadir));

    $persenHadir = $totalPasien > 0 ? round(($totalHadir / $totalPasien) * 100) : 0;
    $persenTidak = $totalPasien > 0 ? round(($totalAbsen / $totalPasien) * 100) : 0;

    $routeHas = fn ($name) => Route::has($name);

    $kategori = $absensi?->kategori ?? 'balita';

    $kategoriMenus = [
        'balita' => [
            'label' => 'Balita',
            'icon' => 'fa-child-reaching',
            'tone' => 'emerald',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'icon' => 'fa-user-graduate',
            'tone' => 'amber',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'icon' => 'fa-person-cane',
            'tone' => 'blue',
        ],
    ];

    if (!array_key_exists($kategori, $kategoriMenus)) {
        $kategori = 'balita';
    }

    $currentKategori = $kategoriMenus[$kategori];
    $kategoriLabel = $currentKategori['label'];
    $kategoriIcon = $currentKategori['icon'];
    $kategoriTone = $currentKategori['tone'];

    $formatTanggal = function ($date, $format = 'd F Y') {
        if (!$date) return '-';
        try {
            return Carbon::parse($date)->translatedFormat($format);
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $toneClass = function ($tone, $type = 'light') {
        return match ($tone) {
            'amber' => match ($type) {
                'solid' => 'bg-amber-500 text-white',
                'light' => 'bg-amber-50 text-amber-600 border-amber-200',
                default => 'bg-amber-50 text-amber-600',
            },
            'blue' => match ($type) {
                'solid' => 'bg-blue-500 text-white',
                'light' => 'bg-blue-50 text-blue-600 border-blue-200',
                default => 'bg-blue-50 text-blue-600',
            },
            'slate' => match ($type) {
                'solid' => 'bg-slate-700 text-white',
                'light' => 'bg-slate-100 text-slate-600 border-slate-200',
                default => 'bg-slate-50 text-slate-600',
            },
            default => match ($type) {
                'solid' => 'bg-emerald-500 text-white',
                'light' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                default => 'bg-emerald-50 text-emerald-600',
            },
        };
    };

    $statusRekap = 'Belum Ada Kehadiran';
    $statusTone = 'slate';

    if ($totalPasien <= 0) {
        $statusRekap = 'Belum Ada Peserta';
        $statusTone = 'slate';
    } elseif ($totalHadir === $totalPasien) {
        $statusRekap = 'Semua Hadir';
        $statusTone = 'emerald';
    } elseif ($totalHadir > 0) {
        $statusRekap = 'Sebagian Hadir';
        $statusTone = 'teal';
    } else {
        $statusRekap = 'Semua Absen';
        $statusTone = 'amber';
    }

    $kodeAbsensi = $absensi?->kode_absensi ?? 'ABS-' . str_pad($absensi?->id ?? 0, 4, '0', STR_PAD_LEFT);
@endphp

@push('styles')
<style>
    body { 
        background-color: #f1f5f9; 
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(150, 100%, 94%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }
    
    .widget-card {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: 2rem; 
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }
    .btn-pill:active {
        transform: scale(0.95);
    }

    .pc-row {
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.8);
        background: rgba(255, 255, 255, 0.6);
        border-radius: 1.5rem;
    }
    .pc-row:hover {
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.04);
        transform: translateY(-2px);
    }

    .slim-scroll::-webkit-scrollbar { width: 6px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: rgba(20, 184, 166, 0.3); border-radius: 9999px; }
    .slim-scroll::-webkit-scrollbar-thumb:hover { background: rgba(20, 184, 166, 0.6); }
    
    /* Fade efek opsional saat scroll bawah */
    .scroll-fade-bottom {
        mask-image: linear-gradient(to top, transparent, black 5%);
        -webkit-mask-image: linear-gradient(to top, transparent, black 5%);
    }
</style>
@endpush

@section('content')
<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6 relative">

    {{-- 1. HEADER INFO SESI --}}
    <div class="widget-card p-6 lg:p-8 flex flex-col xl:flex-row justify-between items-center gap-8 bg-gradient-to-r from-white/90 to-white/50 relative overflow-hidden">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-teal-400/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full xl:w-1/2 flex flex-col gap-4 text-center xl:text-left z-10">
            <div class="inline-flex justify-center xl:justify-start items-center gap-2 mb-1">
                <span class="btn-pill bg-teal-50 border border-teal-100 text-teal-600 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest shadow-inner flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500 relative"></span>
                    Session Detail
                </span>
            </div>
            
            <h1 class="text-3xl sm:text-4xl font-black text-slate-800 tracking-tight">
                Rincian Absensi <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-500 to-emerald-500">{{ $kategoriLabel }}</span>.
            </h1>

            <p class="text-sm font-medium text-slate-500 max-w-xl mx-auto xl:mx-0">
                Menampilkan rincian daftar kehadiran untuk sesi pertemuan Posyandu terpilih. Periksa kembali validitas data sasaran.
            </p>

            <div class="flex flex-wrap justify-center xl:justify-start gap-3 mt-2">
                @if($routeHas('kader.absensi.riwayat'))
                    <a href="{{ route('kader.absensi.riwayat', ['kategori' => $kategori, 'bulan' => $absensi?->bulan ?? now('Asia/Jakarta')->month, 'tahun' => $absensi?->tahun ?? now('Asia/Jakarta')->year]) }}" 
                       class="btn-pill bg-white text-slate-600 border border-slate-200 hover:text-teal-600 hover:bg-slate-50 px-6 py-3 text-sm font-bold shadow-sm flex items-center gap-2 transition-all">
                        <i class="fa-solid fa-arrow-left"></i> Kembali ke Riwayat
                    </a>
                @endif
                
                @if($routeHas('kader.absensi.index'))
                    <a href="{{ route('kader.absensi.index', ['kategori' => $kategori, 'tanggal' => optional($absensi?->tanggal_posyandu)->format('Y-m-d')]) }}" 
                       class="btn-pill bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-white px-6 py-3 text-sm font-bold shadow-[0_8px_20px_rgba(20,184,166,0.3)] flex items-center gap-2 transition-all">
                        <i class="fa-solid fa-pen-to-square"></i> Update Presensi
                    </a>
                @endif
            </div>
        </div>

        {{-- Widget Info Sesi Kanan --}}
        <div class="w-full xl:w-auto grid grid-cols-2 gap-3 z-10 shrink-0">
            <div class="bg-white/80 border border-slate-100 rounded-3xl p-5 shadow-sm min-w-[160px]">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kode Sesi</p>
                <p class="text-sm font-black text-slate-800">{{ $kodeAbsensi }}</p>
            </div>
            <div class="bg-white/80 border border-slate-100 rounded-3xl p-5 shadow-sm min-w-[160px]">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal</p>
                <p class="text-sm font-black text-slate-800">{{ $formatTanggal($absensi?->tanggal_posyandu ?? null, 'd M Y') }}</p>
            </div>
            <div class="bg-white/80 border border-slate-100 rounded-3xl p-5 shadow-sm min-w-[160px]">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pertemuan</p>
                <p class="text-sm font-black text-slate-800">Ke-{{ $absensi?->nomor_pertemuan ?? '-' }}</p>
            </div>
            <div class="bg-white/80 border border-slate-100 rounded-3xl p-5 shadow-sm min-w-[160px]">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Petugas Kader</p>
                <p class="text-sm font-black text-slate-800 line-clamp-1">{{ $absensi?->kader?->name ?? 'Sistem' }}</p>
            </div>
        </div>
    </div>

    {{-- 2. METRIK RINGKASAN --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Sasaran</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ number_format($totalPasien) }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl rotate-3 group-hover:rotate-6 transition-transform">
                <i class="fa-solid fa-users-viewfinder"></i>
            </div>
        </div>
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-500">Hadir</p>
                <div class="flex items-end gap-2 mt-1">
                    <p class="text-3xl font-black text-slate-800 leading-none">{{ number_format($totalHadir) }}</p>
                    <p class="text-sm font-bold text-emerald-500 mb-0.5">({{ $persenHadir }}%)</p>
                </div>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-500 -rotate-3 group-hover:-rotate-6 transition-transform shadow-[0_0_15px_rgba(16,185,129,0.2)]">
                <i class="fa-solid fa-user-check"></i>
            </div>
        </div>
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-500">Tidak Hadir</p>
                <div class="flex items-end gap-2 mt-1">
                    <p class="text-3xl font-black text-slate-800 leading-none">{{ number_format($totalAbsen) }}</p>
                    <p class="text-sm font-bold text-amber-500 mb-0.5">({{ $persenTidak }}%)</p>
                </div>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-500 rotate-3 group-hover:rotate-6 transition-transform shadow-[0_0_15px_rgba(245,158,11,0.2)]">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
        </div>
        <div class="widget-card p-6 flex justify-between items-center group hover:-translate-y-1">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-{{ $statusTone }}-500">Status Capaian</p>
                <p class="mt-1 text-xl font-black text-slate-800">{{ $statusRekap }}</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-{{ $statusTone }}-50 border border-{{ $statusTone }}-100 flex items-center justify-center text-{{ $statusTone }}-500 -rotate-3 group-hover:-rotate-6 transition-transform shadow-[0_0_15px_rgba(20,184,166,0.2)]">
                <i class="fa-solid fa-clipboard-check"></i>
            </div>
        </div>
    </section>

    {{-- 3. MAIN LIST AREA (Dengan Scrollbar) --}}
    <section class="widget-card p-6 flex flex-col">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-6">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Daftar Rincian</p>
                <h2 class="text-lg font-black text-slate-800 mt-1">Sasaran {{ $kategoriLabel }}</h2>
            </div>

            <div class="w-full sm:w-72 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="detailSearch" placeholder="Cari nama, NIK..." class="w-full btn-pill border border-slate-200 bg-white/50 py-3 pl-11 pr-4 text-sm font-semibold text-slate-800 outline-none transition focus:bg-white focus:border-teal-400 focus:ring-4 focus:ring-teal-500/10 shadow-inner">
            </div>
        </div>

        @if($details->isNotEmpty())
            {{-- Wrapper pembatas tinggi agar bisa di-scroll --}}
            <div class="max-h-[600px] overflow-y-auto slim-scroll pr-2 pb-2">
                <div class="grid xl:grid-cols-2 gap-4">
                    @foreach($details as $index => $detail)
                        @php
                            $hadir = (bool) $detail->hadir;
                            $statusText = $hadir ? 'Hadir' : 'Tidak Hadir';
                            $statusColor = $hadir ? 'emerald' : 'amber';
                            $statusIcon = $hadir ? 'fa-check' : 'fa-xmark';

                            $nama = $detail->nama_pasien ?? $detail->pasien?->nama_lengkap ?? $detail->pasien?->nama ?? 'Data sasaran';
                            $nik = $detail->nik_pasien ?? $detail->pasien?->nik ?? '-';
                            $usia = $detail->usia_pasien ?? '-';
                            $infoTambahan = $detail->info_tambahan_pasien ?? '-';
                            $keterangan = $detail->keterangan_text ?? ($detail->keterangan ?: '');
                        @endphp

                        <div class="pc-row p-4" data-search="{{ strtolower($nama . ' ' . $nik . ' ' . $statusText) }}">
                            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                                
                                {{-- Info Kiri --}}
                                <div class="flex items-center gap-4 min-w-0 flex-1">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white border border-slate-100 text-xs font-black text-slate-400 shadow-sm">
                                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="line-clamp-1 text-[15px] font-black text-slate-800 mb-1">{{ $nama }}</h3>
                                        <div class="flex flex-wrap gap-x-3 gap-y-1 text-[11px] font-semibold text-slate-500">
                                            <span><i class="fa-solid fa-id-card mr-1 opacity-60 text-slate-400"></i> {{ $nik }}</span>
                                            <span><i class="fa-solid fa-cake-candles mr-1 opacity-60 text-slate-400"></i> {{ $usia }}</span>
                                        </div>
                                        @if($keterangan)
                                            <div class="mt-2 inline-flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl text-xs font-medium text-slate-600">
                                                <i class="fa-solid fa-note-sticky text-slate-400"></i> <span class="line-clamp-1">{{ $keterangan }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Status Kanan --}}
                                <div class="shrink-0 flex sm:justify-end border-t sm:border-t-0 border-slate-100 pt-3 sm:pt-0">
                                    <span class="btn-pill px-4 py-2 bg-{{ $statusColor }}-50 border border-{{ $statusColor }}-200 text-{{ $statusColor }}-600 text-xs font-bold shadow-sm">
                                        <i class="fa-solid {{ $statusIcon }} mr-1.5 opacity-70"></i> {{ $statusText }}
                                    </span>
                                </div>
                                
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            {{-- Info Footer --}}
            <div class="mt-4 pt-4 border-t border-slate-100 text-center">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Menampilkan {{ number_format($details->count()) }} data sasaran tersimpan</p>
            </div>
        @else
            <div class="flex flex-col items-center justify-center btn-pill border-2 border-dashed border-slate-200 bg-white/50 p-12 text-center rounded-[2.5rem]">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-5 shadow-inner">
                    <i class="fa-solid fa-user-slash text-3xl"></i>
                </div>
                <h3 class="font-black text-slate-800 text-xl">Detail Kosong</h3>
                <p class="text-sm font-medium text-slate-500 mt-2 max-w-sm">
                    Sesi ini belum memiliki detail peserta tersimpan. Anda bisa mengklik tombol Update Presensi di atas.
                </p>
            </div>
        @endif
    </section>

    {{-- 4. BOTTOM GRID: SESI TERDEKAT --}}
    @if($semuaSesi->isNotEmpty())
        <div class="pt-4">
            <div class="flex items-center gap-3 mb-5 px-2">
                <i class="fa-solid fa-clock-rotate-left text-teal-500 text-xl"></i>
                <h3 class="text-lg font-black text-slate-800">Sesi Terdekat Lainnya ({{ $kategoriLabel }})</h3>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($semuaSesi->take(4) as $sesi)
                    <a href="{{ $routeHas('kader.absensi.show') ? route('kader.absensi.show', $sesi->id) : '#' }}" 
                       class="widget-card !rounded-3xl p-5 flex flex-col gap-3 group hover:border-teal-300 hover:-translate-y-1">
                        <div class="flex justify-between items-start">
                            <span class="btn-pill bg-teal-50 border border-teal-100 text-teal-600 px-2.5 py-1 text-[9px] font-black uppercase tracking-wider">
                                Pertemuan-{{ $sesi->nomor_pertemuan ?? '-' }}
                            </span>
                            <div class="w-8 h-8 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center group-hover:bg-teal-500 group-hover:text-white transition-colors">
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-800">{{ $sesi->kode_absensi ?? 'ABS-' . str_pad($sesi->id, 4, '0', STR_PAD_LEFT) }}</p>
                            <p class="text-xs font-semibold text-slate-500 mt-1"><i class="fa-solid fa-calendar-day mr-1"></i> {{ $formatTanggal($sesi->tanggal_posyandu ?? null, 'd M Y') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
(function () {
    const searchInput = document.querySelector('#detailSearch');
    const rows = Array.from(document.querySelectorAll('.pc-row[data-search]'));
    let timer = null;

    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        clearTimeout(timer);

        timer = setTimeout(() => {
            const keyword = this.value.trim().toLowerCase();

            rows.forEach((row) => {
                const text = row.dataset.search || '';
                if (keyword === '' || text.includes(keyword)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }, 150);
    });
})();
</script>
@endpush