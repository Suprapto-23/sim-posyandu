<div class="bg-white rounded-[2rem] md:rounded-[2.5rem] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.05)] border border-slate-100 overflow-hidden group hover:shadow-[0_20px_50px_-15px_rgba(244,63,94,0.15)] hover:border-rose-200 transition-all duration-500 relative">
    
    {{-- Aksen Glow Halus di Sudut Kanan Atas --}}
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-rose-400/10 rounded-full blur-3xl group-hover:bg-rose-400/20 transition-colors duration-500 pointer-events-none"></div>

    <div class="p-6 md:p-8 md:px-10 relative z-10">
        {{-- 1. HEADER PROFIL --}}
        <div class="flex items-start md:items-center gap-4 md:gap-6 mb-8">
            {{-- Icon Profil (Rose & Purple) --}}
            <div class="w-14 h-14 md:w-16 md:h-16 rounded-[1.2rem] md:rounded-[1.5rem] bg-gradient-to-tr from-rose-400 to-purple-500 text-white flex items-center justify-center shadow-lg shadow-rose-500/30 border-2 border-white shrink-0 group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-person-pregnant text-xl md:text-2xl"></i>
            </div>
            
            {{-- Bio & Nama --}}
            <div class="flex-1 min-w-0"> 
                <div class="mb-2">
                    <span class="inline-block px-3 py-1 rounded-full bg-rose-50 text-rose-600 text-[9px] font-black uppercase tracking-[0.2em] border border-rose-100/50 shadow-sm">
                        {{ $data->trimester ?? 'Trimester -' }}
                    </span>
                </div>

                <h2 class="text-xl md:text-2xl font-black text-slate-800 font-poppins tracking-tight truncate group-hover:text-rose-700 transition-colors">
                    {{ $data->nama_lengkap }}
                </h2>
                
                <div class="flex items-center gap-2 md:gap-3 mt-1.5 flex-wrap">
                    <span class="text-[11px] font-black text-rose-600 uppercase tracking-widest">
                        <i class="far fa-calendar-check text-[10px] mr-1"></i> {{ $data->usia_kehamilan ?? 0 }} Minggu
                    </span>
                    <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest truncate max-w-[140px] sm:max-w-none">
                        HPL: {{ $data->hpl ? \Carbon\Carbon::parse($data->hpl)->format('d M Y') : 'Belum Ditentukan' }}
                    </span>
                </div>
            </div>
        </div>

        @php $p = $data->pemeriksaan_terakhir; @endphp

        @if($p)
            {{-- 2. GRID METRIK (Tampilan Presisi Card-in-Card) --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5 mb-8">
                
                {{-- Berat Badan Ibu --}}
                <div class="bg-slate-50/70 p-4 md:p-5 rounded-[1.5rem] md:rounded-[1.8rem] border border-slate-100 hover:bg-white hover:border-rose-100 hover:shadow-lg hover:shadow-rose-100/30 transition-all duration-300">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-full bg-rose-100/50 text-rose-600 flex items-center justify-center">
                            <i class="fas fa-weight text-[12px]"></i>
                        </div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Berat</p>
                    </div>
                    <h4 class="text-lg md:text-xl font-black text-slate-800 tracking-tight">{{ $p->berat_badan ?? '-' }} <span class="text-[10px] text-slate-400 font-bold uppercase">kg</span></h4>
                </div>

                {{-- Tekanan Darah (Tensi) --}}
                <div class="bg-slate-50/70 p-4 md:p-5 rounded-[1.5rem] md:rounded-[1.8rem] border border-slate-100 hover:bg-white hover:border-purple-100 hover:shadow-lg hover:shadow-purple-100/30 transition-all duration-300">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-full bg-purple-100/50 text-purple-600 flex items-center justify-center">
                            <i class="fas fa-heart-pulse text-[12px]"></i>
                        </div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Tensi</p>
                    </div>
                    <h4 class="text-lg md:text-xl font-black text-slate-800 tracking-tight">{{ $p->tekanan_darah ?? '-' }}</h4>
                </div>

                {{-- Status IMT --}}
                <div class="bg-slate-50/70 p-4 md:p-5 rounded-[1.5rem] md:rounded-[1.8rem] border border-slate-100 hover:bg-white hover:border-sky-100 hover:shadow-lg hover:shadow-sky-100/30 transition-all duration-300">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-full bg-sky-100/50 text-sky-600 flex items-center justify-center">
                            <i class="fas fa-child-reaching text-[12px]"></i>
                        </div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">IMT</p>
                    </div>
                    <h4 class="text-lg md:text-xl font-black text-slate-800 tracking-tight">{{ $data->imt_hitung ?? '-' }}</h4>
                </div>

                {{-- Countdown Persalinan (Warna Spesial) --}}
                @php
                    $sisaHari = $data->sisa_hari ?? 0;
                    $isDekat = $sisaHari > 0 && $sisaHari <= 14; 
                @endphp
                <div class="{{ $isDekat ? 'bg-rose-50 border-rose-200 shadow-rose-100/50' : 'bg-slate-50/70 border-slate-100' }} p-4 md:p-5 rounded-[1.5rem] md:rounded-[1.8rem] border hover:bg-white hover:border-rose-100 hover:shadow-lg hover:shadow-rose-100/30 transition-all duration-300">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-full {{ $isDekat ? 'bg-rose-200/50 text-rose-700 animate-pulse' : 'bg-rose-100/50 text-rose-600' }} flex items-center justify-center">
                            <i class="far fa-calendar-alt text-[12px]"></i>
                        </div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Persalinan</p>
                    </div>
                    <h4 class="text-lg md:text-xl font-black {{ $isDekat ? 'text-rose-600' : 'text-slate-800' }} tracking-tight">{{ $sisaHari }} <span class="text-[10px] font-bold uppercase ml-0.5 {{ $isDekat ? 'text-rose-400' : 'text-slate-400' }}">Hari Lagi</span></h4>
                </div>

            </div>
        @else
            {{-- STATE KOSONG --}}
            <div class="mb-8 bg-slate-50/60 rounded-[1.8rem] border border-slate-100 p-5 md:p-6 flex items-start md:items-center gap-5">
                <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 text-slate-300 flex items-center justify-center shrink-0 shadow-sm">
                    <i class="fas fa-notes-medical text-xl"></i>
                </div>
                <div class="flex-1">
                    <h4 class="text-[14px] font-black text-slate-700 font-poppins tracking-tight">Belum Ada Pemeriksaan</h4>
                    <p class="text-[12px] font-medium text-slate-500 mt-1 leading-relaxed">Data pemeriksaan kehamilan belum tercatat oleh Bidan. Silakan lakukan kontrol rutin di Posyandu.</p>
                </div>
            </div>
        @endif

        {{-- 3. FOOTER & TOMBOL CALL-TO-ACTION TUNGGAL --}}
        <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-[1rem] bg-slate-50 text-slate-400 flex items-center justify-center border border-slate-200 shrink-0 shadow-inner">
                    <i class="far fa-clock text-[14px]"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Pemeriksaan Terakhir</p>
                    <p class="text-[12px] font-bold text-slate-700 mt-1.5">{{ $p ? \Carbon\Carbon::parse($p->tanggal_periksa)->translatedFormat('d F Y') : 'Belum Tersedia' }}</p>
                </div>
            </div>
            
            {{-- Tombol Detail Riwayat Terpadu --}}
            <a href="{{ route('user.riwayat.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 bg-gradient-to-r from-rose-400 to-purple-500 hover:from-rose-500 hover:to-purple-600 text-white rounded-[1.2rem] text-[11px] font-black uppercase tracking-[0.15em] transition-all duration-300 shadow-lg shadow-rose-500/30 hover:shadow-rose-500/50 active:scale-95 group/btn">
                Detail Rekam Medis
                <i class="fas fa-arrow-right text-[11px] group-hover/btn:translate-x-1 transition-transform"></i>
            </a>
        </div>
    </div>
</div>