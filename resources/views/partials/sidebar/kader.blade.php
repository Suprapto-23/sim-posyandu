
@php
    $route = request()->route()->getName();
    $isDataWarga = Str::startsWith($route, 'kader.data.');
    
    // =================================================================================
    // NEXUS PREMIUM COLOR THEME & 120FPS ANIMATION TUNING
    // =================================================================================
    
    // Kurva animasi super gesit tapi mendarat halus (Out Expo)
    $smoothBezier = 'ease-[cubic-bezier(0.16,1,0.3,1)]';

    $navActive   = 'bg-white/90 backdrop-blur-md text-blue-600 shadow-[0_8px_30px_-4px_rgba(37,99,235,0.15)] border border-white/80 translate-x-1.5';
    $navPassive  = 'text-slate-500 hover:text-slate-800 hover:bg-white/50 border border-transparent hover:translate-x-1.5 hover:shadow-[0_4px_12px_rgba(0,0,0,0.02)]';
    
    $iconActive  = 'text-blue-500 drop-shadow-[0_4px_10px_rgba(37,99,235,0.35)] scale-110';
    $iconPassive = 'text-slate-400 group-hover:text-blue-500 group-hover:scale-110 transition-all duration-300 ' . $smoothBezier;

    $subAktif    = 'flex items-center text-[13px] font-bold text-blue-600 py-2.5 transition-all duration-300 relative translate-x-1';
    $subPasif    = 'flex items-center text-[13px] font-medium text-slate-500 hover:text-blue-600 py-2.5 transition-all duration-300 relative hover:translate-x-1';
@endphp

<aside id="sidebar" 
    class="fixed inset-y-0 left-0 z-50 w-[290px] flex flex-col transition-transform duration-[600ms] {{ $smoothBezier }} bg-slate-50/60 backdrop-blur-[24px] border-r border-white/80 shadow-[8px_0_32px_rgba(0,0,0,0.03)] layer-gpu"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full xl:translate-x-0'"
>
    {{-- EFEK CAHAYA --}}
    <div class="absolute top-0 left-0 w-[300px] h-[300px] bg-blue-400/15 blur-[100px] rounded-full -ml-20 -mt-20 pointer-events-none"></div>
    <div class="absolute bottom-0 right-0 w-[300px] h-[300px] bg-sky-300/15 blur-[100px] rounded-full -mr-20 -mb-20 pointer-events-none"></div>

    {{-- 1. BRANDING LOGO --}}
    <div class="h-24 flex items-center gap-4 px-8 shrink-0 relative z-10 border-b border-slate-200/50">
        <a href="{{ route('kader.dashboard') }}" class="spa-route flex items-center gap-4 w-full group outline-none">
            <div class="w-11 h-11 rounded-[14px] bg-gradient-to-br from-blue-500 to-sky-400 text-white flex items-center justify-center shadow-[0_8px_20px_rgba(59,130,246,0.3)] group-hover:scale-105 group-hover:-rotate-3 group-hover:shadow-[0_12px_25px_rgba(59,130,246,0.4)] transition-all duration-300 {{ $smoothBezier }} border border-white/40 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-tr from-white/0 via-white/30 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-500 {{ $smoothBezier }}"></div>
                <i class="ph-fill ph-shield-check text-[24px] relative z-10"></i>
            </div>
            <div class="flex flex-col">
                <h1 class="text-[22px] font-black text-slate-800 tracking-tight font-poppins leading-none">Kader<span class="text-blue-500">Care</span></h1>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.3em] mt-1.5 opacity-80">Sistem Pintar</p>
            </div>
        </a>
    </div>

    {{-- 2. MENU NAVIGASI UTAMA --}}
    <nav class="flex-1 overflow-y-auto px-5 py-8 space-y-8 relative z-10 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]">
        
        {{-- SECTION 1: IKHTISAR --}}
        <div>
            <p class="px-4 text-[10px] font-bold text-slate-400/80 uppercase tracking-[0.25em] mb-3">Ikhtisar Sistem</p>
            <div class="space-y-1.5">
                <a href="{{ route('kader.dashboard') }}" class="spa-route flex items-center gap-4 px-4 py-3.5 rounded-2xl text-[13.5px] font-semibold transition-all duration-300 {{ $smoothBezier }} group relative {{ $route == 'kader.dashboard' ? $navActive : $navPassive }}">
                    @if($route == 'kader.dashboard')
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-blue-500 rounded-r-full shadow-[0_0_12px_rgba(59,130,246,0.5)]"></div>
                    @endif
                    <i class="ph ph-stack text-[22px] w-6 text-center {{ $route == 'kader.dashboard' ? $iconActive : $iconPassive }}"></i>
                    <span class="tracking-wide">Dashboard Utama</span>
                </a>
            </div>
        </div>

        {{-- SECTION 2: TUGAS LAPANGAN --}}
        <div>
            <p class="px-4 text-[10px] font-bold text-slate-400/80 uppercase tracking-[0.25em] mb-3">Tugas Lapangan</p>
            <div class="space-y-1.5">
                
                {{-- 2.1 Registrasi Hadir --}}
                <a href="{{ route('kader.absensi.index') }}" class="spa-route flex items-center gap-4 px-4 py-3.5 rounded-2xl text-[13.5px] font-semibold transition-all duration-300 {{ $smoothBezier }} group relative {{ $route == 'kader.absensi.index' ? $navActive : $navPassive }}">
                    @if($route == 'kader.absensi.index')
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-blue-500 rounded-r-full shadow-[0_0_12px_rgba(59,130,246,0.5)]"></div>
                    @endif
                    <i class="ph ph-user-check text-[22px] w-6 text-center {{ $route == 'kader.absensi.index' ? $iconActive : $iconPassive }}"></i>
                    <span class="tracking-wide">Registrasi Hadir</span>
                </a>

                {{-- 2.2 Pengukuran Fisik --}}
                <a href="{{ route('kader.pemeriksaan.index') }}" class="spa-route flex items-center gap-4 px-4 py-3.5 rounded-2xl text-[13.5px] font-semibold transition-all duration-300 {{ $smoothBezier }} group relative {{ Str::startsWith($route, 'kader.pemeriksaan') ? $navActive : $navPassive }}">
                    @if(Str::startsWith($route, 'kader.pemeriksaan'))
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-blue-500 rounded-r-full shadow-[0_0_12px_rgba(59,130,246,0.5)]"></div>
                    @endif
                    <i class="ph ph-stethoscope text-[22px] w-6 text-center {{ Str::startsWith($route, 'kader.pemeriksaan') ? $iconActive : $iconPassive }}"></i>
                    <span class="tracking-wide">Pengukuran Fisik</span>
                </a>

                {{-- 2.3 Log Imunisasi (Read Only) --}}
                <a href="{{ route('kader.imunisasi.index') }}" class="spa-route flex items-center gap-4 px-4 py-3.5 rounded-2xl text-[13.5px] font-semibold transition-all duration-300 {{ $smoothBezier }} group relative {{ Str::startsWith($route, 'kader.imunisasi') ? $navActive : $navPassive }}">
                    @if(Str::startsWith($route, 'kader.imunisasi'))
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-blue-500 rounded-r-full shadow-[0_0_12px_rgba(59,130,246,0.5)]"></div>
                    @endif
                    <i class="ph ph-syringe text-[22px] w-6 text-center {{ Str::startsWith($route, 'kader.imunisasi') ? $iconActive : $iconPassive }}"></i>
                    <span class="tracking-wide">Log Imunisasi</span>
                </a>

            </div>
        </div>

        {{-- SECTION 3: DATABASE WARGA --}}
        <div>
            <p class="px-4 text-[10px] font-bold text-slate-400/80 uppercase tracking-[0.25em] mb-3">Database Warga</p>
            <div class="space-y-1.5" x-data="{ openData: {{ $isDataWarga ? 'true' : 'false' }} }">
                <button @click="openData = !openData" class="w-full group flex items-center justify-between px-4 py-3.5 rounded-2xl text-[13.5px] font-semibold transition-all duration-300 {{ $smoothBezier }} relative outline-none {{ $isDataWarga ? $navActive : $navPassive }}">
                    @if($isDataWarga)
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-blue-500 rounded-r-full shadow-[0_0_12px_rgba(59,130,246,0.5)]"></div>
                    @endif
                    <div class="flex items-center gap-4">
                        <i class="ph ph-address-book text-[22px] w-6 text-center {{ $isDataWarga ? $iconActive : $iconPassive }}"></i>
                        <span class="tracking-wide">Data Pasien</span>
                    </div>
                    {{-- Animasi panah --}}
                    <i class="ph ph-caret-down text-[14px] transition-transform duration-300 {{ $smoothBezier }}" :class="openData ? 'rotate-180 text-blue-500' : 'text-slate-400 group-hover:text-blue-400'"></i>
                </button>
                
                {{-- Dropdown Collapse --}}
                <div x-show="openData" 
                     x-transition:enter="transition-all ease-[cubic-bezier(0.16,1,0.3,1)] duration-300" 
                     x-transition:enter-start="opacity-0 max-h-0 -translate-y-2" 
                     x-transition:enter-end="opacity-100 max-h-[300px] translate-y-0" 
                     x-transition:leave="transition-all ease-[cubic-bezier(0.16,1,0.3,1)] duration-200"
                     x-transition:leave-start="opacity-100 max-h-[300px] translate-y-0"
                     x-transition:leave-end="opacity-0 max-h-0 -translate-y-2"
                     class="overflow-hidden" x-cloak>
                    
                    <div class="pl-12 pr-2 py-2 mt-1 relative">
                        <div class="absolute left-[26px] top-3 bottom-3 w-[2px] bg-slate-200/60 rounded-full"></div>
                        
                        @foreach([
                            ['route' => 'kader.data.balita.index', 'label' => 'Balita & Anak', 'active' => Str::startsWith($route, 'kader.data.balita')],
                            ['route' => 'kader.data.ibu-hamil.index', 'label' => 'Ibu Hamil', 'active' => Str::startsWith($route, 'kader.data.ibu-hamil')],
                            ['route' => 'kader.data.remaja.index', 'label' => 'Remaja', 'active' => Str::startsWith($route, 'kader.data.remaja')],
                            ['route' => 'kader.data.lansia.index', 'label' => 'Lansia', 'active' => Str::startsWith($route, 'kader.data.lansia')],
                        ] as $item)
                        <a href="{{ route($item['route']) }}" class="spa-route {{ $item['active'] ? $subAktif : $subPasif }}">
                            <div class="absolute left-[-22px] top-1/2 -translate-y-1/2 rounded-full transition-all duration-300 {{ $smoothBezier }} z-10 {{ $item['active'] ? 'w-2.5 h-2.5 bg-blue-500 ring-4 ring-blue-100 shadow-[0_0_8px_rgba(59,130,246,0.5)]' : 'w-1.5 h-1.5 bg-slate-300 left-[-21px]' }}"></div>
                            <span class="tracking-wide">{{ $item['label'] }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 4: MANAJEMEN --}}
        <div>
            <p class="px-4 text-[10px] font-bold text-slate-400/80 uppercase tracking-[0.25em] mb-3">Manajemen</p>
            <div class="space-y-1.5">
                <a href="{{ route('kader.jadwal.index') }}" class="spa-route flex items-center gap-4 px-4 py-3.5 rounded-2xl text-[13.5px] font-semibold transition-all duration-300 {{ $smoothBezier }} group relative {{ Str::startsWith($route, 'kader.jadwal') ? $navActive : $navPassive }}">
                    <i class="ph ph-calendar-check text-[22px] w-6 text-center {{ Str::startsWith($route, 'kader.jadwal') ? $iconActive : $iconPassive }}"></i>
                    <span class="tracking-wide">Agenda Posyandu</span>
                </a>
                <a href="{{ route('kader.laporan.index') }}" class="spa-route flex items-center gap-4 px-4 py-3.5 rounded-2xl text-[13.5px] font-semibold transition-all duration-300 {{ $smoothBezier }} group relative {{ Str::startsWith($route, 'kader.laporan') ? $navActive : $navPassive }}">
                    <i class="ph ph-file-text text-[22px] w-6 text-center {{ Str::startsWith($route, 'kader.laporan') ? $iconActive : $iconPassive }}"></i>
                    <span class="tracking-wide">Laporan Admin</span>
                </a>
            </div>
        </div>
        
    </nav>

    {{-- 3. BOTTOM WIDGET --}}
    <div class="p-5 mt-auto relative z-10 shrink-0">
        <div class="p-4 rounded-[22px] bg-white/60 border border-white/80 shadow-[0_8px_30px_-5px_rgba(0,0,0,0.05)] backdrop-blur-2xl hover:bg-white/90 hover:shadow-[0_15px_40px_-5px_rgba(37,99,235,0.12)] transition-all duration-300 {{ $smoothBezier }} group/widget">
            
            <div class="flex items-center gap-3 mb-4 px-1">
                <div class="relative shrink-0 group/ava">
                    <img src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name ?? 'Kader').'&background=3b82f6&color=fff' }}" 
                         class="w-11 h-11 rounded-[14px] object-cover ring-2 ring-white/80 shadow-sm transition-all duration-300 {{ $smoothBezier }} group-hover/ava:scale-110 group-hover/ava:rotate-2" alt="Profile">
                    <div class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-sky-400 border-[2.5px] border-white rounded-full shadow-sm"></div>
                </div>
                <div class="flex-1 min-w-0 transition-transform duration-300 group-hover/widget:translate-x-1">
                    <h4 class="text-[13px] font-bold text-slate-800 truncate font-poppins">{{ Auth::user()->name ?? 'Kader Aktif' }}</h4>
                    <p class="text-[9px] font-bold text-blue-500 uppercase tracking-[0.2em] mt-0.5">Sistem Online</p>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" @click="window.dispatchEvent(new CustomEvent('spa-start'))" class="w-full py-3 bg-rose-50/80 hover:bg-rose-500 text-rose-500 hover:text-white rounded-[14px] text-[11px] font-bold uppercase tracking-[0.15em] transition-all duration-300 {{ $smoothBezier }} border border-rose-100 hover:border-rose-500 hover:shadow-[0_8px_20px_rgba(244,63,94,0.3)] flex items-center justify-center gap-2 group/btn active:scale-95">
                    <i class="ph ph-power text-[16px] group-hover/btn:scale-110 transition-transform duration-300"></i>
                    Akhiri Sesi
                </button>
            </form>
        </div>
    </div>
</aside>