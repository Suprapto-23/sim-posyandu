<div class="h-full flex flex-col bg-white rounded-[2.5rem] shadow-[0_12px_45px_-15px_rgba(0,0,0,0.04)] border border-slate-100 overflow-hidden group hover:shadow-[0_25px_55px_-15px_rgba(20,184,166,0.18)] hover:border-teal-200 transition-all duration-500 relative">
    
    {{-- Ambient Ambient Glow --}}
    <div class="absolute -top-24 -right-24 w-48 h-48 bg-teal-400/10 rounded-full blur-3xl group-hover:bg-teal-400/20 transition-colors duration-500 pointer-events-none"></div>

    {{-- KONTEN UTAMA --}}
    <div class="p-6 md:p-8 relative z-10 flex-1 flex flex-col">
        
        {{-- 1. IDENTITAS UTAMA & KTP DIGITAL BALITA --}}
        <div class="flex items-start gap-4 md:gap-5 mb-6 shrink-0 pb-5 border-b border-slate-100">
            <div class="w-14 h-14 md:w-16 md:h-16 rounded-[1.3rem] md:rounded-[1.6rem] bg-gradient-to-tr from-teal-500 to-emerald-500 text-white flex items-center justify-center shadow-lg shadow-teal-500/30 border-2 border-white shrink-0 group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
                <i class="fas fa-baby text-xl md:text-2xl"></i>
            </div>
            
            <div class="flex-1 min-w-0"> 
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="px-2.5 py-0.5 rounded-md bg-teal-50 text-teal-600 text-[9px] font-black uppercase tracking-[0.15em] border border-teal-100/50">
                        {{ $data->kode_balita ?? 'Anak' }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[9px] font-black uppercase tracking-[0.15em]">
                        {{ \Carbon\Carbon::parse($data->tanggal_lahir)->diff(now())->y }} Thn {{ \Carbon\Carbon::parse($data->tanggal_lahir)->diff(now())->m }} Bln
                    </span>
                </div>

                <h2 class="text-lg md:text-xl font-black text-slate-800 font-poppins tracking-tight truncate group-hover:text-teal-600 transition-colors">
                    {{ $data->nama_lengkap }}
                </h2>
                
                <div class="flex items-center gap-2 mt-1 flex-wrap text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                    <span class="{{ $data->jenis_kelamin == 'L' ? 'text-sky-600 font-black' : 'text-pink-500 font-black' }}">
                        <i class="fas {{ $data->jenis_kelamin == 'L' ? 'fa-mars' : 'fa-venus' }} mr-1"></i>{{ $data->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </span>
                    <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                    <span>NIK: {{ $data->nik ?? '-' }}</span>
                </div>
            </div>
        </div>

        @php $p = $data->pemeriksaan_terakhir; @endphp

        {{-- KOMPLEKSITAS BARU: JIKA DATA PEMERIKSAAN ADA --}}
        @if($p)
            {{-- 2. METRIK ANTROPOMETRI UTAMA --}}
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Hasil Pengukuran Fisik</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                
                {{-- Berat Badan --}}
                <div class="bg-slate-50/80 p-3.5 rounded-2xl border border-slate-100/70 hover:bg-white hover:border-teal-100 hover:shadow-md transition-all">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1.5"><i class="fas fa-weight text-teal-500 mr-1"></i> Berat</p>
                    <h4 class="text-base font-black text-slate-800 tracking-tight">{{ $p->berat_badan ?? '-' }} <span class="text-[9px] text-slate-400 font-bold uppercase">kg</span></h4>
                </div>

                {{-- Tinggi Badan --}}
                <div class="bg-slate-50/80 p-3.5 rounded-2xl border border-slate-100/70 hover:bg-white hover:border-sky-100 hover:shadow-md transition-all">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1.5"><i class="fas fa-ruler-vertical text-sky-500 mr-1"></i> Tinggi</p>
                    <h4 class="text-base font-black text-slate-800 tracking-tight">{{ $p->tinggi_badan ?? '-' }} <span class="text-[9px] text-slate-400 font-bold uppercase">cm</span></h4>
                </div>

                {{-- Lingkar Kepala --}}
                <div class="bg-slate-50/80 p-3.5 rounded-2xl border border-slate-100/70 hover:bg-white hover:border-indigo-100 hover:shadow-lg transition-all">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1.5"><i class="fas fa-brain text-indigo-500 mr-1"></i> Kepala</p>
                    <h4 class="text-base font-black text-slate-800 tracking-tight">{{ $p->lingkar_kepala ?? '-' }} <span class="text-[9px] text-slate-400 font-bold uppercase">cm</span></h4>
                </div>

                {{-- Lingkar Lengan Atas (LILA) - Kompleksitas Tambahan Standard Kemenkes --}}
                <div class="bg-slate-50/80 p-3.5 rounded-2xl border border-slate-100/70 hover:bg-white hover:border-amber-100 hover:shadow-lg transition-all">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1.5"><i class="fas fa-tape text-amber-500 mr-1"></i> LILA</p>
                    <h4 class="text-base font-black text-slate-800 tracking-tight">{{ $p->lila ?? '-' }} <span class="text-[9px] text-slate-400 font-bold uppercase">cm</span></h4>
                </div>
            </div>

            {{-- 3. VALIDASI & DIAGNOSA KLINIS (STANDARD WHO / KEMENKES) --}}
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Status Gizi & Antropometri WHO</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
                
                {{-- Indikator BB/U (Berat menurut Umur) --}}
                @php
                    $bbu = strtolower($p->status_bbu ?? $p->status_gizi ?? '');
                    $bbuClass = str_contains($bbu, 'baik') || str_contains($bbu, 'normal') ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 
                               (str_contains($bbu, 'kurang') || str_contains($bbu, 'risiko') ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-rose-50 text-rose-700 border-rose-100');
                @endphp
                <div class="p-3 border rounded-xl {{ $bbuClass }} flex flex-col justify-between">
                    <span class="text-[8px] font-black uppercase tracking-wider block opacity-70">BB / U (Berat/Umur)</span>
                    <span class="text-[11px] font-black uppercase tracking-wide mt-1 block leading-tight">{{ $p->status_bbu ?? $p->status_gizi ?? 'Normal' }}</span>
                </div>

                {{-- Indikator TB/U (Tinggi menurut Umur - Deteksi Stunting) --}}
                @php
                    $tbu = strtolower($p->status_tbu ?? '');
                    $tbuClass = str_contains($tbu, 'pendek') || str_contains($tbu, 'stunting') ? 'bg-rose-50 text-rose-700 border-rose-100' : 
                               (str_contains($tbu, 'tinggi') ? 'bg-sky-50 text-sky-700 border-sky-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100');
                @endphp
                <div class="p-3 border rounded-xl {{ $tbuClass }} flex flex-col justify-between">
                    <span class="text-[8px] font-black uppercase tracking-wider block opacity-70">TB / U (Tinggi/Umur)</span>
                    <span class="text-[11px] font-black uppercase tracking-wide mt-1 block leading-tight">{{ $p->status_tbu ?? 'Normal (Baik)' }}</span>
                </div>

                {{-- Indikator BB/TB (Massa Tubuh Proporsional) --}}
                @php
                    $bbtb = strtolower($p->status_bbtb ?? '');
                    $bbtbClass = str_contains($bbtb, 'wasting') || str_contains($bbtb, 'buruk') ? 'bg-rose-50 text-rose-700 border-rose-100' : 
                                (str_contains($bbtb, 'gemuk') || str_contains($bbtb, 'lebih') ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100');
                @endphp
                <div class="p-3 border rounded-xl {{ $bbtbClass }} flex flex-col justify-between">
                    <span class="text-[8px] font-black uppercase tracking-wider block opacity-70">BB / TB (Proporsional)</span>
                    <span class="text-[11px] font-black uppercase tracking-wide mt-1 block leading-tight">{{ $p->status_bbtb ?? 'Gizi Baik' }}</span>
                </div>
            </div>

            {{-- 4. TRACKING INTERVENSI WAJIB (Suplemen Kemenkes) --}}
            <div class="grid grid-cols-2 gap-4 mb-6 pt-4 border-t border-slate-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl {{ ($data->vitamin_a ?? true) ? 'bg-orange-50 text-orange-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center text-xs border border-orange-100/50 shrink-0">
                        <i class="fas fa-capsules"></i>
                    </div>
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none">Capsul Vit A</p>
                        <p class="text-[11px] font-black {{ ($data->vitamin_a ?? true) ? 'text-orange-600' : 'text-slate-500' }} mt-1">{{ ($data->vitamin_a ?? true) ? 'Sudah Diberikan' : 'Belum Jadwal' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl {{ ($data->obat_cacing ?? true) ? 'bg-pink-50 text-pink-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center text-xs border border-pink-100/50 shrink-0">
                        <i class="fas fa-pills"></i>
                    </div>
                    <div>
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none">Obat Cacing</p>
                        <p class="text-[11px] font-black {{ ($data->obat_cacing ?? true) ? 'text-pink-600' : 'text-slate-500' }} mt-1">{{ ($data->obat_cacing ?? true) ? 'Sudah Diberikan' : 'Belum Jadwal' }}</p>
                    </div>
                </div>
            </div>

        @else
            {{-- STATE DATA BELUM TERCATAT (Defensive UX) --}}
            <div class="my-auto py-8 bg-slate-50/60 rounded-[1.8rem] border border-slate-100 p-5 flex items-start gap-4 flex-1">
                <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 text-slate-300 flex items-center justify-center shrink-0 shadow-sm">
                    <i class="fas fa-clipboard-list text-xl"></i>
                </div>
                <div class="flex-1">
                    <h4 class="text-[13px] font-black text-slate-700 font-poppins tracking-tight">Belum Ada Riwayat Klinis</h4>
                    <p class="text-[11px] font-medium text-slate-400 mt-1 leading-relaxed">Warga terdaftar namun Bidan belum melakukan verifikasi pengukuran bulan ini di sistem E-Posyandu.</p>
                </div>
            </div>
        @endif

        {{-- 5. FOOTER & EMBEDDED METADATA (Ditekan Rata Bawah Menggunakan mt-auto) --}}
        <div class="mt-auto pt-5 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 text-slate-400 flex items-center justify-center shrink-0 shadow-inner">
                    <i class="far fa-calendar-check text-[13px]"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none">Pemeriksaan Terakhir</p>
                    <p class="text-[11px] font-bold text-slate-700 mt-1 truncate">
                        {{ $p ? \Carbon\Carbon::parse($p->tanggal_periksa)->translatedFormat('d M Y') : 'Belum Ada Sesi' }}
                    </p>
                </div>
            </div>
            
            {{-- Action Button Utama --}}
            <a href="{{ route('user.balita.show', $data->id) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-6 py-3.5 bg-gradient-to-r from-teal-500 to-emerald-500 hover:from-teal-600 hover:to-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-[0.15em] transition-all duration-300 shadow-md shadow-teal-500/20 hover:shadow-teal-500/40 active:scale-95 group/btn shrink-0">
                Buka Buku KIA Digital
                <i class="fas fa-arrow-right text-[10px] group-hover/btn:translate-x-1 transition-transform"></i>
            </a>
        </div>
    </div>
</div>