@extends('layouts.user')

@section('content')
<div class="p-4 md:p-8 font-poppins bg-[#f8fafc] min-h-screen">
    
    {{-- HEADER (Gaya Minimalis Rata Tengah) --}}
    <div class="mb-10 text-center max-w-3xl mx-auto animate-slide-up">
        <div class="inline-flex items-center gap-3 px-4 py-2 bg-white rounded-full shadow-sm border border-slate-100 mb-4">
            <span class="w-2.5 h-2.5 rounded-full bg-teal-500 animate-pulse"></span>
            <span class="text-[11px] font-black tracking-widest uppercase text-slate-600">Agenda Posyandu</span>
        </div>
        <h1 class="text-3xl md:text-4xl font-black text-slate-800 tracking-tight mb-3">Jadwal Kegiatan Anda 📅</h1>
        <p class="text-sm font-medium text-slate-500 leading-relaxed">Berikut adalah daftar jadwal Posyandu yang relevan dengan Anda dan keluarga. Pastikan untuk hadir tepat waktu.</p>
    </div>

    {{-- FILTER TABS (Terpusat secara estetis pada layar besar, bisa di-scroll pada layar kecil) --}}
    <div class="flex overflow-x-auto custom-scrollbar pb-4 mb-8 gap-3 justify-start md:justify-center animate-slide-up-1">
        
        {{-- BUG FIX: Ubah 'target' menjadi 'filter' agar sesuai dengan JadwalController --}}
        <a href="{{ route('user.jadwal.index', ['filter' => 'semua']) }}" 
           class="whitespace-nowrap flex items-center gap-2 px-5 py-2.5 rounded-xl border font-bold text-xs transition-all {{ $filterTarget == 'semua' ? 'bg-teal-600 text-white border-teal-600 shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:border-teal-300 hover:bg-teal-50' }}">
            Semua Jadwal
            <span class="px-2 py-0.5 rounded-md text-[10px] {{ $filterTarget == 'semua' ? 'bg-teal-500 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $summary['semua'] }}</span>
        </a>

        @if(in_array('balita', $hakAkses))
            <a href="{{ route('user.jadwal.index', ['filter' => 'balita']) }}" 
               class="whitespace-nowrap flex items-center gap-2 px-5 py-2.5 rounded-xl border font-bold text-xs transition-all {{ $filterTarget == 'balita' ? 'bg-sky-500 text-white border-sky-500 shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:border-sky-300 hover:bg-sky-50' }}">
                <i class="fas fa-baby {{ $filterTarget == 'balita' ? 'text-white' : 'text-sky-500' }}"></i> Balita
                <span class="px-2 py-0.5 rounded-md text-[10px] {{ $filterTarget == 'balita' ? 'bg-sky-400 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $summary['balita'] }}</span>
            </a>
        @endif

        @if(in_array('ibu_hamil', $hakAkses))
            <a href="{{ route('user.jadwal.index', ['filter' => 'ibu_hamil']) }}" 
               class="whitespace-nowrap flex items-center gap-2 px-5 py-2.5 rounded-xl border font-bold text-xs transition-all {{ $filterTarget == 'ibu_hamil' ? 'bg-pink-500 text-white border-pink-500 shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:border-pink-300 hover:bg-pink-50' }}">
                <i class="fas fa-female {{ $filterTarget == 'ibu_hamil' ? 'text-white' : 'text-pink-500' }}"></i> Ibu Hamil
                <span class="px-2 py-0.5 rounded-md text-[10px] {{ $filterTarget == 'ibu_hamil' ? 'bg-pink-400 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $summary['ibu_hamil'] }}</span>
            </a>
        @endif

        @if(in_array('remaja', $hakAkses))
            <a href="{{ route('user.jadwal.index', ['filter' => 'remaja']) }}" 
               class="whitespace-nowrap flex items-center gap-2 px-5 py-2.5 rounded-xl border font-bold text-xs transition-all {{ $filterTarget == 'remaja' ? 'bg-indigo-500 text-white border-indigo-500 shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300 hover:bg-indigo-50' }}">
                <i class="fas fa-user-graduate {{ $filterTarget == 'remaja' ? 'text-white' : 'text-indigo-500' }}"></i> Remaja
                <span class="px-2 py-0.5 rounded-md text-[10px] {{ $filterTarget == 'remaja' ? 'bg-indigo-400 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $summary['remaja'] }}</span>
            </a>
        @endif

        @if(in_array('lansia', $hakAkses))
            <a href="{{ route('user.jadwal.index', ['filter' => 'lansia']) }}" 
               class="whitespace-nowrap flex items-center gap-2 px-5 py-2.5 rounded-xl border font-bold text-xs transition-all {{ $filterTarget == 'lansia' ? 'bg-orange-500 text-white border-orange-500 shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:border-orange-300 hover:bg-orange-50' }}">
                <i class="fas fa-wheelchair {{ $filterTarget == 'lansia' ? 'text-white' : 'text-orange-500' }}"></i> Lansia
                <span class="px-2 py-0.5 rounded-md text-[10px] {{ $filterTarget == 'lansia' ? 'bg-orange-400 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $summary['lansia'] }}</span>
            </a>
        @endif
    </div>

    {{-- KONTEN GRID JADWAL --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-slide-up-2">
        @forelse($jadwalKegiatan as $jadwal)
            @php
                $tgl = \Carbon\Carbon::parse($jadwal->tanggal);
                $isHariIni = $tgl->isToday();
                $isBesok = $tgl->isTomorrow();
                $isTerlewat = $tgl->isPast() && !$isHariIni;
                
                // Styling warna berdasarkan target peserta
                $targetColor = 'text-teal-600 bg-teal-50 border-teal-100';
                $targetLabel = 'Umum / Semua';
                if($jadwal->target_peserta == 'balita') { $targetColor = 'text-sky-600 bg-sky-50 border-sky-100'; $targetLabel = 'Posyandu Balita'; }
                if($jadwal->target_peserta == 'ibu_hamil') { $targetColor = 'text-pink-600 bg-pink-50 border-pink-100'; $targetLabel = 'Ibu Hamil'; }
                if($jadwal->target_peserta == 'remaja') { $targetColor = 'text-indigo-600 bg-indigo-50 border-indigo-100'; $targetLabel = 'Posyandu Remaja'; }
                if($jadwal->target_peserta == 'lansia') { $targetColor = 'text-orange-600 bg-orange-50 border-orange-100'; $targetLabel = 'Posyandu Lansia'; }
            @endphp

            <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-lg transition-all flex flex-col relative group h-full {{ $isTerlewat ? 'opacity-70 grayscale-[30%]' : '' }}">
                
                @if($isHariIni)
                    <div class="absolute top-0 right-0 bg-rose-500 text-white text-[10px] font-black uppercase tracking-widest px-4 py-1.5 rounded-bl-xl z-10 shadow-sm">
                        HARI INI
                    </div>
                @elseif($isBesok)
                    <div class="absolute top-0 right-0 bg-amber-500 text-white text-[10px] font-black uppercase tracking-widest px-4 py-1.5 rounded-bl-xl z-10 shadow-sm">
                        BESOK
                    </div>
                @elseif($isTerlewat)
                    <div class="absolute top-0 right-0 bg-slate-200 text-slate-500 text-[10px] font-black uppercase tracking-widest px-4 py-1.5 rounded-bl-xl z-10">
                        Selesai
                    </div>
                @endif

                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex items-start gap-4 mb-5">
                        <div class="w-16 h-16 rounded-2xl {{ $isHariIni ? 'bg-teal-500 text-white shadow-md' : ($isTerlewat ? 'bg-slate-100 text-slate-400' : 'bg-slate-50 text-slate-700 border border-slate-200') }} flex flex-col items-center justify-center shrink-0">
                            <span class="text-[11px] font-black uppercase tracking-wider">{{ $tgl->translatedFormat('M') }}</span>
                            <span class="text-2xl font-black leading-none mt-0.5">{{ $tgl->format('d') }}</span>
                        </div>
                        
                        <div>
                            <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider mb-2 border {{ $targetColor }}">{{ $targetLabel }}</span>
                            <h3 class="text-base font-black text-slate-800 leading-tight group-hover:text-teal-600 transition-colors">{{ $jadwal->judul }}</h3>
                        </div>
                    </div>

                    {{-- mt-auto akan memaksa bagian informasi waktu dan lokasi selalu presisi di bagian bawah kartu --}}
                    <div class="space-y-3 mt-auto pt-4 border-t border-slate-100">
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 shrink-0">
                                <i class="far fa-clock text-[11px]"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Waktu Pelaksanaan</p>
                                <p class="text-xs font-bold text-slate-700 mt-0.5">{{ \Carbon\Carbon::parse($jadwal->waktu_mulai)->format('H:i') }} - {{ $jadwal->waktu_selesai ? \Carbon\Carbon::parse($jadwal->waktu_selesai)->format('H:i') : 'Selesai' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 shrink-0">
                                <i class="fas fa-map-marker-alt text-[11px]"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Lokasi / Tempat</p>
                                <p class="text-xs font-bold text-slate-700 mt-0.5">{{ $jadwal->lokasi }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 flex flex-col items-center justify-center bg-white rounded-3xl border-2 border-dashed border-slate-200">
                <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center text-4xl mb-4 shadow-sm">
                    <i class="far fa-calendar-times"></i>
                </div>
                <h3 class="text-lg font-black text-slate-700 mb-1">Jadwal Kosong</h3>
                <p class="text-sm font-medium text-slate-500 text-center max-w-sm leading-relaxed">Belum ada agenda Posyandu untuk kategori yang Anda pilih saat ini.</p>
                @if($filterTarget != 'semua')
                    <a href="{{ route('user.jadwal.index') }}" class="mt-4 text-xs font-bold text-teal-600 hover:text-teal-700 bg-teal-50 px-4 py-2 rounded-xl">Lihat Semua Jadwal</a>
                @endif
            </div>
        @endforelse
    </div>

    @if($jadwalKegiatan->hasPages())
        <div class="mt-8 flex justify-center">
            {{ $jadwalKegiatan->links() }}
        </div>
    @endif

</div>
@endsection