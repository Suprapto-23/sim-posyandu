@extends('layouts.kader')

@section('title', 'Detail Data Balita')
@section('page-name', 'Detail Data Balita')
@section('page-title', 'Detail Data Balita')

@section('content')
@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $indexRoute = $routeHas('kader.data.balita.index')
        ? route('kader.data.balita.index')
        : url('/kader/data/balita');

    $editRoute = $routeHas('kader.data.balita.edit')
        ? route('kader.data.balita.edit', $balita->id)
        : null;

    $syncRoute = $routeHas('kader.data.balita.sync')
        ? route('kader.data.balita.sync', $balita->id)
        : null;

    $tanggalLahir = filled($balita->tanggal_lahir ?? null)
        ? Carbon::parse($balita->tanggal_lahir)
        : null;

    if ($tanggalLahir) {
        $years = $usia_tahun ?? $tanggalLahir->diff(now())->y;
        $months = $sisa_bulan ?? $tanggalLahir->diff(now())->m;
        $days = $usia_hari ?? $tanggalLahir->diff(now())->d;

        $usiaText = $years > 0
            ? $years . ' tahun ' . $months . ' bulan'
            : $months . ' bulan ' . $days . ' hari';
    } else {
        $usiaText = '-';
    }

    $genderLabel = match ($balita->jenis_kelamin ?? null) {
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        default => '-',
    };

    $akunTerhubung = filled($balita->user_id ?? null) || filled($userTerhubung ?? null);

    $initial = Str::upper(Str::substr(trim((string) ($balita->nama_lengkap ?? 'B')), 0, 1)) ?: 'B';

    $kunjungans = collect($balita->kunjungans ?? [])->take(5);

    $pemeriksaanTerakhir = $balita->pemeriksaan_terakhir ?? null;

    if (! $pemeriksaanTerakhir) {
        $pemeriksaanTerakhir = optional($kunjungans->first(function ($item) {
            return filled(data_get($item, 'pemeriksaan'));
        }))->pemeriksaan;
    }

    $formatDate = function ($value, $format = 'd F Y') {
        if (! $value) return '-';
        return Carbon::parse($value)->translatedFormat($format);
    };

    $numberValue = function ($value, $unit = '') {
        if (blank($value)) return '-';
        $value = rtrim(rtrim((string) $value, '0'), '.');
        return trim($value . ' ' . $unit);
    };

    $lastCheckDate = $pemeriksaanTerakhir
        ? $formatDate($pemeriksaanTerakhir->tanggal_periksa ?? $pemeriksaanTerakhir->created_at ?? null, 'd M Y')
        : 'Belum pernah periksa';

    $sessionType = session('success')
        ? 'success'
        : (session('warning') ? 'warning' : (session('error') ? 'error' : null));

    $sessionMessage = session('success') ?? session('warning') ?? session('error');
@endphp

@push('styles')
<style>
    body {
        background-color: #f8fafc;
        background-image: radial-gradient(at 0% 0%, hsla(160, 100%, 94%, 1) 0px, transparent 50%),
                          radial-gradient(at 100% 0%, hsla(190, 100%, 92%, 1) 0px, transparent 50%);
        background-attachment: fixed;
    }

    .animate-pop-in {
        animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }

    @keyframes popIn {
        from { opacity: 0; transform: scale(.96) translateY(12px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .btn-pill {
        border-radius: 9999px;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.95); }

    /* Clean Card Style (Adaptasi Admin) */
    .soft-card {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(18px);
        transition: all .25s ease;
    }
    .soft-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px -10px rgba(15, 23, 42, 0.08);
    }

    /* Info Row Style */
    .info-row {
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding-bottom: 12px;
        border-bottom: 1px dashed #e2e8f0;
    }
    .info-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    @media (min-width: 640px) {
        .info-row {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }
    }

    /* Modal Full Screen */
    .pc-modal-backdrop {
        position: fixed; inset: 0; z-index: 999999; display: none; align-items: center; justify-content: center;
        background: rgba(15, 23, 42, .65); backdrop-filter: blur(12px); padding: 1rem; width: 100vw; height: 100vh;
    }
    .pc-modal-backdrop.is-open { display: flex; }
    .pc-modal-card {
        width: 100%; max-width: 420px; background: white; border-radius: 2rem; padding: 0; overflow: hidden;
        transform: scale(0.95) translateY(15px); opacity: 0; transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35);
    }
    .pc-modal-backdrop.is-open .pc-modal-card { transform: scale(1) translateY(0); opacity: 1; }
</style>
@endpush

<div class="max-w-[1080px] mx-auto animate-pop-in pb-20 px-4 sm:px-6 mt-6">

    {{-- HERO SECTION --}}
    <section class="bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-600 rounded-[3rem] p-8 md:p-12 mb-8 relative overflow-hidden shadow-[0_20px_40px_-12px_rgba(16,185,129,.35)] border border-white/20 text-center">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/15 blur-[80px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col items-center">
            <div class="w-28 h-28 rounded-[2rem] bg-white border-4 border-white/50 text-emerald-500 flex items-center justify-center font-black text-5xl shadow-xl mb-5">
                {{ $initial }}
            </div>

            <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight">
                {{ $balita->nama_lengkap ?? '-' }}
            </h1>

            <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                <span class="bg-white/20 backdrop-blur-md border border-white/30 text-white font-bold text-[11px] uppercase tracking-widest px-4 py-1.5 rounded-full shadow-sm">
                    <i class="fas fa-child-reaching mr-1"></i> Sasaran Balita
                </span>

                <span class="bg-white/20 backdrop-blur-md border border-white/30 text-white font-mono text-[11px] font-bold px-4 py-1.5 rounded-full flex items-center gap-2 shadow-sm">
                    <i class="fas fa-id-card"></i> NIK: {{ $balita->nik ?? '-' }}
                </span>

                @if($akunTerhubung)
                    <span class="bg-emerald-400/90 backdrop-blur-md text-white font-bold text-[11px] uppercase tracking-widest px-4 py-1.5 rounded-full shadow-sm border border-emerald-300/50">
                        <i class="fas fa-link mr-1"></i> Akun Terhubung
                    </span>
                @else
                    <span class="bg-amber-500/90 backdrop-blur-md text-white font-bold text-[11px] uppercase tracking-widest px-4 py-1.5 rounded-full shadow-sm border border-amber-400/50">
                        <i class="fas fa-link-slash mr-1"></i> Belum Terhubung
                    </span>
                @endif
            </div>

            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                <a href="{{ $indexRoute }}" class="btn-pill bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 text-white text-xs uppercase tracking-widest font-bold px-6 py-3.5 transition-all">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>

                @if($editRoute)
                    <a href="{{ $editRoute }}" class="btn-pill bg-white hover:bg-emerald-50 text-emerald-600 text-xs uppercase tracking-widest font-bold px-6 py-3.5 transition-all shadow-lg hover:-translate-y-0.5">
                        <i class="fas fa-edit mr-1"></i> Edit Data
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- SYSTEM ALERT --}}
    @if($sessionType && $sessionMessage)
        @php
            $alertClass = match ($sessionType) {
                'error' => 'border-rose-200 bg-rose-50 text-rose-800',
                'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
                default => 'border-emerald-200 bg-emerald-50 text-emerald-800',
            };
            $alertIcon = match ($sessionType) {
                'error' => 'fa-triangle-exclamation text-rose-500',
                'warning' => 'fa-circle-exclamation text-amber-500',
                default => 'fa-circle-check text-emerald-500',
            };
            $alertTitle = match ($sessionType) {
                'error' => 'Aksi Gagal',
                'warning' => 'Perhatian',
                default => 'Berhasil',
            };
        @endphp

        <div class="rounded-2xl p-5 shadow-sm border flex items-center gap-4 {{ $alertClass }} mb-8">
            <div class="bg-white rounded-full w-10 h-10 flex items-center justify-center shrink-0 shadow-inner">
                <i class="fa-solid {{ $alertIcon }} text-lg"></i>
            </div>
            <div>
                <h3 class="font-black text-sm">{{ $alertTitle }}</h3>
                <p class="font-medium text-xs mt-0.5 opacity-80">{{ $sessionMessage }}</p>
            </div>
        </div>
    @endif

    {{-- MAIN CONTENT GRID --}}
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        {{-- KOLOM KIRI: BIODATA & DATA LAHIR --}}
        <div class="soft-card bg-white rounded-[2rem] border border-slate-100 shadow-sm p-6 sm:p-8">
            <div class="flex items-center gap-4 mb-6 border-b border-slate-50 pb-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-child-reaching"></i>
                </div>
                <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">
                    Biodata Balita
                </h4>
            </div>

            <div class="space-y-4">
                <div class="info-row">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Nama Lengkap</span>
                    <span class="text-[13px] font-black text-slate-700">{{ $balita->nama_lengkap ?? '-' }}</span>
                </div>

                <div class="info-row">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Jenis Kelamin</span>
                    <span class="text-[13px] font-black text-slate-700">{{ $genderLabel }}</span>
                </div>

                <div class="info-row">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Tempat Lahir</span>
                    <span class="text-[13px] font-black text-slate-700">{{ $balita->tempat_lahir ?? '-' }}</span>
                </div>

                <div class="info-row">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Tanggal Lahir</span>
                    <span class="text-[13px] font-black text-slate-700">
                        {{ $tanggalLahir ? $tanggalLahir->translatedFormat('d F Y') : '-' }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Usia Saat Ini</span>
                    <span class="text-[12px] font-black text-emerald-700 bg-emerald-50 border border-emerald-100 px-3 py-1 rounded-full">
                        {{ $usiaText }}
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Berat Lahir</span>
                    <span class="text-[13px] font-black text-slate-700">{{ $numberValue($balita->berat_lahir ?? null, 'kg') }}</span>
                </div>

                <div class="info-row">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Panjang Lahir</span>
                    <span class="text-[13px] font-black text-slate-700">{{ $numberValue($balita->panjang_lahir ?? null, 'cm') }}</span>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: KELUARGA & SISTEM --}}
        <div class="flex flex-col gap-6">
            {{-- Card Keluarga --}}
            <div class="soft-card bg-white rounded-[2rem] border border-slate-100 shadow-sm p-6 sm:p-8 flex-1">
                <div class="flex items-center gap-4 mb-6 border-b border-slate-50 pb-4">
                    <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-500 flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-house-chimney-user"></i>
                    </div>
                    <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">
                        Keluarga & Domisili
                    </h4>
                </div>

                <div class="space-y-4">
                    <div class="info-row">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Nama Ibu</span>
                        <span class="text-[13px] font-black text-slate-700">{{ $balita->nama_ibu ?? '-' }}</span>
                    </div>

                    <div class="info-row">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Nama Ayah</span>
                        <span class="text-[13px] font-black text-slate-700">{{ $balita->nama_ayah ?: '-' }}</span>
                    </div>

                    <div class="flex flex-col border-b border-dashed border-slate-200 pb-3 gap-1">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1">Alamat Tinggal</span>
                        <span class="text-[13px] font-black text-slate-700 leading-relaxed">{{ $balita->alamat ?? '-' }}</span>
                    </div>

                    <div class="info-row">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Pemeriksaan Terakhir</span>
                        <span class="text-[13px] font-black text-slate-700">{{ $lastCheckDate }}</span>
                    </div>
                </div>
            </div>

            {{-- Card Sinkronisasi Akun --}}
            <div class="soft-card bg-white rounded-[2rem] border border-slate-100 shadow-sm p-6 sm:p-8">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Akses Akun Warga</h4>
                    <i class="fas fa-mobile-screen-button text-slate-300 text-xl"></i>
                </div>
                
                @if($akunTerhubung)
                    <div class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50/80 p-4 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-1">Status</p>
                            <p class="text-sm font-black text-emerald-900">Sudah Terhubung</p>
                        </div>
                        <i class="fa-solid fa-circle-check text-emerald-400 text-2xl"></i>
                    </div>
                    <p class="text-xs font-semibold text-slate-500 mt-3">Orang tua dapat memantau data tumbuh kembang balita ini melalui aplikasi warga.</p>
                @else
                    <div class="mt-4 rounded-2xl border border-amber-100 bg-amber-50/80 p-4">
                        <p class="text-[10px] font-black uppercase tracking-widest text-amber-600 mb-1">Status</p>
                        <p class="text-sm font-black text-amber-900">Belum Terhubung</p>
                    </div>
                    <p class="text-xs font-semibold text-slate-500 mt-3 mb-4">Sinkronkan data agar orang tua dapat mengakses riwayat balita secara mandiri.</p>
                    
                    @if($syncRoute)
                        <form id="syncBalitaForm" action="{{ $syncRoute }}" method="POST">
                            @csrf
                            <button type="button" id="openSyncConfirm" class="btn-pill w-full h-11 bg-slate-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-emerald-600 transition-colors shadow-md">
                                <i class="fa-solid fa-rotate mr-2"></i> Sinkronkan Akun
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>

    </section>

    {{-- RIWAYAT KUNJUNGAN (FULL WIDTH) --}}
    <section class="soft-card bg-white rounded-[2rem] border border-slate-100 shadow-sm p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 border-b border-slate-50 pb-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest">Riwayat Kunjungan</h4>
                    <p class="text-[11px] font-bold text-slate-400">5 Kunjungan Terakhir</p>
                </div>
            </div>
            
            @if(Route::has('kader.pemeriksaan.balita.create'))
                <a href="{{ route('kader.pemeriksaan.balita.create', ['balita_id' => $balita->id]) }}" class="btn-pill bg-emerald-50 hover:bg-emerald-500 hover:text-white text-emerald-600 border border-emerald-200 px-5 py-2.5 text-[11px] font-black uppercase tracking-wider transition-colors shadow-sm inline-flex items-center justify-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Kunjungan
                </a>
            @endif
        </div>

        <div class="space-y-3">
            @forelse($kunjungans as $kunjungan)
                @php
                    $tanggalKunjungan = filled($kunjungan->tanggal_kunjungan ?? null)
                        ? Carbon::parse($kunjungan->tanggal_kunjungan)
                        : null;

                    $jenisKunjungan = $kunjungan->jenis_kunjungan ?? 'kunjungan';

                    $jenisLabel = match ($jenisKunjungan) {
                        'imunisasi' => 'Imunisasi',
                        'pemeriksaan' => 'Pemeriksaan Fisik',
                        default => 'Kunjungan Posyandu',
                    };

                    $petugas = data_get($kunjungan, 'petugas.name')
                        ?? data_get($kunjungan, 'petugas.nama_lengkap')
                        ?? '-';
                @endphp

                <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4 hover:bg-white hover:border-emerald-200 hover:shadow-sm transition-all duration-300">
                    <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-sm font-black text-teal-700">
                            {{ $loop->iteration }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="text-sm font-black text-slate-800">
                                        {{ $jenisLabel }}
                                    </h3>
                                    <p class="mt-0.5 text-xs font-bold text-slate-500">
                                        {{ $tanggalKunjungan ? $tanggalKunjungan->translatedFormat('d F Y') : '-' }}
                                    </p>
                                </div>

                                <span class="inline-flex w-max rounded-full bg-white px-3 py-1 text-[10px] font-black uppercase tracking-widest text-slate-600 border border-slate-200 shadow-sm">
                                    <i class="fas fa-user-nurse mr-1.5 text-teal-500"></i> Bidan {{ Str::limit($petugas, 15) }}
                                </span>
                            </div>

                            <div class="mt-3 bg-white rounded-xl border border-slate-100 p-3">
                                @if($kunjungan->pemeriksaan)
                                    <p class="text-xs font-bold text-slate-700 flex flex-wrap gap-4">
                                        <span><i class="fa-solid fa-weight-scale text-slate-400 mr-1"></i> Berat: <span class="text-emerald-600">{{ $numberValue($kunjungan->pemeriksaan->berat_badan ?? null, 'kg') }}</span></span>
                                        <span><i class="fa-solid fa-ruler-vertical text-slate-400 mr-1"></i> Tinggi: <span class="text-sky-600">{{ $numberValue($kunjungan->pemeriksaan->tinggi_badan ?? null, 'cm') }}</span></span>
                                    </p>
                                @else
                                    <p class="text-xs font-medium text-slate-500 italic">Tidak ada pencatatan antropometri pada kunjungan ini.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 p-8 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-xl font-black text-slate-300 shadow-sm border border-slate-100 mb-4">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3 class="text-base font-black text-slate-800">Riwayat Masih Kosong</h3>
                    <p class="mx-auto mt-1 max-w-sm text-xs font-semibold leading-relaxed text-slate-500">
                        Belum ada catatan kunjungan atau pemeriksaan fisik untuk Balita ini.
                    </p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection

@push('modals')
{{-- MODAL KONFIRMASI SINKRONISASI (FULL SCREEN) --}}
<div id="nexusSyncConfirm" class="pc-modal-backdrop">
    <div class="pc-modal-card">
        <div class="relative overflow-hidden bg-gradient-to-br from-amber-500 to-orange-600 px-6 py-6 text-white text-center">
            <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-white/10 pointer-events-none"></div>
            <div class="absolute -bottom-16 left-8 h-28 w-44 rounded-t-[80px] bg-white/10 pointer-events-none"></div>

            <div class="relative z-10">
                <i class="fa-solid fa-rotate text-4xl mb-3 opacity-90"></i>
                <h3 class="text-xl font-black tracking-tight mb-1">Sinkronkan Akun Warga?</h3>
                <p class="text-xs font-semibold leading-relaxed opacity-90 px-4">
                    Sistem akan mencari akun warga dengan NIK yang sama dengan NIK Balita ini.
                </p>
            </div>
        </div>

        <div class="p-6 bg-white text-center">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-bold leading-6 text-amber-800 mb-5 text-left">
                <i class="fa-solid fa-circle-info mr-1"></i> Sinkronisasi otomatis berhasil jika akun warga sudah terdaftar di sistem dengan NIK yang sesuai.
            </div>

            <div class="grid grid-cols-2 gap-3">
                <button type="button" id="nexusSyncCancel" class="btn-pill h-11 border border-slate-200 bg-white text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="button" id="nexusSyncOk" class="btn-pill h-11 bg-amber-500 text-sm font-black text-amber-950 shadow-md hover:bg-amber-400 transition-colors">
                    Sinkronkan
                </button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('nexusSyncConfirm');
        const openButton = document.getElementById('openSyncConfirm');
        const cancelButton = document.getElementById('nexusSyncCancel');
        const okButton = document.getElementById('nexusSyncOk');
        const form = document.getElementById('syncBalitaForm');

        // Pastikan modal ada di luar container untuk menghindari z-index trap
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        function openModal() {
            modal?.classList.add('is-open');
            document.body.style.overflow = 'hidden'; // Kunci scroll layar
        }

        function closeModal() {
            modal?.classList.remove('is-open');
            document.body.style.overflow = ''; // Lepas scroll layar
        }

        openButton?.addEventListener('click', openModal);
        cancelButton?.addEventListener('click', closeModal);

        modal?.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal?.classList.contains('is-open')) {
                closeModal();
            }
        });

        okButton?.addEventListener('click', function () {
            closeModal();
            
            // Loading state
            okButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Proses...';
            okButton.disabled = true;

            if (form) {
                HTMLFormElement.prototype.submit.call(form);
            }
        });
    });
</script>
@endpush