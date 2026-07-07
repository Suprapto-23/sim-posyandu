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
        $healthItems->push(['type' => 'Balita', 'name' => $getName($anak), 'meta' => $ageText($getDob($anak)), 'href' => $balitaShowRoute(data_get($anak, 'id')), 'icon' => 'fa-baby', 'tone' => 'rose']);
    }
    foreach ($remajaList as $remaja) {
        $healthItems->push(['type' => 'Remaja', 'name' => $getName($remaja), 'meta' => $ageText($getDob($remaja)), 'href' => $remajaShowRoute(data_get($remaja, 'id')), 'icon' => 'fa-child', 'tone' => 'sky']);
    }
    foreach ($lansiaList as $lansia) {
        $healthItems->push(['type' => 'Lansia', 'name' => $getName($lansia), 'meta' => $ageText($getDob($lansia)), 'href' => $lansiaShowRoute(data_get($lansia, 'id')), 'icon' => 'fa-person-cane', 'tone' => 'amber']);
    }

    $statCards = [
        ['id' => 'stat-keluarga', 'label' => 'Keluarga', 'value' => $totalSasaran, 'icon' => 'fa-users', 'color' => 'text-emerald-500', 'bg' => 'bg-emerald-50'],
        ['id' => 'stat-notif', 'label' => 'Pesan Baru', 'value' => $jumlahNotif, 'icon' => 'fa-bell', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50'],
        ['id' => 'stat-jadwal', 'label' => 'Agenda', 'value' => $jadwalList->count(), 'icon' => 'fa-calendar-day', 'color' => 'text-sky-500', 'bg' => 'bg-sky-50'],
        ['id' => 'stat-riwayat', 'label' => 'Rekam Medis', 'value' => $healthItems->count(), 'icon' => 'fa-notes-medical', 'color' => 'text-rose-500', 'bg' => 'bg-rose-50'],
    ];

    $toneMap = [
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200'],
        'rose'    => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-rose-200'],
        'sky'     => ['bg' => 'bg-sky-50', 'text' => 'text-sky-600', 'border' => 'border-sky-200'],
        'amber'   => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-200'],
    ];
@endphp

@push('styles')
<style>
    body {
        background-color: #f8fafc;
        background-image: radial-gradient(at 100% 0%, hsla(160, 100%, 96%, 0.5) 0px, transparent 50%),
                          radial-gradient(at 0% 100%, hsla(190, 100%, 96%, 0.5) 0px, transparent 50%);
        background-attachment: fixed;
    }

    .animate-pop-up { animation: popUp .5s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popUp {
        from { opacity: 0; transform: translateY(15px) scale(0.99); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Premium Green Banner */
    .hero-banner {
        background: linear-gradient(135deg, #059669 0%, #0f766e 100%);
        position: relative;
        overflow: hidden;
    }
    .hero-banner::before {
        content: ''; position: absolute; width: 500px; height: 500px;
        background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 60%);
        top: -150px; right: -100px; border-radius: 50%; pointer-events: none;
    }
    .hero-banner::after {
        content: ''; position: absolute; width: 400px; height: 400px;
        background: radial-gradient(circle, rgba(16,185,129,0.15) 0%, transparent 60%);
        bottom: -200px; left: -100px; border-radius: 50%; pointer-events: none;
    }

    .glass-badge {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .premium-card {
        background: #ffffff;
        border-radius: 1.5rem;
        border: 1px solid rgba(226, 232, 240, 0.7);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
        transition: box-shadow 0.3s ease;
    }
    .premium-card:hover { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.06), 0 4px 6px -2px rgba(0, 0, 0, 0.03); }

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
<div class="max-w-[1280px] mx-auto animate-pop-up pb-24 px-4 sm:px-6 lg:px-8 mt-4">

    {{-- 1. HERO BANNER --}}
    <div class="hero-banner rounded-[2rem] pt-8 pb-16 px-6 sm:px-10 shadow-md">
        <div class="relative z-10 flex flex-col justify-center">
            <div class="flex items-center gap-3 mb-4">
                <span class="glass-badge px-3 py-1.5 rounded-full flex items-center gap-2 text-[10px] font-bold text-emerald-50 uppercase tracking-widest shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span> Sinkron
                </span>
                {{-- FIX: Hapus /90 dan ganti ke text-emerald-50 murni dengan opacity terpisah --}}
                <span class="text-emerald-50 opacity-90 text-xs font-semibold tracking-wide" id="realtime-date">Memuat waktu...</span>
            </div>
            {{-- FIX: Turunkan dari font-extrabold/font-black menjadi font-bold saja agar lebih elegan --}}
            <h1 class="text-3xl sm:text-4xl font-bold text-white tracking-tight">
                <span id="dynamic-greeting" class="font-normal opacity-90">Halo,</span> {{ ucwords($firstName) }}! 👋
            </h1>
            <p class="text-emerald-50 opacity-90 text-sm mt-2 max-w-xl leading-relaxed">
                Pantau jadwal kegiatan Posyandu dan kesehatan keluarga Anda dengan mudah di satu tempat.
            </p>
        </div>
    </div>

    {{-- 2. OVERLAPPING STATS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 -mt-10 relative z-20 mx-2 sm:mx-6">
        @foreach($statCards as $card)
            <div class="premium-card p-5 flex items-center gap-4 group cursor-default">
                <div class="flex h-12 w-12 rounded-xl {{ $card['bg'] }} {{ $card['color'] }} items-center justify-center text-xl shrink-0 transition-transform group-hover:scale-110 duration-300">
                    <i class="fas {{ $card['icon'] }}"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-3xl font-bold text-slate-800 leading-none tracking-tight transition-colors" id="{{ $card['id'] }}">{{ $card['value'] }}</h3>
                    <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest mt-1.5 truncate">{{ $card['label'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ALERT ERROR --}}
    @if(isset($pesanError) && $pesanError)
        <div class="mt-6 mx-2 sm:mx-6 rounded-2xl bg-amber-50 border border-amber-200 p-4 flex items-center gap-4 shadow-sm">
            <div class="h-10 w-10 rounded-full bg-white flex items-center justify-center text-amber-500 shrink-0 shadow-sm"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="flex-1"><h3 class="text-sm font-bold text-amber-900">Perhatian</h3><p class="text-xs font-medium text-amber-700 mt-0.5">{{ $pesanError }}</p></div>
        </div>
    @endif

    {{-- 3. MAIN CONTENT GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 mt-6 sm:mt-8 mx-0 sm:mx-2">
        
        {{-- KOLOM KIRI (Agenda & Keluarga) --}}
        <div class="lg:col-span-8 flex flex-col gap-6 sm:gap-8">
            
            {{-- WIDGET AGENDA --}}
            <div class="premium-card overflow-hidden">
                <div class="flex items-center justify-between p-5 sm:px-6 border-b border-slate-100 bg-white">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-emerald-500 text-lg"></i> Agenda Terdekat
                    </h2>
                    <a href="{{ $jadwalRoute }}" class="text-[10px] font-bold uppercase text-emerald-600 hover:text-emerald-800 transition-colors tracking-widest">Lihat Semua</a>
                </div>
                
                <div class="p-5 sm:p-6 bg-slate-50/30">
                    @if($jadwalUtama)
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 bg-white border border-slate-200 rounded-2xl p-5 hover:border-emerald-200 transition-all shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                            
                            {{-- Date Box --}}
                            <div class="flex flex-col items-center justify-center w-20 h-20 rounded-xl bg-emerald-50/50 border border-emerald-100 shrink-0">
                                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">{{ $formatDate($jadwalUtama->tanggal, 'M Y') }}</span>
                                <span class="text-3xl font-bold text-slate-800 leading-none mt-0.5">{{ $formatDate($jadwalUtama->tanggal, 'd') }}</span>
                            </div>
                            
                            {{-- Info --}}
                            <div class="flex-1 min-w-0 w-full">
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <span class="px-2.5 py-1 rounded-md text-[9px] font-bold uppercase tracking-widest bg-emerald-50 text-emerald-700">
                                        {{ ucwords(str_replace('_', ' ', $jadwalUtama->target_peserta ?? 'Semua Sasaran')) }}
                                    </span>
                                    <span class="px-2.5 py-1 rounded-md text-[9px] font-bold uppercase tracking-widest bg-slate-100 text-slate-600">
                                        <i class="far fa-clock"></i> {{ $formatTime($jadwalUtama->waktu_mulai) }} WIB
                                    </span>
                                </div>
                                <h3 class="text-lg font-bold text-slate-800 truncate" title="{{ $jadwalUtama->judul }}">{{ $jadwalUtama->judul }}</h3>
                                <p class="text-xs font-medium text-slate-500 mt-1 truncate">
                                    <i class="fas fa-map-pin text-slate-300 mr-1.5"></i> {{ $jadwalUtama->lokasi ?? 'Lokasi belum ditentukan' }}
                                </p>
                            </div>
                            
                            {{-- Action --}}
                            <div class="shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
                                <a href="{{ route('user.jadwal.show', $jadwalUtama->id) }}" class="flex items-center justify-center w-full sm:w-auto px-6 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold tracking-wide rounded-xl shadow-sm transition-colors">
                                    Detail <i class="fas fa-arrow-right ml-2 text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="py-8 text-center bg-white rounded-2xl border border-dashed border-slate-200">
                            <i class="fa-regular fa-calendar-xmark text-4xl text-slate-300 mb-3"></i>
                            <h3 class="text-sm font-bold text-slate-700">Tidak ada jadwal</h3>
                            <p class="text-xs text-slate-500">Belum ada agenda posyandu dalam waktu dekat.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- WIDGET KELUARGA --}}
            <div class="premium-card overflow-hidden">
                <div class="flex items-center justify-between p-5 sm:px-6 border-b border-slate-100 bg-white">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-users text-sky-500 text-lg"></i> Profil Medis Keluarga
                    </h2>
                    <a href="{{ $monitoringRoute }}" class="text-[10px] font-bold uppercase text-sky-600 hover:text-sky-800 transition-colors tracking-widest">Buku Rekam</a>
                </div>
                
                <div class="p-5 sm:p-6 bg-slate-50/30">
                    @if($healthItems->isEmpty())
                        <div class="py-8 text-center bg-white rounded-2xl border border-dashed border-slate-200">
                            <i class="fas fa-user-plus text-3xl text-slate-300 mb-3"></i>
                            <h3 class="text-sm font-bold text-slate-700">Belum Ada Data</h3>
                            <a href="{{ $profileRoute }}" class="text-xs font-bold text-sky-600 mt-2 inline-block hover:underline">Sinkronisasi NIK Sekarang</a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($healthItems->take(4) as $item)
                                @php $tone = $toneMap[$item['tone']]; @endphp
                                <a href="{{ $item['href'] }}" class="flex items-center gap-4 p-4 rounded-2xl border border-slate-200 hover:border-slate-300 hover:shadow-sm transition-all group bg-white">
                                    <div class="h-12 w-12 rounded-xl {{ $tone['bg'] }} {{ $tone['text'] }} flex items-center justify-center text-xl shrink-0">
                                        <i class="fas {{ $item['icon'] }}"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-sm font-bold text-slate-800 truncate">{{ $item['name'] }}</h4>
                                        <p class="text-xs font-medium text-slate-500 mt-0.5 truncate">{{ $item['meta'] }}</p>
                                    </div>
                                    <span class="text-[9px] font-bold uppercase tracking-widest {{ $tone['text'] }} {{ $tone['bg'] }} px-2 py-1 rounded-md">{{ $item['type'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN (Pesan Bidan) --}}
        <div class="lg:col-span-4 h-full relative">
            <div class="premium-card flex flex-col h-full lg:absolute lg:inset-0">
                <div class="flex items-center justify-between p-5 sm:px-6 border-b border-slate-100 bg-white shadow-sm shrink-0 relative z-10">
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-envelope-open-text text-amber-500 text-lg"></i> Pesan Bidan
                        @if($jumlahNotif > 0)
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-white text-[10px] font-bold" id="notif-badge">{{ $jumlahNotif }}</span>
                        @endif
                    </h2>
                    <a href="{{ $notifikasiRoute }}" class="text-[10px] font-bold uppercase text-amber-600 hover:text-amber-800 transition-colors tracking-widest">Semua</a>
                </div>

                <div class="flex-1 overflow-y-auto hide-scroll p-4 space-y-2 bg-slate-50/50">
                    @forelse($notifList as $notif)
                        @php $isNew = !($notif['is_read'] ?? true); @endphp
                        <a href="{{ $notifikasiRoute }}" class="block p-4 rounded-2xl transition-all {{ $isNew ? 'bg-white border border-amber-100 shadow-[0_2px_8px_rgba(245,158,11,0.06)]' : 'border border-transparent hover:bg-white hover:border-slate-200' }}">
                            <div class="flex gap-3">
                                <div class="mt-1.5 shrink-0">
                                    <div class="h-2 w-2 rounded-full {{ $isNew ? 'bg-amber-500' : 'bg-slate-300' }}"></div>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-bold text-slate-800 truncate">{{ $notif['judul'] ?? 'Pemberitahuan' }}</h4>
                                    <p class="text-[11px] font-medium text-slate-500 line-clamp-2 mt-1 leading-relaxed">{{ $notif['pesan'] ?? '-' }}</p>
                                    <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-widest mt-2 block">{{ $notif['waktu'] ?? '-' }}</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center flex flex-col items-center justify-center h-full">
                            <i class="fas fa-check-circle text-4xl text-slate-200 mb-3"></i>
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
            dateEl.textContent = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
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