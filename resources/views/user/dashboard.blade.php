@extends('layouts.user')

@section('title', 'Beranda Saya')

@push('styles')
<style>
    .animate-slide-up { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .animate-slide-up-1 { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.1s forwards; }
    .animate-slide-up-2 { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards; }
    @keyframes slideUpFade { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    
    /* Efek Hover Kartu Menu Cerdas */
    .quick-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .quick-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px -10px rgba(20, 184, 166, 0.3); }
    .quick-icon { transition: all 0.3s ease; }
    .quick-card:hover .quick-icon { transform: scale(1.15) rotate(5deg); }
    
    /* Scrollbar minimalis untuk Kotak Masuk */
    .notif-scroll::-webkit-scrollbar { width: 4px; }
    .notif-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>
@endpush

@section('content')
<div class="w-full pb-10">

    {{-- 1. HERO SECTION --}}
    <div class="animate-slide-up bg-gradient-to-r from-teal-600 to-emerald-500 rounded-[32px] p-8 md:p-10 shadow-[0_15px_40px_-10px_rgba(20,184,166,0.4)] relative overflow-hidden mb-8 text-white flex flex-col md:flex-row md:items-center justify-between gap-6 min-h-[200px]">
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-white opacity-10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-10 bottom-0 opacity-10 pointer-events-none hidden md:block">
            <i class="fas fa-heartbeat text-[150px]"></i>
        </div>
        
        <div class="relative z-10 w-full">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/20 backdrop-blur-md text-white text-[10px] font-black uppercase tracking-widest rounded-full border border-white/30 mb-4 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span> E-Posyandu Aktif
            </span>
            <h2 class="text-3xl md:text-4xl font-black tracking-tight leading-tight font-poppins mb-2">
                Halo, {{ ucwords(Auth::user()->name) }}! 👋
            </h2>
            <p class="text-teal-50 font-medium text-[13px] md:text-[14px] max-w-lg leading-relaxed">
                Pantau jadwal posyandu, rekam medis, dan perkembangan kesehatan keluarga Anda secara presisi dalam satu layar.
            </p>
        </div>
    </div>

    {{-- 2. WARNING NIK (Akses Terkunci) --}}
    @if(isset($pesanError) && $pesanError)
    <div class="animate-slide-up-1 bg-gradient-to-r from-rose-50 to-orange-50 border border-rose-200 p-5 rounded-[24px] flex items-center gap-5 shadow-sm mb-8 relative overflow-hidden">
        <i class="fas fa-id-card-clip absolute -right-4 -bottom-6 text-6xl text-rose-500/10 pointer-events-none"></i>
        <div class="w-12 h-12 rounded-[16px] bg-white text-rose-500 flex items-center justify-center text-2xl shrink-0 shadow-sm border border-rose-100">
            <i class="fas fa-lock animate-pulse"></i>
        </div>
        <div class="flex-1 flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div>
                <h4 class="text-[13px] font-black text-rose-800 uppercase tracking-widest">Akses Terbatas</h4>
                <p class="text-[12px] font-medium text-rose-600 mt-0.5 leading-snug">{{ $pesanError }}</p>
            </div>
            <a href="{{ route('user.profile.edit') }}" class="smooth-route shrink-0 inline-flex items-center gap-2 px-5 py-3 bg-rose-500 hover:bg-rose-600 text-white text-[11px] font-black uppercase tracking-widest rounded-xl shadow-sm transition-all hover:-translate-y-0.5">
                Lengkapi NIK <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    @endif

    {{-- 3. QUICK MENU GRID --}}
    <div class="animate-slide-up-1 grid grid-cols-4 md:grid-cols-5 gap-3 sm:gap-5 mb-10">
        @php
            $menus = [
                ['icon' => 'heartbeat', 'color' => 'rose', 'label' => 'Pantau', 'route' => 'user.monitoring.index'],
                ['icon' => 'file-medical', 'color' => 'sky', 'label' => 'Riwayat', 'route' => 'user.riwayat.index'],
                ['icon' => 'calendar-check', 'color' => 'teal', 'label' => 'Jadwal', 'route' => 'user.jadwal.index'],
                ['icon' => 'bell', 'color' => 'amber', 'label' => 'Pesan', 'route' => 'user.notifikasi.index'],
                ['icon' => 'user-cog', 'color' => 'slate', 'label' => 'Profil', 'route' => 'user.profile.edit'],
            ];
        @endphp

        @foreach($menus as $m)
        <a href="{{ route($m['route']) }}" class="smooth-route bg-white border border-slate-100 rounded-[24px] p-4 flex flex-col items-center justify-center gap-3 quick-card h-full">
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-[16px] bg-{{ $m['color'] }}-50 border border-{{ $m['color'] }}-100 text-{{ $m['color'] }}-500 flex items-center justify-center text-xl sm:text-2xl quick-icon shrink-0">
                <i class="fas fa-{{ $m['icon'] }}"></i>
            </div>
            <span class="text-[10px] sm:text-[11px] font-black text-slate-600 uppercase tracking-widest text-center leading-tight mt-auto">{{ $m['label'] }}</span>
        </a>
        @endforeach
    </div>

    {{-- 4. KONTEN UTAMA (GRID PRESISI) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {{-- KOLOM KIRI (Jadwal & Ringkasan Medis) --}}
        <div class="lg:col-span-7 xl:col-span-8 flex flex-col gap-8 animate-slide-up-2 w-full">
            
            {{-- A. JADWAL TERDEKAT --}}
            <div class="w-full">
                <div class="flex items-center justify-between mb-4 px-1">
                    <h3 class="font-black text-slate-800 text-[15px] uppercase tracking-tight font-poppins"><i class="fas fa-calendar-day text-teal-500 mr-2"></i> Agenda Terdekat</h3>
                    <a href="{{ route('user.jadwal.index') }}" class="smooth-route text-[10px] font-black text-teal-600 bg-teal-50 hover:bg-teal-500 hover:text-white px-4 py-2 rounded-xl transition-all uppercase tracking-widest">Semua Jadwal</a>
                </div>
                
                @if(isset($jadwalTerdekat) && $jadwalTerdekat->isNotEmpty())
                    @php $jadwal = $jadwalTerdekat->first(); @endphp
                    <div class="bg-white rounded-[24px] p-6 border border-slate-200 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden group hover:border-teal-300 transition-colors w-full">
                        <div class="absolute right-0 top-0 w-2 h-full bg-teal-500"></div>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-5">
                            <div class="w-16 h-16 bg-teal-50 text-teal-600 rounded-[18px] flex flex-col items-center justify-center shrink-0 border border-teal-100">
                                <span class="text-[10px] font-black uppercase mb-0.5">{{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('M') }}</span>
                                <span class="text-2xl font-black font-poppins leading-none">{{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('d') }}</span>
                            </div>
                            <div class="flex-1">
                                <span class="inline-block px-2.5 py-1 bg-slate-100 text-slate-500 rounded-md text-[9px] font-black tracking-widest uppercase mb-2 border border-slate-200">
                                    Target: {{ str_replace('_', ' ', $jadwal->target_peserta) }}
                                </span>
                                <h4 class="font-black text-lg text-slate-800 leading-snug mb-2">{{ $jadwal->judul }}</h4>
                                <div class="flex flex-wrap gap-4 text-[12px] font-bold text-slate-500">
                                    <span class="flex items-center gap-1.5"><i class="far fa-clock text-slate-300"></i> Pukul {{ date('H:i', strtotime($jadwal->waktu_mulai)) }} WIB</span>
                                    <span class="flex items-center gap-1.5"><i class="fas fa-map-marker-alt text-slate-300"></i> {{ $jadwal->lokasi }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white border-2 border-dashed border-slate-200 rounded-[24px] p-8 text-center w-full">
                        <div class="w-14 h-14 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-300 text-2xl">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <h4 class="text-[14px] font-black text-slate-700 mb-1">Tidak Ada Jadwal</h4>
                        <p class="text-[12px] font-medium text-slate-500">Belum ada agenda Posyandu dalam waktu dekat.</p>
                    </div>
                @endif
            </div>

            {{-- B. STATUS KESEHATAN MULTI-ROLE (GRID PRESISI) --}}
            @if((isset($dataAnak) && $dataAnak->isNotEmpty()) || isset($dataLansia) || isset($dataRemaja) || isset($dataBumil))
            <div class="w-full flex flex-col h-full">
                <div class="flex items-center justify-between mb-4 px-1 shrink-0">
                    <h3 class="font-black text-slate-800 text-[15px] uppercase tracking-tight font-poppins"><i class="fas fa-file-medical-alt text-rose-500 mr-2"></i> Pantau Kesehatan</h3>
                </div>
                
                {{-- items-stretch memastikan semua kolom di dalam grid memiliki tinggi maksimum yang sama --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-stretch flex-1">
                    
                    {{-- 1. BALITA --}}
                    @if(isset($dataAnak) && $dataAnak->isNotEmpty())
                        @foreach($dataAnak->take(2) as $anak)
                        <a href="{{ route('user.balita.show', $anak->id) }}" class="smooth-route flex flex-col h-full bg-white border border-slate-200 rounded-[24px] p-5 shadow-sm hover:shadow-[0_10px_30px_rgba(244,63,94,0.1)] hover:border-rose-300 transition-all group">
                            <div class="flex items-center gap-3 mb-4 flex-1">
                                <div class="w-10 h-10 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center text-lg shrink-0 border border-rose-100"><i class="fas fa-child"></i></div>
                                <div>
                                    <h4 class="text-[13px] font-black text-slate-800 leading-tight group-hover:text-rose-600 transition-colors line-clamp-1">{{ $anak->nama_lengkap }}</h4>
                                    <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-wider">Kategori: Balita</p>
                                </div>
                            </div>
                            {{-- mt-auto memaksa bagian ini menempel ke bawah secara presisi --}}
                            <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Lihat KMS Buku KIA</span>
                                <div class="w-6 h-6 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-rose-50 group-hover:text-rose-500 transition-colors shrink-0"><i class="fas fa-arrow-right text-[9px]"></i></div>
                            </div>
                        </a>
                        @endforeach
                    @endif

                    {{-- 2. IBU HAMIL --}}
                    @if(isset($dataBumil) && $dataBumil)
                        <a href="{{ route('user.ibu_hamil.show', $dataBumil->id) }}" class="smooth-route flex flex-col h-full bg-white border border-slate-200 rounded-[24px] p-5 shadow-sm hover:shadow-[0_10px_30px_rgba(236,72,153,0.1)] hover:border-pink-300 transition-all group">
                            <div class="flex items-center gap-3 mb-4 flex-1">
                                <div class="w-10 h-10 rounded-full bg-pink-50 text-pink-500 flex items-center justify-center text-lg shrink-0 border border-pink-100"><i class="fas fa-female"></i></div>
                                <div>
                                    <h4 class="text-[13px] font-black text-slate-800 leading-tight group-hover:text-pink-600 transition-colors line-clamp-1">{{ $dataBumil->nama_lengkap }}</h4>
                                    <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-wider">Kehamilan & Kandungan</p>
                                </div>
                            </div>
                            <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Pantau Kehamilan</span>
                                <div class="w-6 h-6 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-pink-50 group-hover:text-pink-500 transition-colors shrink-0"><i class="fas fa-arrow-right text-[9px]"></i></div>
                            </div>
                        </a>
                    @endif

                    {{-- 3. LANSIA --}}
                    @if(isset($dataLansia) && $dataLansia)
                        <a href="{{ route('user.lansia.show', $dataLansia->id) }}" class="smooth-route flex flex-col h-full bg-white border border-slate-200 rounded-[24px] p-5 shadow-sm hover:shadow-[0_10px_30px_rgba(249,115,22,0.1)] hover:border-orange-300 transition-all group">
                            <div class="flex items-center gap-3 mb-4 flex-1">
                                <div class="w-10 h-10 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center text-lg shrink-0 border border-orange-100"><i class="fas fa-wheelchair"></i></div>
                                <div>
                                    <h4 class="text-[13px] font-black text-slate-800 leading-tight group-hover:text-orange-600 transition-colors line-clamp-1">{{ $dataLansia->nama_lengkap }}</h4>
                                    <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-wider">Kategori: Lansia</p>
                                </div>
                            </div>
                            <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Cek Gula & Tensi</span>
                                <div class="w-6 h-6 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-orange-50 group-hover:text-orange-500 transition-colors shrink-0"><i class="fas fa-arrow-right text-[9px]"></i></div>
                            </div>
                        </a>
                    @endif

                    {{-- 4. REMAJA --}}
                    @if(isset($dataRemaja) && $dataRemaja)
                        <a href="{{ route('user.remaja.show', $dataRemaja->id) }}" class="smooth-route flex flex-col h-full bg-white border border-slate-200 rounded-[24px] p-5 shadow-sm hover:shadow-[0_10px_30px_rgba(59,130,246,0.1)] hover:border-blue-300 transition-all group">
                            <div class="flex items-center gap-3 mb-4 flex-1">
                                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-lg shrink-0 border border-blue-100"><i class="fas fa-user-graduate"></i></div>
                                <div>
                                    <h4 class="text-[13px] font-black text-slate-800 leading-tight group-hover:text-blue-600 transition-colors line-clamp-1">{{ $dataRemaja->nama_lengkap }}</h4>
                                    <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-wider">Kategori: Remaja</p>
                                </div>
                            </div>
                            <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">Cek IMT & Anemia</span>
                                <div class="w-6 h-6 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-blue-50 group-hover:text-blue-500 transition-colors shrink-0"><i class="fas fa-arrow-right text-[9px]"></i></div>
                            </div>
                        </a>
                    @endif

                </div>
            </div>
            @endif
        </div>

        {{-- KOLOM KANAN (Kotak Masuk Notifikasi) --}}
        <div class="lg:col-span-5 xl:col-span-4 w-full flex flex-col h-full animate-slide-up-2">
            <div class="flex items-center justify-between mb-4 px-1 shrink-0">
                <h3 class="font-black text-slate-800 text-[15px] uppercase tracking-tight font-poppins"><i class="fas fa-bullhorn text-indigo-500 mr-2"></i> Kotak Masuk</h3>
                <a href="{{ route('user.notifikasi.index') }}" class="smooth-route text-[10px] font-black text-indigo-600 bg-indigo-50 hover:bg-indigo-500 hover:text-white px-4 py-2 rounded-xl transition-all uppercase tracking-widest">Semua</a>
            </div>
            
            <div id="main-notif-wrapper" class="bg-white border border-slate-200 rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.03)] overflow-hidden flex-1 flex flex-col">
                {{-- max-h dihapus dan diganti flex-1 agar tingginya adaptif dengan kolom kiri --}}
                <div class="flex-1 overflow-y-auto notif-scroll p-2 space-y-1">
                    @forelse($notifikasiTerbaru ?? [] as $notif)
                        <a href="{{ route('user.notifikasi.index') }}" class="smooth-route block p-4 hover:bg-slate-50 transition-colors rounded-[18px] {{ !$notif['is_read'] ? 'bg-indigo-50/30 border border-indigo-100/50' : 'border border-transparent' }}">
                            <div class="flex gap-4">
                                <div class="w-10 h-10 rounded-full {{ !$notif['is_read'] ? 'bg-indigo-100 text-indigo-600 border border-indigo-200' : 'bg-slate-100 text-slate-400 border border-slate-200' }} flex items-center justify-center text-lg shrink-0">
                                    <i class="fas {{ !$notif['is_read'] ? 'fa-envelope' : 'fa-envelope-open' }}"></i>
                                </div>
                                <div class="flex-1 min-w-0 pt-0.5">
                                    <div class="flex justify-between items-start gap-2 mb-1">
                                        <h4 class="text-[13px] font-black text-slate-800 truncate pr-2 {{ !$notif['is_read'] ? 'text-indigo-900' : '' }}">{{ $notif['judul'] }}</h4>
                                        @if(!$notif['is_read']) 
                                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0 mt-1 shadow-sm shadow-rose-200 animate-pulse"></span> 
                                        @endif
                                    </div>
                                    <p class="text-[12px] font-medium text-slate-500 line-clamp-2 leading-relaxed">{{ $notif['pesan'] }}</p>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-2 block"><i class="fas fa-clock mr-1"></i> {{ $notif['waktu'] }}</p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-16 px-4 flex flex-col items-center justify-center h-full">
                            <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl text-slate-300">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <h4 class="text-[14px] font-black text-slate-700 mb-1">Kotak Masuk Bersih</h4>
                            <p class="text-[12px] font-medium text-slate-500">Anda sudah membaca semua pemberitahuan.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>
@endsection