<div class="bg-white rounded-[2rem] md:rounded-[2.5rem] shadow-[0_10px_40px_-15px_rgba(0,0,0,0.05)] border border-slate-100 overflow-hidden group hover:shadow-[0_20px_50px_-15px_rgba(245,158,11,0.15)] hover:border-amber-200 transition-all duration-500 relative">
    
    {{-- Aksen Glow Halus di Sudut Kanan Atas --}}
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-amber-400/10 rounded-full blur-3xl group-hover:bg-amber-400/20 transition-colors duration-500 pointer-events-none"></div>

    <div class="p-6 md:p-8 md:px-10 relative z-10">
        {{-- 1. HEADER PROFIL --}}
        <div class="flex items-start md:items-center gap-4 md:gap-6 mb-8">
            {{-- Icon Profil (Amber & Orange) --}}
            <div class="w-14 h-14 md:w-16 md:h-16 rounded-[1.2rem] md:rounded-[1.5rem] bg-gradient-to-tr from-amber-400 to-orange-500 text-white flex items-center justify-center shadow-lg shadow-amber-500/30 border-2 border-white shrink-0 group-hover:scale-110 transition-transform duration-500">
                <i class="fas fa-person-cane text-xl md:text-2xl"></i>
            </div>
            
            {{-- Bio & Nama --}}
            <div class="flex-1 min-w-0"> 
                <div class="mb-2">
                    <span class="inline-block px-3 py-1 rounded-full bg-amber-50 text-amber-600 text-[9px] font-black uppercase tracking-[0.2em] border border-amber-100/50 shadow-sm">
                        {{ $data->kategori_sop ?? 'Lansia' }}
                    </span>
                </div>

                <h2 class="text-xl md:text-2xl font-black text-slate-800 font-poppins tracking-tight truncate group-hover:text-amber-600 transition-colors">
                    {{ $data->nama_lengkap }}
                </h2>
                
                <div class="flex items-center gap-2 md:gap-3 mt-1.5 flex-wrap">
                    <span class="text-[11px] font-black text-amber-600 uppercase tracking-widest">
                        <i class="fas fa-hourglass-half text-[10px] mr-1"></i> {{ $data->usia_tahun }} Tahun
                    </span>
                    <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest truncate max-w-[140px] sm:max-w-none">
                        NIK: {{ $data->nik ?? '-' }}
                    </span>
                </div>
            </div>
        </div>

        @php $p = $data->pemeriksaan_terakhir; @endphp

        @if($p)
            {{-- 2. GRID METRIK UTAMA --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5 mb-5">
                
                {{-- Tensi Darah (Diberi Highlight Merah jika Tinggi) --}}
                @php
                    $tensi = $p->tekanan_darah ?? '0/0';
                    $sistolik = intval(explode('/', $tensi)[0]);
                    $isTensiTinggi = $sistolik >= 140;
                @endphp
                <div class="{{ $isTensiTinggi ? 'bg-rose-50 border-rose-200 shadow-rose-100/50' : 'bg-slate-50/70 border-slate-100 hover:border-rose-100 hover:shadow-rose-100/30' }} p-4 md:p-5 rounded-[1.5rem] md:rounded-[1.8rem] border hover:bg-white hover:shadow-lg transition-all duration-300 relative overflow-hidden">
                    @if($isTensiTinggi)
                        <div class="absolute top-0 right-0 w-8 h-8 bg-rose-500 rounded-bl-[1.5rem] flex items-center justify-center text-white shadow-sm">
                            <i class="fas fa-exclamation text-[10px] animate-pulse"></i>
                        </div>
                    @endif
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-full {{ $isTensiTinggi ? 'bg-rose-200 text-rose-700' : 'bg-rose-100/50 text-rose-600' }} flex items-center justify-center">
                            <i class="fas fa-heart-pulse text-[12px]"></i>
                        </div>
                        <p class="text-[9px] font-black {{ $isTensiTinggi ? 'text-rose-500' : 'text-slate-400' }} uppercase tracking-widest">Tensi</p>
                    </div>
                    <h4 class="text-lg md:text-xl font-black {{ $isTensiTinggi ? 'text-rose-700' : 'text-slate-800' }} tracking-tight">{{ $tensi }}</h4>
                </div>

                {{-- Berat Badan --}}
                <div class="bg-slate-50/70 p-4 md:p-5 rounded-[1.5rem] md:rounded-[1.8rem] border border-slate-100 hover:bg-white hover:border-amber-100 hover:shadow-lg hover:shadow-amber-100/30 transition-all duration-300">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-full bg-amber-100/50 text-amber-600 flex items-center justify-center">
                            <i class="fas fa-weight text-[12px]"></i>
                        </div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Berat</p>
                    </div>
                    <h4 class="text-lg md:text-xl font-black text-slate-800 tracking-tight">{{ $p->berat_badan ?? '-' }} <span class="text-[10px] text-slate-400 font-bold uppercase">kg</span></h4>
                </div>

                {{-- Status IMT --}}
                <div class="bg-slate-50/70 p-4 md:p-5 rounded-[1.5rem] md:rounded-[1.8rem] border border-slate-100 hover:bg-white hover:border-emerald-100 hover:shadow-lg hover:shadow-emerald-100/30 transition-all duration-300">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-100/50 text-emerald-600 flex items-center justify-center">
                            <i class="fas fa-chart-simple text-[12px]"></i>
                        </div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Status IMT</p>
                    </div>
                    <h4 class="text-[14px] md:text-[15px] font-black text-slate-800 tracking-tight line-clamp-1">{{ $data->status_imt ?? 'Normal' }}</h4>
                </div>

                {{-- Kemandirian --}}
                <div class="bg-slate-50/70 p-4 md:p-5 rounded-[1.5rem] md:rounded-[1.8rem] border border-slate-100 hover:bg-white hover:border-sky-100 hover:shadow-lg hover:shadow-sky-100/30 transition-all duration-300">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-full bg-sky-100/50 text-sky-600 flex items-center justify-center">
                            <i class="fas fa-walking text-[12px]"></i>
                        </div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Kemandirian</p>
                    </div>
                    <h4 class="text-[14px] md:text-[15px] font-black text-slate-800 tracking-tight line-clamp-1">{{ $p->kemandirian ?? 'Mandiri' }}</h4>
                </div>
            </div>

            {{-- 3. EXTRA INFO: Catatan Penyakit (Full Width Card) --}}
            <div class="mb-8 bg-amber-50/50 hover:bg-amber-50 border border-amber-100 p-4 md:p-5 rounded-[1.5rem] md:rounded-[1.8rem] flex items-start gap-4 transition-colors">
                <div class="w-10 h-10 rounded-xl bg-white text-amber-500 flex items-center justify-center shadow-sm border border-amber-100/50 shrink-0 mt-0.5">
                    <i class="fas fa-notes-medical text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[9px] font-black text-amber-600/70 uppercase tracking-widest mb-1">Catatan Kesehatan / Penyakit Bawaan</p>
                    <p class="text-[12px] md:text-[13px] font-bold text-slate-700 leading-relaxed truncate md:whitespace-normal">
                        {{ $data->info_penyakit ?: 'Tidak ada catatan penyakit bawaan.' }}
                    </p>
                </div>
            </div>

        @else
            {{-- STATE KOSONG --}}
            <div class="mb-8 bg-slate-50/60 rounded-[1.8rem] border border-slate-100 p-5 md:p-6 flex items-start md:items-center gap-5">
                <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 text-slate-300 flex items-center justify-center shrink-0 shadow-sm">
                    <i class="fas fa-stethoscope text-xl"></i>
                </div>
                <div class="flex-1">
                    <h4 class="text-[14px] font-black text-slate-700 font-poppins tracking-tight">Belum Ada Pengukuran</h4>
                    <p class="text-[12px] font-medium text-slate-500 mt-1 leading-relaxed">Data vitalitas kesehatan lansia belum tercatat. Pastikan rutin menghadiri Posyandu Lansia.</p>
                </div>
            </div>
        @endif

        {{-- 4. FOOTER & TOMBOL CALL-TO-ACTION TUNGGAL --}}
        <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-[1rem] bg-slate-50 text-slate-400 flex items-center justify-center border border-slate-200 shrink-0 shadow-inner">
                    <i class="far fa-clock text-[14px]"></i>
                </div>
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Pemeriksaan Terakhir</p>
                    <p class="text-[12px] font-bold text-slate-700 mt-1.5">{{ $p ? \Carbon\Carbon::parse($p->created_at)->translatedFormat('d F Y') : 'Belum Tersedia' }}</p>
                </div>
            </div>
            
            {{-- Tombol Detail Riwayat Terpadu --}}
            <a href="{{ route('user.riwayat.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white rounded-[1.2rem] text-[11px] font-black uppercase tracking-[0.15em] transition-all duration-300 shadow-lg shadow-amber-500/30 hover:shadow-amber-500/50 active:scale-95 group/btn">
                Detail Rekam Medis
                <i class="fas fa-arrow-right text-[11px] group-hover/btn:translate-x-1 transition-transform"></i>
            </a>
        </div>
    </div>
</div>