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

    // Ambil Collection Data (Catatan: Idealnya ini di-passing dari Controller)
    $anakList = collect($dataAnak ?? []);
    $remajaList = collect($dataRemaja ?? []);
    $lansiaList = collect($dataLansia ?? []);

    $jadwalList = collect($jadwalTerdekat ?? []);
    $jadwalUtama = $jadwalList->first();

    $notifList = collect($notifikasiTerbaru ?? [])->take(5);
    $jumlahNotif = $totalNotifikasiBelumDibaca ?? $notifList->where('is_read', false)->count();

    $totalSasaran = $anakList->count() + $remajaList->count() + $lansiaList->count();

    // Routes
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
    $getName = fn($item) => data_get($item, 'nama_lengkap') ?: data_get($item, 'nama_remaja') ?: data_get($item, 'nama_balita') ?: data_get($item, 'nama_lansia') ?: data_get($item, 'nama') ?: 'Tanpa Nama';
    $getDob = fn($item) => data_get($item, 'tanggal_lahir') ?: data_get($item, 'tgl_lahir');
    $getGender = fn($item) => data_get($item, 'jenis_kelamin') ?: data_get($item, 'jk');

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
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200', 'hover' => 'hover:border-emerald-400'],
        'rose'    => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-rose-200', 'hover' => 'hover:border-rose-400'],
        'sky'     => ['bg' => 'bg-sky-50', 'text' => 'text-sky-600', 'border' => 'border-sky-200', 'hover' => 'hover:border-sky-400'],
        'amber'   => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-200', 'hover' => 'hover:border-amber-400'],
    ];

    $statCards = [
        ['label' => 'Keluarga', 'value' => $totalSasaran, 'icon' => 'fa-users', 'tone' => 'emerald', 'desc' => 'Terdaftar di NIK'],
        ['label' => 'Notifikasi', 'value' => $jumlahNotif, 'icon' => 'fa-bell', 'tone' => 'amber', 'desc' => 'Belum dibaca'],
        ['label' => 'Jadwal', 'value' => $jadwalList->count(), 'icon' => 'fa-calendar-days', 'tone' => 'sky', 'desc' => 'Agenda mendatang'],
        ['label' => 'Riwayat', 'value' => $healthItems->count(), 'icon' => 'fa-book-medical', 'tone' => 'rose', 'desc' => 'Buku kesehatan'],
    ];
@endphp

@push('styles')
<style>
    body {
        background-color: #f8fafc;
        background-attachment: fixed;
    }

    .animate-pop-in {
        animation: popIn .6s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }

    @keyframes popIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .hero-grid {
        background-image: radial-gradient(rgba(255,255,255,.3) 1px, transparent 1px);
        background-size: 24px 24px;
    }

    .btn-action {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .btn-action:active { 
        transform: scale(0.96); 
    }

    .glass-panel {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .clean-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px -10px rgba(0,0,0,0.05);
    }

    .clean-scroll::-webkit-scrollbar { width: 5px; }
    .clean-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .clean-scroll::-webkit-scrollbar-track { background: transparent; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto animate-pop-in pb-24 px-4 sm:px-6 lg:px-8 mt-6 space-y-6 sm:space-y-8">

    {{-- 1. HERO SECTION --}}
    <section class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-3xl p-6 sm:p-10 relative overflow-hidden shadow-lg border border-teal-500/30">
        <div class="hero-grid absolute inset-0 pointer-events-none"></div>
        <div class="absolute -right-20 -top-20 w-72 h-72 bg-white/10 blur-[80px] rounded-full pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex flex-col gap-4">
                {{-- REALTIME CLOCK WIDGET --}}
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full glass-panel px-3 py-1.5 text-xs font-bold tracking-wide text-white shadow-sm">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-300 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                        </span>
                        Portal Aktif
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full glass-panel px-3 py-1.5 text-xs font-bold tracking-wide text-white shadow-sm font-mono">
                        <i class="far fa-clock"></i>
                        <span id="realtime-clock">Memuat waktu...</span>
                    </span>
                </div>

                <div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                        <span id="dynamic-greeting">Halo</span>, {{ ucwords($firstName) }}! 
                        <span id="dynamic-emoji" class="inline-block hover:scale-125 transition-transform origin-bottom-right cursor-default">👋</span>
                    </h1>
                    <p class="text-emerald-50 text-sm sm:text-base mt-2 font-medium leading-relaxed max-w-2xl opacity-90">
                        Pantau agenda kegiatan, rekam medis, dan pemberitahuan penting Posyandu keluarga Anda di satu tempat terpadu.
                    </p>
                </div>
            </div>
            
            <div class="shrink-0 pt-2 md:pt-0">
                <a href="{{ $profileRoute }}" class="btn-action inline-flex items-center justify-center glass-panel hover:bg-white/20 text-white px-6 py-3.5 rounded-xl text-sm font-bold tracking-wide w-full sm:w-auto">
                    <i class="fas fa-user-gear mr-2"></i> Pengaturan Profil
                </a>
            </div>
        </div>
    </section>

    {{-- ALERT --}}
    @if(isset($pesanError) && $pesanError)
        <div class="rounded-2xl border-l-4 border-l-amber-500 border-y border-r border-amber-200 bg-amber-50 p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex gap-4 items-center">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-amber-500 shadow-sm">
                    <i class="fas fa-exclamation-triangle animate-pulse text-lg"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-amber-900">Akses Terbatas</h3>
                    <p class="text-xs font-medium text-amber-700 mt-1">{{ $pesanError }}</p>
                </div>
            </div>
            <a href="{{ $profileRoute }}" class="btn-action shrink-0 bg-amber-500 px-6 py-2.5 rounded-lg text-xs font-bold tracking-wide text-white hover:bg-amber-600 text-center">
                Lengkapi Profil
            </a>
        </div>
    @endif

    {{-- 2. METRIK UTAMA --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($statCards as $card)
            @php $tone = $toneMap[$card['tone']]; @endphp
            <div class="clean-card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:-translate-y-1 transition-transform duration-300">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">{{ $card['label'] }}</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-extrabold text-slate-800 leading-none">{{ $card['value'] }}</h3>
                    </div>
                    <p class="text-[11px] font-medium text-slate-400 mt-1 truncate">{{ $card['desc'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-2xl {{ $tone['bg'] }} {{ $tone['text'] }} flex items-center justify-center text-xl shrink-0 shadow-inner">
                    <i class="fas {{ $card['icon'] }}"></i>
                </div>
            </div>
        @endforeach
    </div>

    {{-- 3. KONTEN UTAMA --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 sm:gap-8">
        
        {{-- KOLOM KIRI --}}
        <div class="xl:col-span-8 flex flex-col gap-6 sm:gap-8">
            
            {{-- WIDGET: Anggota Keluarga --}}
            <div class="clean-card flex flex-col overflow-hidden">
                <div class="flex justify-between items-center bg-slate-50/80 p-5 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
                        <i class="fas fa-users text-emerald-500 mr-2"></i> Data Keluarga
                    </h2>
                    <a href="{{ $monitoringRoute }}" class="text-xs font-bold text-slate-500 hover:text-emerald-600 transition-colors">
                        Kelola <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>

                <div class="p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse($healthItems as $item)
                            @php $tone = $toneMap[$item['tone']]; @endphp
                            <a href="{{ $item['href'] }}" class="group flex items-center justify-between gap-4 rounded-xl bg-white hover:bg-slate-50 border border-slate-100 hover:border-slate-300 p-4 transition-all duration-300 shadow-sm hover:shadow-md">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full {{ $tone['bg'] }} {{ $tone['text'] }} text-lg">
                                        <i class="fas {{ $item['icon'] }}"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-[10px] font-bold uppercase tracking-wider {{ $tone['text'] }} border {{ $tone['border'] }} px-2 py-0.5 rounded-md bg-white">{{ $item['type'] }}</span>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-800 truncate">{{ $item['name'] }}</h4>
                                        <p class="text-xs font-medium text-slate-500 truncate mt-0.5">{{ $item['meta'] }}</p>
                                    </div>
                                </div>
                                <div class="shrink-0 text-slate-300 group-hover:text-slate-600 transition-colors">
                                    <i class="fas fa-chevron-right text-sm"></i>
                                </div>
                            </a>
                        @empty
                            <div class="col-span-full flex flex-col items-center justify-center py-10 text-center">
                                <div class="w-16 h-16 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center mb-4">
                                    <i class="fas fa-users-slash text-2xl text-slate-400"></i>
                                </div>
                                <h4 class="text-sm font-bold text-slate-800">Keluarga Belum Terkoneksi</h4>
                                <p class="text-xs font-medium text-slate-500 mt-2 max-w-sm mx-auto">Pastikan NIK keluarga di menu Pengaturan sudah tersinkronisasi.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- WIDGET: Agenda Posyandu --}}
            <div class="clean-card flex flex-col overflow-hidden">
                <div class="flex justify-between items-center bg-slate-50/80 p-5 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
                        <i class="fas fa-calendar-alt text-sky-500 mr-2"></i> Agenda Terdekat
                    </h2>
                    <a href="{{ $jadwalRoute }}" class="text-xs font-bold text-slate-500 hover:text-sky-600 transition-colors">
                        Semua Jadwal <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>

                <div class="p-5">
                    @if($jadwalUtama)
                        <div class="flex flex-col sm:flex-row gap-5 items-center rounded-2xl bg-gradient-to-r from-sky-50 to-white border border-sky-100 p-5 group hover:shadow-md transition-all">
                            
                            <div class="flex gap-5 items-center w-full sm:w-auto flex-1 min-w-0">
                                <div class="flex flex-col items-center justify-center shrink-0 w-20 h-20 rounded-2xl bg-white border border-sky-200 shadow-sm group-hover:scale-105 transition-transform">
                                    <span class="text-xs font-bold text-sky-600 uppercase tracking-wider">{{ $formatDate($jadwalUtama->tanggal, 'M') }}</span>
                                    <span class="text-3xl font-black text-slate-800 leading-none mt-1">{{ $formatDate($jadwalUtama->tanggal, 'd') }}</span>
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border border-sky-200 bg-sky-100 text-sky-700">
                                            {{ ucwords(str_replace('_', ' ', $jadwalUtama->target_peserta ?? 'Semua Sasaran')) }}
                                        </span>
                                        <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider border border-slate-200 bg-white text-slate-600 shadow-sm">
                                            <i class="far fa-clock mr-1"></i> {{ $formatTime($jadwalUtama->waktu_mulai) }} WIB
                                        </span>
                                    </div>
                                    <h4 class="text-base sm:text-lg font-bold text-slate-800 leading-tight mb-1 truncate" title="{{ $jadwalUtama->judul }}">{{ $jadwalUtama->judul }}</h4>
                                    <p class="text-xs font-medium text-slate-500 truncate">
                                        <i class="fas fa-map-marker-alt text-slate-400 mr-1.5"></i> {{ $jadwalUtama->lokasi ?? 'Lokasi belum ditentukan' }}
                                    </p>
                                </div>
                            </div>
                            
                            <a href="{{ route('user.jadwal.show', $jadwalUtama->id) }}" class="btn-action shrink-0 w-full sm:w-auto bg-white border-2 border-sky-500 hover:bg-sky-50 text-sky-600 px-6 py-3 rounded-xl text-xs font-bold uppercase tracking-wider shadow-sm text-center">
                                Buka Detail
                            </a>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-10 text-center">
                            <i class="far fa-calendar-circle-plus text-4xl text-slate-300 mb-4"></i>
                            <h4 class="text-sm font-bold text-slate-700">Tidak Ada Jadwal</h4>
                            <p class="text-xs font-medium text-slate-500 mt-1">Belum ada kegiatan Posyandu terdekat untuk keluarga Anda.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN: Notifikasi --}}
        <div class="xl:col-span-4 relative h-[450px] xl:h-auto">
            <div class="h-full xl:absolute xl:inset-0">
                <div class="clean-card flex flex-col h-full overflow-hidden">
                    
                    <div class="flex justify-between items-center bg-slate-50/80 p-5 border-b border-slate-100 shrink-0">
                        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
                            <i class="fas fa-bell text-amber-500 mr-2"></i> Notifikasi
                        </h2>
                        <a href="{{ $notifikasiRoute }}" class="text-xs font-bold text-slate-500 hover:text-amber-600 transition-colors">
                            Semua <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>

                    <div class="clean-scroll flex-1 min-h-0 overflow-y-auto p-4 space-y-3">
                        @forelse($notifList as $notif)
                            @php $isNew = !($notif['is_read'] ?? true); @endphp
                            <a href="{{ $notifikasiRoute }}" class="group block rounded-xl p-4 transition-all duration-300 hover:bg-slate-50 border {{ $isNew ? 'bg-amber-50/40 border-amber-200' : 'bg-white border-slate-100 hover:border-slate-300' }}">
                                <div class="flex gap-3">
                                    <div class="mt-1.5 flex h-2 w-2 shrink-0 rounded-full {{ $isNew ? 'bg-amber-500 animate-pulse' : 'bg-slate-300' }}"></div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-xs font-bold text-slate-800 line-clamp-1 mb-1 group-hover:text-amber-600 transition-colors">{{ $notif['judul'] ?? 'Pemberitahuan Baru' }}</h4>
                                        <p class="text-[11px] font-medium text-slate-500 line-clamp-2 leading-relaxed">
                                            {{ $notif['pesan'] ?? '-' }}
                                        </p>
                                        <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase tracking-wider flex items-center">
                                            <i class="far fa-clock mr-1.5"></i> {{ $notif['waktu'] ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="flex flex-col items-center justify-center h-full text-center p-6">
                                <i class="fas fa-inbox text-4xl text-slate-300 mb-4"></i>
                                <h4 class="text-sm font-bold text-slate-700">Kotak Masuk Kosong</h4>
                                <p class="text-xs font-medium text-slate-500 mt-1">Belum ada pemberitahuan baru.</p>
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
        
        // Fix: Real-time update with seconds
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        if (clockEl) {
            clockEl.textContent = `${dayName}, ${date} ${month} ${year} • ${hours}:${minutes}:${seconds} WIB`;
        }

        // Greeting Logic
        const h = now.getHours();
        let greeting = 'Selamat Malam';
        let emoji = '🌙';

        if (h >= 4 && h < 11) { greeting = 'Selamat Pagi'; emoji = '🌅'; }
        else if (h >= 11 && h < 15) { greeting = 'Selamat Siang'; emoji = '☀️'; }
        else if (h >= 15 && h < 18) { greeting = 'Selamat Sore'; emoji = '🌇'; }

        if (greetingEl && greetingEl.textContent !== greeting) greetingEl.textContent = greeting;
        if (emojiEl && emojiEl.textContent !== emoji) emojiEl.textContent = emoji;
    }

    // Initialize immediately, then set interval to 1000ms (1 second) instead of 60000ms
    updateClockAndGreeting();
    setInterval(updateClockAndGreeting, 1000);
});
</script>
@endpush