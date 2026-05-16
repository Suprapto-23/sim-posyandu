@php
    $route = request()->route()->getName();
    
    // =================================================================================
    // SMART ROUTE HIGHLIGHTING
    // =================================================================================
    $isDashboard  = $route === 'user.dashboard';
    $isJadwal     = Str::startsWith($route, 'user.jadwal');
    
    // [PERBAIKAN KUNCI] Tambahkan deteksi untuk remaja, lansia, dan ibu_hamil
    $isMonitoring = Str::startsWith($route, 'user.monitoring') || 
                    Str::startsWith($route, 'user.balita') ||
                    Str::startsWith($route, 'user.remaja') ||
                    Str::startsWith($route, 'user.lansia') ||
                    Str::startsWith($route, 'user.ibu_hamil');
                    
    $isRiwayat    = Str::startsWith($route, 'user.riwayat');
    $isNotifikasi = Str::startsWith($route, 'notifikasi') || Str::startsWith($route, 'user.notifikasi');

    // =================================================================================
    // NEXUS PREMIUM COLOR THEME (LIGHT GLASSMORPHISM)
    // =================================================================================
    $navActive   = 'bg-white text-teal-600 shadow-[0_8px_20px_rgba(20,184,166,0.08)] border border-white translate-x-1';
    $navPassive  = 'text-slate-500 hover:text-teal-600 hover:bg-white/60 border border-transparent hover:translate-x-1';
    
    $iconActive  = 'text-teal-500 drop-shadow-[0_4px_8px_rgba(20,184,166,0.3)] scale-110';
    $iconPassive = 'text-slate-400 group-hover:text-teal-500 group-hover:scale-110 transition-all duration-300';
@endphp

<aside id="sidebar" class="fixed md:relative z-50 w-[290px] h-full flex flex-col transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] -translate-x-full md:translate-x-0 overflow-hidden bg-slate-50/80 backdrop-blur-2xl border-r border-white/60 shadow-[4px_0_24px_rgba(0,0,0,0.02)]">
    
    {{-- Efek Cahaya / Bias Kaca (Ambient Glow) --}}
    <div class="absolute top-0 left-0 w-64 h-64 bg-teal-300/10 blur-[80px] rounded-full -ml-20 -mt-20 pointer-events-none"></div>
    <div class="absolute bottom-0 right-0 w-64 h-64 bg-sky-300/10 blur-[80px] rounded-full -mr-20 -mb-20 pointer-events-none"></div>

    {{-- LOGO BRANDING NEXUS --}}
    <div class="h-24 flex items-center gap-4 px-8 shrink-0 relative z-10 border-b border-slate-200/50">
        <a href="{{ route('user.dashboard') }}" class="flex items-center gap-4 w-full group outline-none">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-teal-400 to-emerald-500 text-white flex items-center justify-center shadow-lg shadow-teal-500/20 group-hover:scale-110 group-hover:rotate-3 transition-all duration-500 border-2 border-white relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-tr from-white/0 via-white/30 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
                <i class="fas fa-heart-pulse text-[20px] relative z-10"></i>
            </div>
            <div class="flex flex-col">
                <h1 class="text-[21px] font-black text-slate-800 tracking-tight font-poppins leading-none">Portal<span class="text-teal-500">Warga</span></h1>
                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-[0.3em] mt-1.5">Layanan Mandiri</p>
            </div>
        </a>
    </div>

    {{-- MENU NAVIGASI --}}
    <nav class="flex-1 overflow-y-auto no-scrollbar px-5 py-8 space-y-8 relative z-10">
        
        {{-- SECTION 1: EKSPLORASI --}}
        <div>
            <p class="px-4 text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4">Eksplorasi</p>
            <div class="space-y-2">
                
                {{-- 1. HOME --}}
                <a href="{{ route('user.dashboard') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-[13px] font-bold transition-all duration-300 group relative {{ $isDashboard ? $navActive : $navPassive }}">
                    @if($isDashboard)
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-teal-500 rounded-r-full shadow-[2px_0_8px_rgba(20,184,166,0.6)]"></div>
                    @endif
                    <i class="fas fa-home text-[18px] w-6 text-center {{ $isDashboard ? $iconActive : $iconPassive }}"></i>
                    <span>Beranda Saya</span>
                </a>

                {{-- 2. JADWAL --}}
                <a href="{{ route('user.jadwal.index') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-[13px] font-bold transition-all duration-300 group relative {{ $isJadwal ? $navActive : $navPassive }}">
                    @if($isJadwal)
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-teal-500 rounded-r-full shadow-[2px_0_8px_rgba(20,184,166,0.6)]"></div>
                    @endif
                    <i class="far fa-calendar-alt text-[18px] w-6 text-center {{ $isJadwal ? $iconActive : $iconPassive }}"></i>
                    <span>Agenda Posyandu</span>
                </a>

            </div>
        </div>

        {{-- SECTION 2: MEDIS --}}
        <div>
            <p class="px-4 text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4">Rekam Medis</p>
            <div class="space-y-2">
                
                {{-- 3. PANTAU --}}
                <a href="{{ route('user.monitoring.index') }}" class="flex items-center justify-between px-4 py-3.5 rounded-2xl text-[13px] font-bold transition-all duration-300 group relative {{ $isMonitoring ? $navActive : $navPassive }}">
                    @if($isMonitoring)
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-teal-500 rounded-r-full shadow-[2px_0_8px_rgba(20,184,166,0.6)]"></div>
                    @endif
                    <div class="flex items-center gap-4">
                        <i class="fas fa-heartbeat text-[18px] w-6 text-center {{ $isMonitoring ? $iconActive : $iconPassive }}"></i>
                        <span>Pantau Kesehatan</span>
                    </div>
                </a>

                {{-- 4. RIWAYAT --}}
                <a href="{{ route('user.riwayat.index') }}" class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-[13px] font-bold transition-all duration-300 group relative {{ $isRiwayat ? $navActive : $navPassive }}">
                    @if($isRiwayat)
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-teal-500 rounded-r-full shadow-[2px_0_8px_rgba(20,184,166,0.6)]"></div>
                    @endif
                    <i class="fas fa-notes-medical text-[18px] w-6 text-center {{ $isRiwayat ? $iconActive : $iconPassive }}"></i>
                    <span>Riwayat Terpadu</span>
                </a>

            </div>
        </div>

        {{-- SECTION 3: EKSTRA --}}
        <div>
            <p class="px-4 text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4">Informasi</p>
            <div class="space-y-2">
                <a href="{{ route('user.notifikasi.index') }}" class="flex items-center justify-between px-4 py-3.5 rounded-2xl text-[13px] font-bold transition-all duration-300 group relative {{ $isNotifikasi ? $navActive : $navPassive }}">
                    @if($isNotifikasi)
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-teal-500 rounded-r-full shadow-[2px_0_8px_rgba(20,184,166,0.6)]"></div>
                    @endif
                    <div class="flex items-center gap-4">
                        <i class="far fa-bell text-[18px] w-6 text-center {{ $isNotifikasi ? $iconActive : $iconPassive }}"></i>
                        <span>Pesan Bidan</span>
                        
                        {{-- Fitur Cerdas: Badge Notifikasi Real-time (Opsional jika Anda sudah punya logic badge-nya di backend) --}}
                        {{-- <span class="bg-rose-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full shadow-sm">2</span> --}}
                    </div>
                </a>
            </div>
        </div>

    </nav>

    {{-- BOTTOM PROFILE & LOGOUT CARD --}}
    <div class="p-6 mt-auto relative z-10 shrink-0">
        <div class="p-4 rounded-[24px] bg-white/70 border border-white shadow-[0_8px_30px_rgba(0,0,0,0.03)] space-y-4 backdrop-blur-xl hover:shadow-[0_15px_40px_rgba(20,184,166,0.1)] transition-all duration-300 group/bottom">
            
            {{-- User Mini Profile --}}
            <a href="{{ route('user.profile.edit') }}" class="flex items-center gap-3 px-1 group cursor-pointer outline-none">
                <div class="w-10 h-10 rounded-[14px] bg-teal-50 border border-teal-100/50 flex items-center justify-center text-teal-600 group-hover:bg-teal-500 group-hover:text-white transition-all duration-300 shadow-sm">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[12.5px] font-black text-slate-800 truncate group-hover:text-teal-600 transition-colors">{{ ucwords(Auth::user()->name) }}</p>
                    <p class="text-[8px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-0.5 group-hover:text-teal-500/70 transition-colors">Pengaturan Akun</p>
                </div>
                <div class="w-6 h-6 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-teal-50 transition-colors border border-slate-100">
                    <i class="fas fa-chevron-right text-[9px] text-slate-400 group-hover:text-teal-600"></i>
                </div>
            </a>

            {{-- Logout Button --}}
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="w-full py-3.5 bg-rose-50 hover:bg-rose-500 text-rose-500 hover:text-white rounded-[16px] text-[10px] font-black uppercase tracking-[0.2em] transition-all duration-300 border border-rose-100 hover:border-rose-500 hover:shadow-[0_8px_20px_rgba(244,63,94,0.25)] flex items-center justify-center gap-2 group/logout">
                    <i class="fas fa-power-off text-[13px] group-hover/logout:scale-110 transition-transform"></i>
                    Keluar Sistem
                </button>
            </form>
        </div>
    </div>
</aside>