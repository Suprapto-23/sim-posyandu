<div class="h-full flex flex-col bg-white rounded-[2rem] md:rounded-[2.5rem] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.05)] border border-slate-100 overflow-hidden group hover:shadow-[0_20px_50px_-15px_rgba(99,102,241,0.15)] hover:border-indigo-200 transition-all duration-500 relative">
    
    {{-- Aksen Glow Halus --}}
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-400/10 rounded-full blur-3xl group-hover:bg-indigo-400/20 transition-colors duration-500 pointer-events-none"></div>

    {{-- KUNCI PRESISI: flex-1 dan flex-col --}}
    <div class="p-6 md:p-8 md:px-10 relative z-10 flex-1 flex flex-col">
        
        {{-- 1. HEADER PROFIL --}}
        <div class="flex items-start md:items-center gap-4 md:gap-6 mb-8 shrink-0">
            <div class="w-14 h-14 md:w-16 md:h-16 rounded-[1.2rem] md:rounded-[1.5rem] bg-gradient-to-tr from-indigo-500 to-sky-400 text-white flex items-center justify-center shadow-lg shadow-indigo-500/30 border-2 border-white shrink-0 group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-person-snowboarding text-xl md:text-2xl"></i>
            </div>
            
            <div class="flex-1 min-w-0"> 
                <div class="mb-2">
                    <span class="inline-block px-3 py-1 rounded-full bg-indigo-50 text-indigo-600 text-[9px] font-black uppercase tracking-[0.2em] border border-indigo-100/50 shadow-sm">
                        {{ $data->kategori_sop ?? 'Remaja' }}
                    </span>
                </div>

                <h2 class="text-xl md:text-2xl font-black text-slate-800 font-poppins tracking-tight truncate group-hover:text-indigo-700 transition-colors">
                    {{ $data->nama_lengkap }}
                </h2>
                
                <div class="flex items-center gap-2 md:gap-3 mt-1.5 flex-wrap">
                    <span class="text-[11px] font-black text-indigo-600 uppercase tracking-widest">
                        <i class="fas fa-user-graduate text-[10px] mr-1"></i> {{ $data->usia_tahun }} Tahun
                    </span>
                    <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest truncate max-w-[140px] sm:max-w-none">
                        {{ $data->sekolah ?? 'Sekolah belum diisi' }}
                    </span>
                </div>
            </div>
        </div>

        @php $p = $data->pemeriksaan_terakhir; @endphp

        @if($p)
            {{-- 2. GRID METRIK UTAMA --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5 mb-8">
                
                {{-- Berat --}}
                <div class="bg-slate-50/70 p-4 md:p-5 rounded-[1.5rem] md:rounded-[1.8rem] border border-slate-100 hover:bg-white hover:border-indigo-100 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-100/50 text-indigo-600 flex items-center justify-center"><i class="fas fa-weight text-[12px]"></i></div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Berat</p>
                    </div>
                    <h4 class="text-lg md:text-xl font-black text-slate-800 tracking-tight">{{ $p->berat_badan ?? '-' }} <span class="text-[10px] text-slate-400 font-bold uppercase">kg</span></h4>
                </div>

                {{-- Tinggi --}}
                <div class="bg-slate-50/70 p-4 md:p-5 rounded-[1.5rem] md:rounded-[1.8rem] border border-slate-100 hover:bg-white hover:border-sky-100 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-full bg-sky-100/50 text-sky-600 flex items-center justify-center"><i class="fas fa-ruler-vertical text-[12px]"></i></div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Tinggi</p>
                    </div>
                    <h4 class="text-lg md:text-xl font-black text-slate-800 tracking-tight">{{ $p->tinggi_badan ?? '-' }} <span class="text-[10px] text-slate-400 font-bold uppercase">cm</span></h4>
                </div>

                {{-- IMT / BMI --}}
                <div class="bg-slate-50/70 p-4 md:p-5 rounded-[1.5rem] md:rounded-[1.8rem] border border-slate-100 hover:bg-white hover:border-emerald-100 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-100/50 text-emerald-600 flex items-center justify-center"><i class="fas fa-chart-simple text-[12px]"></i></div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">IMT</p>
                    </div>
                    <h4 class="text-lg md:text-xl font-black text-slate-800 tracking-tight">{{ $p->imt ?? '-' }}</h4>
                </div>

                {{-- Status HB --}}
                @php
                    $hb = (float) ($p->hb ?? 0);
                    $isLowHB = $hb > 0 && $hb < 12;
                @endphp
                <div class="{{ $isLowHB ? 'bg-rose-50 border-rose-200' : 'bg-slate-50/70 border-slate-100' }} p-4 md:p-5 rounded-[1.5rem] md:rounded-[1.8rem] border hover:bg-white hover:border-indigo-100 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-full {{ $isLowHB ? 'bg-rose-200 text-rose-700 animate-pulse' : 'bg-rose-100/50 text-rose-600' }} flex items-center justify-center"><i class="fas fa-droplet text-[12px]"></i></div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">HB</p>
                    </div>
                    <h4 class="text-lg md:text-xl font-black {{ $isLowHB ? 'text-rose-700' : 'text-slate-800' }} tracking-tight">
                        {{ $p->hb ?? '-' }} <span class="text-[10px] font-bold uppercase">{{ $hb > 0 ? 'g/dL' : '' }}</span>
                    </h4>
                </div>

            </div>
        @else
            {{-- STATE KOSONG --}}
            <div class="mb-8 bg-slate-50/60 rounded-[1.8rem] border border-slate-100 p-5 md:p-6 flex items-start md:items-center gap-5 flex-1">
                <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 text-slate-300 flex items-center justify-center shrink-0 shadow-sm"><i class="fas fa-user-clock text-xl"></i></div>
                <div class="flex-1">
                    <h4 class="text-[14px] font-black text-slate-700 font-poppins tracking-tight">Belum Ada Data</h4>
                    <p class="text-[12px] font-medium text-slate-500 mt-1 leading-relaxed">Rekam medis remaja belum tercatat. Silakan lakukan pengecekan rutin di Posyandu Remaja.</p>
                </div>
            </div>
        @endif

        {{-- 3. FOOTER & TOMBOL CALL-TO-ACTION (KUNCI mt-auto) --}}
        <div class="mt-auto pt-6 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-5 shrink-0">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-[1rem] bg-slate-50 text-slate-400 flex items-center justify-center border border-slate-200 shrink-0 shadow-inner"><i class="far fa-clock text-[14px]"></i></div>
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Pembaruan Terakhir</p>
                    <p class="text-[12px] font-bold text-slate-700 mt-1.5">{{ $p ? \Carbon\Carbon::parse($p->tanggal_periksa)->translatedFormat('d F Y') : 'Belum Tersedia' }}</p>
                </div>
            </div>
            
            {{-- PERBAIKAN: Route diarahkan ke detail Remaja yang benar --}}
            <a href="{{ route('user.remaja.show', $data->id) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 bg-gradient-to-r from-indigo-500 to-sky-500 hover:from-indigo-600 hover:to-sky-600 text-white rounded-[1.2rem] text-[11px] font-black uppercase tracking-[0.15em] transition-all duration-300 shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 active:scale-95 group/btn">
                Buka Rekam Medis Remaja
                <i class="fas fa-arrow-right text-[11px] group-hover/btn:translate-x-1 transition-transform"></i>
            </a>
        </div>
    </div>
</div>