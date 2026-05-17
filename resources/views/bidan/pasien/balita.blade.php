@extends('layouts.bidan')

@section('title', 'Buku Induk Balita')
@section('page-name', 'Direktori Bayi & Balita')

@push('styles')
<style>
    /* ANIMASI MASUK HALUS */
    .fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    /* EFEK HOVER TABEL NEXUS */
    .nexus-table-row { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-bottom: 1px solid #f8fafc; background: #ffffff; }
    .nexus-table-row:last-child { border-bottom: none; }
    .nexus-table-row:hover { 
        background-color: #f0f9ff; /* Sky-50 */
        box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.1); 
        transform: translateY(-2px);
        position: relative; z-index: 10; border-radius: 16px;
        border-color: transparent;
    }

    /* KUSTOMISASI SCROLLBAR TABEL */
    .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    
    /* INPUT PENCARIAN */
    .search-input {
        transition: all 0.3s ease;
    }
    .search-input:focus {
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
        border-color: #0ea5e9;
    }
</style>
@endpush

@section('content')

{{-- =================================================================
     LOADER SISTEM KHUSUS BALITA (TEMA SKY/CYAN)
     ================================================================= --}}
<div id="smoothLoader" class="fixed inset-0 bg-slate-50/90 backdrop-blur-sm z-[9999] flex flex-col items-center justify-center transition-all duration-300 opacity-0 pointer-events-none">
    <div class="relative w-20 h-20 flex items-center justify-center mb-6">
        <div class="absolute inset-0 border-4 border-sky-100 rounded-full"></div>
        <div class="absolute inset-0 border-4 border-sky-500 rounded-full border-t-transparent animate-spin"></div>
        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg text-sky-500 text-xl">
            <i class="fas fa-baby"></i>
        </div>
    </div>
    <div class="bg-white px-6 py-2.5 rounded-full shadow-lg border border-slate-100 flex items-center gap-3">
        <div class="w-2.5 h-2.5 rounded-full bg-sky-500 animate-ping"></div>
        <p class="text-[11px] font-black text-sky-700 uppercase tracking-[0.2em] font-poppins" id="loaderText">MEMUAT DATA BALITA...</p>
    </div>
</div>

<div class="max-w-[1250px] mx-auto fade-in-up pb-20">

    {{-- NAVIGASI KEMBALI --}}
    <div class="mb-6 px-1">
        <a href="{{ route('bidan.rekam-medis.index') }}" class="inline-flex items-center gap-2.5 text-[11px] font-black text-slate-400 hover:text-sky-600 transition-colors uppercase tracking-widest group">
            <div class="w-10 h-10 rounded-full bg-white border border-slate-200 flex items-center justify-center group-hover:border-sky-200 group-hover:bg-sky-50 transition-all shadow-sm">
                <i class="fas fa-arrow-left"></i>
            </div>
            Kembali ke Dashboard EMR
        </a>
    </div>

    {{-- HERO HEADER (Tema Sky) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-8 bg-white p-6 md:p-8 rounded-[32px] border border-slate-100 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.04)] relative overflow-hidden">
        <div class="absolute right-0 top-0 w-64 h-64 bg-sky-50 rounded-bl-full pointer-events-none opacity-60 transition-transform duration-700 hover:scale-110"></div>
        
        <div class="flex items-center gap-5 relative z-10">
            <div class="w-16 h-16 rounded-[20px] bg-gradient-to-br from-sky-400 to-sky-600 text-white flex items-center justify-center text-3xl shadow-[0_10px_25px_rgba(14,165,233,0.3)] border border-sky-300 shrink-0">
                <i class="fas fa-baby"></i>
            </div>
            <div>
                <h1 class="text-[24px] md:text-[28px] font-black text-slate-800 tracking-tight font-poppins mb-1 leading-none">Direktori Balita</h1>
                <p class="text-slate-500 font-medium text-[13px] max-w-lg leading-relaxed mt-1">Pilih data balita di bawah ini untuk melihat kurva pertumbuhan, KMS, dan riwayat imunisasi dasar.</p>
            </div>
        </div>
    </div>

    {{-- KONTANER TABEL & PENCARIAN --}}
    <div class="bg-white rounded-[32px] border border-slate-100 shadow-[0_15px_50px_-15px_rgba(0,0,0,0.05)] flex flex-col overflow-hidden relative z-10">
        
        {{-- BAR PENCARIAN & FILTER --}}
        <div class="px-6 md:px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-100/50 text-sky-600 flex items-center justify-center shadow-inner text-sm border border-sky-100"><i class="fas fa-search"></i></div>
                <h3 class="font-black text-slate-800 text-[15px] font-poppins tracking-tight">Pencarian Data</h3>
            </div>
            
            <form id="filterForm" action="{{ url()->current() }}" method="GET" class="w-full sm:w-[350px] relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIK balita..." class="search-input w-full bg-white border border-slate-200 text-slate-700 text-[13px] font-semibold rounded-2xl py-3 pl-12 pr-4 outline-none shadow-sm placeholder:font-medium placeholder:text-slate-400">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                @if(request('search'))
                    <a href="{{ url()->current() }}" class="absolute right-4 top-1/2 -translate-y-1/2 text-rose-400 hover:text-rose-600 text-sm" title="Reset Pencarian"><i class="fas fa-times-circle"></i></a>
                @endif
            </form>
        </div>

        {{-- TABEL DATA --}}
        <div class="overflow-x-auto custom-scrollbar flex-1 p-2 md:p-4">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 w-16 text-center">No</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Identitas Anak</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Jenis Kelamin</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Kelahiran & Usia</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Nama Ibu</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right pr-8">Rekam Medis</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($balitas as $index => $pasien)
                    <tr class="nexus-table-row group">
                        
                        {{-- NO --}}
                        <td class="px-6 py-5 text-[13px] font-black text-slate-400 align-middle text-center">{{ $balitas->firstItem() + $index }}</td>
                        
                        {{-- IDENTITAS --}}
                        <td class="px-6 py-5 align-middle">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center font-black text-[16px] shrink-0 border border-sky-200 shadow-sm group-hover:scale-110 transition-transform">
                                    {{ substr($pasien->nama_lengkap, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-black text-slate-800 text-[14.5px] mb-0.5 font-poppins group-hover:text-sky-600 transition-colors">{{ $pasien->nama_lengkap }}</p>
                                    <p class="text-[11.5px] font-bold text-slate-500"><i class="fas fa-fingerprint text-slate-300 mr-1"></i> {{ $pasien->nik ?? '-' }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- JENIS KELAMIN --}}
                        <td class="px-6 py-5 align-middle">
                            @if(strtolower($pasien->jenis_kelamin) == 'l' || strtolower($pasien->jenis_kelamin) == 'laki-laki')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black rounded-lg border border-blue-100 uppercase tracking-widest shadow-sm">
                                    <i class="fas fa-mars text-[12px]"></i> Laki-Laki
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-pink-50 text-pink-600 text-[10px] font-black rounded-lg border border-pink-100 uppercase tracking-widest shadow-sm">
                                    <i class="fas fa-venus text-[12px]"></i> Perempuan
                                </span>
                            @endif
                        </td>

                        {{-- KELAHIRAN & USIA --}}
                        <td class="px-6 py-5 align-middle">
                            <p class="text-[13px] font-bold text-slate-700 mb-1 flex items-center gap-2">
                                <i class="far fa-calendar-alt text-sky-400"></i>
                                {{ $pasien->tanggal_lahir ? \Carbon\Carbon::parse($pasien->tanggal_lahir)->translatedFormat('d M Y') : '-' }}
                            </p>
                            @php
                                $usia = $pasien->tanggal_lahir ? \Carbon\Carbon::parse($pasien->tanggal_lahir)->diff(\Carbon\Carbon::now()) : null;
                            @endphp
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest bg-slate-100 px-2.5 py-1 rounded-md border border-slate-200">
                                @if($usia)
                                    {{ $usia->y > 0 ? $usia->y . ' Thn ' : '' }}{{ $usia->m }} Bln
                                @else
                                    Usia Belum Diatur
                                @endif
                            </span>
                        </td>

                        {{-- NAMA IBU --}}
                        <td class="px-6 py-5 align-middle">
                            <div class="flex items-center gap-2 text-[13px] font-bold text-slate-600">
                                <i class="fas fa-female text-slate-300"></i> {{ $pasien->nama_ibu ?? 'Tidak Dicatat' }}
                            </div>
                        </td>

                        {{-- AKSI (LIHAT EMR) --}}
                        <td class="px-6 py-5 text-right align-middle pr-8">
                            <a href="{{ route('bidan.rekam-medis.show', ['pasien_type' => 'balita', 'pasien_id' => $pasien->id]) }}" class="smooth-route inline-flex items-center justify-center gap-2 w-10 h-10 rounded-[12px] bg-white border border-slate-200 text-sky-500 hover:bg-sky-50 hover:text-sky-600 hover:border-sky-300 transition-all shadow-sm group/btn" title="Buka Rekam Medis">
                                <i class="fas fa-folder-open text-[14px] group-hover/btn:scale-110 transition-transform"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-20 bg-slate-50/30">
                            <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center text-slate-300 mx-auto mb-5 border-2 border-dashed border-slate-200 shadow-sm"><i class="fas fa-baby-carriage text-4xl"></i></div>
                            <h4 class="font-black text-slate-800 text-[18px] font-poppins mb-1.5">Data Tidak Ditemukan</h4>
                            <p class="text-[13.5px] font-medium text-slate-500 max-w-sm mx-auto leading-relaxed">
                                {{ request('search') ? 'Tidak ada balita yang cocok dengan kata kunci pencarian Anda.' : 'Belum ada data warga kategori balita yang terdaftar di sistem.' }}
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if(isset($balitas) && $balitas->hasPages())
        <div class="px-8 py-5 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest text-center sm:text-left">
                Menampilkan <span class="text-slate-800">{{ $balitas->firstItem() }}</span> - <span class="text-slate-800">{{ $balitas->lastItem() }}</span> dari <span class="text-slate-800">{{ $balitas->total() }}</span> Balita
            </p>
            <div class="pagination-wrapper text-xs">
                {{ $balitas->links() }}
            </div>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    const showLoader = (text = 'MEMUAT DATA BALITA...') => {
        const loader = document.getElementById('smoothLoader');
        if(loader) {
            document.getElementById('loaderText').innerText = text;
            loader.style.display = 'flex';
            void loader.offsetWidth; 
            loader.classList.remove('opacity-0', 'pointer-events-none');
            loader.classList.add('opacity-100');
        }
    };
    
    // Matikan loader saat halaman selesai dimuat (Back/Forward Cache Support)
    window.addEventListener('pageshow', (e) => {
        const loader = document.getElementById('smoothLoader');
        if(loader) {
            loader.classList.remove('opacity-100');
            loader.classList.add('opacity-0');
            setTimeout(() => {
                loader.classList.add('pointer-events-none');
                loader.style.display = 'none';
            }, 300);
        }
    });

    // Auto-submit search form saat menekan Enter
    document.getElementById('filterForm').addEventListener('submit', () => showLoader('MENCARI DATA...'));
    
    // Trigger loader untuk tombol EMR & Pagination
    document.querySelectorAll('.smooth-route, .pagination-wrapper a').forEach(link => {
        link.addEventListener('click', function(e) {
            if(!this.classList.contains('target-blank') && this.target !== '_blank' && !e.ctrlKey && !e.metaKey) {
                showLoader('MEMBUKA REKAM MEDIS...');
            }
        });
    });
</script>
@endpush