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
            if (Route::has($name)) return route($name, $params);
        }
        return '#';
    };

    // Ambil Collection Data
    $anakList = collect($dataAnak ?? []);
    $remajaList = collect($dataRemaja ?? []);
    $lansiaList = collect($dataLansia ?? []);

    $jadwalList = collect($jadwalTerdekat ?? []);
    $jadwalUtama = $jadwalList->first();

    $notifList = collect($notifikasiTerbaru ?? [])->take(5);
    $jumlahNotif = $totalNotifikasiBelumDibaca ?? $notifList->where('is_read', false)->count();

    $totalSasaran = $anakList->count() + $remajaList->count() + $lansiaList->count();

    $monitoringRoute = $routeTo('user.monitoring.index');
    $jadwalRoute = $routeTo('user.jadwal.index');
    $notifikasiRoute = $routeTo('user.notifikasi.index');
    $profileRoute = $routeTo('user.profile.edit');

    $balitaShowRoute = fn ($id) => $routeTo(['user.balita.show', 'user.monitoring.balita.show'], [$id]);
    $remajaShowRoute = fn ($id) => $routeTo(['user.remaja.show', 'user.monitoring.remaja.show'], [$id]);
    $lansiaShowRoute = fn ($id) => $routeTo(['user.lansia.show', 'user.monitoring.lansia.show'], [$id]);

    $formatDate = fn ($value, $format = 'd F Y') => filled($value) && $value !== '-' ? Carbon::parse($value)->translatedFormat($format) : '-';
    $formatTime = fn ($value) => filled($value) && $value !== '-' ? Carbon::parse($value)->format('H:i') : '-';

    // EKSTRAKTOR DATA
    $getName = function($item) {
        return data_get($item, 'nama_lengkap') 
            ?: data_get($item, 'nama_remaja') 
            ?: data_get($item, 'nama_balita') 
            ?: data_get($item, 'nama_lansia') 
            ?: data_get($item, 'nama') 
            ?: 'Tanpa Nama';
    };

    $getDob = function($item) {
        return data_get($item, 'tanggal_lahir') ?: data_get($item, 'tgl_lahir');
    };

    $getGender = function($item) {
        return data_get($item, 'jenis_kelamin') ?: data_get($item, 'jk');
    };

    $ageText = function ($dob) {
        if (blank($dob)) return '-';
        try {
            $diff = Carbon::parse($dob)->diff(now());
            return $diff->y > 0 ? $diff->y . ' tahun' : $diff->m . ' bulan';
        } catch (\Throwable $e) { return '-'; }
    };

    $genderText = fn ($value) => match (strtolower(trim($value))) {
        'l', 'laki-laki' => 'Laki-laki',
        'p', 'perempuan' => 'Perempuan',
        default => 'Belum diisi',
    };

    $healthItems = collect();

    foreach ($anakList->take(3) as $anak) {
        $healthItems->push([
            'type' => 'Balita',
            'name' => $getName($anak),
            'meta' => $ageText($getDob($anak)) . ' • ' . $genderText($getGender($anak)),
            'href' => $balitaShowRoute(data_get($anak, 'id')),
            'icon' => 'fa-child',
            'tone' => 'rose',
        ]);
    }

    foreach ($remajaList->take(3) as $remaja) {
        $healthItems->push([
            'type' => 'Remaja',
            'name' => $getName($remaja),
            'meta' => $ageText($getDob($remaja)) . ' • NIK: ' . (data_get($remaja, 'nik') ?: '-'),
            'href' => $remajaShowRoute(data_get($remaja, 'id')),
            'icon' => 'fa-user-graduate',
            'tone' => 'sky',
        ]);
    }

    foreach ($lansiaList->take(3) as $lansia) {
        $healthItems->push([
            'type' => 'Lansia',
            'name' => $getName($lansia),
            'meta' => $ageText($getDob($lansia)) . ' • ' . ucwords(str_replace('_', ' ', data_get($lansia, 'tingkat_kemandirian') ?: 'Mandiri')),
            'href' => $lansiaShowRoute(data_get($lansia, 'id')),
            'icon' => 'fa-heart-pulse',
            'tone' => 'amber',
        ]);
    }

    $toneMap = [
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-500', 'border' => 'border-emerald-100', 'hover' => 'hover:border-emerald-300'],
        'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-500', 'border' => 'border-rose-100', 'hover' => 'hover:border-rose-300'],
        'sky' => ['bg' => 'bg-sky-50', 'text' => 'text-sky-500', 'border' => 'border-sky-100', 'hover' => 'hover:border-sky-300'],
        'amber' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-500', 'border' => 'border-amber-100', 'hover' => 'hover:border-amber-300'],
    ];

    $statCards = [
        ['label' => 'Anggota Keluarga', 'value' => $totalSasaran, 'icon' => 'fa-users', 'tone' => 'emerald', 'desc' => 'Terdaftar di NIK'],
        ['label' => 'Pemberitahuan', 'value' => $jumlahNotif, 'icon' => 'fa-bell', 'tone' => 'amber', 'desc' => 'Belum dibaca'],
        ['label' => 'Agenda Jadwal', 'value' => $jadwalList->count(), 'icon' => 'fa-calendar-days', 'tone' => 'sky', 'desc' => 'Mendatang'],
        ['label' => 'Buku Kesehatan', 'value' => $healthItems->count(), 'icon' => 'fa-book-medical', 'tone' => 'rose', 'desc' => 'Riwayat aktif'],
    ];
@endphp

@push('styles')
<style>
    body {
        background-color: #f8fafc;
        background-attachment: fixed;
    }

    .animate-pop-in {
        animation: popIn .5s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }

    @keyframes popIn {
        from { opacity: 0; transform: translateY(16px) scale(.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .hero-grid {
        background-image: radial-gradient(rgba(255,255,255,.45) 1px, transparent 1px);
        background-size: 24px 24px;
    }

    .btn-pill {
        border-radius: 12px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.97); }

    .clean-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid rgba(226,232,240,.6);
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.04);
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px 16px;
        border-bottom: 1px solid #f8fafc;
    }

    @media (min-width: 640px) {
        .section-header { padding: 24px 32px 20px; }
        .clean-card { border-radius: 28px; }
    }

    .clean-scroll::-webkit-scrollbar { width: 4px; }
    .clean-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .clean-scroll::-webkit-scrollbar-track { background: transparent; }
</style>
@endpush

@section('content')
<div class="max-w-[1280px] mx-auto animate-pop-in pb-24 px-4 sm:px-6 lg:px-8 mt-4 sm:mt-6 space-y-5 sm:space-y-8">

    {{-- 1. HERO SECTION --}}
    <section class="bg-gradient-to-br from-emerald-500 via-teal-500 to-teal-600 rounded-[2rem] sm:rounded-[2.5rem] p-6 sm:p-8 relative overflow-hidden shadow-[0_15px_35px_-12px_rgba(20,184,166,.35)] border border-white/20">
        <div class="hero-grid absolute inset-0 opacity-20 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-white/15 blur-[60px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-5 sm:gap-6">
            <div class="flex flex-col gap-2.5 sm:gap-3">
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <span class="inline-flex items-center gap-1.5 sm:gap-2 rounded-full border border-white/30 bg-white/20 px-2.5 py-1 text-[8px] sm:text-[9px] font-black uppercase tracking-widest text-white backdrop-blur-md shadow-sm">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                        Portal Aktif
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-2.5 py-1 text-[8px] sm:text-[9px] font-black uppercase tracking-widest text-white/90 backdrop-blur-md shadow-sm">
                        <i class="far fa-clock"></i>
                        <span id="realtime-clock">Memuat...</span>
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-black text-white font-poppins tracking-tight mt-1">
                    <span id="dynamic-greeting">Halo</span>, {{ ucwords($firstName) }}! 
                    <span id="dynamic-emoji" class="inline-block hover:scale-125 transition-transform origin-bottom-right">👋</span>
                </h1>
                <p class="text-emerald-50 text-[12px] sm:text-[13px] font-medium leading-relaxed max-w-xl">
                    Pantau agenda kegiatan, rekam medis, dan pemberitahuan penting Posyandu keluarga Anda di satu tempat terpadu.
                </p>
            </div>
            
            <div class="shrink-0 flex gap-3">
                <a href="{{ $profileRoute }}" class="btn-pill bg-white/10 hover:bg-white/20 border border-white/30 text-white px-5 sm:px-6 py-3 sm:py-3.5 text-[9px] sm:text-[10px] font-black uppercase tracking-widest backdrop-blur-md transition-all">
                    <i class="fas fa-user-gear mr-1"></i> Pengaturan
                </a>
            </div>
        </div>
    </section>

    {{-- ALERT --}}
    @if(isset($pesanError) && $pesanError)
        <div class="rounded-[1.5rem] sm:rounded-[2rem] border border-amber-200 bg-amber-50/90 p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex gap-3 sm:gap-4 items-center">
                <div class="flex h-9 w-9 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-full bg-white text-amber-500 shadow-sm">
                    <i class="fas fa-exclamation animate-pulse"></i>
                </div>
                <div>
                    <h3 class="text-xs sm:text-sm font-black text-amber-900">Akses Terbatas</h3>
                    <p class="mt-0.5 text-[10px] sm:text-[11px] font-semibold text-amber-700">{{ $pesanError }}</p>
                </div>
            </div>
            <a href="{{ $profileRoute }}" class="btn-pill shrink-0 bg-amber-500 px-5 sm:px-6 py-2 sm:py-2.5 text-[9px] sm:text-[10px] font-black tracking-widest uppercase text-white hover:bg-amber-600 text-center">
                Lengkapi Profil
            </a>
        </div>
    @endif

    {{-- 2. METRIK UTAMA --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach($statCards as $card)
            @php $tone = $toneMap[$card['tone']]; @endphp
            <div class="clean-card p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 hover:-translate-y-1 transition-transform duration-300">
                <div>
                    <p class="text-[8px] sm:text-[9px] font-black uppercase tracking-widest text-slate-400 mb-0.5 sm:mb-1">{{ $card['label'] }}</p>
                    <div class="flex items-end gap-1.5 sm:gap-2">
                        <h3 class="text-2xl sm:text-3xl font-black text-slate-800 leading-none">{{ $card['value'] }}</h3>
                        <span class="text-[9px] sm:text-[10px] font-bold text-slate-400 mb-0.5 sm:mb-1 truncate max-w-[60px] sm:max-w-none">{{ $card['desc'] }}</span>
                    </div>
                </div>
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full {{ $tone['bg'] }} {{ $tone['text'] }} flex items-center justify-center text-sm sm:text-lg shrink-0">
                    <i class="fas {{ $card['icon'] }}"></i>
                </div>
            </div>
        @endforeach
    </div>

    {{-- 3. KONTEN UTAMA --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 sm:gap-8">
        
        {{-- KOLOM KIRI --}}
        <div class="xl:col-span-8 flex flex-col gap-5 sm:gap-8">
            
            {{-- WIDGET: Anggota Keluarga --}}
            <div class="clean-card flex flex-col">
                <div class="section-header">
                    <div>
                        <h2 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-widest"><i class="fas fa-users text-emerald-500 mr-1.5 sm:mr-2"></i> Keluarga</h2>
                    </div>
                    <a href="{{ $monitoringRoute }}" class="text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-emerald-500 transition-colors">Kelola <i class="fas fa-arrow-right ml-1"></i></a>
                </div>

                <div class="p-4 sm:px-8 sm:py-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                        @forelse($healthItems as $item)
                            @php $tone = $toneMap[$item['tone']]; @endphp
                            <a href="{{ $item['href'] }}" class="group flex items-center justify-between gap-3 sm:gap-4 rounded-[1rem] bg-white hover:bg-slate-50 border border-transparent hover:border-slate-100 p-3 transition-all duration-300">
                                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                                    <div class="flex h-9 w-9 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-full {{ $tone['bg'] }} {{ $tone['text'] }} text-xs sm:text-sm">
                                        <i class="fas {{ $item['icon'] }}"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 sm:gap-2 mb-0.5">
                                            <span class="text-[7px] sm:text-[8px] font-black uppercase tracking-widest {{ $tone['text'] }} border {{ $tone['border'] }} px-1.5 py-0.5 rounded">{{ $item['type'] }}</span>
                                            <h4 class="text-xs sm:text-sm font-black text-slate-800 truncate">{{ $item['name'] }}</h4>
                                        </div>
                                        <p class="text-[10px] sm:text-[11px] font-bold text-slate-500 truncate">{{ $item['meta'] }}</p>
                                    </div>
                                </div>
                                <div class="shrink-0 text-slate-300 group-hover:text-slate-600 transition-colors">
                                    <i class="fas fa-chevron-right text-[10px] sm:text-xs"></i>
                                </div>
                            </a>
                        @empty
                            <div class="col-span-full flex flex-col items-center justify-center py-8 text-center opacity-70">
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center mb-3 sm:mb-4">
                                    <i class="fas fa-users-slash text-xl sm:text-2xl text-slate-300"></i>
                                </div>
                                <h4 class="text-xs sm:text-sm font-black text-slate-800">Keluarga Belum Terkoneksi</h4>
                                <p class="text-[10px] sm:text-xs font-semibold text-slate-500 mt-1 max-w-sm mx-auto">Pastikan NIK di menu Pengaturan sudah benar.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- WIDGET: Agenda Posyandu --}}
            <div class="clean-card flex flex-col">
                <div class="section-header">
                    <div>
                        <h2 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-widest"><i class="fas fa-calendar-alt text-sky-500 mr-1.5 sm:mr-2"></i> Agenda Terdekat</h2>
                    </div>
                    <a href="{{ $jadwalRoute }}" class="text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-sky-500 transition-colors">Semua Jadwal <i class="fas fa-arrow-right ml-1"></i></a>
                </div>

                <div class="p-4 sm:px-8 sm:py-6">
                    @if($jadwalUtama)
                        <div class="flex flex-col sm:flex-row gap-4 sm:gap-5 items-center rounded-[1.2rem] sm:rounded-[1.5rem] bg-sky-50/40 border border-sky-100 p-4 sm:p-5 group hover:bg-sky-50/70 transition-colors">
                            
                            {{-- Container Flex Bersebelahan Untuk Mobile --}}
                            <div class="flex gap-4 items-center w-full sm:w-auto flex-1 min-w-0">
                                <div class="flex flex-col items-center justify-center shrink-0 w-16 h-16 sm:w-20 sm:h-20 rounded-xl sm:rounded-[1.2rem] bg-white border border-sky-100 shadow-sm group-hover:scale-105 transition-transform">
                                    <span class="text-[9px] sm:text-[10px] font-black text-sky-500 uppercase tracking-widest">{{ $formatDate($jadwalUtama->tanggal, 'M') }}</span>
                                    <span class="text-2xl sm:text-3xl font-black text-slate-800 leading-none mt-0.5">{{ $formatDate($jadwalUtama->tanggal, 'd') }}</span>
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap gap-1.5 sm:gap-2 mb-1.5 sm:mb-2">
                                        <span class="inline-block px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest border border-sky-200 bg-sky-50 text-sky-600">
                                            {{ ucwords(str_replace('_', ' ', $jadwalUtama->target_peserta ?? 'Semua Sasaran')) }}
                                        </span>
                                        <span class="inline-block px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest border border-slate-200 bg-slate-50 text-slate-600">
                                            <i class="far fa-clock mr-0.5"></i> {{ $formatTime($jadwalUtama->waktu_mulai) }} WIB
                                        </span>
                                    </div>
                                    <h4 class="text-sm sm:text-lg font-black text-slate-800 leading-tight mb-0.5 sm:mb-1 truncate" title="{{ $jadwalUtama->judul }}">{{ $jadwalUtama->judul }}</h4>
                                    <p class="text-[10px] sm:text-xs font-semibold text-slate-500 truncate">
                                        <i class="fas fa-map-marker-alt text-slate-300 mr-1"></i> {{ $jadwalUtama->lokasi ?? 'Lokasi belum ditentukan' }}
                                    </p>
                                </div>
                            </div>
                            
                            <a href="{{ route('user.jadwal.show', $jadwalUtama->id) }}" class="btn-pill shrink-0 w-full sm:w-auto bg-white border border-slate-200 hover:border-sky-300 hover:text-sky-600 text-slate-500 px-5 py-2.5 sm:py-3 text-[10px] font-black uppercase tracking-widest shadow-sm text-center transition-all mt-1 sm:mt-0">
                                Buka
                            </a>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-6 text-center opacity-70">
                            <i class="far fa-calendar-circle-plus text-2xl sm:text-3xl text-slate-300 mb-2 sm:mb-3"></i>
                            <h4 class="text-[11px] sm:text-xs font-black text-slate-600">Tidak Ada Jadwal</h4>
                            <p class="text-[10px] sm:text-[11px] font-semibold text-slate-400 mt-1">Belum ada kegiatan Posyandu terdekat.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN: Notifikasi --}}
        <div class="xl:col-span-4 relative h-[360px] xl:h-auto">
            <div class="h-full xl:absolute xl:inset-0">
                <div class="clean-card flex flex-col h-full">
                    
                    <div class="section-header shrink-0 border-b border-slate-100 bg-slate-50/50 rounded-t-[24px] sm:rounded-t-[28px]">
                        <h2 class="text-xs sm:text-sm font-black text-slate-800 uppercase tracking-widest"><i class="fas fa-bell text-amber-500 mr-1.5 sm:mr-2"></i> Notifikasi</h2>
                        <a href="{{ $notifikasiRoute }}" class="text-[9px] sm:text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-amber-500 transition-colors">Semua <i class="fas fa-arrow-right ml-1"></i></a>
                    </div>

                    <div class="clean-scroll flex-1 min-h-0 overflow-y-auto p-3 sm:p-4 space-y-2">
                        @forelse($notifList as $notif)
                            @php $isNew = !($notif['is_read'] ?? true); @endphp
                            <a href="{{ $notifikasiRoute }}" class="group block rounded-[1rem] sm:rounded-[1.2rem] p-3 sm:p-4 transition-all duration-300 hover:bg-slate-50 border border-transparent hover:border-slate-100 {{ $isNew ? 'bg-amber-50/30 border-amber-100' : '' }}">
                                <div class="flex gap-2.5 sm:gap-3">
                                    <div class="mt-1 flex h-1.5 w-1.5 shrink-0 rounded-full {{ $isNew ? 'bg-amber-500 animate-pulse' : 'bg-slate-200' }}"></div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-[11px] sm:text-xs font-black text-slate-800 line-clamp-1 mb-0.5 sm:mb-1 group-hover:text-amber-600 transition-colors">{{ $notif['judul'] ?? 'Pemberitahuan Baru' }}</h4>
                                        <p class="text-[10px] sm:text-[11px] font-semibold text-slate-500 line-clamp-2 leading-relaxed">
                                            {{ $notif['pesan'] ?? '-' }}
                                        </p>
                                        <p class="text-[8px] sm:text-[9px] font-black text-slate-400 mt-1.5 sm:mt-2 uppercase tracking-widest">
                                            <i class="far fa-clock mr-0.5"></i> {{ $notif['waktu'] ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="flex flex-col items-center justify-center h-full text-center opacity-70 p-6">
                                <i class="fas fa-inbox text-2xl sm:text-3xl text-slate-300 mb-2 sm:mb-3"></i>
                                <h4 class="text-[11px] sm:text-xs font-black text-slate-600">Kosong</h4>
                                <p class="text-[10px] sm:text-[11px] font-semibold text-slate-400 mt-1">Belum ada pemberitahuan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const clockEl = document.getElementById('realtime-clock');
    const greetingEl = document.getElementById('dynamic-greeting');
    const emojiEl = document.getElementById('dynamic-emoji');

    function updateClockAndGreeting() {
        const now = new Date();
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        const dayName = days[now.getDay()];
        const date = String(now.getDate()).padStart(2, '0');
        const month = months[now.getMonth()];
        const year = now.getFullYear();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        if (clockEl) {
            clockEl.textContent = `${dayName}, ${date} ${month} ${year} • ${hours}:${minutes} WIB`;
        }

        const h = now.getHours();
        let greeting = 'Selamat Malam';
        let emoji = '🌙';

        if (h >= 5 && h < 11) { greeting = 'Selamat Pagi'; emoji = '🌅'; }
        else if (h >= 11 && h < 15) { greeting = 'Selamat Siang'; emoji = '☀️'; }
        else if (h >= 15 && h < 18) { greeting = 'Selamat Sore'; emoji = '🌇'; }

        if (greetingEl) greetingEl.textContent = greeting;
        if (emojiEl) emojiEl.textContent = emoji;
    }

    updateClockAndGreeting();
    setInterval(updateClockAndGreeting, 60000);
});
</script>
@endpush