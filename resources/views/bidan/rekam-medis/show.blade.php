@extends('layouts.bidan')

@section('title', 'Detail Rekam Medis')
@section('page-name', 'Detail Rekam Medis')
@section('page-title', 'Detail Rekam Medis')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    $pasienType = $pasienType ?? $pasien_type ?? request('pasien_type', 'balita');
    $pasien_type = $pasien_type ?? $pasienType;

    $riwayatMedis = collect($riwayatMedis ?? []);
    $riwayatImunisasi = collect($riwayatImunisasi ?? []);
    $summary = $summary ?? [];

    $typeOptions = [
        'balita' => ['label' => 'Balita', 'icon' => 'fa-baby'],
        'remaja' => ['label' => 'Remaja', 'icon' => 'fa-user-graduate'],
        'lansia' => ['label' => 'Lansia', 'icon' => 'fa-person-cane'],
    ];

    $currentTypeMeta = $typeOptions[$pasienType] ?? $typeOptions['balita'];
    $pasienTypeLabel = $currentTypeMeta['label'];
    $pasienIcon = $currentTypeMeta['icon'];

    $getValue = function ($item, array $keys, mixed $default = '-') {
        foreach ($keys as $key) {
            $value = data_get($item, $key);
            if ($value !== null && $value !== '') return $value;
        }
        return $default;
    };

    $formatDate = fn($date) => $date ? Carbon::parse($date)->translatedFormat('d M Y') : '-';
    $formatDateTime = fn($date) => $date ? Carbon::parse($date)->translatedFormat('d M Y, H:i') . ' WIB' : '-';
    $displayValue = fn($value, $suffix = '') => ($value === null || $value === '' || $value === '-') ? '-' : trim((string) $value . ' ' . $suffix);

    $getNama = fn($item) => $getValue($item, ['nama_lengkap', 'nama', 'nama_balita', 'nama_remaja', 'nama_lansia'], 'Nama tidak tersedia');
    $getNik = fn($item) => $getValue($item, ['nik', 'nik_anak', 'nik_remaja', 'nik_lansia'], '-');
    $getAlamat = fn($item) => $getValue($item, ['alamat', 'alamat_lengkap', 'dusun'], '-');
    $getKontak = fn($item) => $getValue($item, ['no_hp', 'nomor_hp', 'telepon', 'no_telepon'], '-');
    
    $getWali = function ($item) use ($getValue, $pasienType) {
        if ($pasienType === 'balita') return $getValue($item, ['nama_ibu', 'nama_ayah', 'nama_wali'], '-');
        if ($pasienType === 'remaja') return $getValue($item, ['nama_orang_tua', 'nama_wali', 'sekolah'], '-');
        return $getValue($item, ['kontak_keluarga', 'nama_keluarga', 'nama_wali'], '-');
    };

    $getGender = function ($item) use ($getValue) {
        $gender = strtolower((string) $getValue($item, ['jenis_kelamin', 'jk', 'gender'], '-'));
        return match ($gender) {
            'l', 'laki-laki', 'male' => 'Laki-laki',
            'p', 'perempuan', 'female' => 'Perempuan',
            default => '-'
        };
    };

    $getTanggalLahir = fn($item) => $formatDate($getValue($item, ['tanggal_lahir', 'tgl_lahir', 'lahir'], null));

    $getPersonName = function ($item, array $paths) {
        foreach ($paths as $path) {
            $value = data_get($item, $path . '.name') ?? data_get($item, $path . '.nama');
            if ($value) return $value;
        }
        return '-';
    };

    $pasienNama = $getNama($pasien);
    $pasienNik = $getNik($pasien);
    $pasienAlamat = $getAlamat($pasien);
    $pasienKontak = $getKontak($pasien);
    $pasienWali = $getWali($pasien);
    $pasienGender = $getGender($pasien);
    $pasienTanggalLahirRaw = $getValue($pasien, ['tanggal_lahir', 'tgl_lahir', 'lahir'], null);
    $pasienTanggalLahir = $formatDate($pasienTanggalLahirRaw);
    $usia = $pasienTanggalLahirRaw ? Carbon::parse($pasienTanggalLahirRaw)->diff(now())->format('%y Thn %m Bln') : '-';
    $initial = Str::upper(Str::substr(trim($pasienNama), 0, 1)) ?: 'P';

    $latestParams = data_get($summary, 'parameter_terakhir', []);
    $latestParams = is_array($latestParams) ? $latestParams : [];

    $summaryCards = [
        ['label' => 'Total Pemeriksaan', 'value' => data_get($summary, 'total_medis', $riwayatMedis->count()), 'icon' => 'fa-stethoscope', 'theme' => 'emerald'],
        ['label' => $pasienType === 'balita' ? 'Total Imunisasi' : 'Ruang Lingkup', 'value' => $pasienType === 'balita' ? data_get($summary, 'total_imunisasi', $riwayatImunisasi->count()) : 'Klinis', 'icon' => $pasienType === 'balita' ? 'fa-syringe' : 'fa-folder-open', 'theme' => 'sky'],
        ['label' => 'Kunjungan Terakhir', 'value' => data_get($summary, 'pemeriksaan_terakhir', '-'), 'icon' => 'fa-clock-rotate-left', 'theme' => 'amber'],
        ['label' => 'Kategori Sasaran', 'value' => $pasienTypeLabel, 'icon' => $pasienIcon, 'theme' => 'teal'],
    ];

    $medicalFields = function ($item) use ($pasienType, $displayValue) {
        if ($pasienType === 'lansia') {
            return [
                ['label' => 'Tensi', 'value' => $displayValue(data_get($item, 'tekanan_darah')), 'icon' => 'fa-heart-pulse'],
                ['label' => 'Gula', 'value' => $displayValue(data_get($item, 'gula_darah'), 'mg/dL'), 'icon' => 'fa-droplet'],
                ['label' => 'Koles', 'value' => $displayValue(data_get($item, 'kolesterol'), 'mg/dL'), 'icon' => 'fa-vial'],
                ['label' => 'Asam', 'value' => $displayValue(data_get($item, 'asam_urat'), 'mg/dL'), 'icon' => 'fa-flask'],
                ['label' => 'L.Perut', 'value' => $displayValue(data_get($item, 'lingkar_perut'), 'cm'), 'icon' => 'fa-ruler'],
            ];
        }

        if ($pasienType === 'remaja') {
            return [
                ['label' => 'BB', 'value' => $displayValue(data_get($item, 'berat_badan'), 'kg'), 'icon' => 'fa-weight-scale'],
                ['label' => 'TB', 'value' => $displayValue(data_get($item, 'tinggi_badan'), 'cm'), 'icon' => 'fa-ruler-vertical'],
                ['label' => 'IMT', 'value' => $displayValue(data_get($item, 'imt')), 'icon' => 'fa-chart-line'],
                ['label' => 'Tensi', 'value' => $displayValue(data_get($item, 'tekanan_darah')), 'icon' => 'fa-heart-pulse'],
                ['label' => 'LILA', 'value' => $displayValue(data_get($item, 'lingkar_lengan'), 'cm'), 'icon' => 'fa-ruler'],
            ];
        }

        return [
            ['label' => 'BB', 'value' => $displayValue(data_get($item, 'berat_badan'), 'kg'), 'icon' => 'fa-weight-scale'],
            ['label' => 'TB/PB', 'value' => $displayValue(data_get($item, 'tinggi_badan'), 'cm'), 'icon' => 'fa-ruler-vertical'],
            ['label' => 'L.Kepala', 'value' => $displayValue(data_get($item, 'lingkar_kepala'), 'cm'), 'icon' => 'fa-circle'],
            ['label' => 'LILA', 'value' => $displayValue(data_get($item, 'lingkar_lengan'), 'cm'), 'icon' => 'fa-ruler'],
            ['label' => 'Gizi', 'value' => $displayValue(data_get($item, 'status_gizi')), 'icon' => 'fa-bowl-food'],
        ];
    };
@endphp

@push('styles')
<style>
    html { scroll-behavior: smooth; }
    body { background-color: #f4f7f6; } 

    .bg-mesh-fixed {
        position: fixed; inset: 0; z-index: -10;
        background-image: 
            radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, hsla(150, 100%, 94%, 1) 0px, transparent 50%);
        pointer-events: none;
    }

    .widget-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .widget-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
    }

    .btn-pill { border-radius: 9999px; transition: all 0.2s ease; cursor: pointer; }
    .btn-pill:active { transform: scale(0.97); }

    .slim-scroll { -webkit-overflow-scrolling: touch; overscroll-behavior-y: contain; }
    .slim-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .slim-scroll::-webkit-scrollbar-track { background: transparent; }
    .slim-scroll::-webkit-scrollbar-thumb { background: rgba(20, 184, 166, 0.3); border-radius: 9999px; }
    .slim-scroll::-webkit-scrollbar-thumb:hover { background: rgba(20, 184, 166, 0.5); }

    .animate-pop-in { animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popIn { from { opacity: 0; transform: scale(.98) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
</style>
@endpush

@section('content')
<div class="bg-mesh-fixed"></div>

<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6 animate-pop-in">

    {{-- 1. HERO BANNER --}}
    <section class="relative overflow-hidden rounded-[3rem] bg-gradient-to-r from-emerald-500 via-teal-500 to-green-500 p-8 sm:p-10 shadow-2xl shadow-emerald-500/20 flex flex-col lg:flex-row justify-between items-center gap-8 border-[6px] border-white/40" style="transform: translateZ(0);">
        <div class="absolute inset-0 bg-white/10 backdrop-blur-[2px]"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 w-full lg:w-2/3 flex flex-col gap-4 text-center lg:text-left">
            <div class="inline-flex justify-center lg:justify-start items-center gap-2 mb-2">
                <a href="{{ route('bidan.rekam-medis.index', ['type' => $pasienType]) }}" class="btn-pill bg-white/20 border border-white/30 text-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest backdrop-blur-md shadow-inner flex items-center gap-2 hover:bg-white/30 transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Direktori
                </a>
                <span class="btn-pill bg-white/20 border border-white/30 text-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest backdrop-blur-md shadow-inner flex items-center gap-2">
                    <i class="fa-solid {{ $pasienIcon }}"></i> {{ $pasienTypeLabel }}
                </span>
            </div>
            
            <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight leading-tight">
                {{ $pasienNama }}
            </h1>

            <div class="flex flex-wrap items-center justify-center lg:justify-start gap-x-5 gap-y-2 text-white/90 text-sm font-semibold">
                <span class="flex items-center gap-1.5"><i class="fa-solid fa-id-card opacity-70"></i> NIK: {{ $pasienNik }}</span>
                <span class="flex items-center gap-1.5"><i class="fa-solid fa-cake-candles opacity-70"></i> Usia: {{ $usia }}</span>
            </div>
        </div>
    </section>

    {{-- 2. SUMMARY CARDS --}}
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach($summaryCards as $card)
            <div class="widget-card p-5 group flex items-center justify-between">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1 group-hover:text-{{ $card['theme'] }}-500 transition-colors">{{ $card['label'] }}</p>
                    <h2 class="text-xl font-black text-slate-800 leading-none">{{ $card['value'] }}</h2>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-{{ $card['theme'] }}-50 text-{{ $card['theme'] }}-500 border border-{{ $card['theme'] }}-100 flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition-transform">
                    <i class="fa-solid {{ $card['icon'] }}"></i>
                </div>
            </div>
        @endforeach
    </section>

    {{-- 3. GRID UTAMA (KUNCI PRESISI 100%: Menggunakan items-stretch agar tinggi kiri & kanan mutlak sama) --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-stretch">
        
        {{-- KOLOM KIRI (Col 4): Identitas & Parameter Terakhir --}}
        <div class="xl:col-span-4 flex flex-col gap-6 h-full">
            
            {{-- Identitas Pasien (Tinggi Natural) --}}
            <div class="widget-card p-6 flex flex-col shrink-0">
                <div class="flex items-center gap-3 mb-5 border-b border-slate-100 pb-4">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-500 border border-sky-100 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-user-circle"></i>
                    </div>
                    <div>
                        <h4 class="text-[11px] font-black text-slate-800 uppercase tracking-widest">Identitas</h4>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Data Demografi Pasien</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="bg-slate-50 border border-slate-100 rounded-2xl p-3.5">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Nama Lengkap</p>
                        <p class="text-sm font-bold text-slate-800">{{ $pasienNama }}</p>
                    </div>
                    <div class="flex gap-3">
                        <div class="flex-1 bg-slate-50 border border-slate-100 rounded-2xl p-3.5">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Gender</p>
                            <p class="text-xs font-bold text-slate-700">{{ $pasienGender }}</p>
                        </div>
                        <div class="flex-1 bg-slate-50 border border-slate-100 rounded-2xl p-3.5">
                            <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Tgl Lahir</p>
                            <p class="text-xs font-bold text-slate-700">{{ $pasienTanggalLahir }}</p>
                        </div>
                    </div>
                    <div class="bg-slate-50 border border-slate-100 rounded-2xl p-3.5">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Ibu / Wali / Kontak</p>
                        <p class="text-xs font-bold text-slate-700">{{ $pasienWali }} <span class="mx-1 text-slate-300">•</span> {{ $pasienKontak }}</p>
                    </div>
                    <div class="bg-slate-50 border border-slate-100 rounded-2xl p-3.5">
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Alamat Domisili</p>
                        <p class="text-xs font-medium text-slate-600 leading-relaxed">{{ $pasienAlamat }}</p>
                    </div>
                </div>
            </div>

            {{-- Parameter Terakhir (MENGGUNAKAN flex-1 AGAR MERENGGANG MENGISI RUANG BAWAH) --}}
            <div class="widget-card p-6 flex flex-col flex-1">
                <div class="flex items-center gap-3 mb-5 border-b border-slate-100 pb-4 shrink-0">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 border border-emerald-100 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div>
                        <h4 class="text-[11px] font-black text-slate-800 uppercase tracking-widest">Parameter Klinis</h4>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Hasil Pengukuran Terakhir</p>
                    </div>
                </div>

                {{-- Konten Parameter (Bisa grid jika ada data, atau di-tengah-kan jika kosong) --}}
                <div class="flex-1 flex flex-col {{ count($latestParams) > 0 ? '' : 'justify-center items-center' }}">
                    @if(count($latestParams) > 0)
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($latestParams as $label => $value)
                                <div class="bg-emerald-50/50 border border-emerald-100 rounded-2xl p-3 flex flex-col justify-center">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-emerald-600 mb-1 truncate">{{ $label }}</p>
                                    <p class="text-sm font-black text-slate-800">{{ $value ?: '-' }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="w-full text-center py-6 border border-dashed border-slate-200 rounded-2xl bg-slate-50 flex flex-col items-center justify-center flex-1 min-h-[150px]">
                            <i class="fa-solid fa-folder-open text-2xl text-slate-300 mb-2"></i>
                            <p class="text-[11px] font-bold text-slate-500">Belum ada parameter</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN (Col 8): Riwayat Medis & Imunisasi --}}
        <div class="xl:col-span-8 flex flex-col gap-6 h-full">
            
            {{-- Riwayat Pemeriksaan Medis (MENGGUNAKAN flex-1 AGAR TINGGINYA SAMA DENGAN KOLOM KIRI) --}}
            <div class="widget-card p-6 flex flex-col flex-1">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 border-b border-slate-100 pb-4 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-500 border border-teal-100 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid fa-stethoscope"></i>
                        </div>
                        <div>
                            <h4 class="text-[11px] font-black text-slate-800 uppercase tracking-widest">Riwayat Pemeriksaan</h4>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Data Medis Tervalidasi Bidan</p>
                        </div>
                    </div>
                    <span class="bg-teal-50 border border-teal-100 text-teal-600 text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full shadow-sm whitespace-nowrap shrink-0">
                        <i class="fa-solid fa-check-circle mr-1"></i> {{ $riwayatMedis->count() }} Data
                    </span>
                </div>

                {{-- Konten Riwayat Medis --}}
                <div class="flex-1 flex flex-col {{ $riwayatMedis->count() > 0 ? '' : 'justify-center items-center' }}">
                    @if($riwayatMedis->count() > 0)
                        <div class="max-h-[500px] overflow-y-auto slim-scroll pr-2 space-y-4">
                            @foreach($riwayatMedis as $pemeriksaan)
                                @php
                                    $tglPeriksa = $formatDateTime(data_get($pemeriksaan, 'tanggal_periksa') ?? data_get($pemeriksaan, 'created_at'));
                                    $petugas = $getPersonName($pemeriksaan, ['pemeriksa', 'verifikator', 'verifikatorLegacy']);
                                    $fields = $medicalFields($pemeriksaan);
                                    $catatanKlinis = $getValue($pemeriksaan, ['catatan_bidan', 'catatan', 'keterangan', 'hasil_pemeriksaan', 'keluhan'], '-');
                                    $tindakanLayanan = $getValue($pemeriksaan, ['tindakan', 'tindakan_lanjut', 'layanan', 'rekomendasi_tindakan'], '-');
                                    $edukasiKesehatan = $getValue($pemeriksaan, ['catatan_edukasi', 'edukasi', 'edukasi_kesehatan'], '-');
                                @endphp

                                <article class="bg-slate-50 border border-slate-100 rounded-3xl p-5 relative overflow-hidden group">
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-teal-400 rounded-l-3xl"></div>
                                    
                                    <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-4 border-b border-slate-200/60 pb-4">
                                        <div>
                                            <h3 class="text-sm font-black text-slate-800"><i class="fa-solid fa-file-medical text-teal-500 mr-1.5"></i> Pemeriksaan {{ $pasienTypeLabel }}</h3>
                                            <p class="text-[10px] font-bold text-slate-500 mt-1"><i class="fa-solid fa-user-nurse mr-1"></i> Bidan: {{ $petugas }}</p>
                                        </div>
                                        <span class="bg-white border border-slate-200 text-slate-500 text-[9px] font-black uppercase tracking-widest px-3 py-1.5 rounded-md shadow-sm whitespace-nowrap">
                                            <i class="fa-solid fa-clock text-slate-400 mr-1"></i> {{ $tglPeriksa }}
                                        </span>
                                    </div>

                                    <div class="flex flex-wrap gap-2 mb-4">
                                        @foreach($fields as $field)
                                            <div class="bg-white border border-slate-100 rounded-lg p-2.5 flex-1 min-w-[80px] shadow-sm">
                                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-1 truncate" title="{{ $field['label'] }}"><i class="fa-solid {{ $field['icon'] }} mr-1 text-slate-300"></i> {{ $field['label'] }}</p>
                                                <p class="text-[11px] font-bold text-slate-800 truncate">{{ $field['value'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="grid gap-3 md:grid-cols-3">
                                        <div class="bg-white border border-slate-100 rounded-xl p-4 shadow-sm">
                                            <p class="text-[9px] font-black uppercase tracking-widest text-teal-600 mb-1.5">Catatan Klinis</p>
                                            <p class="text-[11px] font-semibold text-slate-600 leading-relaxed">{{ $catatanKlinis }}</p>
                                        </div>
                                        <div class="bg-white border border-slate-100 rounded-xl p-4 shadow-sm">
                                            <p class="text-[9px] font-black uppercase tracking-widest text-sky-600 mb-1.5">Tindakan / Layanan</p>
                                            <p class="text-[11px] font-semibold text-slate-600 leading-relaxed">{{ $tindakanLayanan }}</p>
                                        </div>
                                        <div class="bg-white border border-slate-100 rounded-xl p-4 shadow-sm">
                                            <p class="text-[9px] font-black uppercase tracking-widest text-amber-600 mb-1.5">Edukasi</p>
                                            <p class="text-[11px] font-semibold text-slate-600 leading-relaxed">{{ $edukasiKesehatan }}</p>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="w-full text-center py-12 border border-dashed border-slate-200 rounded-3xl bg-slate-50 flex flex-col items-center justify-center flex-1 min-h-[300px]">
                            <i class="fa-solid fa-folder-open text-4xl text-slate-300 mb-3"></i>
                            <h3 class="text-sm font-black text-slate-700">Belum Ada Rekam Medis</h3>
                            <p class="text-xs font-medium text-slate-500 mt-1">Data akan muncul setelah Bidan memvalidasi hasil pemeriksaan.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Riwayat Imunisasi (KHUSUS BALITA) - natural height --}}
            @if($pasienType === 'balita')
                <div class="widget-card p-6 flex flex-col shrink-0">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 border-b border-slate-100 pb-4 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-500 border border-sky-100 flex items-center justify-center text-lg shrink-0">
                                <i class="fa-solid fa-syringe"></i>
                            </div>
                            <div>
                                <h4 class="text-[11px] font-black text-slate-800 uppercase tracking-widest">Riwayat Imunisasi</h4>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Catatan Pemberian Vaksin</p>
                            </div>
                        </div>
                        <span class="bg-sky-50 border border-sky-100 text-sky-600 text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full shadow-sm whitespace-nowrap shrink-0">
                            <i class="fa-solid fa-list-check mr-1"></i> {{ $riwayatImunisasi->count() }} Data
                        </span>
                    </div>

                    <div class="max-h-[350px] overflow-y-auto slim-scroll pr-2 space-y-3">
                        @forelse($riwayatImunisasi as $imun)
                            @php
                                $tglImun = $formatDate($getValue($imun, ['tanggal_imunisasi', 'tanggal', 'created_at'], null));
                                $jnsImun = $getValue($imun, ['jenis_imunisasi', 'jenis', 'nama_imunisasi'], 'Imunisasi');
                                $namaVaksin = $getValue($imun, ['vaksin', 'nama_vaksin'], '-');
                                $dsImun = $getValue($imun, ['dosis', 'dosis_ke'], '-');
                                $btImun = $getValue($imun, ['batch_number', 'no_batch', 'nomor_batch'], '-');
                                $ketImun = $getValue($imun, ['keterangan', 'catatan'], '-');
                            @endphp

                            <article class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row md:items-center gap-4 hover:border-sky-300 transition-colors">
                                <div class="min-w-[180px]">
                                    <h3 class="text-sm font-black text-slate-800">{{ $jnsImun }}</h3>
                                    <p class="text-[10px] font-bold text-slate-400 mt-1"><i class="fa-solid fa-calendar-day mr-1"></i> {{ $tglImun }}</p>
                                </div>
                                <div class="flex-1 grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <div class="bg-slate-50 rounded-xl p-2.5">
                                        <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Vaksin</p>
                                        <p class="text-xs font-bold text-sky-600 truncate" title="{{ $namaVaksin }}">{{ $namaVaksin }}</p>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-2.5">
                                        <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Dosis</p>
                                        <p class="text-xs font-bold text-slate-800">{{ $dsImun }}</p>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-2.5">
                                        <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Batch</p>
                                        <p class="text-xs font-bold text-slate-800 font-mono">{{ $btImun }}</p>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-2.5">
                                        <p class="text-[8px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Catatan</p>
                                        <p class="text-xs font-medium text-slate-600 truncate" title="{{ $ketImun }}">{{ $ketImun }}</p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="text-center py-8 border border-dashed border-slate-200 rounded-2xl bg-slate-50 w-full">
                                <i class="fa-solid fa-syringe text-3xl text-slate-300 mb-2"></i>
                                <p class="text-[11px] font-bold text-slate-500">Belum ada riwayat imunisasi.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection