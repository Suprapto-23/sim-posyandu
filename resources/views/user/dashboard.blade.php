@extends('layouts.user')

@section('title', 'Beranda Saya')
@section('page_title', 'Beranda Saya')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $firstName = trim(explode(' ', Auth::user()->name ?? 'Warga')[0]) ?: 'Warga';
    $routeTo = function ($names, $params = []) {
        foreach ((array) $names as $n) { if (Route::has($n)) return route($n, $params); }
        return '#';
    };

    $anakList = collect($dataAnak ?? []);
    $remajaList = collect($dataRemaja ?? []);
    $lansiaList = collect($dataLansia ?? []);
    $jadwalList = collect($jadwalTerdekat ?? []);
    $jumlahNotif = $totalNotifikasiBelumDibaca ?? collect($notifikasiTerbaru ?? [])->where('is_read', false)->count();

    $monitoringRoute = $routeTo('user.monitoring.index');
    $jadwalRoute = $routeTo('user.jadwal.index');
    $notifikasiRoute = $routeTo('user.notifikasi.index');
    $profileRoute = $routeTo('user.profile.edit');

    $formatDate = fn ($v, $f = 'd M Y') => filled($v) && $v !== '-' ? Carbon::parse($v)->translatedFormat($f) : '-';
    $formatTime = fn ($v) => filled($v) && $v !== '-' ? Carbon::parse($v)->format('H:i') : '-';
    $getName = fn ($i) => data_get($i, 'nama_lengkap') ?: data_get($i, 'nama_remaja') ?: data_get($i, 'nama_balita') ?: data_get($i, 'nama_lansia') ?: data_get($i, 'nama') ?: 'Tanpa Nama';
    $getDob = fn ($i) => data_get($i, 'tanggal_lahir') ?: data_get($i, 'tgl_lahir');
    $ageText = function ($dob) {
        if (blank($dob)) return '-';
        try { $d = Carbon::parse($dob)->diff(now()); return $d->y > 0 ? $d->y . ' thn' : $d->m . ' bln'; }
        catch (\Throwable $e) { return '-'; }
    };

    $groups = [
        ['Balita', $anakList, ['user.balita.show', 'user.monitoring.balita.show'], 'fa-child-reaching', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        ['Remaja', $remajaList, ['user.remaja.show', 'user.monitoring.remaja.show'], 'fa-user-graduate', 'bg-amber-50 text-amber-700 border-amber-200'],
        ['Lansia', $lansiaList, ['user.lansia.show', 'user.monitoring.lansia.show'], 'fa-person-cane', 'bg-blue-50 text-blue-700 border-blue-200'],
    ];
    $healthItems = collect();
    foreach ($groups as [$type, $list, $routes, $icon, $badge]) {
        foreach ($list as $row) {
            $healthItems->push(['type' => $type, 'icon' => $icon, 'badge' => $badge, 'name' => $getName($row),
                'meta' => $ageText($getDob($row)), 'href' => $routeTo($routes, [data_get($row, 'id')])]);
        }
    }
    $totalSasaran = $healthItems->count();
    $kategoriAda = $healthItems->pluck('type')->map(fn ($t) => strtolower($t))->unique()->values();

    $statCards = [
        ['id' => 'stat-keluarga', 'label' => 'Total anggota', 'value' => $totalSasaran, 'desc' => 'Terdaftar di KK', 'icon' => 'fa-users', 'tone' => 'bg-emerald-50 text-emerald-600 border-emerald-100', 'bar' => 'bg-emerald-500', 'url' => $profileRoute],
        ['id' => 'stat-riwayat', 'label' => 'Rekam medis', 'value' => $healthItems->count(), 'desc' => 'Buku KIA & Lansia', 'icon' => 'fa-notes-medical', 'tone' => 'bg-teal-50 text-teal-600 border-teal-100', 'bar' => 'bg-teal-500', 'url' => $monitoringRoute],
        ['id' => 'stat-jadwal', 'label' => 'Agenda posyandu', 'value' => $jadwalList->count(), 'desc' => 'Jadwal terdekat', 'icon' => 'fa-calendar-check', 'tone' => 'bg-indigo-50 text-indigo-600 border-indigo-100', 'bar' => 'bg-indigo-500', 'url' => $jadwalRoute],
        ['id' => 'stat-notif', 'label' => 'Pemberitahuan', 'value' => $jumlahNotif, 'desc' => 'Pesan belum dibaca', 'icon' => 'fa-bell', 'tone' => 'bg-rose-50 text-rose-600 border-rose-100', 'bar' => 'bg-rose-500', 'url' => $notifikasiRoute],
    ];

    $sasaranList = collect($sasaranList ?? []);
    $sasaranAktif = $sasaranAktif ?? $sasaranList->first();
    $grafikPeriodeAktif = $grafikPeriode ?? 'bulanan';
    $grafikRingkasan = $grafikData['ringkasan'] ?? null;
    $grafikTahunAktif = $grafikData['tahun'] ?? null;
    $grafikTahunTersedia = collect($grafikData['available_years'] ?? []);
    $adaGrafik = !empty($grafikData['labels'] ?? []);

    // Umur (tahun) tiap sasaran, dipakai untuk membaca IMT dengan benar
    $allRows = $anakList->concat($remajaList)->concat($lansiaList);
    $umurMap = $sasaranList->mapWithKeys(function ($s) use ($allRows, $getName, $getDob) {
        $row = $allRows->first(fn ($r) => data_get($r, 'id') == $s['id'] && $getName($r) == ($s['nama'] ?? null)) ?: $allRows->firstWhere('id', $s['id']);
        try { $y = $row && filled($getDob($row)) ? Carbon::parse($getDob($row))->age : null; } catch (\Throwable $e) { $y = null; }
        return [$s['kategori'] . '|' . $s['id'] => $y];
    });
@endphp

@push('styles')
<style>
    body{background-color:#f8fafc;background-image:radial-gradient(at 0% 0%,rgba(16,185,129,.08) 0,transparent 50%),radial-gradient(at 100% 0%,rgba(13,148,136,.07) 0,transparent 50%),radial-gradient(at 50% 100%,#f1f5f9 0,transparent 50%);background-attachment:fixed}
    .premium-card{position:relative;overflow:hidden;background:rgba(255,255,255,.96);backdrop-filter:blur(16px);border:1px solid rgba(226,232,240,.9);border-radius:1.5rem;box-shadow:0 4px 20px -4px rgba(15,23,42,.05),0 2px 6px -1px rgba(15,23,42,.03);transition:transform .3s,box-shadow .3s,border-color .3s}
    .premium-card::before{content:"";position:absolute;inset:0;pointer-events:none;opacity:0;transition:opacity .3s;background:radial-gradient(280px circle at var(--mx,50%) var(--my,50%),rgba(16,185,129,.09),transparent 70%)}
    .premium-card:hover::before{opacity:1}
    a.premium-card:hover{transform:translateY(-4px);border-color:#cbd5e1;box-shadow:0 20px 36px -14px rgba(15,23,42,.16)}
    .stat-bar{position:absolute;left:0;bottom:0;height:3px;width:0;transition:width .5s cubic-bezier(.2,.7,.2,1)}
    a.premium-card:hover .stat-bar{width:100%}
    a.premium-card:hover .stat-ico{transform:rotate(-8deg) scale(1.1)}
    .stat-ico{transition:transform .35s}

    .rv{opacity:0;transform:translateY(22px);transition:opacity .8s cubic-bezier(.2,.7,.2,1),transform .8s cubic-bezier(.2,.7,.2,1);transition-delay:var(--d,0ms)}
    .rv.in{opacity:1;transform:none}
    .orb{position:absolute;border-radius:50%;filter:blur(50px);animation:drift 9s ease-in-out infinite;pointer-events:none}
    @keyframes drift{50%{transform:translate(26px,-20px) scale(1.12)}}
    .dots{position:absolute;inset:0;pointer-events:none;opacity:.5;background-image:radial-gradient(rgba(255,255,255,.35) 1.2px,transparent 1.2px);background-size:26px 26px;-webkit-mask-image:linear-gradient(120deg,transparent 30%,#000);mask-image:linear-gradient(120deg,transparent 30%,#000)}
    .sheen{position:relative;overflow:hidden}
    .sheen::after{content:"";position:absolute;top:0;left:-60%;width:40%;height:100%;transform:skewX(-20deg);background:linear-gradient(90deg,transparent,rgba(16,185,129,.25),transparent);animation:sheen 4.5s ease-in-out infinite}
    @keyframes sheen{0%,60%{left:-60%}100%{left:140%}}

    #tip-bar{height:3px;background:linear-gradient(90deg,#10b981,#14b8a6);animation:bar 9s linear forwards;border-radius:9px}
    @keyframes bar{from{width:0}to{width:100%}}
    .tip-in{animation:tipin .55s cubic-bezier(.2,.7,.2,1)}
    @keyframes tipin{from{opacity:0;transform:translateY(10px)}}
    .pop{animation:pop .5s cubic-bezier(.2,.7,.2,1) both;animation-delay:var(--d,0ms)}
    @keyframes pop{from{opacity:0;transform:translateY(14px) scale(.96)}}

    .seg{display:flex;background:#f1f5f9;padding:.25rem;border-radius:.85rem;border:1px solid rgba(226,232,240,.7)}
    .seg button{padding:.4rem .85rem;border-radius:.6rem;font-size:.75rem;font-weight:700;color:#64748b;transition:all .25s}
    .seg button.on{background:#fff;color:#1e293b;box-shadow:0 1px 3px rgba(15,23,42,.12)}
    .row-item{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.85rem;border-radius:1rem;border:1px solid #f1f5f9;background:rgba(248,250,252,.6);transition:all .25s}
    .row-item:hover{background:#fff;border-color:#cbd5e1;transform:translateX(4px);box-shadow:0 4px 12px -4px rgba(15,23,42,.1)}
    .row-item:hover .av{background:#10b981;color:#fff;border-color:#10b981}
    .av{width:2.5rem;height:2.5rem;border-radius:.8rem;display:flex;align-items:center;justify-content:center;background:#fff;border:1px solid #e2e8f0;color:#475569;transition:all .25s;flex-shrink:0}

    #imt-mark{transition:left 1.1s cubic-bezier(.2,.8,.2,1)}
    .chk{display:flex;align-items:center;gap:.7rem;width:100%;text-align:left;padding:.6rem .7rem;border-radius:.85rem;transition:background .2s}
    .chk:hover{background:#f8fafc}
    .chk .bx{width:1.4rem;height:1.4rem;border-radius:.5rem;border:2px solid #cbd5e1;display:flex;align-items:center;justify-content:center;color:transparent;font-size:.65rem;transition:all .25s;flex-shrink:0}
    .chk.on .bx{background:#10b981;border-color:#10b981;color:#fff;transform:scale(1.12)}
    .chk.on .t{text-decoration:line-through;color:#94a3b8}
    .skel{background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:skel 1.2s infinite}
    @keyframes skel{to{background-position:-200% 0}}
    .apexcharts-tooltip{border-radius:1rem!important;box-shadow:0 12px 28px -4px rgba(0,0,0,.12)!important;border:1px solid #e2e8f0!important;font-family:inherit!important}
    .hero-bg{background:radial-gradient(700px circle at 88% 0%,rgba(255,255,255,.2),transparent 60%),radial-gradient(520px circle at 0% 100%,rgba(6,78,59,.38),transparent 65%),linear-gradient(120deg,#059669 0%,#0d9488 55%,#10b981 100%);box-shadow:0 30px 60px -28px rgba(5,150,105,.55);border:1px solid rgba(255,255,255,.25)}
    .clk{display:inline-flex;align-items:center;gap:.65rem;padding:.4rem 1rem .4rem .8rem;border-radius:999px;white-space:nowrap;background:rgba(2,44,34,.3);border:1px solid rgba(255,255,255,.2);backdrop-filter:blur(10px);color:#d1fae5;font-size:.75rem;font-weight:600;font-variant-numeric:tabular-nums}
    .clk-sep{width:1px;height:1rem;background:rgba(255,255,255,.28)}
    .clk-time{display:inline-flex;align-items:baseline;min-width:5.2rem;color:#fff;font-size:.95rem;font-weight:800;letter-spacing:.02em}
    .clk-s{font-size:.72rem;font-weight:700;color:#fde68a;margin-left:2px}
    .clk-s::before,.clk-colon{content:":"}
    .clk-colon{animation:blink 1s steps(2,start) infinite}
    @keyframes blink{50%{opacity:.25}}
    .clk-tz{font-size:.65rem;font-weight:700;opacity:.8}
    .tip-card{background:linear-gradient(120deg,#ecfdf5,#f0fdfa 60%,#ecfdf5)!important;border-color:#a7f3d0!important}
    .tip-dot{width:.4rem;height:.4rem;border-radius:9px;background:#a7f3d0;transition:all .3s}
    .tip-dot.on{width:1.2rem;background:#10b981}
    .rv:not(.in){transition-delay:0ms}
    @media (prefers-reduced-motion:reduce){.rv{opacity:1;transform:none;transition:none}.orb,.sheen::after,#tip-bar,.pop,.tip-in,.clk-colon{animation:none}}
</style>
@endpush

@section('content')
<div class="px-3 py-4 sm:px-6 sm:py-6 lg:px-8 max-w-[1400px] mx-auto space-y-5 sm:space-y-6">

    {{-- 1. HERO --}}
    <section class="rv relative overflow-hidden rounded-[2rem] sm:rounded-[2.5rem] hero-bg p-6 sm:p-10">
        <div class="dots"></div>
        <div class="orb w-72 h-72 -right-10 -top-16 bg-white/25"></div>
        <div class="orb w-56 h-56 left-1/3 -bottom-24 bg-amber-200/25" style="animation-delay:-4s"></div>

        <div class="relative z-10 flex flex-col-reverse lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-5 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 border border-white/30 text-white text-[11px] font-black uppercase tracking-wider backdrop-blur-md">
                        <span class="w-2 h-2 rounded-full bg-emerald-100 animate-pulse"></span>Portal Warga
                    </span>
                    <span class="clk"><i class="fa-regular fa-calendar"></i><span id="clk-date">Memuat...</span><span class="clk-sep"></span><span class="clk-time"><span id="clk-hm">--</span><span class="clk-colon"></span><span id="clk-mn">--</span><span class="clk-s" id="clk-s">--</span></span><span class="clk-tz">WIB</span></span>
                </div>
                <div>
                    <h1 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight leading-tight">
                        <span id="dynamic-greeting">Selamat datang</span>, {{ $firstName }} 🏡
                    </h1>
                    <p class="text-teal-50 text-sm sm:text-base font-medium leading-relaxed mt-2 max-w-xl">
                        Pantau jadwal, riwayat imunisasi, dan tumbuh kembang keluarga Anda, sambil belajar hal-hal kecil yang menjaga kesehatan setiap hari.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ $monitoringRoute }}" class="sheen bg-white text-emerald-700 hover:bg-teal-50 px-6 py-3 rounded-full text-sm font-bold shadow-md hover:shadow-lg active:scale-95 transition flex items-center gap-2"><i class="fa-solid fa-notes-medical"></i>Buku Rekam Medis</a>
                    <a href="{{ $jadwalRoute }}" class="bg-emerald-700/30 hover:bg-emerald-700/50 border border-white/30 text-white px-6 py-3 rounded-full text-sm font-bold backdrop-blur-md active:scale-95 transition flex items-center gap-2"><i class="fa-regular fa-calendar-days"></i>Lihat Jadwal</a>
                </div>
            </div>
            <div data-lottie="edu" class="w-52 h-52 sm:w-72 sm:h-72 shrink-0 mx-auto lg:mx-0 lg:mr-6"></div>
        </div>
    </section>

    {{-- 2. TIPS HARIAN --}}
    <section class="rv premium-card tip-card" style="--d:80ms">
        <div class="flex items-center gap-4 p-4 sm:p-5">
            <div class="w-16 h-16 sm:w-[4.5rem] sm:h-[4.5rem] rounded-2xl bg-white border border-emerald-100 shadow-sm shrink-0"><div data-lottie="tip" class="w-full h-full"></div></div>
            <div id="tip-body" class="min-w-0 flex-1 tip-in">
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700" id="tip-label">Tips hari ini</span>
                    <span id="tip-cat" class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-700 text-[10px] font-bold"></span>
                </div>
                <h3 id="tip-title" class="text-sm sm:text-base font-extrabold text-emerald-900"></h3>
                <p id="tip-desc" class="text-xs sm:text-sm text-emerald-800/80 leading-relaxed"></p>
                <div id="tip-dots" class="flex items-center gap-1.5 mt-2"></div>
            </div>
            <div class="flex gap-1.5 shrink-0">
                <button type="button" id="tip-prev" class="w-9 h-9 rounded-xl bg-white border border-emerald-100 text-emerald-700 hover:bg-emerald-500 hover:text-white transition active:scale-90" title="Tips sebelumnya"><i class="fas fa-chevron-left text-xs"></i></button>
                <button type="button" id="tip-next" class="w-9 h-9 rounded-xl bg-white border border-emerald-100 text-emerald-700 hover:bg-emerald-500 hover:text-white transition active:scale-90" title="Tips berikutnya"><i class="fas fa-chevron-right text-xs"></i></button>
            </div>
        </div>
        <div class="h-[3px] bg-emerald-100"><div id="tip-bar"></div></div>
    </section>

    {{-- 3. METRIK --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
        @foreach($statCards as $i => $c)
            <a href="{{ $c['url'] }}" class="rv premium-card p-4 sm:p-5 pb-5" style="--d:{{ 140 + $i * 70 }}ms">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="stat-ico w-12 h-12 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center text-lg sm:text-xl border shrink-0 {{ $c['tone'] }}"><i class="fa-solid {{ $c['icon'] }}"></i></div>
                    <div class="min-w-0">
                        <p class="text-[10px] sm:text-[11px] font-extrabold uppercase tracking-wider text-slate-400 truncate">{{ $c['label'] }}</p>
                        <p class="text-3xl sm:text-4xl font-black text-slate-800 tracking-tight tabular-nums leading-none mt-1" id="{{ $c['id'] }}" data-count="{{ (int) $c['value'] }}">{{ $c['value'] }}</p>
                    </div>
                </div>
                <p class="text-[11px] font-medium text-slate-400 mt-3 truncate">{{ $c['desc'] }}</p>
                <span class="stat-bar {{ $c['bar'] }}"></span>
            </a>
        @endforeach
    </div>

    {{-- 4. KONTEN UTAMA --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">

        <div class="lg:col-span-8 space-y-5 sm:space-y-6">

            {{-- Grafik + IMT --}}
            <div class="premium-card rv p-4 sm:p-6" id="widget-grafik" style="--d:200ms">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center"><i class="fas fa-chart-line"></i></div>
                        <div><h2 class="text-sm sm:text-base font-bold text-slate-800">Grafik Perkembangan Fisik</h2><p class="text-xs text-slate-400">Tren berat dan tinggi badan dari waktu ke waktu</p></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div id="grafik-year-nav" class="seg items-center {{ $grafikPeriodeAktif === 'tahunan' ? 'hidden' : '' }}">
                            <button type="button" id="grafik-year-prev" title="Tahun sebelumnya"><i class="fas fa-chevron-left text-[10px]"></i></button>
                            <select id="grafik-year-select" class="bg-transparent text-xs font-bold text-slate-700 px-1 focus:outline-none cursor-pointer">
                                @forelse($grafikTahunTersedia as $th)
                                    <option value="{{ $th }}" @selected($th == $grafikTahunAktif)>{{ $th }}</option>
                                @empty
                                    <option value="{{ $grafikTahunAktif ?? now()->year }}">{{ $grafikTahunAktif ?? now()->year }}</option>
                                @endforelse
                            </select>
                            <button type="button" id="grafik-year-next" title="Tahun berikutnya"><i class="fas fa-chevron-right text-[10px]"></i></button>
                        </div>
                        <div class="seg">
                            <button type="button" class="filter-pill-btn {{ $grafikPeriodeAktif === 'bulanan' ? 'on' : '' }}" data-periode="bulanan">Bulanan</button>
                            <button type="button" class="filter-pill-btn {{ $grafikPeriodeAktif === 'tahunan' ? 'on' : '' }}" data-periode="tahunan">Tahunan</button>
                        </div>
                    </div>
                </div>

                @if($sasaranAktif)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        @if($sasaranList->count() > 1)
                            <select id="grafik-sasaran-select" class="min-w-[200px] bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:outline-none cursor-pointer">
                                @foreach($sasaranList as $s)
                                    <option value="{{ $s['kategori'] }}|{{ $s['id'] }}" @selected($sasaranAktif['id'] == $s['id'] && $sasaranAktif['kategori'] == $s['kategori'])>{{ $s['nama'] }} ({{ $s['label'] }})</option>
                                @endforeach
                            </select>
                        @else
                            <div class="text-sm font-bold text-slate-700 flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>{{ $sasaranAktif['nama'] ?? 'Anggota Keluarga' }} ({{ $sasaranAktif['label'] ?? 'Sasaran' }})</div>
                        @endif
                        <div class="grid grid-cols-3 sm:flex gap-2">
                            <div class="bg-emerald-50/80 border border-emerald-200/80 rounded-xl px-3 py-1.5 text-center sm:min-w-[90px]"><span class="text-[9px] font-extrabold text-emerald-700 uppercase tracking-wider block">Berat badan</span><b class="text-sm font-black text-slate-800" id="ringkasan-berat">{{ data_get($grafikRingkasan, 'berat_terakhir', '-') }}</b> <span class="text-[10px] font-bold text-slate-500">kg</span></div>
                            <div class="bg-indigo-50/80 border border-indigo-200/80 rounded-xl px-3 py-1.5 text-center sm:min-w-[90px]"><span class="text-[9px] font-extrabold text-indigo-700 uppercase tracking-wider block">Tinggi badan</span><b class="text-sm font-black text-slate-800" id="ringkasan-tinggi">{{ data_get($grafikRingkasan, 'tinggi_terakhir', '-') }}</b> <span class="text-[10px] font-bold text-slate-500">cm</span></div>
                            <div class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-center sm:min-w-[105px]"><span class="text-[9px] font-extrabold text-slate-500 uppercase tracking-wider block">Cek terakhir</span><b class="text-xs font-bold text-slate-700 block truncate" id="ringkasan-tanggal">{{ data_get($grafikRingkasan, 'tanggal_terakhir', '-') }}</b></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-bold text-slate-600 pt-2 border-t border-slate-100">
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span>Berat Badan (Kg)</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-indigo-500"></span>Tinggi Badan (Cm)</span>
                    </div>
                @endif

                <div class="relative w-full h-[340px] mt-2">
                    <div id="grafik-loading" class="skel hidden absolute inset-0 rounded-2xl z-10"></div>
                    <div id="apexGrowthChart" class="w-full h-full {{ $adaGrafik ? '' : 'hidden' }}"></div>
                    <div id="grafik-empty" class="{{ $adaGrafik ? 'hidden' : '' }} flex flex-col items-center justify-center h-full bg-slate-50/60 rounded-2xl border border-dashed border-slate-200 p-6 text-center">
                        <div data-lottie="float" class="w-32 h-32"></div>
                        <h4 class="text-sm font-bold text-slate-700">Belum Ada Riwayat Pengukuran</h4>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs">Grafik akan muncul setelah berat dan tinggi badan dicatat di posyandu.</p>
                    </div>
                </div>
                <div id="grafik-single-notice" class="hidden mt-3 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-emerald-500 shrink-0"></i><span>Baru ada 1 pemeriksaan. Timbang rutin di jadwal posyandu berikutnya agar kurva pertumbuhan terlihat.</span>
                </div>

                {{-- IMT --}}
                <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800"><i class="fa-solid fa-scale-balanced text-emerald-500 mr-1.5"></i>Indeks Massa Tubuh (IMT)</h3>
                            <p class="text-xs text-slate-400">Dihitung otomatis dari berat dan tinggi terakhir</p>
                        </div>
                        <div class="flex items-center gap-2"><span id="imt-val" class="text-3xl font-black text-slate-800 tabular-nums">-</span><span id="imt-label" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-200 text-slate-600">Belum ada data</span></div>
                    </div>
                    <div id="imt-bar" class="relative mt-5 transition-opacity duration-500">
                        <div class="flex h-3 rounded-full overflow-hidden"><i class="w-1/4 bg-amber-400"></i><i class="w-1/4 bg-emerald-500"></i><i class="w-1/4 bg-orange-400"></i><i class="w-1/4 bg-rose-500"></i></div>
                        <span id="imt-mark" class="absolute -top-1 w-5 h-5 -ml-2.5 rounded-full bg-white border-[3px] border-slate-700 shadow" style="left:0"></span>
                        <div class="flex text-[10px] font-bold text-slate-400 mt-2"><span class="w-1/4">Kurus</span><span class="w-1/4">Normal</span><span class="w-1/4">Gemuk</span><span class="w-1/4">Obesitas</span></div>
                    </div>
                    <p id="imt-note" class="text-xs text-slate-600 leading-relaxed mt-3">Isi berat dan tinggi badan melalui pemeriksaan posyandu untuk melihat hasilnya.</p>
                </div>
            </div>

            {{-- Pusat Edukasi --}}
            <div class="premium-card rv p-4 sm:p-6" id="edu" style="--d:240ms">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 border border-teal-100 flex items-center justify-center"><i class="fa-solid fa-book-open-reader"></i></div>
                        <div><h2 class="text-sm sm:text-base font-bold text-slate-800">Pusat Edukasi Kesehatan</h2><p class="text-xs text-slate-400">Panduan singkat sesuai kelompok usia</p></div>
                    </div>
                    <div class="seg" id="edu-tabs">
                        <button type="button" data-k="balita">Balita</button><button type="button" data-k="remaja">Remaja</button><button type="button" data-k="lansia">Lansia</button>
                    </div>
                </div>
                <div class="flex items-center gap-4 p-3 pr-5 mb-4 rounded-2xl border border-emerald-100" style="background:linear-gradient(120deg,#ecfdf5,#f0fdfa)">
                    <div data-lottie="learn" class="w-20 h-20 sm:w-24 sm:h-24 shrink-0"></div>
                    <div><h3 class="text-sm sm:text-base font-extrabold text-emerald-900">Tumbuh sehat dimulai dari kebiasaan kecil</h3><p class="text-xs sm:text-sm text-emerald-800/80 mt-0.5">Pilih kelompok usia di atas, lalu pelajari langkah sederhana yang bisa dilakukan di rumah mulai hari ini.</p></div>
                </div>
                <div id="edu-body" class="grid sm:grid-cols-2 gap-3"></div>
                <div id="edu-extra" class="mt-4"></div>
                <p class="text-[11px] text-slate-400 mt-4"><i class="fa-solid fa-shield-heart mr-1"></i>Informasi umum untuk edukasi, bukan pengganti pemeriksaan tenaga kesehatan.</p>
            </div>
        </div>

        {{-- KOLOM KANAN --}}
        <div class="lg:col-span-4 space-y-5 sm:space-y-6">

            <div class="premium-card rv p-5 sm:p-6" style="--d:260ms">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center"><i class="fa-solid fa-house-chimney-medical"></i></div>
                        <div><h2 class="text-sm sm:text-base font-bold text-slate-800">Sasaran Keluarga</h2><p class="text-xs text-slate-400">Anggota terhubung di KK</p></div>
                    </div>
                    <a href="{{ $profileRoute }}" class="text-slate-400 hover:text-blue-600 transition hover:rotate-90" title="Kelola Kartu Keluarga"><i class="fa-solid fa-gear"></i></a>
                </div>
                <div class="space-y-2.5">
                    @forelse($healthItems as $item)
                        <a href="{{ $item['href'] }}" class="row-item">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="av"><i class="fas {{ $item['icon'] }} text-sm"></i></div>
                                <div class="min-w-0"><h4 class="text-sm font-bold text-slate-800 truncate">{{ $item['name'] }}</h4><p class="text-[11px] font-semibold text-slate-400">Usia: {{ $item['meta'] }}</p></div>
                            </div>
                            <span class="px-2.5 py-1 text-[10px] font-black uppercase rounded-lg border shrink-0 {{ $item['badge'] }}">{{ $item['type'] }}</span>
                        </a>
                    @empty
                        <div class="flex flex-col items-center p-5 text-center bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                            <div data-lottie="float" class="w-28 h-28"></div>
                            <p class="text-sm font-bold text-slate-600">Belum Ada Anggota Keluarga</p>
                            <p class="text-xs text-slate-400 mt-0.5">Lengkapi profil atau hubungi kader untuk verifikasi data NIK.</p>
                            <a href="{{ $profileRoute }}" class="mt-3 text-xs font-bold text-blue-600 hover:underline">Lengkapi NIK Sekarang</a>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="premium-card rv p-5 sm:p-6" style="--d:320ms">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center"><i class="fa-regular fa-calendar-days"></i></div>
                        <div><h2 class="text-sm sm:text-base font-bold text-slate-800">Jadwal Posyandu</h2><p class="text-xs text-slate-400">Agenda terdekat warga</p></div>
                    </div>
                    <a href="{{ $jadwalRoute }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">Lihat Semua</a>
                </div>
                <div class="space-y-2.5">
                    @forelse($jadwalList as $jadwal)
                        @php $isToday = !empty($jadwal->tanggal) && Carbon::parse($jadwal->tanggal)->isToday(); @endphp
                        <a href="{{ route('user.jadwal.show', $jadwal->id) }}" class="row-item !justify-start {{ $isToday ? '!bg-amber-50/70 !border-amber-200' : '' }}">
                            <div class="flex flex-col items-center justify-center w-11 h-11 rounded-xl shrink-0 font-bold {{ $isToday ? 'bg-amber-500 text-white' : 'bg-white text-slate-700 border border-slate-200' }}">
                                <span class="text-sm font-black leading-none">{{ $formatDate($jadwal->tanggal, 'd') }}</span><span class="text-[8px] uppercase tracking-wider mt-0.5">{{ $formatDate($jadwal->tanggal, 'M') }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5"><h4 class="text-xs font-bold text-slate-800 truncate">{{ $jadwal->judul }}</h4>@if($isToday)<span class="px-1.5 py-0.5 text-[8px] font-black uppercase rounded bg-amber-200 text-amber-900">Hari Ini</span>@endif</div>
                                <div class="flex flex-wrap gap-x-2.5 text-[11px] text-slate-500"><span><i class="fa-regular fa-clock text-slate-400"></i> {{ $formatTime($jadwal->waktu_mulai) }}</span><span class="truncate max-w-[130px]"><i class="fa-solid fa-map-pin text-rose-400"></i> {{ $jadwal->lokasi }}</span></div>
                            </div>
                        </a>
                    @empty
                        <div class="flex flex-col items-center p-5 text-center bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                            <div data-lottie="float" class="w-24 h-24"></div>
                            <p class="text-sm font-bold text-slate-600">Belum ada agenda</p><p class="text-xs text-slate-400">Jadwal baru akan muncul di sini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="premium-card rv p-5 sm:p-6" style="--d:380ms">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center"><i class="fa-solid fa-list-check"></i></div>
                        <div><h2 class="text-sm sm:text-base font-bold text-slate-800">Persiapan ke Posyandu</h2><p class="text-xs text-slate-400">Centang yang sudah siap</p></div>
                    </div>
                    <span id="chk-count" class="text-xs font-black text-emerald-600">0/4</span>
                </div>
                <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden mb-2"><div id="chk-bar" class="h-full w-0 rounded-full bg-gradient-to-r from-emerald-500 to-teal-400 transition-all duration-500"></div></div>
                @foreach(['Bawa Buku KIA atau KMS', 'Bawa KTP atau Kartu Keluarga', 'Catat keluhan dan pertanyaan untuk kader', 'Pakai baju berlengan mudah disingkap'] as $t)
                    <button type="button" class="chk"><span class="bx"><i class="fa-solid fa-check"></i></span><span class="t text-sm font-semibold text-slate-700">{{ $t }}</span></button>
                @endforeach
                <p id="chk-done" class="hidden pop text-xs font-bold text-emerald-600 text-center mt-2">Semua siap, sampai jumpa di posyandu! 🎉</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = id => document.getElementById(id);

    /* ---------- Animasi masuk & cahaya kursor ---------- */
    const io = new IntersectionObserver(es => es.forEach(e => e.target.classList.toggle('in', e.isIntersecting)), { threshold: .08 });
    document.querySelectorAll('.rv').forEach(el => io.observe(el));
    document.querySelectorAll('.premium-card').forEach(c => c.addEventListener('mousemove', e => {
        const r = c.getBoundingClientRect(); c.style.setProperty('--mx', (e.clientX - r.left) + 'px'); c.style.setProperty('--my', (e.clientY - r.top) + 'px');
    }));

    /* ---------- Lottie buatan sendiri (tanpa file eksternal) ---------- */
    const hex = h => [1, 3, 5].map(i => parseInt(h.substr(i, 2), 16) / 255).concat(1);
    const V = k => ({ a: 0, k: k });
    const A = (f, d) => ({ a: 1, k: f.map(([t, s, lin]) => ({ t: t, s: s, i: { x: Array(d).fill(lin ? 1 : .5), y: Array(d).fill(1) }, o: { x: Array(d).fill(lin ? 0 : .5), y: Array(d).fill(0) } })) });
    const grp = (it, t) => ({ ty: 'gr', it: it.concat(Object.assign({ ty: 'tr', p: V([0, 0]), a: V([0, 0]), s: V([100, 100]), r: V(0), o: V(100) }, t || {})) });
    const fl = c => ({ ty: 'fl', c: V(hex(c)), o: V(100) });
    const st = (c, w) => ({ ty: 'st', c: V(hex(c)), o: V(100), w: V(w), lc: 2, lj: 2 });
    const el = (d, p, h) => ({ ty: 'el', p: V(p || [0, 0]), s: V([d, h || d]) });
    const rc = (w, h, r, p) => ({ ty: 'rc', d: 1, s: V([w, h]), p: V(p || [0, 0]), r: V(r) });
    const L = (i, sh, ks) => ({ ty: 4, ind: i, nm: 'l' + i, sr: 1, ddd: 0, ao: 0, ip: 0, op: 90, st: 0, bm: 0, shapes: sh,
        ks: Object.assign({ o: V(100), r: V(0), p: V([100, 100, 0]), a: V([0, 0, 0]), s: V([100, 100, 100]) }, ks || {}) });
    const spin = () => A([[0, [0], 1], [90, [360]]], 1);
    const cross = c => [grp([rc(40, 13, 6.5), fl(c)]), grp([rc(13, 40, 6.5), fl(c)])];

    const pls = () => A([[0, [30]], [20, [75]], [45, [30]], [90, [30]]], 1);
    const sp = (c, r, p, a, b) => grp([{ ty: 'sr', sy: 1, pt: V(4), p: V([0, 0]), r: V(0), ir: V(r * .38), is: V(0), or: V(r), os: V(0) }, fl(c)],
        { p: V(p), s: A([[0, [a, a]], [45, [b, b]], [90, [a, a]]], 2) });
    function bulb(x, y, sc, gc) {                                   // lampu ide yang menyala berdenyut
        const rays = [-80, -40, 0, 40, 80].map(a => grp([rc(5, 13, 2.5, [0, -33]), fl('#fbbf24')], { r: V(a), o: pls() }));
        return L(9, [grp([el(9, [-7, -10]), fl('#ffffff')]), grp([rc(18, 9, 3, [0, 18]), fl('#f59e0b')]), grp([el(38, [0, -4]), fl('#fde68a')])]
            .concat(rays, [grp([el(76), fl(gc)], { o: pls() })]),
            { p: V([x, y, 0]), s: A([[0, [sc, sc, 100]], [20, [sc * 1.08, sc * 1.08, 100]], [45, [sc, sc, 100]], [90, [sc, sc, 100]]], 3) });
    }
    function build(type) {
        const lay = [], bob = y => A([[0, [100, y + 6, 0]], [45, [100, y - 6, 0]], [90, [100, y + 6, 0]]], 3);
        if (type === 'edu') {                                       // buku terbuka + lampu ide + bintang mengorbit
            const pg = (x, c) => grp([rc(60, 76, 8), fl(c)], { p: V([x, 6]) });
            const ln = x => grp([rc(34, 5, 2.5, [x, -12]), rc(34, 5, 2.5, [x, 0]), rc(22, 5, 2.5, [x - 6, 12]), fl('#a7f3d0')], { p: V([0, 6]) });
            const flip = grp([rc(60, 76, 8, [30, 0]), fl('#ecfdf5')], { p: V([0, 6]),
                s: A([[0, [100, 100]], [40, [-100, 100]], [85, [-100, 100]], [86, [100, 100]], [90, [100, 100]]], 2),
                o: A([[0, [100]], [40, [100]], [48, [0]], [84, [0]], [90, [100]]], 1) });         // halaman terbalik
            lay.push(L(1, [grp([el(176), fl('#ffffff')], { o: V(14) })]));
            lay.push(L(2, [sp('#fde68a', 11, [0, -84], 80, 150), sp('#c7d2fe', 9, [73, 42], 150, 80), sp('#ffffff', 10, [-73, 42], 80, 150)], { r: spin() }));
            lay.push(L(3, [flip, ln(-31), ln(31), grp([rc(4, 76, 2), fl('#6ee7b7')], { p: V([0, 6]) }), pg(-31, '#ffffff'), pg(31, '#f0fdf4'), grp([rc(136, 14, 6), fl('#047857')], { p: V([0, 46]) })], { p: bob(138) }));
            lay.push(bulb(100, 62, 100, '#ffffff'));
        } else if (type === 'tip') {
            lay.push(bulb(100, 108, 230, '#fde68a'));
        } else if (type === 'learn') {                              // tunas tumbuh = tumbuh kembang
            const g = f => A(f, 3);
            lay.push(L(1, [grp([el(124), fl('#a7f3d0')], { s: V([100, 34]) })], { p: V([100, 156, 0]) }));
            lay.push(L(2, [grp([rc(8, 72, 4), fl('#059669')])], { p: V([100, 156, 0]), a: V([0, 36, 0]),
                s: g([[0, [100, 0, 100]], [30, [100, 100, 100]], [78, [100, 100, 100]], [88, [100, 0, 100]], [90, [100, 0, 100]]]) }));
            lay.push(L(3, [grp([el(20, [0, -14], 28), fl('#6ee7b7')]), grp([el(46, [-23, 0], 22), fl('#34d399')], { r: V(24) }), grp([el(46, [23, 0], 22), fl('#10b981')], { r: V(-24) })],
                { p: V([100, 86, 0]), s: g([[0, [0, 0, 100]], [30, [0, 0, 100]], [50, [100, 100, 100]], [78, [100, 100, 100]], [88, [0, 0, 100]], [90, [0, 0, 100]]]) }));
            lay.push(L(4, [sp('#fbbf24', 10, [54, -30], 140, 70), sp('#34d399', 8, [-50, -10], 70, 140)]));
        } else {
            const b = bob(100);
            lay.push(L(1, [grp([el(160), st('#a7f3d0', 3)]), grp([el(13, [0, -80]), fl('#10b981')])], { r: spin() }));
            lay.push(L(2, [grp([el(108), fl('#d1fae5')])], { p: b }));
            lay.push(L(3, cross('#10b981'), { p: bob(100) }));
        }
        return { v: '5.7.0', fr: 30, ip: 0, op: 90, w: 200, h: 200, nm: type, ddd: 0, assets: [], layers: lay.reverse() };
    }
    if (window.lottie) document.querySelectorAll('[data-lottie]').forEach(n => lottie.loadAnimation(Object.assign(
        { container: n, renderer: 'svg', loop: true, autoplay: true },
        n.dataset.path ? { path: n.dataset.path } : { animationData: build(n.dataset.lottie) })));

    /* ---------- Angka naik ---------- */
    function countTo(n, to) {
        const from = parseInt(n.innerText) || 0, t0 = performance.now();
        (function tick(t) { const p = Math.min((t - t0) / 1100, 1); n.innerText = Math.round(from + (to - from) * (1 - Math.pow(1 - p, 3))); if (p < 1) requestAnimationFrame(tick); })(t0);
    }
    document.querySelectorAll('[data-count]').forEach(n => { const v = +n.dataset.count; n.innerText = 0; setTimeout(() => countTo(n, v), 400); });

    /* ---------- Jam & salam ---------- */
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    function clock() {
        const n = new Date(), p = v => String(v).padStart(2, '0'), h = n.getHours();
        $('clk-date').innerText = days[n.getDay()] + ', ' + n.getDate() + ' ' + months[n.getMonth()];
        $('clk-hm').innerText = p(h); $('clk-mn').innerText = p(n.getMinutes()); $('clk-s').innerText = p(n.getSeconds());
        $('dynamic-greeting').innerText = h >= 4 && h < 11 ? 'Selamat Pagi' : h >= 11 && h < 15 ? 'Selamat Siang' : h >= 15 && h < 18 ? 'Selamat Sore' : 'Selamat Malam';
    }
    clock(); setInterval(clock, 1000);

    /* ---------- Tips harian: paket 5 tips baru setiap hari, menyesuaikan anggota keluarga ---------- */
    const POOL = [
        ['Balita', 'Timbang setiap bulan', 'Datang ke posyandu tiap bulan. Grafik berat dan tinggi membantu mengetahui tumbuh kembang anak sejak dini.'],
        ['Balita', 'ASI saja selama 6 bulan', 'Bayi cukup diberi ASI tanpa tambahan makanan atau minuman lain pada 6 bulan pertama.'],
        ['Balita', 'Protein hewani saat MPASI', 'Mulai MPASI di usia 6 bulan. Telur, ikan, daging, dan hati ayam membantu mencegah stunting.'],
        ['Balita', 'Imunisasi tepat waktu', 'Imunisasi lengkap melindungi anak dari campak, difteri, dan polio. Cek jadwalnya di Buku KIA.'],
        ['Balita', 'Kapsul vitamin A gratis', 'Anak usia 6-59 bulan mendapat kapsul vitamin A di posyandu setiap Februari dan Agustus.'],
        ['Balita', 'Sikat gigi sejak gigi pertama', 'Sikat gigi anak dua kali sehari memakai pasta gigi berfluoride sebesar butir beras.'],
        ['Balita', 'Cegah dehidrasi saat diare', 'Beri oralit dan zinc sesuai anjuran petugas. Segera ke fasilitas kesehatan bila anak lemas, tidak mau minum, atau tinjanya berdarah.'],
        ['Balita', 'Kurangi layar gawai', 'Hindari tontonan layar untuk anak di bawah 2 tahun. Bermain dan mengobrol langsung jauh lebih bermanfaat.'],
        ['Balita', 'Ajak bicara setiap hari', 'Bernyanyi, membaca buku bergambar, dan bermain bersama melatih bahasa serta perkembangan otak anak.'],
        ['Balita', 'Kurangi gula dan garam', 'Hindari minuman manis kemasan dan jajanan asin untuk anak kecil. Pilih buah dan makanan rumahan.'],
        ['Balita', 'Bayi demam di bawah 3 bulan', 'Bayi berusia kurang dari 3 bulan yang demam perlu segera diperiksa tenaga kesehatan.'],
        ['Balita', 'Tidur cukup untuk tumbuh', 'Balita butuh tidur sekitar 10-14 jam sehari, termasuk tidur siang. Hormon pertumbuhan bekerja saat tidur.'],
        ['Remaja', 'Tablet tambah darah mingguan', 'Remaja putri minum 1 tablet seminggu. Gunakan air putih, bukan teh atau kopi yang menghambat penyerapan zat besi.'],
        ['Remaja', 'Jangan lewatkan sarapan', 'Sarapan membantu fokus belajar. Pilih nasi, telur, sayur, dan buah daripada gorengan atau camilan manis.'],
        ['Remaja', 'Bergerak 60 menit per hari', 'Olahraga, jalan kaki, atau bersepeda. Aktivitas rutin menjaga berat badan, tulang, dan suasana hati.'],
        ['Remaja', 'Tidur 8-10 jam', 'Kurang tidur membuat sulit konsentrasi dan mudah lelah. Jauhkan gawai satu jam sebelum tidur.'],
        ['Remaja', 'Batasi minuman manis', 'Batas gula harian sekitar 4 sendok makan. Satu minuman manis kemasan bisa menghabiskan banyak dari jatah itu.'],
        ['Remaja', 'Boleh meminta bantuan', 'Bila merasa sedih atau cemas lebih dari dua minggu, ceritakan pada orang tepercaya atau tenaga kesehatan.'],
        ['Remaja', 'Tolak rokok dan vape', 'Zat di dalamnya merusak paru-paru dan otak yang masih berkembang. Menolak ajakan adalah keputusan yang kuat.'],
        ['Remaja', 'Jaga kebersihan saat menstruasi', 'Ganti pembalut setiap 4-6 jam, cuci tangan sebelum dan sesudahnya, dan bersihkan area kewanitaan dengan air bersih.'],
        ['Remaja', 'Istirahatkan mata', 'Aturan 20-20-20: tiap 20 menit melihat layar, pandang objek jauh selama 20 detik.'],
        ['Lansia', 'Periksa tensi tiap bulan', 'Hipertensi sering tanpa gejala. Hasil 140/90 mmHg atau lebih pada beberapa kali ukur perlu konsultasi.'],
        ['Lansia', 'Waspada gula darah', 'Periksa berkala, terutama bila sering haus, sering buang air kecil, atau berat badan turun tanpa sebab.'],
        ['Lansia', 'Jalan kaki santai', 'Sekitar 30 menit, 5 kali seminggu, menjaga jantung, sendi, dan kebugaran. Mulailah pelan-pelan.'],
        ['Lansia', 'Cegah jatuh di rumah', 'Pastikan lantai tidak licin, lampu cukup terang, dan kamar mandi punya pegangan.'],
        ['Lansia', 'Minum meski tidak haus', 'Rasa haus berkurang seiring usia. Biasakan minum air putih sepanjang hari.'],
        ['Lansia', 'Jangan hentikan obat sendiri', 'Minum obat tekanan darah atau gula sesuai anjuran dokter walaupun badan terasa sehat.'],
        ['Lansia', 'Kenali tanda stroke', 'Mulut mencong, satu sisi tubuh lemah, atau bicara pelo mendadak: segera bawa ke rumah sakit.'],
        ['Lansia', 'Tetap bergaul', 'Ikut senam atau pertemuan lansia membantu menjaga daya ingat dan suasana hati.'],
        ['Lansia', 'Cukupi protein', 'Ikan, telur, tahu, dan tempe membantu menjaga massa otot agar tidak cepat menyusut.'],
        ['Lansia', 'Cek mata dan gigi', 'Periksa penglihatan dan gigi minimal setahun sekali. Gigi sehat membuat asupan gizi lebih baik.'],
        ['Ibu', 'Periksa kehamilan teratur', 'Minimal 6 kali selama hamil, termasuk 2 kali pemeriksaan USG oleh dokter.'],
        ['Ibu', 'Tablet tambah darah untuk ibu hamil', 'Minum minimal 90 tablet selama kehamilan untuk mencegah anemia dan bayi lahir dengan berat rendah.'],
        ['Ibu', '1.000 hari pertama kehidupan', 'Masa dari kehamilan hingga anak berusia 2 tahun adalah kunci mencegah stunting.'],
        ['Ibu', 'Ibu menyusui perlu makan lebih', 'Tambah porsi makan bergizi dan perbanyak minum agar ASI lancar dan tubuh ibu tetap kuat.'],
        ['Ibu', 'Tanda bahaya kehamilan', 'Perdarahan, sakit kepala hebat, atau bengkak di wajah dan tangan: segera ke fasilitas kesehatan.'],
        ['Umum', 'Cuci tangan 20 detik', 'Gunakan sabun, sebelum makan dan setelah dari toilet. Kebiasaan sederhana ini mencegah diare.'],
        ['Umum', 'Bawa Buku KIA', 'Catatan penimbangan dan imunisasi ada di sana, jadi petugas bisa memantau perkembangan dengan tepat.'],
        ['Umum', 'Isi piringku', 'Separuh piring diisi sayur dan buah, separuhnya lagi makanan pokok dan lauk.'],
        ['Umum', 'Gula 4, garam 1, lemak 5', 'Pesan Kemenkes per hari: gula 4 sendok makan, garam 1 sendok teh, minyak 5 sendok makan.'],
        ['Umum', 'Lakukan 3M Plus', 'Menguras, menutup, dan mendaur ulang barang bekas seminggu sekali mencegah jentik demam berdarah.'],
        ['Umum', 'Pakai garam beryodium', 'Yodium dibutuhkan untuk tumbuh kembang otak dan mencegah gondok.'],
        ['Umum', 'Jamban sehat dan air bersih', 'Buang air besar di jamban dan memasak air hingga mendidih mencegah diare serta stunting.'],
        ['Umum', 'Rumah bebas asap rokok', 'Asap rokok berbahaya bagi balita, ibu hamil, dan lansia. Merokoklah di luar rumah, atau lebih baik berhenti.'],
        ['Umum', 'Cuci sayur dan buah', 'Bilas di bawah air mengalir sebelum dimakan untuk menghilangkan kotoran dan kuman.'],
        ['Umum', 'Sikat gigi dua kali sehari', 'Pagi setelah sarapan dan malam sebelum tidur, dengan pasta gigi berfluoride.'],
        ['Umum', 'Pasang pengingat jadwal', 'Catat jadwal posyandu di ponsel supaya pemeriksaan keluarga tidak terlewat.'],
        ['Umum', 'Kesehatan jiwa itu penting', 'Merasa terus sedih atau cemas lebih dari dua minggu perlu dibicarakan dengan tenaga kesehatan.']
    ];
    const CHIP = { Balita: 'bg-emerald-100 text-emerald-700', Remaja: 'bg-amber-100 text-amber-700', Lansia: 'bg-blue-100 text-blue-700', Ibu: 'bg-rose-100 text-rose-700', Umum: 'bg-teal-100 text-teal-700' };
    const punya = @json($kategoriAda);
    const boleh = k => k === 'Umum' || !punya.length || punya.includes(k.toLowerCase()) || (k === 'Ibu' && punya.includes('balita'));
    const byCat = {}; POOL.filter(t => boleh(t[0])).forEach(t => (byCat[t[0]] = byCat[t[0]] || []).push(t));
    const cats = Object.keys(byCat), mix = [];
    for (let i = 0, mx = Math.max(...cats.map(k => byCat[k].length)); i < mx; i++) cats.forEach(k => { if (byCat[k][i]) mix.push(byCat[k][i]); });
    const nw = new Date(), doy = Math.floor((nw - new Date(nw.getFullYear(), 0, 0)) / 864e5) + nw.getFullYear() * 7;
    const SET = Array.from({ length: 5 }, (_, j) => mix[(doy * 5 + j) % mix.length]);
    let ti = 0, tt;
    $('tip-dots').innerHTML = SET.map((_, i) => '<button type="button" class="tip-dot" data-i="' + i + '" title="Tips ' + (i + 1) + '"></button>').join('');
    function showTip(n) {
        ti = (n + SET.length) % SET.length;
        const t = SET[ti], b = $('tip-body'), bar = $('tip-bar');
        $('tip-cat').innerText = t[0]; $('tip-cat').className = 'px-2 py-0.5 rounded-md text-[10px] font-bold ' + (CHIP[t[0]] || CHIP.Umum);
        $('tip-title').innerText = t[1]; $('tip-desc').innerText = t[2];
        $('tip-label').innerText = 'Tips hari ini ' + (ti + 1) + '/' + SET.length;
        document.querySelectorAll('.tip-dot').forEach((d, i) => d.classList.toggle('on', i === ti));
        [b, bar].forEach(x => { x.style.animation = 'none'; void x.offsetWidth; x.style.animation = ''; });
        b.classList.add('tip-in'); clearTimeout(tt); tt = setTimeout(() => showTip(ti + 1), 9000);
    }
    $('tip-prev').onclick = () => showTip(ti - 1); $('tip-next').onclick = () => showTip(ti + 1);
    $('tip-dots').onclick = e => { const d = e.target.closest('.tip-dot'); if (d) showTip(+d.dataset.i); };
    showTip(0);

    /* ---------- Pusat edukasi ---------- */
    const EDU = {
        balita: { items: [
            ['fa-baby', 'ASI eksklusif', 'Beri hanya ASI selama 6 bulan pertama, lalu lanjutkan ASI bersama MPASI hingga usia 2 tahun.', 'bg-emerald-50 text-emerald-600'],
            ['fa-bowl-food', 'MPASI bergizi', 'Mulai usia 6 bulan. Sertakan protein hewani (telur, ikan, hati ayam) setiap hari.', 'bg-teal-50 text-teal-600'],
            ['fa-weight-scale', 'Timbang tiap bulan', 'Berat yang tidak naik dua bulan berturut-turut perlu ditindaklanjuti kader atau bidan.', 'bg-indigo-50 text-indigo-600'],
            ['fa-puzzle-piece', 'Stimulasi harian', 'Ajak bicara, bernyanyi, dan bermain agar perkembangan otak dan bahasa anak terlatih.', 'bg-amber-50 text-amber-600']],
          imun: [['0 bln', 'HB-0'], ['1 bln', 'BCG & Polio'], ['2-4 bln', 'DPT-HB-Hib & Polio'], ['9 bln', 'Campak-Rubella'], ['18 bln', 'Lanjutan DPT & MR']] },
        remaja: { items: [
            ['fa-pills', 'Tablet tambah darah', 'Remaja putri dianjurkan minum 1 tablet setiap minggu untuk mencegah anemia.', 'bg-rose-50 text-rose-600'],
            ['fa-apple-whole', 'Isi piringku', 'Sarapan, perbanyak sayur dan buah, kurangi gorengan serta minuman manis.', 'bg-emerald-50 text-emerald-600'],
            ['fa-person-running', 'Aktif bergerak', 'Bergerak minimal 60 menit per hari, bisa berupa olahraga, jalan kaki, atau bersepeda.', 'bg-indigo-50 text-indigo-600'],
            ['fa-moon', 'Tidur & kesehatan mental', 'Tidur 8-10 jam. Ceritakan beban pikiran pada orang tua, guru, atau tenaga kesehatan.', 'bg-amber-50 text-amber-600']] },
        lansia: { items: [
            ['fa-heart-pulse', 'Cek tekanan darah', 'Periksa rutin. Hasil 140/90 mmHg atau lebih pada beberapa kali ukur perlu konsultasi.', 'bg-rose-50 text-rose-600'],
            ['fa-droplet', 'Cek gula darah', 'Periksa berkala, terutama bila sering haus, sering buang air kecil, atau berat badan turun tanpa sebab.', 'bg-indigo-50 text-indigo-600'],
            ['fa-shoe-prints', 'Aktif & cegah jatuh', 'Jalan kaki ringan atau senam lansia, dan pastikan lantai rumah tidak licin.', 'bg-emerald-50 text-emerald-600'],
            ['fa-carrot', 'Gizi seimbang', 'Kurangi garam, gula, dan lemak. Cukupi protein, sayur, buah, dan air putih.', 'bg-amber-50 text-amber-600']] }
    };
    function showEdu(k) {
        if (!EDU[k]) k = 'balita';
        document.querySelectorAll('#edu-tabs button').forEach(b => b.classList.toggle('on', b.dataset.k === k));
        $('edu-body').innerHTML = EDU[k].items.map((x, i) =>
            '<div class="pop flex gap-3 p-4 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:shadow-md hover:-translate-y-0.5 transition" style="--d:' + i * 70 + 'ms">' +
            '<div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 ' + x[3] + '"><i class="fa-solid ' + x[0] + '"></i></div>' +
            '<div><h4 class="text-sm font-bold text-slate-800">' + x[1] + '</h4><p class="text-xs text-slate-500 leading-relaxed mt-0.5">' + x[2] + '</p></div></div>').join('');
        $('edu-extra').innerHTML = EDU[k].imun ? '<h4 class="text-sm font-bold text-slate-800 mb-3"><i class="fa-solid fa-syringe text-emerald-500 mr-1.5"></i>Jalur imunisasi dasar anak</h4>' +
            '<div class="flex overflow-x-auto pb-2">' + EDU[k].imun.map((s, i) =>
                '<div class="pop flex-1 min-w-[110px] text-center relative" style="--d:' + (250 + i * 120) + 'ms">' +
                (i ? '<span class="absolute top-4 right-1/2 w-full h-0.5 bg-emerald-200"></span>' : '') +
                '<span class="relative z-10 mx-auto w-8 h-8 rounded-full bg-emerald-500 text-white text-xs font-black flex items-center justify-center ring-4 ring-white">' + (i + 1) + '</span>' +
                '<p class="text-xs font-extrabold text-slate-700 mt-2">' + s[0] + '</p><p class="text-[11px] text-slate-500 px-1">' + s[1] + '</p></div>').join('') +
            '</div><p class="text-[11px] text-slate-400">Jadwal dapat menyesuaikan ketentuan terbaru. Tanyakan ke kader atau bidan.</p>' : '';
    }
    document.querySelectorAll('#edu-tabs button').forEach(b => b.onclick = () => showEdu(b.dataset.k));
    const guessKey = txt => /lansia/i.test(txt) ? 'lansia' : /remaja/i.test(txt) ? 'remaja' : 'balita';
    showEdu(guessKey(@json(($sasaranAktif['label'] ?? '') . ' ' . ($sasaranAktif['kategori'] ?? ''))));

    /* ---------- Checklist ---------- */
    const chks = document.querySelectorAll('.chk');
    chks.forEach(b => b.onclick = () => {
        b.classList.toggle('on');
        const n = document.querySelectorAll('.chk.on').length;
        $('chk-count').innerText = n + '/' + chks.length; $('chk-bar').style.width = (n / chks.length * 100) + '%'; $('chk-done').classList.toggle('hidden', n < chks.length);
    });

    /* ---------- IMT ---------- */
    const umurMap = @json($umurMap);
    const num = v => parseFloat(String(v).replace(',', '.'));
    function updateImt() {
        const b = num($('ringkasan-berat') && $('ringkasan-berat').innerText), t = num($('ringkasan-tinggi') && $('ringkasan-tinggi').innerText);
        const val = $('imt-val'), lab = $('imt-label'), note = $('imt-note'), bar = $('imt-bar'), mark = $('imt-mark');
        if (!(b > 0 && t > 0)) { val.innerText = '-'; lab.innerText = 'Belum ada data'; lab.className = 'px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-200 text-slate-600'; bar.style.opacity = .35; return; }
        const imt = b / Math.pow(t / 100, 2), umur = umurMap[kategori + '|' + pasienId];
        val.innerText = imt.toFixed(1);
        if (umur === null || umur === undefined || umur < 18) {
            lab.innerText = 'Dinilai petugas'; lab.className = 'px-2.5 py-1 rounded-lg text-[11px] font-bold bg-indigo-100 text-indigo-700'; bar.style.opacity = .3;
            note.innerText = 'Status gizi anak dan remaja di bawah 18 tahun dinilai petugas memakai kurva pertumbuhan WHO sesuai usia, bukan batas IMT dewasa. Angka di atas hanya gambaran.'; return;
        }
        const B = [[14, 18.5], [18.5, 25], [25, 27], [27, 35]], i = imt < 18.5 ? 0 : imt < 25 ? 1 : imt < 27 ? 2 : 3;
        const frac = Math.min(Math.max((imt - B[i][0]) / (B[i][1] - B[i][0]), 0), 1);
        const S = [['Kurus', 'bg-amber-100 text-amber-700', 'Berat badan di bawah ideal. Tambah makanan bergizi dan konsultasikan ke tenaga kesehatan.'],
            ['Normal', 'bg-emerald-100 text-emerald-700', 'Berat badan ideal. Pertahankan pola makan seimbang dan rajin bergerak.'],
            ['Gemuk', 'bg-orange-100 text-orange-700', 'Berat badan berlebih. Kurangi gula dan gorengan, serta perbanyak aktivitas fisik.'],
            ['Obesitas', 'bg-rose-100 text-rose-700', 'Risiko penyakit meningkat. Konsultasikan program penurunan berat badan ke tenaga kesehatan.']][i];
        lab.innerText = S[0]; lab.className = 'px-2.5 py-1 rounded-lg text-[11px] font-bold ' + S[1];
        note.innerText = S[2] + ' (Panduan umum untuk usia 18 tahun ke atas, bukan diagnosis.)';
        bar.style.opacity = 1; mark.style.left = (i * 25 + frac * 25) + '%';
    }

    /* ---------- Grafik ---------- */
    const C1 = '#10b981', C2 = '#6366f1';
    const grafikUrl = '{{ Route::has("user.dashboard.chart") ? route("user.dashboard.chart") : "" }}';
    const box = $('apexGrowthChart'), emptyEl = $('grafik-empty'), noticeEl = $('grafik-single-notice'), loadEl = $('grafik-loading');
    let chart = null, periode = @json($grafikPeriodeAktif), kategori = @json($sasaranAktif['kategori'] ?? null),
        pasienId = @json($sasaranAktif['id'] ?? null), tahun = @json($grafikTahunAktif) || new Date().getFullYear();

    const ok = v => v !== null && v !== undefined && !isNaN(v);
    function fill(arr) {
        const o = arr.slice(); let prev = -1;
        arr.forEach((v, i) => {
            if (!ok(v)) return;
            for (let j = prev + 1; prev >= 0 && j < i; j++) o[j] = +(arr[prev] + (v - arr[prev]) * (j - prev) / (i - prev)).toFixed(1);
            prev = i;
        });
        return o;
    }
    function prep(labels, b, t) {
        b = Array.isArray(b) ? b : []; t = Array.isArray(t) ? t : [];
        const vb = b.filter(ok), vt = t.filter(ok);
        const rng = (a, pad, d) => a.length ? [Math.max(0, Math.floor(Math.min(...a) - pad)), Math.ceil(Math.max(...a) + pad)] : d;
        return { labels: labels || [], b: fill(b), t: fill(t), n: vb.length, yb: rng(vb, 3, [5, 25]), yt: rng(vt, 8, [50, 120]) };
    }
    const axis = (title, color, r, opp) => ({ opposite: !!opp, min: r[0], max: r[1], tickAmount: 4,
        title: { text: title, style: { color: color, fontWeight: 700, fontSize: '11px' } }, labels: { style: { colors: color, fontWeight: 700, fontSize: '11px' } } });
    const series = p => [{ name: 'Berat Badan (Kg)', type: 'area', data: p.b }, { name: 'Tinggi Badan (Cm)', type: 'line', data: p.t }];

    function renderChart(labels, b, t) {
        const p = prep(labels, b, t);
        noticeEl.classList.toggle('hidden', p.n !== 1);
        if (chart) {
            chart.updateOptions({ xaxis: { categories: p.labels }, yaxis: [axis('Berat (Kg)', C1, p.yb), axis('Tinggi (Cm)', C2, p.yt, true)] }, false, true);
            chart.updateSeries(series(p), true); return;
        }
        chart = new ApexCharts(box, {
            chart: { type: 'area', height: 340, toolbar: { show: false }, fontFamily: 'inherit', animations: { enabled: true, easing: 'easeinout', speed: 900, dynamicAnimation: { speed: 500 } } },
            colors: [C1, C2], series: series(p),
            stroke: { curve: 'smooth', width: [3.5, 3.5], lineCap: 'round' },
            fill: { type: ['gradient', 'solid'], gradient: { shade: 'light', type: 'vertical', shadeIntensity: .2, opacityFrom: .35, opacityTo: .05, stops: [0, 95, 100] } },
            markers: { size: [6, 6], strokeColors: '#fff', strokeWidth: 3, hover: { size: 9 } },
            dataLabels: { enabled: false }, legend: { show: false },
            xaxis: { categories: p.labels, axisBorder: { show: false }, axisTicks: { show: false }, labels: { rotate: 0, hideOverlappingLabels: true, style: { fontSize: '11px', fontWeight: 600, colors: '#64748b' } } },
            yaxis: [axis('Berat (Kg)', C1, p.yb), axis('Tinggi (Cm)', C2, p.yt, true)],
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4, padding: { top: 12, right: 10, bottom: 0, left: 10 } },
            tooltip: { shared: true, intersect: false, theme: 'light', y: { formatter: (v, o) => v ? v + (o.seriesIndex === 0 ? ' Kg' : ' Cm') : '-' } }
        });
        chart.render();
    }
    const toggleView = has => { box.classList.toggle('hidden', !has); emptyEl.classList.toggle('hidden', has); };

    async function fetchGrafik() {
        if (!grafikUrl || !kategori || !pasienId) return;
        const q = new URLSearchParams({ periode: periode, kategori: kategori, pasien_id: pasienId });
        if (periode === 'bulanan' && tahun) q.set('tahun', tahun);
        loadEl.classList.remove('hidden');
        try {
            const res = await fetch(grafikUrl + '?' + q, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const d = await res.json();
            if (d.status === 'success' && d.labels && d.labels.length) {
                toggleView(true); renderChart(d.labels, d.berat, d.tinggi);
                if (d.ringkasan) {
                    $('ringkasan-berat').innerText = d.ringkasan.berat_terakhir ?? '-';
                    $('ringkasan-tinggi').innerText = d.ringkasan.tinggi_terakhir ?? '-';
                    $('ringkasan-tanggal').innerText = d.ringkasan.tanggal_terakhir ?? '-';
                }
            } else toggleView(false);
        } catch (e) { console.error('Error fetching growth chart:', e); }
        finally { loadEl.classList.add('hidden'); updateImt(); }
    }

    const pills = document.querySelectorAll('.filter-pill-btn');
    pills.forEach(btn => btn.addEventListener('click', function () {
        pills.forEach(b => b.classList.remove('on')); this.classList.add('on');
        periode = this.dataset.periode;
        const nav = $('grafik-year-nav'); if (nav) nav.classList.toggle('hidden', periode !== 'bulanan');
        fetchGrafik();
    }));
    const sasaran = $('grafik-sasaran-select');
    if (sasaran) sasaran.addEventListener('change', function () {
        const s = this.value.split('|'); kategori = s[0]; pasienId = s[1];
        showEdu(guessKey(this.options[this.selectedIndex].text + ' ' + kategori)); fetchGrafik();
    });
    const yr = $('grafik-year-select');
    if (yr) {
        yr.addEventListener('change', () => { tahun = yr.value; fetchGrafik(); });
        $('grafik-year-prev').addEventListener('click', () => { if (yr.selectedIndex > 0) { yr.selectedIndex--; tahun = yr.value; fetchGrafik(); } });
        $('grafik-year-next').addEventListener('click', () => { if (yr.selectedIndex < yr.options.length - 1) { yr.selectedIndex++; tahun = yr.value; fetchGrafik(); } });
    }

    @if($adaGrafik)
        renderChart({!! json_encode($grafikData['labels']) !!}, {!! json_encode($grafikData['berat']) !!}, {!! json_encode($grafikData['tinggi']) !!});
        toggleView(true);
    @endif
    updateImt();

    /* ---------- Statistik otomatis ---------- */
    const statsUrl = '{{ Route::has("user.dashboard.stats") ? route("user.dashboard.stats") : "" }}';
    if (statsUrl) setInterval(() => {
        fetch(statsUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.ok ? r.json() : null)
            .then(d => {
                if (!d || d.status !== 'success') return;
                [['stat-keluarga', d.total_sasaran], ['stat-notif', d.unread_count], ['stat-jadwal', d.total_jadwal]].forEach(([id, v]) => { const n = $(id); if (n && n.innerText != v) countTo(n, +v); });
            }).catch(() => {});
    }, 30000);
});
</script>
@endpush