@extends('layouts.bidan')

@section('title', 'Data Ibu Hamil (KIA)')
@section('page-name', 'Pantau Kesehatan Ibu')

@push('styles')
<style>
    /* ANIMASI MASUK HALUS */
    .animate-slide-up { opacity: 0; animation: slideUpFade 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes slideUpFade { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    
    /* CUSTOM SCROLLBAR */
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    
    /* GLASS CARD HOVER */
    .table-row-hover:hover { background-color: #fff1f2; transition: all 0.2s ease; }
</style>
@endpush

@section('content')
{{-- Loader Sistem Nexus --}}
<div id="smoothLoader" class="fixed inset-0 bg-slate-50/90 backdrop-blur-md z-[9999] flex flex-col items-center justify-center transition-all duration-300 opacity-0 pointer-events-none">
    <div class="relative w-20 h-20 flex items-center justify-center mb-4">
        <div class="absolute inset-0 border-4 border-pink-100 rounded-full"></div>
        <div class="absolute inset-0 border-4 border-pink-500 rounded-full border-t-transparent animate-spin"></div>
        <i class="fas fa-female text-pink-500 text-2xl animate-pulse"></i>
    </div>
    <p class="text-pink-800 font-extrabold tracking-widest text-[11px] uppercase animate-pulse">Memuat Database KIA...</p>
</div>

<div class="max-w-7xl mx-auto animate-slide-up pb-10">

    {{-- HERO HEADER (PINK THEME - KIA) --}}
    <div class="bg-gradient-to-br from-pink-400 to-rose-500 rounded-[32px] p-8 md:p-10 mb-8 relative overflow-hidden shadow-lg border border-pink-400 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
        <div class="absolute -right-10 -bottom-10 opacity-10 text-[120px] pointer-events-none"><i class="fas fa-female"></i></div>
        
        <div class="relative z-10 w-full md:w-auto text-center md:text-left flex items-center gap-6">
            <div class="w-20 h-20 rounded-[24px] bg-white/20 backdrop-blur border border-white/30 text-white flex items-center justify-center text-4xl shrink-0 shadow-sm transform -rotate-3 hover:rotate-0 transition-transform">
                <i class="fas fa-hand-holding-heart"></i>
            </div>
            <div>
                <h2 class="text-2xl md:text-3xl font-black text-white font-poppins tracking-tight mb-1">Database Ibu Hamil</h2>
                <p class="text-pink-50 text-sm font-medium max-w-md mx-auto md:mx-0">Pemantauan Ante Natal Care (ANC), usia kehamilan, dan deteksi risiko tinggi kehamilan secara *real-time*.</p>
            </div>
        </div>
        
        <a href="{{ route('bidan.laporan.cetak', ['jenis' => 'ibu_hamil']) }}" target="_blank" class="relative z-10 inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white text-pink-600 font-extrabold text-[13px] rounded-xl hover:bg-pink-50 shadow-[0_8px_20px_rgba(225,29,72,0.2)] transition-all hover:-translate-y-0.5">
            <i class="fas fa-print"></i> Cetak Laporan KIA
        </a>
    </div>

    {{-- STATISTIK QUICK VIEW --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white p-6 rounded-[24px] border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Aktif</p>
                <h3 class="text-2xl font-black text-slate-800 font-poppins">{{ $ibu_hamils->total() }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-pink-50 text-pink-500 flex items-center justify-center text-xl"><i class="fas fa-users"></i></div>
        </div>
        <div class="bg-white p-6 rounded-[24px] border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Trimester I</p>
                <h3 class="text-2xl font-black text-blue-500 font-poppins">
                    {{ $ibu_hamils->filter(fn($i) => $i->trimester_angka == 1)->count() }}
                </h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center text-xl"><i class="fas fa-leaf"></i></div>
        </div>
        <div class="bg-white p-6 rounded-[24px] border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Trimester II</p>
                <h3 class="text-2xl font-black text-amber-500 font-poppins">
                    {{ $ibu_hamils->filter(fn($i) => $i->trimester_angka == 2)->count() }}
                </h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl"><i class="fas fa-seedling"></i></div>
        </div>
        <div class="bg-white p-6 rounded-[24px] border border-slate-200/80 shadow-sm flex items-center justify-between border-l-4 border-l-rose-500">
            <div>
                <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest mb-1">Trimester III</p>
                <h3 class="text-2xl font-black text-rose-600 font-poppins">
                    {{ $ibu_hamils->filter(fn($i) => $i->trimester_angka == 3)->count() }}
                </h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center text-xl"><i class="fas fa-baby-carriage"></i></div>
        </div>
    </div>

    {{-- TABEL DATA UTAMA --}}
    <div class="bg-white rounded-[32px] border border-slate-200/80 shadow-[0_8px_30px_rgb(0,0,0,0.03)] overflow-hidden">
        
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <form id="filterForm" action="{{ route('bidan.pasien.ibu_hamil') }}" method="GET" class="flex flex-col md:flex-row gap-3">
                <div class="w-full relative flex-1">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama ibu hamil atau NIK (Tekan Enter)..." class="w-full pl-12 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-[13px] font-medium focus:ring-4 focus:ring-pink-500/10 focus:border-pink-500 outline-none transition-all shadow-sm">
                </div>
                @if(request('search'))
                    <a href="{{ route('bidan.pasien.ibu_hamil') }}" class="smooth-route flex items-center justify-center px-6 py-3.5 bg-slate-100 text-slate-600 rounded-2xl hover:bg-slate-200 transition-colors font-bold text-[13px]">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse min-w-[1000px]">
                <thead>
                    <tr class="bg-white border-b border-slate-100">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Identitas Ibu</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Informasi Kehamilan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Kondisi Fisik Terakhir</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($ibu_hamils as $ibu)
                    <tr class="table-row-hover group">
                        
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-pink-100 text-pink-500 flex items-center justify-center border border-pink-200 shrink-0"><i class="fas fa-female"></i></div>
                                <div>
                                    <p class="font-black text-slate-800 text-[14px] mb-0.5">{{ $ibu->nama_lengkap }}</p>
                                    <p class="text-[11px] font-bold text-slate-400">Suami: <span class="text-slate-600">{{ $ibu->nama_suami ?? '-' }}</span></p>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                <p class="text-[12px] font-bold text-slate-700">HPL: <span class="text-pink-600">{{ $ibu->hpl ? $ibu->hpl->format('d M Y') : '-' }}</span></p>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-black rounded border border-slate-200 uppercase tracking-widest">
                                        {{ $ibu->trimester }}
                                    </span>
                                    <span class="text-[11px] font-bold text-slate-400">{{ $ibu->usia_kehamilan ?? 0 }} Minggu</span>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            @if($ibu->pemeriksaan_terakhir)
                                <div class="flex flex-wrap gap-2 text-[10px] font-black">
                                    <span class="bg-white px-2.5 py-1 rounded-md border border-slate-200 shadow-sm">BB: {{ $ibu->pemeriksaan_terakhir->berat_badan ?? '-' }}kg</span>
                                    @php
                                        $tensi = intval(explode('/', $ibu->pemeriksaan_terakhir->tekanan_darah ?? '0')[0]);
                                        $tensiClass = $tensi >= 140 ? 'bg-rose-50 text-rose-600 border-rose-200' : 'bg-emerald-50 text-emerald-600 border-emerald-200';
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-md border shadow-sm {{ $tensiClass }}">TD: {{ $ibu->pemeriksaan_terakhir->tekanan_darah ?? '-' }}</span>
                                    <span class="bg-indigo-50 text-indigo-600 px-2.5 py-1 rounded-md border border-indigo-100 shadow-sm">LILA: {{ $ibu->pemeriksaan_terakhir->lila ?? '-' }}cm</span>
                                </div>
                            @else
                                <span class="text-[11px] text-slate-400 font-bold italic">Belum diperiksa</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-center">
                            @if($ibu->status == 'aktif')
                                <span class="px-3 py-1.5 rounded-xl bg-cyan-50 text-cyan-700 text-[10px] font-black border border-cyan-200 uppercase tracking-widest shadow-sm">
                                    <i class="fas fa-check-circle mr-1"></i> Sedang Hamil
                                </span>
                            @else
                                <span class="px-3 py-1.5 rounded-xl bg-slate-50 text-slate-500 text-[10px] font-black border border-slate-200 uppercase tracking-widest">
                                    Sudah Melahirkan
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('bidan.rekam-medis.show', ['pasien_type' => 'ibu_hamil', 'pasien_id' => $ibu->id]) }}" class="smooth-route inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white border border-slate-200 text-slate-600 text-[12px] font-bold rounded-xl hover:bg-pink-50 hover:text-pink-600 hover:border-pink-200 transition-all shadow-sm">
                                <i class="fas fa-folder-open"></i> Buku Medis
                            </a>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-20">
                            <div class="w-20 h-20 bg-slate-50 rounded-[24px] flex items-center justify-center text-slate-300 mx-auto mb-4 text-4xl shadow-inner border border-slate-100"><i class="fas fa-search"></i></div>
                            <h4 class="font-black text-slate-700 text-[15px] font-poppins">Data KIA Kosong</h4>
                            <p class="text-[13px] text-slate-500 mt-1 font-medium">Belum ada data ibu hamil yang terdaftar dalam sistem.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($ibu_hamils->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 pagination-wrapper">
            {{ $ibu_hamils->links() }}
        </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
    const showLoader = () => {
        const loader = document.getElementById('smoothLoader');
        if(loader) {
            loader.style.display = 'flex';
            loader.offsetHeight; 
            loader.classList.remove('opacity-0', 'pointer-events-none');
            loader.classList.add('opacity-100');
        }
    };
    window.addEventListener('pageshow', () => {
        const loader = document.getElementById('smoothLoader');
        if(loader) {
            loader.classList.remove('opacity-100');
            loader.classList.add('opacity-0', 'pointer-events-none');
            setTimeout(() => loader.style.display = 'none', 300);
        }
    });
    document.getElementById('filterForm').addEventListener('submit', showLoader);
    document.querySelectorAll('.smooth-route, .pagination-wrapper a').forEach(link => {
        link.addEventListener('click', function(e) {
            if(!this.classList.contains('target-blank') && this.target !== '_blank' && !e.ctrlKey) showLoader();
        });
    });
</script>
@endpush