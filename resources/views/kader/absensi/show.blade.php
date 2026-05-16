@extends('layouts.kader')

@section('title', 'Detail Sesi Absensi')
@section('page-name', 'Arsip Sesi #' . $absensi->nomor_pertemuan)

@push('styles')
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    /* ====================================================================
       1. GLOBAL OPTIMIZATION & ANTI-LAG
       ==================================================================== */
    html { scroll-behavior: smooth; }
    body { background-color: #f8fafc; -webkit-font-smoothing: antialiased; }
    .gpu-accel { transform: translateZ(0); will-change: transform, opacity; }

    /* ====================================================================
       2. SNAPPY ENTRANCE ANIMATIONS (120 FPS FEEL)
       ==================================================================== */
    @keyframes snappyFadeUp {
        0% { opacity: 0; transform: translateY(10px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .stagger-fast > * { opacity: 0; animation: snappyFadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .stagger-fast > *:nth-child(1) { animation-delay: 50ms; }
    .stagger-fast > *:nth-child(2) { animation-delay: 100ms; }

    /* ====================================================================
       3. UI COMPONENTS & SCROLL LOCK
       ==================================================================== */
    .search-input {
        background-color: #f8fafc; border: 1px solid #e2e8f0; color: #1e293b; 
        font-size: 0.85rem; font-weight: 600; border-radius: 12px; 
        padding: 0.6rem 1rem 0.6rem 2.5rem; width: 100%; outline: none; transition: all 0.2s ease;
    }
    .search-input:focus { background-color: #ffffff; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }

    .nexus-table { width: 100%; border-collapse: separate; border-spacing: 0; text-align: left; }
    .nexus-table th { 
        background: #f8fafc; color: #64748b; font-size: 0.65rem; font-weight: 800; 
        text-transform: uppercase; letter-spacing: 0.05em; padding: 1rem 1.5rem; 
        border-bottom: 1px solid #e2e8f0; white-space: nowrap; position: sticky; top: 0; z-index: 10; 
    }
    .nexus-table td { padding: 1rem 1.5rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; transition: background-color 0.2s; }
    .nexus-table tr:hover td { background-color: #f8fafc; }

    /* Custom Scrollbar */
    .custom-scroll::-webkit-scrollbar { width: 4px; }
    .custom-scroll::-webkit-scrollbar-track { background: transparent; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endpush

@section('content')
<div class="max-w-[1250px] mx-auto pb-16 mt-2 relative z-10 gpu-accel stagger-fast">

    {{-- 1. HEADER RINGKAS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-6 bg-white p-6 md:p-8 rounded-[24px] border border-slate-100 shadow-sm relative overflow-hidden">
        <div class="flex items-center gap-5 relative z-10 w-full md:w-auto">
            <a href="{{ route('kader.absensi.riwayat') }}" class="w-12 h-12 rounded-[14px] bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-sm shrink-0 active:scale-95" title="Kembali">
                <i class="ph-bold ph-arrow-left text-[18px]"></i>
            </a>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-600 border border-indigo-100 text-[9px] font-black uppercase tracking-widest">{{ str_replace('_', ' ', $absensi->kategori) }}</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1.5"><i class="ph-bold ph-calendar-blank"></i> {{ \Carbon\Carbon::parse($absensi->tanggal_posyandu)->locale('id')->translatedFormat('d F Y') }}</span>
                </div>
                <h1 class="text-[20px] md:text-2xl font-black text-slate-800 tracking-tight font-poppins truncate">{{ $absensi->kode_absensi }}</h1>
            </div>
        </div>
        <a href="{{ route('kader.absensi.index', ['kategori' => $absensi->kategori]) }}" class="w-full md:w-auto px-6 py-3 bg-indigo-50 border border-indigo-100 text-indigo-600 font-black text-[11px] uppercase tracking-widest rounded-[14px] hover:bg-indigo-600 hover:text-white transition-all shadow-sm flex items-center justify-center gap-2 active:scale-95">
            <i class="ph-bold ph-pencil-simple text-[14px]"></i> Koreksi Data
        </a>
    </div>

    {{-- 2. KONTEN GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start relative">
        
        {{-- KOLOM KIRI: STATISTIK (STICKY) --}}
        <aside class="lg:col-span-4 flex flex-col gap-4 sticky top-6 z-20">
            
            {{-- Persentase Kehadiran --}}
            <div class="bg-slate-900 rounded-[24px] p-6 text-white shadow-lg border border-slate-700 relative overflow-hidden shrink-0">
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-indigo-500/20 rounded-full blur-2xl pointer-events-none"></div>
                <h4 class="font-bold text-[10px] text-slate-400 uppercase tracking-widest mb-1 relative z-10">Tingkat Kehadiran</h4>
                <div class="flex items-baseline gap-1.5 mb-4 relative z-10">
                    <span class="text-[42px] font-black text-white font-poppins leading-none">{{ $totalPasien > 0 ? round(($totalHadir / $totalPasien) * 100) : 0 }}</span>
                    <span class="text-lg font-bold text-slate-400">%</span>
                </div>
                <div class="w-full h-1.5 bg-slate-700/50 rounded-full overflow-hidden relative z-10">
                    <div class="h-full bg-emerald-400 rounded-full transition-all duration-1000" style="width: {{ $totalPasien > 0 ? ($totalHadir / $totalPasien) * 100 : 0 }}%"></div>
                </div>
            </div>

            {{-- Stat Cards Kecil (Pindahan dari Kanan) --}}
            <div class="grid grid-cols-1 gap-3">
                <div class="bg-white border border-slate-200 rounded-[18px] p-4 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 text-[18px]"><i class="ph-fill ph-users-three"></i></div>
                        <div>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Total Sasaran</p>
                            <h3 class="text-xl font-black text-slate-800 font-poppins leading-none">{{ $totalPasien }}</h3>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-slate-200 rounded-[18px] p-4 flex items-center justify-between shadow-sm border-l-4 border-l-emerald-500">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-emerald-50 text-emerald-500 flex items-center justify-center text-[18px]"><i class="ph-fill ph-check-circle"></i></div>
                        <div>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Total Hadir</p>
                            <h3 class="text-xl font-black text-emerald-600 font-poppins leading-none">{{ $totalHadir }}</h3>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-slate-200 rounded-[18px] p-4 flex items-center justify-between shadow-sm border-l-4 border-l-rose-500">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-rose-50 text-rose-500 flex items-center justify-center text-[18px]"><i class="ph-fill ph-x-circle"></i></div>
                        <div>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Total Absen</p>
                            <h3 class="text-xl font-black text-rose-500 font-poppins leading-none">{{ $totalAbsen }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Riwayat Sesi Lain (Lebih Pendek) --}}
            <div class="bg-white border border-slate-200 rounded-[20px] p-5 shadow-sm flex flex-col max-h-[280px]">
                <h3 class="font-black text-slate-800 text-[11px] uppercase tracking-widest mb-3 flex items-center gap-2 pb-3 border-b border-slate-100 shrink-0">
                    <i class="ph-bold ph-clock-counter-clockwise text-indigo-500 text-[14px]"></i> Sesi Lain
                </h3>
                <div class="space-y-1 overflow-y-auto pr-1 custom-scroll">
                    @foreach($semuaSesi as $sesi)
                        <a href="{{ route('kader.absensi.show', $sesi->id) }}" class="flex items-center gap-3 p-2 rounded-[10px] transition-colors border {{ $sesi->id == $absensi->id ? 'bg-indigo-50 border-indigo-100' : 'border-transparent hover:bg-slate-50' }}">
                            <div class="w-8 h-8 rounded-[8px] flex items-center justify-center shrink-0 {{ $sesi->id == $absensi->id ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                                <span class="text-[11px] font-black">#{{ $sesi->nomor_pertemuan }}</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-black text-slate-800 truncate">{{ \Carbon\Carbon::parse($sesi->tanggal_posyandu)->locale('id')->translatedFormat('d M Y') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </aside>

        {{-- KOLOM KANAN: RINCIAN TABEL --}}
        <main class="lg:col-span-8 flex flex-col h-full">
            <div class="bg-white rounded-[24px] border border-slate-200 shadow-sm flex flex-col overflow-hidden">
                
                {{-- Header Tabel (Anteng) --}}
                <div class="px-6 py-5 bg-white border-b border-slate-100 shrink-0 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <h3 class="font-black text-slate-800 text-[13px] uppercase tracking-widest font-poppins">Rincian Daftar Hadir</h3>
                        <p class="text-[10px] font-bold text-slate-400 mt-0.5">Daftar per individu peserta posyandu.</p>
                    </div>
                    <div class="relative w-full sm:w-[240px]">
                        <i class="ph-bold ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[14px]"></i>
                        <input type="text" id="manifestSearch" placeholder="Cari warga..." class="search-input">
                    </div>
                </div>

                {{-- Area Tabel Scrollable (Maksimal 10 Baris Tampilan) --}}
                <div class="overflow-y-auto custom-scroll w-full max-h-[580px]">
                    <table class="nexus-table min-w-full">
                        <thead>
                            <tr>
                                <th class="w-16 pl-6 text-center">No</th>
                                <th>Identitas Peserta</th>
                                <th class="w-28 text-center">Status</th>
                                <th class="w-[30%]">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="manifestTableBody">
                            @foreach($details as $index => $row)
                            <tr>
                                <td class="pl-6 text-center">
                                    <span class="text-[11px] font-black text-slate-400 font-mono">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td>
                                    <p class="manifest-nama font-black text-slate-800 text-[13px] mb-0.5 truncate font-poppins">{{ $row->pasien_data->nama_lengkap ?? 'Data Terhapus' }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 font-mono tracking-widest">{{ $row->pasien_data->nik ?? '-' }}</p>
                                </td>
                                <td class="text-center">
                                    <div class="inline-flex items-center justify-center px-4 py-1.5 rounded-full {{ $row->hadir ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-rose-50 text-rose-600 border border-rose-200' }} text-[9px] font-black uppercase tracking-widest">
                                        {{ $row->hadir ? 'Hadir' : 'Absen' }}
                                    </div>
                                </td>
                                <td>
                                    <p class="text-[11px] font-bold text-slate-500 truncate max-w-[150px]">{{ $row->keterangan ?: '-' }}</p>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Footer Info (Anteng) --}}
                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Pencatat: <span class="text-slate-800 ml-1">{{ $absensi->pencatat->name ?? 'Sistem' }}</span></p>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1.5"><i class="ph-bold ph-clock"></i> <span class="text-slate-800">{{ $absensi->updated_at->format('H:i') }} WIB</span></p>
                </div>

            </div>
        </main>
    </div>
</div>

@push('scripts')
<script>
    // Fitur Pencarian Manifest
    const searchInput = document.getElementById('manifestSearch');
    if(searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#manifestTableBody tr');
            rows.forEach(row => {
                const textData = row.textContent.toLowerCase();
                row.style.display = textData.includes(filter) ? '' : 'none';
            });
        });
    }
</script>
@endpush
@endsection