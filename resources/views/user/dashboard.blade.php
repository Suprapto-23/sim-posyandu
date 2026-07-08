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
    
    $ageText = function ($dob) {
        if (blank($dob)) return '-';
        try {
            $diff = Carbon::parse($dob)->diff(now());
            return $diff->y > 0 ? $diff->y . ' thn' : $diff->m . ' bln';
        } catch (\Throwable $e) { return '-'; }
    };

    $healthItems = collect();

    foreach ($anakList as $anak) {
        $healthItems->push(['type' => 'Balita', 'name' => $getName($anak), 'meta' => $ageText($getDob($anak)), 'href' => $balitaShowRoute(data_get($anak, 'id')), 'icon' => 'fa-baby', 'tone' => 'blue']);
    }
    foreach ($remajaList as $remaja) {
        $healthItems->push(['type' => 'Remaja', 'name' => $getName($remaja), 'meta' => $ageText($getDob($remaja)), 'href' => $remajaShowRoute(data_get($remaja, 'id')), 'icon' => 'fa-child', 'tone' => 'emerald']);
    }
    foreach ($lansiaList as $lansia) {
        $healthItems->push(['type' => 'Lansia', 'name' => $getName($lansia), 'meta' => $ageText($getDob($lansia)), 'href' => $lansiaShowRoute(data_get($lansia, 'id')), 'icon' => 'fa-person-cane', 'tone' => 'amber']);
    }

    $statCards = [
        ['id' => 'stat-keluarga', 'label' => 'Total Sasaran', 'value' => $totalSasaran, 'icon' => 'fa-users', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50', 'label_color' => 'text-blue-600'],
        ['id' => 'stat-riwayat', 'label' => 'Rekam Medis', 'value' => $healthItems->count(), 'icon' => 'fa-notes-medical', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50', 'label_color' => 'text-emerald-600'],
        ['id' => 'stat-jadwal', 'label' => 'Agenda', 'value' => $jadwalList->count(), 'icon' => 'fa-calendar-check', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50', 'label_color' => 'text-amber-600'],
        ['id' => 'stat-notif', 'label' => 'Pesan Baru', 'value' => $jumlahNotif, 'icon' => 'fa-envelope', 'color' => 'text-rose-500', 'bg' => 'bg-rose-50', 'label_color' => 'text-rose-600'],
    ];

    $toneMap = [
        'blue'    => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
        'amber'   => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
        'rose'    => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600'],
    ];
@endphp

@push('styles')
<style>
    body {
        background-color: #f8fafc; 
    }

    .animate-pop-up { animation: popUp .5s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Gradient Banner meniru Gambar 1 (Teal/Emerald murni) dan bentuk PILL */
    .hero-banner {
        background: linear-gradient(to right, #10b981 0%, #0d9488 100%);
        position: relative;
        overflow: hidden;
        /* Menggunakan border-radius 3rem/48px agar bentuknya seperti kapsul/pill */
        border-radius: 3rem; 
        box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.3);
    }
    
    /* Lingkaran stat transparan di sebelah kanan */
    .banner-circle {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 50%;
    }

    .glass-badge {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .premium-card {
        background: #ffffff;
        border-radius: 24px; 
        box-shadow: 0 4px 20px -4px rgba(0, 0, 0, 0.04);
        transition: box-shadow 0.3s ease, transform 0.3s ease;
    }
    .premium-card:hover { 
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08); 
    }

    .value-update { animation: highlightUpdate 1s ease; }
    @keyframes highlightUpdate {
        0% { color: #10b981; transform: scale(1.1); }
        100% { color: inherit; transform: scale(1); }
    }

    .hide-scroll::-webkit-scrollbar { width: 4px; }
    .hide-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .hide-scroll::-webkit-scrollbar-track { background: transparent; }
</style>
@endpush

@section('content')
<div class="max-w-[1280px] mx-auto animate-pop-up pb-24 px-4 sm:px-6 lg:px-8 mt-6">

    {{-- 1. HERO BANNER (Desain Kapsul & Hijau Gambar 1) --}}
    <div class="hero-banner pt-10 pb-12 px-8 sm:px-14 mb-8 flex flex-col md:flex-row justify-between items-center gap-6">
        
        <div class="relative z-10 flex flex-col justify-center max-w-2xl w-full">
            {{-- Badges Kiri Atas (Dengan titik kuning seperti di gambar) --}}
            <div class="flex flex-wrap items-center gap-3 mb-5">
                <span class="glass-badge px-4 py-1.5 rounded-full flex items-center gap-2 text-[10px] font-bold text-white uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-yellow-300"></span> Mode Warga
                </span>
                <span class="glass-badge px-4 py-1.5 rounded-full flex items-center gap-2 text-[10px] font-bold text-white tracking-wider" id="realtime-date">
                    <i class="far fa-clock text-white/80"></i> Memuat waktu...
                </span>
            </div>

            {{-- Greeting & Subtext --}}
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white tracking-tight mb-3">
                <span id="dynamic-greeting" class="font-normal opacity-90">Selamat Siang,</span> {{ ucwords($firstName) }}!
            </h1>
            <p class="text-emerald-50 opacity-90 text-sm sm:text-base max-w-xl leading-relaxed">
                Pusat informasi dan kendali Posyandu keluarga Anda. Pantau jadwal, riwayat imunisasi, dan pesan dari bidan secara real-time.
            </p>
        </div>

        {{-- Lingkaran Bulat di Kanan (Menduplikasi gaya "TOTAL AGENDA" dari Gambar 1) --}}
        <div class="hidden md:flex banner-circle w-32 h-32 flex-col items-center justify-center shrink-0 shadow-sm">
            <span class="text-[9px] font-bold text-white uppercase tracking-widest text-center opacity-90 mb-1">Total Sasaran</span>
            <span class="text-4xl font-extrabold text-white leading-none">{{ $totalSasaran }}</span>
        </div>
        
    </div>

    {{-- 2. STATS CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6 mb-8">
        @foreach($statCards as $card)
            <div class="premium-card p-6 flex flex-col justify-between group cursor-default">
                <div class="flex h-14 w-14 rounded-[16px] {{ $card['bg'] }} {{ $card['color'] }} items-center justify-center text-2xl mb-5 transition-transform group-hover:scale-110 duration-300">
                    <i class="fas {{ $card['icon'] }}"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold {{ $card['label_color'] }} uppercase tracking-widest mb-1 truncate">{{ $card['label'] }}</p>
                    <h3 class="text-4xl font-extrabold text-slate-800 leading-none tracking-tight" id="{{ $card['id'] }}">{{ $card['value'] }}</h3>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ALERT ERROR --}}
    @if(isset($pesanError) && $pesanError)
        <div class="mb-8 rounded-[24px] bg-rose-50/80 border border-rose-100 p-5 flex items-center gap-4 shadow-sm">
            <div class="h-12 w-12 rounded-full bg-white flex items-center justify-center text-rose-500 shrink-0 shadow-sm"><i class="fas fa-exclamation-triangle text-xl"></i></div>
            <div class="flex-1"><h3 class="text-sm font-bold text-rose-900">Pemberitahuan Penting</h3><p class="text-xs font-medium text-rose-700 mt-1">{{ $pesanError }}</p></div>
        </div>
    @endif

    {{-- 3. MAIN CONTENT GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8">
        
        {{-- KOLOM KIRI (Agenda & Keluarga) --}}
        <div class="lg:col-span-8 flex flex-col gap-6 sm:gap-8">
            
            {{-- WIDGET AGENDA TERDEKAT --}}
            <div class="premium-card p-6 sm:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-base font-bold text-slate-800 tracking-wide flex items-center gap-3">
                        <span class="flex h-8 w-8 rounded-lg bg-emerald-50 items-center justify-center text-emerald-500"><i class="fas fa-calendar-alt"></i></span>
                        Agenda Terdekat
                    </h2>
                    <a href="{{ $jadwalRoute }}" class="text-[11px] font-bold uppercase bg-slate-50 hover:bg-emerald-50 text-slate-500 hover:text-emerald-600 px-4 py-2 rounded-xl transition-colors tracking-widest">Lihat Semua</a>
                </div>
                
                <div>
                    @if($jadwalUtama)
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 bg-white border border-slate-100 rounded-[20px] p-5 hover:border-emerald-200 transition-all shadow-[0_2px_12px_rgba(0,0,0,0.02)] group">
                            
                            {{-- Date Box --}}
                            <div class="flex flex-col items-center justify-center w-[84px] h-[84px] rounded-[18px] bg-emerald-50 text-emerald-600 shrink-0 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                                <span class="text-[10px] font-bold uppercase tracking-widest">{{ $formatDate($jadwalUtama->tanggal, 'M Y') }}</span>
                                <span class="text-3xl font-extrabold leading-none mt-1">{{ $formatDate($jadwalUtama->tanggal, 'd') }}</span>
                            </div>
                            
                            {{-- Info --}}
                            <div class="flex-1 min-w-0 w-full">
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <span class="px-3 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-emerald-50 text-emerald-600">
                                        {{ ucwords(str_replace('_', ' ', $jadwalUtama->target_peserta ?? 'Semua Sasaran')) }}
                                    </span>
                                    <span class="px-3 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-slate-100 text-slate-600">
                                        <i class="far fa-clock mr-1"></i> {{ $formatTime($jadwalUtama->waktu_mulai) }} WIB
                                    </span>
                                </div>
                                <h3 class="text-lg font-bold text-slate-800 truncate" title="{{ $jadwalUtama->judul }}">{{ $jadwalUtama->judul }}</h3>
                                <p class="text-sm font-medium text-slate-500 mt-1.5 truncate">
                                    <i class="fas fa-map-pin text-slate-300 mr-1.5"></i> {{ $jadwalUtama->lokasi ?? 'Lokasi belum ditentukan' }}
                                </p>
                            </div>
                            
                            {{-- Action --}}
                            <div class="shrink-0 w-full sm:w-auto mt-3 sm:mt-0">
                                <a href="{{ route('user.jadwal.show', $jadwalUtama->id) }}" class="flex items-center justify-center w-full sm:w-auto px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold tracking-wide rounded-xl shadow-sm transition-colors">
                                    Detail <i class="fas fa-arrow-right ml-2 text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="py-10 text-center bg-slate-50/50 rounded-[20px] border border-dashed border-slate-200">
                            <i class="fa-regular fa-calendar-xmark text-4xl text-slate-300 mb-3"></i>
                            <h3 class="text-sm font-bold text-slate-700">Tidak ada jadwal</h3>
                            <p class="text-xs text-slate-500 mt-1">Belum ada agenda posyandu dalam waktu dekat.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- WIDGET KELUARGA --}}
            <div class="premium-card p-6 sm:p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-base font-bold text-slate-800 tracking-wide flex items-center gap-3">
                        <span class="flex h-8 w-8 rounded-lg bg-blue-50 items-center justify-center text-blue-500"><i class="fas fa-users"></i></span>
                        Profil Medis Keluarga
                    </h2>
                    <a href="{{ $monitoringRoute }}" class="text-[11px] font-bold uppercase bg-slate-50 hover:bg-blue-50 text-slate-500 hover:text-blue-600 px-4 py-2 rounded-xl transition-colors tracking-widest">Buku Rekam</a>
                </div>
                
                <div>
                    @if($healthItems->isEmpty())
                        <div class="py-10 text-center bg-slate-50/50 rounded-[20px] border border-dashed border-slate-200">
                            <i class="fas fa-user-plus text-4xl text-slate-300 mb-3"></i>
                            <h3 class="text-sm font-bold text-slate-700">Belum Ada Data</h3>
                            <a href="{{ $profileRoute }}" class="text-xs font-bold text-blue-600 mt-2 inline-block hover:underline">Sinkronisasi NIK Sekarang</a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($healthItems->take(4) as $item)
                                @php $tone = $toneMap[$item['tone']]; @endphp
                                <a href="{{ $item['href'] }}" class="flex items-center gap-4 p-4 rounded-[20px] border border-slate-100 hover:border-slate-300 hover:shadow-md transition-all group bg-white">
                                    <div class="h-14 w-14 rounded-2xl {{ $tone['bg'] }} {{ $tone['text'] }} flex items-center justify-center text-2xl shrink-0">
                                        <i class="fas {{ $item['icon'] }}"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-sm font-bold text-slate-800 truncate">{{ $item['name'] }}</h4>
                                        <p class="text-xs font-medium text-slate-500 mt-1 truncate">{{ $item['meta'] }}</p>
                                    </div>
                                    <span class="text-[10px] font-bold uppercase tracking-widest {{ $tone['text'] }} {{ $tone['bg'] }} px-2.5 py-1.5 rounded-lg">{{ $item['type'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN (Pesan Bidan) --}}
        <div class="lg:col-span-4 h-full relative">
            <div class="premium-card p-6 flex flex-col h-full lg:absolute lg:inset-0">
                <div class="flex items-center justify-between mb-6 shrink-0">
                    <h2 class="text-base font-bold text-slate-800 tracking-wide flex items-center gap-3">
                        <span class="flex h-8 w-8 rounded-lg bg-rose-50 items-center justify-center text-rose-500"><i class="fas fa-envelope-open-text"></i></span>
                        Pesan Bidan
                        @if($jumlahNotif > 0)
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-white text-[10px] font-bold" id="notif-badge">{{ $jumlahNotif }}</span>
                        @endif
                    </h2>
                    <a href="{{ $notifikasiRoute }}" class="text-[11px] font-bold uppercase text-rose-500 hover:text-rose-700 transition-colors tracking-widest">Semua</a>
                </div>

                <div class="flex-1 overflow-y-auto hide-scroll space-y-3 pr-2">
                    @forelse($notifList as $notif)
                        @php $isNew = !($notif['is_read'] ?? true); @endphp
                        <a href="{{ $notifikasiRoute }}" class="block p-5 rounded-[20px] transition-all {{ $isNew ? 'bg-rose-50/50 border border-rose-100' : 'bg-slate-50/50 border border-transparent hover:bg-slate-100' }}">
                            <div class="flex gap-4">
                                <div class="mt-1 shrink-0">
                                    <div class="h-2.5 w-2.5 rounded-full {{ $isNew ? 'bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.5)]' : 'bg-slate-300' }}"></div>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-slate-800 truncate">{{ $notif['judul'] ?? 'Pemberitahuan' }}</h4>
                                    <p class="text-xs font-medium text-slate-500 line-clamp-2 mt-1.5 leading-relaxed">{{ $notif['pesan'] ?? '-' }}</p>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-3 block flex items-center gap-1.5">
                                        <i class="far fa-clock"></i> {{ $notif['waktu'] ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="py-12 text-center flex flex-col items-center justify-center h-full">
                            <i class="fas fa-check-circle text-5xl text-emerald-200 mb-4"></i>
                            <h4 class="text-sm font-bold text-slate-600">Semua Terbaca</h4>
                            <p class="text-xs text-slate-400 mt-1">Belum ada pesan baru untuk Anda.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. DATE & GREETING
    const dateEl = document.getElementById('realtime-date');
    const greetingEl = document.getElementById('dynamic-greeting');

    function updateTimeInfo() {
        const now = new Date();
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        if (dateEl) {
            // Mempertahankan ikon jam di depan teks
            dateEl.innerHTML = `<i class="far fa-clock text-white/80"></i> ${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()} • ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')} WIB`;
        }

        const h = now.getHours();
        let greeting = 'Selamat Malam,';
        if (h >= 4 && h < 11) greeting = 'Selamat Pagi,';
        else if (h >= 11 && h < 15) greeting = 'Selamat Siang,';
        else if (h >= 15 && h < 18) greeting = 'Selamat Sore,';

        if (greetingEl && greetingEl.textContent !== greeting) greetingEl.textContent = greeting;
    }
    
    updateTimeInfo();
    setInterval(updateTimeInfo, 60000); 

    // 2. SAFE AJAX POLLING
    const statsUrl = '{{ Route::has("user.dashboard.stats") ? route("user.dashboard.stats") : "" }}'; 
    const statKeluarga = document.getElementById('stat-keluarga');
    const statNotif = document.getElementById('stat-notif');
    const statJadwal = document.getElementById('stat-jadwal');
    
    function fetchDashboardStatsSafe() {
        if (!statsUrl || statsUrl.trim() === '') return;

        fetch(statsUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.ok ? res.json() : Promise.reject('Network err'))
        .then(data => {
            if (data.status === 'success') {
                updateValueWithAnimation(statKeluarga, data.total_sasaran);
                updateValueWithAnimation(statNotif, data.unread_count);
                updateValueWithAnimation(statJadwal, data.total_jadwal);
                
                const badge = document.getElementById('notif-badge');
                if(badge && data.unread_count !== undefined) {
                    badge.textContent = data.unread_count;
                    badge.style.display = data.unread_count > 0 ? 'flex' : 'none';
                }
            }
        })
        .catch(() => {});
    }

    function updateValueWithAnimation(element, newValue) {
        if (element && element.innerText != newValue && newValue !== undefined) {
            element.innerText = newValue;
            element.classList.remove('value-update');
            void element.offsetWidth; 
            element.classList.add('value-update');
        }
    }

    if(statsUrl) {
        setInterval(fetchDashboardStatsSafe, 15000);
    }
});
</script>
@endpush