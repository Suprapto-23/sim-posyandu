@php 
    $route = request()->route()->getName() ?? '';
    
    // ========================================================================
    // NEXUS ACTIVE STATE ENGINE (CLINICAL CYAN THEME)
    // ========================================================================
    $menuAktif = 'bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-bold shadow-[0_8px_16px_rgba(6,182,212,0.25)] border border-cyan-400/30 transform scale-[1.02]';
    $menuPasif = 'text-slate-500 font-medium hover:bg-slate-50 hover:text-cyan-600 transition-all border border-transparent hover:border-slate-100';
    $iconAktif = 'text-white drop-shadow-md';
    $iconPasif = 'text-slate-400 group-hover:text-cyan-500 transition-colors';

    // Failsafe Query untuk menghitung antrian Pemeriksaan Klinis
    try {
        $pendingCount = \App\Models\Pemeriksaan::pending()->count();
    } catch (\Exception $e) {
        $pendingCount = 0;
    }
@endphp

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-[280px] bg-white/95 backdrop-blur-xl border-r border-slate-100 transform xl:translate-x-0 transition-transform duration-500 cubic-bezier(0.4, 0, 0.2, 1) flex flex-col shadow-[20px_0_50px_-20px_rgba(0,0,0,0.1)] xl:shadow-none">
    
    {{-- 1. BRAND LOGO PREMIUM (CLINICAL EDITION) --}}
    <div class="h-[85px] flex items-center px-8 border-b border-slate-100/60 shrink-0 bg-transparent">
        <div class="flex items-center gap-4 w-full cursor-pointer group" onclick="window.location.href='{{ route('bidan.dashboard') }}'">
            <div class="w-[42px] h-[42px] rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 text-white flex items-center justify-center shadow-[0_8px_15px_rgba(6,182,212,0.3)] shrink-0 group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 border border-cyan-400/50">
                <i class="fas fa-user-nurse text-xl drop-shadow-sm"></i>
            </div>
            <div class="flex-1 min-w-0 pt-0.5">
                <h1 class="text-[22px] font-black text-slate-900 tracking-tight truncate font-poppins leading-none">Bidan<span class="text-cyan-500">Care</span></h1>
            </div>
            <button @click.stop="sidebarOpen = false" class="xl:hidden w-8 h-8 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-xl transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
    </div>
    
    {{-- 2. NAVIGASI UTAMA --}}
    <nav class="flex-1 overflow-y-auto px-6 py-8 custom-scrollbar space-y-8 bg-transparent">
        
        {{-- Grup 1: Ikhtisar Sistem --}}
        <div>
            <p class="px-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 font-poppins">Ikhtisar Sistem</p>
            <a href="{{ route('bidan.dashboard') }}" class="spa-route group flex items-center gap-4 px-4 py-3.5 rounded-[18px] text-[13.5px] transition-all duration-300 {{ $route == 'bidan.dashboard' ? $menuAktif : $menuPasif }}">
                <div class="w-6 flex justify-center shrink-0"><i class="fas fa-chart-pie text-[18px] {{ $route == 'bidan.dashboard' ? $iconAktif : $iconPasif }} transition-transform duration-300 group-hover:scale-110"></i></div>
                <span class="transition-transform duration-300 {{ $route != 'bidan.dashboard' ? 'group-hover:translate-x-1' : '' }}">Dashboard</span>
            </a>
        </div>

        {{-- Grup 2: Layanan Medis --}}
        <div>
            <p class="px-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 font-poppins">Layanan Medis</p>
            <div class="space-y-2">
                {{-- Pemeriksaan Klinis --}}
                <a href="{{ route('bidan.pemeriksaan.index') }}" class="spa-route group flex items-center justify-between px-4 py-3.5 rounded-[18px] text-[13.5px] transition-all duration-300 {{ Str::startsWith($route, 'bidan.pemeriksaan') ? $menuAktif : $menuPasif }}">
                    <div class="flex items-center gap-4">
                        <div class="w-6 flex justify-center shrink-0"><i class="fas fa-stethoscope text-[18px] {{ Str::startsWith($route, 'bidan.pemeriksaan') ? $iconAktif : $iconPasif }} transition-transform duration-300 group-hover:scale-110"></i></div>
                        <span class="transition-transform duration-300 {{ !Str::startsWith($route, 'bidan.pemeriksaan') ? 'group-hover:translate-x-1' : '' }}">Pemeriksaan Klinis</span>
                    </div>
                    @if($pendingCount > 0)
                        <span class="bg-rose-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full animate-pulse shadow-sm">{{ $pendingCount }}</span>
                    @endif
                </a>

                {{-- Vaksinasi & Imunisasi --}}
                <a href="{{ route('bidan.imunisasi.index') }}" class="spa-route group flex items-center gap-4 px-4 py-3.5 rounded-[18px] text-[13.5px] transition-all duration-300 {{ Str::startsWith($route, 'bidan.imunisasi') ? $menuAktif : $menuPasif }}">
                    <div class="w-6 flex justify-center shrink-0"><i class="fas fa-syringe text-[18px] {{ Str::startsWith($route, 'bidan.imunisasi') ? $iconAktif : $iconPasif }} transition-transform duration-300 group-hover:scale-110"></i></div>
                    <span class="transition-transform duration-300 {{ !Str::startsWith($route, 'bidan.imunisasi') ? 'group-hover:translate-x-1' : '' }}">Vaksinasi & Imunisasi</span>
                </a>
            </div>
        </div>

        {{-- Grup 3: Arsip & Database --}}
        <div>
            <p class="px-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 font-poppins">Arsip & Database</p>
            <div class="space-y-2">
                {{-- Rekam Medis (EMR) --}}
                <a href="{{ route('bidan.rekam-medis.index') }}" class="spa-route group flex items-center gap-4 px-4 py-3.5 rounded-[18px] text-[13.5px] transition-all duration-300 {{ Str::startsWith($route, 'bidan.rekam-medis') ? $menuAktif : $menuPasif }}">
                    <div class="w-6 flex justify-center shrink-0"><i class="fas fa-folder-open text-[18px] {{ Str::startsWith($route, 'bidan.rekam-medis') ? $iconAktif : $iconPasif }} transition-transform duration-300 group-hover:scale-110"></i></div>
                    <span class="transition-transform duration-300 {{ !Str::startsWith($route, 'bidan.rekam-medis') ? 'group-hover:translate-x-1' : '' }}">Rekam Medis (EMR)</span>
                </a>
            </div>
        </div>

        {{-- Grup 4: Administrasi & Pelaporan --}}
        <div>
            <p class="px-2 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 font-poppins">Administrasi & Pelaporan</p>
            <div class="space-y-2">
                {{-- Kelola Jadwal --}}
                <a href="{{ route('bidan.jadwal.index') }}" class="spa-route group flex items-center gap-4 px-4 py-3.5 rounded-[18px] text-[13.5px] transition-all duration-300 {{ Str::startsWith($route, 'bidan.jadwal') ? $menuAktif : $menuPasif }}">
                    <div class="w-6 flex justify-center shrink-0"><i class="fas fa-calendar-alt text-[18px] {{ Str::startsWith($route, 'bidan.jadwal') ? $iconAktif : $iconPasif }} transition-transform duration-300 group-hover:scale-110"></i></div>
                    <span class="transition-transform duration-300 {{ !Str::startsWith($route, 'bidan.jadwal') ? 'group-hover:translate-x-1' : '' }}">Kelola Jadwal</span>
                </a>
                
                {{-- Laporan Medis --}}
                <a href="{{ route('bidan.laporan.index') }}" class="spa-route group flex items-center gap-4 px-4 py-3.5 rounded-[18px] text-[13.5px] transition-all duration-300 {{ Str::startsWith($route, 'bidan.laporan') ? $menuAktif : $menuPasif }}">
                    <div class="w-6 flex justify-center shrink-0"><i class="fas fa-print text-[18px] {{ Str::startsWith($route, 'bidan.laporan') ? $iconAktif : $iconPasif }} transition-transform duration-300 group-hover:scale-110"></i></div>
                    <span class="transition-transform duration-300 {{ !Str::startsWith($route, 'bidan.laporan') ? 'group-hover:translate-x-1' : '' }}">Laporan Medis</span>
                </a>
            </div>
        </div>
    </nav>
    
    {{-- 3. BOTTOM INDICATOR (ONLINE STATUS) --}}
    <div class="p-6 bg-transparent border-t border-slate-100/60 flex justify-center items-center shrink-0">
        <div class="px-5 py-2.5 rounded-full bg-cyan-50/80 border border-cyan-100/50 flex items-center gap-2.5 shadow-sm hover:shadow-md hover:bg-cyan-50 transition-all cursor-default">
            <div class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-cyan-500"></span>
            </div>
            <p class="text-[11px] font-black text-cyan-600 uppercase tracking-widest">Akses Klinis Aktif</p>
        </div>
    </div>
</aside>