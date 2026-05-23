@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;

    $route = request()->route()?->getName() ?? '';
    $name = Auth::user()->name ?? 'Kader Posyandu';
    $initial = strtoupper(substr($name, 0, 1));
    
    // Perluas pengecekan data agar submenu otomatis terbuka jika berada di salah satu halamannya
    $isData = Str::startsWith($route, ['kader.data.', 'kader.balita', 'kader.remaja', 'kader.lansia']);
    
    $to = fn ($name, $fallback = null) => Route::has($name) ? route($name) : (($fallback && Route::has($fallback)) ? route($fallback) : '#');

    $mainMenus = [
        ['Dashboard', 'fa-house', $to('kader.dashboard'), $route === 'kader.dashboard'],
        ['Registrasi Hadir', 'fa-user-check', $to('kader.absensi.index'), $route === 'kader.absensi.index'],
        ['Pengukuran Fisik', 'fa-stethoscope', $to('kader.pemeriksaan.index'), Str::startsWith($route, 'kader.pemeriksaan')],
        ['Log Imunisasi', 'fa-syringe-💉', $to('kader.imunisasi.index'), Str::startsWith($route, 'kader.imunisasi')], // Gunakan fa-syringe jika tanpa emoji
    ];

    // Rapihkan fallback route jika ada perbedaan penamaan di web.php
    $dataMenus = [
        ['Balita', $to('kader.data.balita.index', 'kader.balita.index'), Str::startsWith($route, ['kader.data.balita', 'kader.balita'])],
        ['Remaja', $to('kader.data.remaja.index', 'kader.remaja.index'), Str::startsWith($route, ['kader.data.remaja', 'kader.remaja'])],
        ['Lansia', $to('kader.data.lansia.index', 'kader.lansia.index'), Str::startsWith($route, ['kader.data.lansia', 'kader.lansia'])],
    ];

    $manageMenus = [
        ['Agenda Posyandu', 'fa-calendar-check', $to('kader.jadwal.index'), Str::startsWith($route, 'kader.jadwal')],
        ['Laporan Kader', 'fa-file-lines', $to('kader.laporan.index'), Str::startsWith($route, 'kader.laporan')],
    ];
@endphp

<nav class="side-card" aria-label="Sidebar Kader">
    <!-- Header Sidebar -->
    <div class="side-head">
        <a href="{{ $to('kader.dashboard') }}" class="side-logo" title="Dashboard Kader" @click="if (window.innerWidth < 1024) $dispatch('close-sidebar')">
            <img src="{{ asset('img/logo.png') }}" alt="Logo PosyanduCare">
        </a>
        <button type="button" class="side-close" @click="$dispatch('close-sidebar')" aria-label="Tutup sidebar">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Profil User Info -->
    <div class="side-user">
        <div class="avatar">
            @if(Auth::check() && !empty(Auth::user()->foto))
                <img src="{{ asset('storage/' . Auth::user()->foto) }}" alt="Foto Kader">
            @else
                {{ $initial }}
            @endif
        </div>
        <div class="side-user-info">
            <h4>{{ $name }}</h4>
            <p>Kader Aktif</p>
        </div>
    </div>

    <!-- Scrollable Nav -->
    <div class="side-scroll">
        <!-- Section: Menu Utama -->
        <section class="menu-group">
            <p class="menu-title">Menu Utama</p>
            <div class="menu-list">
                @foreach($mainMenus as [$label, $icon, $url, $active])
                    <!-- Normalisasi format icon jika ada yang menyisipkan emoji -->
                    @php $iconClass = str_replace('-💉', '', $icon); @endphp
                    
                    <a href="{{ $url }}" class="menu-item {{ $active ? 'active' : '' }}" title="{{ $label }}" @click="if (window.innerWidth < 1024) $dispatch('close-sidebar')">
                        <span class="menu-icon"><i class="fa-solid {{ $iconClass }}"></i></span>
                        <span class="menu-text">{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        <!-- Section: Database Warga (Dengan Submenu Dropdown) -->
        <section class="menu-group" x-data="{ open: {{ $isData ? 'true' : 'false' }} }">
            <p class="menu-title">Database Warga</p>
            <div class="menu-list">
                <button type="button" class="menu-item {{ $isData ? 'active' : '' }}" title="Data Pasien" @click="open = !open">
                    <span class="menu-icon"><i class="fa-solid fa-address-book"></i></span>
                    <span class="menu-text">Data Pasien</span>
                    <span class="caret" :class="{ 'rotate-180': open }"><i class="fa-solid fa-chevron-down"></i></span>
                </button>

                <!-- Animasi max-height dan padding dihandle oleh class .open di CSS layout -->
                <div class="submenu" :class="{ 'open': open }">
                    @foreach($dataMenus as [$label, $url, $active])
                        <a href="{{ $url }}" class="{{ $active ? 'active' : '' }}" @click="if (window.innerWidth < 1024) $dispatch('close-sidebar')">
                            <!-- Indikator bulat mikro diganti agar lebih elegan dari sebelumnya -->
                            <i class="fa-solid fa-circle submenu-icon" style="font-size: 6px;"></i>
                            <span>{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Section: Manajemen -->
        <section class="menu-group">
            <p class="menu-title">Manajemen</p>
            <div class="menu-list">
                @foreach($manageMenus as [$label, $icon, $url, $active])
                    <a href="{{ $url }}" class="menu-item {{ $active ? 'active' : '' }}" title="{{ $label }}" @click="if (window.innerWidth < 1024) $dispatch('close-sidebar')">
                        <span class="menu-icon"><i class="fa-solid {{ $icon }}"></i></span>
                        <span class="menu-text">{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        <!-- Section: Sistem -->
        <section class="menu-group mt-2">
            <p class="menu-title">Sistem</p>
            <div class="menu-list">
                <form method="POST" action="{{ route('logout') }}" class="js-logout-form m-0 p-0">
                    @csrf
                    <button type="submit" class="menu-item logout w-full text-left" title="Keluar">
                        <span class="menu-icon"><i class="fa-solid fa-power-off"></i></span>
                        <span class="menu-text">Keluar Aplikasi</span>
                    </button>
                </form>
            </div>
        </section>
    </div>
</nav>