@extends('layouts.kader')

@section('title', 'Log Pemeriksaan Klinis')
@section('page-name', 'Rekam Medis (EMR)')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* ====================================================================
       1. GLOBAL OPTIMIZATION & ANTI-LAG ENGINE (Dari Referensi Absensi)
       ==================================================================== */
    html { scroll-behavior: smooth; }
    body { 
        background-color: #f4f7fe; 
        -webkit-font-smoothing: antialiased; 
        text-rendering: optimizeLegibility;
    }
    .gpu-accel { transform: translateZ(0); will-change: transform, opacity; }

    /* ====================================================================
       2. SNAPPY ENTRANCE ANIMATIONS (120 FPS FEEL)
       ==================================================================== */
    @keyframes snappyFadeUp {
        0% { opacity: 0; transform: translateY(15px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    
    .stagger-fast > * {
        opacity: 0;
        animation: snappyFadeUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    
    .stagger-fast > *:nth-child(1) { animation-delay: 40ms; }
    .stagger-fast > *:nth-child(2) { animation-delay: 80ms; }
    .stagger-fast > *:nth-child(3) { animation-delay: 120ms; }
    .stagger-fast > *:nth-child(4) { animation-delay: 160ms; }

    /* ====================================================================
       3. PIXEL PERFECT UI (Menyamakan dengan Desain Absensi)
       ==================================================================== */
    .crm-search {
        width: 100%; background-color: #ffffff; border: 1px solid #e2e8f0; color: #1e293b;
        font-size: 0.85rem; font-weight: 600; font-family: 'Plus Jakarta Sans', sans-serif;
        border-radius: 9999px; padding: 0.6rem 1.2rem 0.6rem 2.5rem;
        outline: none; transition: all 0.2s ease;
    }
    .crm-search:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }

    .crm-select {
        background-color: #ffffff; border: 1px solid #e2e8f0; color: #1e293b;
        font-size: 0.85rem; font-weight: 600; font-family: 'Plus Jakarta Sans', sans-serif;
        border-radius: 9999px; padding: 0.6rem 2.5rem 0.6rem 1.2rem;
        outline: none; transition: all 0.2s ease; appearance: none; cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-position: right 1rem center; background-repeat: no-repeat; background-size: 1.2em;
    }
    .crm-select:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }

    .nexus-card { 
        background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px;
        box-shadow: 0 4px 15px -5px rgba(15, 23, 42, 0.02);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* iOS Segmented Control dari Absensi */
    .ios-segment { display: flex; background: #f1f5f9; padding: 4px; border-radius: 9999px; overflow-x: auto; }
    .ios-segment::-webkit-scrollbar { display: none; }
    .ios-btn { flex: 1; text-align: center; padding: 8px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-radius: 9999px; color: #64748b; transition: all 0.2s ease; white-space: nowrap; }
    .ios-btn.active { background: #ffffff; color: #4f46e5; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
    .ios-btn:hover:not(.active) { color: #334155; }

    /* Table Styles */
    .tr-nexus { transition: all 0.2s ease; border-bottom: 1px solid #f1f5f9; }
    .tr-nexus:hover { background-color: #f8fafc; transform: translateY(-1px); }
    
    .med-badge {
        display: inline-flex; align-items: center; padding: 0.25rem 0.5rem; border-radius: 6px; 
        font-size: 11px; font-weight: 600; white-space: nowrap; border: 1px solid transparent; 
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    /* ==========================================================
       4. SWEETALERT 2 - CLEAN UI (Dari Referensi Absensi)
       ========================================================== */
    div:where(.swal2-container).swal2-backdrop-show { background: rgba(15, 23, 42, 0.5) !important; backdrop-filter: blur(4px) !important; z-index: 99999 !important; }
    .swal2-popup:not(.swal2-toast) { border-radius: 24px !important; padding: 2.5rem 2rem 2rem !important; background: #ffffff !important; width: 24em !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15) !important; border: none !important; }
    .swal2-title { font-family: 'Poppins', sans-serif !important; font-weight: 900 !important; font-size: 1.3rem !important; color: #1e293b !important; padding-top: 0 !important; }
    .swal2-html-container { font-family: 'Plus Jakarta Sans', sans-serif !important; color: #64748b !important; font-size: 0.85rem !important; line-height: 1.6 !important; margin: 1em 0 0.5em !important; }
    .swal2-actions { gap: 10px !important; margin-top: 1.5rem !important; width: 100% !important; justify-content: center !important; }
    
    .swal-btn-danger { background: #f43f5e !important; color: #ffffff !important; border-radius: 9999px !important; padding: 12px 28px !important; font-size: 11px !important; font-weight: 900 !important; text-transform: uppercase !important; box-shadow: 0 4px 15px -3px rgba(244,63,94,0.3) !important; border: none !important; transition: all 0.2s ease !important; }
    .swal-btn-cancel { background: #f1f5f9 !important; color: #475569 !important; border-radius: 9999px !important; padding: 12px 28px !important; font-size: 11px !important; font-weight: 900 !important; text-transform: uppercase !important; border: none !important; transition: all 0.2s ease !important; }
</style>
@endpush

@section('content')
{{-- PRELOADER SISTEM (Sama persis dengan Absensi) --}}
<div id="smoothLoader" class="fixed inset-0 bg-slate-50/90 backdrop-blur-sm z-[9999] flex flex-col items-center justify-center transition-all duration-200 opacity-100 pointer-events-auto">
    <div class="w-10 h-10 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin mb-3"></div>
    <p class="text-indigo-900 font-black tracking-widest text-[9px] uppercase font-poppins">MEMUAT...</p>
</div>

<div class="max-w-[1450px] mx-auto relative z-10 pb-16 mt-2 gpu-accel stagger-fast">

    {{-- TEKS HEADER UTAMA --}}
    <div class="mb-6 px-1 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-[22px] md:text-[24px] font-black text-slate-800 tracking-tight font-poppins leading-none mb-1.5">Log Pemeriksaan Fisik</h1>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Database Rekam Medis &bull; Meja 2-4</p>
        </div>
        <a href="{{ route('kader.pemeriksaan.create') }}" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full text-[11px] font-black uppercase tracking-widest shadow-[0_4px_15px_rgba(79,70,229,0.3)] hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
            <i class="ph-bold ph-plus text-[16px]"></i> Input Pemeriksaan Baru
        </a>
    </div>

    {{-- 1. BANNER UTAMA (Menggunakan style banner dari Absensi) --}}
    <div class="bg-white rounded-[24px] border border-slate-100 shadow-sm p-6 md:p-8 mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        
        <div class="flex items-center gap-5 w-full md:w-auto">
            <div class="w-16 h-16 rounded-[20px] bg-sky-600 text-white flex items-center justify-center text-[32px] shadow-lg shadow-sky-200 shrink-0">
                <i class="ph-fill ph-stethoscope"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Sistem EMR Berjalan</span>
                </div>
                <h2 class="text-xl md:text-[26px] font-black text-slate-800 tracking-tight font-poppins leading-none mb-2">Rekapitulasi Medis</h2>
                <p class="text-slate-500 font-medium text-[12px]">Pusat pencatatan fisik yang telah dan sedang diproses.</p>
            </div>
        </div>

        <div class="shrink-0 bg-slate-50 p-4 rounded-[20px] border border-slate-100 flex items-center gap-5 w-full md:w-auto justify-between md:justify-center">
            <div class="text-left md:text-right">
                <p class="text-[9px] font-black text-sky-400 uppercase tracking-widest mb-1">Total Pemeriksaan</p>
                <p class="text-3xl font-black text-sky-600 font-poppins leading-none">{{ count($pemeriksaans ?? []) }}</p>
            </div>
            <div class="w-12 h-12 rounded-[14px] bg-white text-sky-400 flex items-center justify-center text-[24px] shadow-sm"><i class="ph-bold ph-files"></i></div>
        </div>
    </div>

    {{-- 2. SMART FILTER BAR --}}
    @php
        $reqKategori = request('kategori', '');
        $reqStatus   = request('status', '');
    @endphp
    
    <div class="nexus-card p-4 mb-6">
        <form action="{{ route('kader.pemeriksaan.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-stretch">
            
            {{-- Kategori (iOS Segmented Control) --}}
            <div class="ios-segment flex-1 lg:max-w-[550px]">
                @foreach([
                    ''          => 'Semua',
                    'balita'    => 'Balita',
                    'ibu_hamil' => 'Ibu Hamil',
                    'remaja'    => 'Remaja',
                    'lansia'    => 'Lansia'
                ] as $val => $label)
                    <button type="submit" name="kategori" value="{{ $val }}" class="ios-btn {{ $reqKategori === $val ? 'active' : '' }}">
                        {{ $label }}
                    </button>
                @endforeach
                <input type="hidden" name="status" value="{{ $reqStatus }}">
            </div>

            {{-- Filter Status --}}
            <div class="w-full lg:w-[220px] shrink-0">
                <select name="status" onchange="this.form.submit()" class="crm-select w-full">
                    <option value="">Semua Validasi</option>
                    <option value="pending" {{ $reqStatus == 'pending' ? 'selected' : '' }}>Menunggu Bidan</option>
                    <option value="verified" {{ $reqStatus == 'verified' ? 'selected' : '' }}>Sah (Verified)</option>
                    <option value="ditolak" {{ $reqStatus == 'ditolak' ? 'selected' : '' }}>Ditolak / Revisi</option>
                </select>
            </div>

            {{-- Live Search --}}
            <div class="flex-1 relative flex items-center min-w-[260px]">
                <i class="ph-bold ph-magnifying-glass absolute left-4 text-slate-400 text-[16px]"></i>
                <input type="text" id="liveSearchInput" name="search" value="{{ request('search') }}" placeholder="Cari Nama Warga atau NIK..." class="crm-search">
            </div>

        </form>
    </div>

    {{-- 3. THE MASTERPIECE TABLE --}}
    <div class="nexus-card overflow-hidden flex flex-col p-1">
        <div class="overflow-x-auto custom-scrollbar bg-white rounded-[18px] min-h-[400px]">
            <table class="w-full text-left border-collapse min-w-[1000px]">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="py-4 px-6 text-[11px] font-black text-slate-400 uppercase tracking-widest w-[15%] text-center border-b border-slate-100">Tanggal Input</th>
                        <th class="py-4 px-6 text-[11px] font-black text-slate-400 uppercase tracking-widest w-[25%] border-b border-slate-100">Identitas Pasien</th>
                        <th class="py-4 px-6 text-[11px] font-black text-slate-400 uppercase tracking-widest w-[35%] border-b border-slate-100">Klinis & Pengukuran</th>
                        <th class="py-4 px-6 text-[11px] font-black text-slate-400 uppercase tracking-widest w-[13%] text-center border-b border-slate-100">Validasi</th>
                        <th class="py-4 px-6 text-[11px] font-black text-slate-400 uppercase tracking-widest w-[12%] text-right border-b border-slate-100">Aksi</th>
                    </tr>
                </thead>
                <tbody id="medisTableBody">
                    
                    @forelse($pemeriksaans ?? [] as $item)
                    @php
                        $kategori   = ucwords(str_replace('_', ' ', $item->kategori_pasien ?? 'Umum'));
                        $namaPasien = $item->nama_pasien ?? 'Anonim';
                        $nikPasien  = $item->nik_pasien ?? '-';
                        
                        $statusRaw  = $item->status_verifikasi ?? 'pending';
                        $badgeColor = $statusRaw == 'verified' ? 'emerald' : ($statusRaw == 'ditolak' ? 'rose' : 'amber');
                        $statusText = $statusRaw == 'verified' ? 'Verified' : ($statusRaw == 'ditolak' ? 'Ditolak' : 'Pending');
                        $iconStatus = $badgeColor == 'emerald' ? 'ph-fill ph-seal-check' : ($badgeColor == 'rose' ? 'ph-fill ph-x-circle' : 'ph-fill ph-hourglass-high');
                    @endphp
                    
                    <tr class="tr-nexus med-row group/row" data-search="{{ strtolower($namaPasien . ' ' . $nikPasien) }}">
                        
                        {{-- 1. WAKTU --}}
                        <td class="py-4 px-6 text-center align-middle">
                            <div class="flex flex-col items-center justify-center">
                                <span class="text-[13.5px] font-bold text-slate-700">{{ \Carbon\Carbon::parse($item->tanggal_periksa ?? $item->created_at)->format('d M Y') }}</span>
                                <span class="text-[11px] font-medium text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($item->tanggal_periksa ?? $item->created_at)->format('H:i') }} WIB</span>
                            </div>
                        </td>

                        {{-- 2. IDENTITAS & KATEGORI --}}
                        <td class="py-4 px-6 align-middle">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-full bg-slate-50 border border-slate-100 text-slate-500 flex items-center justify-center font-bold text-[16px] shrink-0 font-poppins group-hover/row:bg-indigo-50 group-hover/row:text-indigo-600 transition-colors">
                                    {{ strtoupper(substr($namaPasien, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex flex-col items-start">
                                    <h4 class="text-[14px] font-bold text-slate-800 truncate group-hover/row:text-indigo-600 transition-colors mb-0.5" title="{{ $namaPasien }}">{{ $namaPasien }}</h4>
                                    <div class="flex items-center gap-1.5 text-[11px]">
                                        <span class="text-slate-500 font-medium">{{ $kategori }}</span>
                                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                        <span class="text-slate-400 font-mono">{{ $nikPasien }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- 3. HASIL FISIK (Tipografi seimbang) --}}
                        <td class="py-4 px-6 whitespace-normal align-middle">
                            <div class="flex flex-wrap gap-1.5 items-center max-w-[340px]">
                                @if($item->berat_badan) <span class="med-badge bg-sky-50 text-sky-700 border-sky-100"><span class="font-medium opacity-70 mr-1">BB:</span><span class="font-bold">{{ $item->berat_badan }} kg</span></span> @endif
                                @if($item->tinggi_badan) <span class="med-badge bg-emerald-50 text-emerald-700 border-emerald-100"><span class="font-medium opacity-70 mr-1">TB:</span><span class="font-bold">{{ $item->tinggi_badan }} cm</span></span> @endif
                                @if($item->lingkar_kepala) <span class="med-badge bg-amber-50 text-amber-700 border-amber-100"><span class="font-medium opacity-70 mr-1">LK:</span><span class="font-bold">{{ $item->lingkar_kepala }} cm</span></span> @endif
                                @if($item->lingkar_lengan) <span class="med-badge bg-amber-50 text-amber-700 border-amber-100"><span class="font-medium opacity-70 mr-1">LILA:</span><span class="font-bold">{{ $item->lingkar_lengan }} cm</span></span> @endif
                                @if($item->lingkar_perut) <span class="med-badge bg-amber-50 text-amber-700 border-amber-100"><span class="font-medium opacity-70 mr-1">LP:</span><span class="font-bold">{{ $item->lingkar_perut }} cm</span></span> @endif
                                
                                @if($item->tekanan_darah) <span class="med-badge bg-rose-50 text-rose-700 border-rose-100"><span class="font-medium opacity-70 mr-1">Tensi:</span><span class="font-bold">{{ $item->tekanan_darah }}</span></span> @endif
                                @if($item->hemoglobin) <span class="med-badge bg-rose-50 text-rose-700 border-rose-100"><span class="font-medium opacity-70 mr-1">Hb:</span><span class="font-bold">{{ $item->hemoglobin }} g/dL</span></span> @endif
                                
                                @if($item->gula_darah) <span class="med-badge bg-purple-50 text-purple-700 border-purple-100"><span class="font-medium opacity-70 mr-1">Gula:</span><span class="font-bold">{{ $item->gula_darah }}</span></span> @endif
                                @if($item->kolesterol) <span class="med-badge bg-purple-50 text-purple-700 border-purple-100"><span class="font-medium opacity-70 mr-1">Koles:</span><span class="font-bold">{{ $item->kolesterol }}</span></span> @endif
                                @if($item->asam_urat) <span class="med-badge bg-purple-50 text-purple-700 border-purple-100"><span class="font-medium opacity-70 mr-1">AU:</span><span class="font-bold">{{ $item->asam_urat }}</span></span> @endif

                                @if(empty($item->berat_badan) && empty($item->tinggi_badan) && empty($item->tekanan_darah))
                                    <span class="text-[11px] font-medium text-slate-400 italic">Belum ada data klinis</span>
                                @endif
                            </div>
                        </td>

                        {{-- 4. STATUS VALIDASI --}}
                        <td class="py-4 px-6 text-center align-middle">
                            <div class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-full bg-{{ $badgeColor }}-50 border border-{{ $badgeColor }}-100 w-full max-w-[90px] mx-auto">
                                <i class="{{ $iconStatus }} text-{{ $badgeColor }}-500 text-[12px] {{ $statusRaw == 'pending' ? 'animate-pulse' : '' }}"></i>
                                <span class="text-[10px] font-bold text-{{ $badgeColor }}-600">{{ $statusText }}</span>
                            </div>
                        </td>

                        {{-- 5. AKSI CRUD --}}
                        <td class="py-4 px-6 text-right align-middle">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('kader.pemeriksaan.show', $item->id) }}" class="w-8 h-8 rounded-[8px] bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 hover:border-indigo-200 flex items-center justify-center transition-colors shadow-sm" title="Lihat Detail">
                                    <i class="ph-bold ph-eye text-[15px]"></i>
                                </a>
                                
                                @if($statusRaw !== 'verified')
                                    <a href="{{ route('kader.pemeriksaan.edit', $item->id) }}" class="w-8 h-8 rounded-[8px] bg-white border border-slate-200 text-slate-400 hover:text-amber-500 hover:bg-amber-50 hover:border-amber-200 flex items-center justify-center transition-colors shadow-sm" title="Edit Data">
                                        <i class="ph-bold ph-pencil-simple text-[15px]"></i>
                                    </a>
                                    
                                    <form action="{{ route('kader.pemeriksaan.destroy', $item->id) }}" method="POST" class="delete-form m-0 p-0">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn-delete w-8 h-8 rounded-[8px] bg-white border border-slate-200 text-slate-400 hover:text-rose-500 hover:bg-rose-50 hover:border-rose-200 flex items-center justify-center transition-colors shadow-sm" title="Hapus Data">
                                            <i class="ph-bold ph-trash text-[15px]"></i>
                                        </button>
                                    </form>
                                @else
                                    <div class="w-8 h-8 rounded-[8px] bg-slate-50 border border-slate-100 text-slate-300 flex items-center justify-center cursor-not-allowed" title="Data Terkunci">
                                        <i class="ph-bold ph-lock-key text-[15px]"></i>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    {{-- EMPTY STATE --}}
                    <tr id="emptyStateRow" style="{{ (isset($pemeriksaans) && count($pemeriksaans) > 0) ? 'display:none;' : '' }}">
                        <td colspan="5" class="py-24 text-center bg-transparent">
                            <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                <div class="w-20 h-20 bg-white rounded-full border border-slate-100 flex items-center justify-center text-slate-300 text-[40px] mb-4 shadow-sm">
                                    <i class="ph-fill ph-folder-dashed"></i>
                                </div>
                                <h4 class="text-[16px] font-bold text-slate-700 font-poppins mb-1">Data Tidak Ditemukan</h4>
                                <p class="text-[13px] text-slate-500 mb-6 font-medium">Sistem tidak menemukan log pemeriksaan yang cocok. Coba ubah kata kunci atau filter.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- 4. PAGINATION --}}
    @if(isset($pemeriksaans) && count($pemeriksaans) > 0 && method_exists($pemeriksaans, 'links'))
    <div class="mt-6 flex justify-end">
        {{ $pemeriksaans->withQueryString()->links() }}
    </div>
    @endif

</div>

@push('scripts')
<script>
    // System Loader Logic
    window.hideLoader = () => { const l = document.getElementById('smoothLoader'); if(l) { l.classList.remove('opacity-100','pointer-events-auto'); l.classList.add('opacity-0','pointer-events-none'); setTimeout(()=> l.style.display = 'none', 200); } };
    window.showLoader = () => { const l = document.getElementById('smoothLoader'); if(l) { l.style.display = 'flex'; l.classList.remove('opacity-0','pointer-events-none'); l.classList.add('opacity-100','pointer-events-auto'); } };

    window.onload = hideLoader;
    document.addEventListener('DOMContentLoaded', hideLoader);
    window.addEventListener('pageshow', hideLoader);

    document.addEventListener('DOMContentLoaded', function() {
        // LIVE SEARCH INSTAN
        const searchInput = document.getElementById('liveSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const keyword = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('.med-row');
                const emptyState = document.getElementById('emptyStateRow');
                let adaDataYangCocok = false;

                rows.forEach(row => {
                    const dataSearch = row.getAttribute('data-search');
                    if(dataSearch.includes(keyword)) {
                        row.style.display = ''; 
                        adaDataYangCocok = true;
                    } else {
                        row.style.display = 'none'; 
                    }
                });

                if (emptyState) {
                    emptyState.style.display = adaDataYangCocok ? 'none' : '';
                }
            });
        }

        // DELETE CONFIRMATION (Gaya Alert Absensi)
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('.delete-form');
                
                Swal.fire({
                    title: 'Hapus Rekam Medis?',
                    html: `Data pengukuran fisik ini akan <b class="text-rose-500 font-bold">dihapus secara permanen</b> dari sistem.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus Data',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    customClass: { 
                        popup: 'swal2-popup nexus-swal', 
                        confirmButton: 'swal-btn-danger',
                        cancelButton: 'swal-btn-cancel'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        showLoader();
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush
@endsection