@extends('layouts.kader')

@section('title', 'Rincian Sesi Absensi')
@section('page-name', 'Detail Arsip #' . $absensi->nomor_pertemuan)

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet"></script>
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    /* ====================================================================
       1. GLOBAL HEALTHCARE EMERALD THEME & ANTI-LAG
       ==================================================================== */
    html { scroll-behavior: smooth; }
    body { 
        background-color: #f0fdf6; 
        background-image: 
            radial-gradient(at 0% 0%, hsla(152, 100%, 96%, 1) 0, transparent 50%), 
            radial-gradient(at 100% 0%, hsla(43, 100%, 96%, 1) 0, transparent 50%);
        background-attachment: fixed;
        -webkit-font-smoothing: antialiased; 
        text-rendering: optimizeLegibility;
    }
    .gpu-layer { transform: translateZ(0); will-change: transform, opacity; }

    /* ====================================================================
       2. MODERN GLASSMORPHISM PANELS
       ==================================================================== */
    .glass-panel {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        box-shadow: 0 12px 40px -12px rgba(6, 78, 59, 0.08);
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.7);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .glass-card:hover {
        background: #ffffff;
        border-color: rgba(16, 185, 129, 0.3);
        box-shadow: 0 10px 25px -5px rgba(6, 78, 59, 0.05);
    }

    /* ====================================================================
       3. PREMIUM TABLE & INPUT COMPONENTS
       ==================================================================== */
    .search-pill {
        background-color: rgba(255, 255, 255, 0.7); border: 1px solid rgba(16, 185, 129, 0.2); color: #064e3b; 
        font-size: 0.85rem; font-weight: 600; border-radius: 9999px; 
        padding: 0.6rem 1rem 0.6rem 2.5rem; width: 100%; outline: none; transition: all 0.25s ease;
    }
    .search-pill:focus { background-color: #ffffff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15); }

    .nexus-table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; }
    .nexus-table th { 
        background: rgba(240, 253, 244, 0.95); color: #064e3b; font-size: 0.65rem; font-weight: 900; 
        text-transform: uppercase; letter-spacing: 0.05em; padding: 1rem 1.5rem; 
        border-bottom: 1px solid rgba(16, 185, 129, 0.2); white-space: nowrap; position: sticky; top: 0; z-index: 10; 
    }
    .nexus-table td { padding: 1rem 1.5rem; vertical-align: middle; border-bottom: 1px dashed rgba(203, 213, 225, 0.5); transition: background-color 0.2s; }
    .nexus-table tr:hover td { background-color: rgba(255, 255, 255, 0.9); }
    .nexus-table tr:last-child td { border-bottom: none; }

    /* Custom Scrollbar */
    .custom-scroll::-webkit-scrollbar { width: 6px; }
    .custom-scroll::-webkit-scrollbar-track { background: transparent; }
    .custom-scroll::-webkit-scrollbar-thumb { background: rgba(16, 185, 129, 0.25); border-radius: 10px; }
    .custom-scroll::-webkit-scrollbar-thumb:hover { background: rgba(16, 185, 129, 0.5); }

    @keyframes snappyFadeUp {
        0% { opacity: 0; transform: translateY(12px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .stagger-grid > * { opacity: 0; animation: snappyFadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .stagger-grid > *:nth-child(1) { animation-delay: 50ms; }
    .stagger-grid > *:nth-child(2) { animation-delay: 100ms; }
</style>
@endpush

@section('content')
<div class="max-w-[1350px] mx-auto pb-16 mt-2 relative z-10 gpu-layer stagger-grid">

    {{-- 1. PREMIUM HEADER ACTION BAR --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 mb-6 glass-panel p-6 rounded-[24px] relative overflow-hidden">
        <div class="flex items-center gap-4 relative z-10 w-full md:w-auto">
            <a href="{{ route('kader.absensi.riwayat') }}" class="w-12 h-12 rounded-[14px] bg-white border border-emerald-100 text-emerald-600 flex items-center justify-center hover:bg-emerald-50 hover:text-emerald-800 transition-all shadow-sm shrink-0 active:scale-95" title="Kembali ke Riwayat">
                <i class="ph-bold ph-arrow-left text-[18px]"></i>
            </a>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200/50 text-[9px] font-black uppercase tracking-widest">
                        {{ str_replace('_', ' ', $absensi->kategori) }} {{ $absensi->kategori === 'balita' ? '(12-59 Bln)' : '' }}
                    </span>
                    <span class="text-[9.5px] font-bold text-emerald-700/60 uppercase tracking-widest flex items-center gap-1.5"><i class="ph-bold ph-calendar-blank"></i> {{ \Carbon\Carbon::parse($absensi->tanggal_posyandu)->locale('id')->translatedFormat('d F Y') }}</span>
                </div>
                <h1 class="text-[20px] md:text-[22px] font-black text-emerald-950 tracking-tight font-poppins truncate">{{ $absensi->kode_absensi }}</h1>
            </div>
        </div>
        
        {{-- LOGIKA TOMBOL KOREKSI SMART UPDATE --}}
        @if(\Carbon\Carbon::parse($absensi->tanggal_posyandu)->isToday())
            <a href="{{ route('kader.absensi.index', ['kategori' => $absensi->kategori]) }}" onclick="window.showLoader()" class="w-full md:w-auto px-6 py-3 bg-amber-50 border border-amber-200 text-amber-600 font-black text-[11px] uppercase tracking-widest rounded-[14px] hover:bg-amber-500 hover:text-white transition-all shadow-sm flex items-center justify-center gap-2 active:scale-95 group">
                <i class="ph-bold ph-pencil-simple text-[14px] group-hover:animate-bounce"></i> Koreksi Sesi Ini
            </a>
        @else
            <div class="w-full md:w-auto px-6 py-3 bg-slate-50 border border-slate-200 text-slate-400 font-black text-[11px] uppercase tracking-widest rounded-[14px] flex items-center justify-center gap-2 cursor-not-allowed shadow-sm" title="Data masa lalu sudah dikunci oleh sistem">
                <i class="ph-bold ph-lock-key text-[15px]"></i> Arsip Terkunci
            </div>
        @endif
    </div>

    {{-- 2. GRID KONTEN UTAMA (STRUKTUR TERKUNCI UNTUK PRESISI SEMPURNA) --}}
    <div class="relative w-full">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            {{-- KOLOM KIRI: ANALITIK (BOS TINGGI LAYOUT) --}}
            <aside class="col-span-1 lg:col-span-4 flex flex-col gap-4 relative z-10 w-full">
                
                {{-- Kartu Persentase Kehadiran --}}
                @php $persentase = $totalPasien > 0 ? round(($totalHadir / $totalPasien) * 100) : 0; @endphp
                <div class="bg-gradient-to-br from-emerald-800 to-emerald-950 rounded-[24px] p-6 text-white shadow-[0_15px_30px_-10px_rgba(6,78,59,0.3)] relative overflow-hidden shrink-0 border border-emerald-700">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-amber-400/20 rounded-full blur-2xl pointer-events-none"></div>
                    <h4 class="font-bold text-[10px] text-emerald-300 uppercase tracking-widest mb-1 relative z-10 flex items-center gap-1.5"><i class="ph-fill ph-chart-donut"></i> Tingkat Partisipasi</h4>
                    
                    <div class="flex items-baseline gap-1.5 mb-4 relative z-10">
                        <span class="text-[46px] font-black text-white font-poppins leading-none">{{ $persentase }}</span>
                        <span class="text-xl font-bold text-emerald-400">%</span>
                    </div>
                    
                    <div class="w-full h-2 bg-emerald-950/60 rounded-full overflow-hidden relative z-10 shadow-inner">
                        <div class="h-full bg-gradient-to-r from-amber-400 to-amber-300 rounded-full transition-all duration-1000" style="width: {{ $persentase }}%"></div>
                    </div>
                </div>

                {{-- Stat Cards --}}
                <div class="grid grid-cols-1 gap-3 shrink-0">
                    <div class="glass-card rounded-[18px] p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-[12px] bg-white border border-emerald-100 flex items-center justify-center text-emerald-600 text-[20px] shadow-sm"><i class="ph-fill ph-users-three"></i></div>
                            <div>
                                <p class="text-[9px] font-black text-emerald-700/60 uppercase tracking-widest">Total Terdaftar</p>
                                <h3 class="text-[22px] font-black text-emerald-950 font-poppins leading-none mt-0.5">{{ $totalPasien }}</h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="glass-card rounded-[18px] p-4 flex items-center justify-between border-l-[5px] border-l-emerald-500">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-[12px] bg-emerald-50 text-emerald-600 flex items-center justify-center text-[20px]"><i class="ph-fill ph-check-circle"></i></div>
                            <div>
                                <p class="text-[9px] font-black text-emerald-700/60 uppercase tracking-widest">Warga Hadir</p>
                                <h3 class="text-[22px] font-black text-emerald-600 font-poppins leading-none mt-0.5">{{ $totalHadir }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card rounded-[18px] p-4 flex items-center justify-between border-l-[5px] border-l-rose-500">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-[12px] bg-rose-50 text-rose-500 flex items-center justify-center text-[20px]"><i class="ph-fill ph-x-circle"></i></div>
                            <div>
                                <p class="text-[9px] font-black text-rose-700/60 uppercase tracking-widest">Warga Absen</p>
                                <h3 class="text-[22px] font-black text-rose-600 font-poppins leading-none mt-0.5">{{ $totalAbsen }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sesi Sebelumnya --}}
                <div class="glass-panel rounded-[20px] p-5 flex flex-col flex-1 min-h-[250px] shrink-0">
                    <h3 class="font-black text-emerald-900 text-[10.5px] uppercase tracking-widest mb-3 flex items-center gap-2 pb-3 border-b border-emerald-100 shrink-0">
                        <i class="ph-bold ph-clock-counter-clockwise text-amber-500 text-[15px]"></i> Sesi Sebelumnya
                    </h3>
                    <div class="space-y-1.5 overflow-y-auto pr-1 custom-scroll flex-1 h-0">
                        @forelse($semuaSesi as $sesi)
                            <a href="{{ route('kader.absensi.show', $sesi->id) }}" class="flex items-center gap-3 p-2.5 rounded-[12px] transition-colors border {{ $sesi->id == $absensi->id ? 'bg-emerald-50 border-emerald-200' : 'border-transparent hover:bg-white hover:border-emerald-100' }}">
                                <div class="w-9 h-9 rounded-[10px] flex items-center justify-center shrink-0 {{ $sesi->id == $absensi->id ? 'bg-emerald-600 text-white shadow-md' : 'bg-emerald-100/50 text-emerald-700' }}">
                                    <span class="text-[11.5px] font-black">#{{ $sesi->nomor_pertemuan }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[11px] font-black text-emerald-950 truncate">{{ \Carbon\Carbon::parse($sesi->tanggal_posyandu)->locale('id')->translatedFormat('d M Y') }}</p>
                                    @if($sesi->id == $absensi->id) <span class="text-[8.5px] text-emerald-600 font-bold tracking-widest uppercase">Sedang Dibuka</span> @endif
                                </div>
                            </a>
                        @empty
                            <p class="text-center text-[10px] text-emerald-600/70 font-bold py-4">Belum ada sesi historis lain.</p>
                        @endforelse
                    </div>
                </div>
            </aside>

            <div class="hidden lg:block lg:col-span-8"></div>
        </div>

        {{-- KOLOM KANAN: TABEL MANIFES (MUTLAK TERKUNCI MENGIKUTI KIRI) --}}
        <main class="w-full lg:w-[calc(66.666667%-8px)] lg:absolute lg:top-0 lg:bottom-0 lg:right-0 mt-6 lg:mt-0 flex flex-col z-20">
            <div class="glass-panel rounded-[24px] flex flex-col overflow-hidden shadow-sm h-full w-full">
                
                {{-- Header Tabel & Search --}}
                <div class="px-6 py-5 bg-white/60 border-b border-emerald-100 shrink-0 flex flex-col sm:flex-row items-center justify-between gap-4 relative z-20">
                    <div>
                        <h3 class="font-black text-emerald-950 text-[13px] uppercase tracking-widest font-poppins">Rincian Manifes Kehadiran</h3>
                        <p class="text-[10px] font-bold text-emerald-700/70 mt-0.5">Daftar presensi individu peserta posyandu.</p>
                    </div>
                    <div class="relative w-full sm:w-[260px]">
                        <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-500 text-[15px]"></i>
                        <input type="text" id="manifestSearch" placeholder="Cari warga berdasarkan nama..." class="search-pill">
                    </div>
                </div>

                {{-- Area Body Tabel --}}
                <div class="overflow-y-auto custom-scroll w-full flex-1 h-0">
                    <table class="nexus-table min-w-full relative z-10">
                        <thead>
                            <tr>
                                <th class="w-16 pl-6 text-center">No</th>
                                <th>Identitas Sasaran</th>
                                <th class="w-28 text-center">Status</th>
                                <th class="w-[35%]">Alasan / Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="manifestTableBody">
                            @forelse($details as $index => $row)
                            <tr>
                                <td class="pl-6 text-center">
                                    <span class="text-[11.5px] font-black text-emerald-700/50 font-mono">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td>
                                    <p class="manifest-nama font-black text-slate-800 text-[13.5px] mb-0.5 truncate font-poppins">{{ $row->pasien_data->nama_lengkap ?? 'Data Terhapus' }}</p>
                                    <p class="text-[9.5px] font-bold text-slate-400 font-mono tracking-widest"><i class="ph-fill ph-identification-card text-slate-300"></i> {{ $row->pasien_data->nik ?? 'N/A' }}</p>
                                </td>
                                <td class="text-center">
                                    <div class="inline-flex items-center justify-center px-4 py-1.5 rounded-full {{ $row->hadir ? 'bg-emerald-50 text-emerald-600 border border-emerald-200/60' : 'bg-rose-50 text-rose-600 border border-rose-200/60' }} text-[9.5px] font-black uppercase tracking-widest shadow-sm">
                                        {{ $row->hadir ? 'Hadir' : 'Absen' }}
                                    </div>
                                </td>
                                <td>
                                    @if(!$row->hadir && $row->keterangan)
                                        <div class="flex items-start gap-1.5 bg-amber-50/50 p-2 rounded-[10px] border border-amber-100">
                                            <i class="ph-fill ph-warning-circle text-amber-500 mt-0.5 shrink-0"></i>
                                            <p class="text-[11px] font-bold text-amber-800 line-clamp-2 leading-tight">{{ $row->keterangan }}</p>
                                        </div>
                                    @else
                                        <p class="text-[11px] font-bold text-slate-400 px-2">-</p>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-12">
                                    <p class="text-[12px] font-bold text-emerald-600/60">Tidak ada data manifest pada sesi ini.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Footer Info Box --}}
                <div class="px-6 py-4 bg-emerald-50/50 border-t border-emerald-100 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0 z-20 relative">
                    <p class="text-[9px] font-black text-emerald-700/60 uppercase tracking-widest flex items-center gap-1.5">
                        <i class="ph-fill ph-user-circle text-emerald-400 text-[14px]"></i> Pencatat: <span class="text-emerald-900">{{ $absensi->pencatat->name ?? 'Sistem Auth' }}</span>
                    </p>
                    <p class="text-[9px] font-black text-emerald-700/60 uppercase tracking-widest flex items-center gap-1.5">
                        <i class="ph-bold ph-arrows-clockwise text-emerald-400"></i> Terakhir Diperbarui: <span class="text-emerald-900">{{ $absensi->updated_at->format('H:i:s') }}</span>
                    </p>
                </div>

            </div>
        </main>
    </div>
</div>

@push('scripts')
<script>
    const searchInput = document.getElementById('manifestSearch');
    if(searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#manifestTableBody tr');
            rows.forEach(row => {
                const namaKolom = row.querySelector('.manifest-nama');
                if(namaKolom) {
                    const textData = namaKolom.textContent.toLowerCase();
                    row.style.display = textData.includes(filter) ? '' : 'none';
                }
            });
        });
    }
</script>
@endpush
@endsection