@extends('layouts.bidan')

@section('title', 'Ruang Tunggu Meja 5 - Pemeriksaan Medis')

@section('content')
<div class="bg-[#f8fafc] min-h-screen pb-12 font-poppins w-full animate-fade-in">
    
    {{-- BARIS JUDUL UTAMA & STATISTIK RINGKAS --}}
    <div class="pt-6 pb-6 px-4 md:px-8 max-w-7xl mx-auto flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fas fa-notes-medical text-teal-500"></i> Ruang Pemeriksaan Meja 5
            </h1>
            <p class="text-xs md:text-sm font-medium text-slate-500 mt-1">Kelola validasi antrian klinis dan keputusan medis hasil input dari Meja 1-4.</p>
        </div>
        
        {{-- Badge Total Antrian Aktif --}}
        <div class="bg-teal-50 border border-teal-100 px-4 py-2 rounded-2xl flex items-center gap-3 shadow-sm shrink-0">
            <div class="w-2.5 h-2.5 rounded-full bg-teal-500 animate-pulse"></div>
            <p class="text-xs font-bold text-teal-800">
                Antrian Belum Diperiksa: <span class="text-sm font-black">{{ $pendingCount }} Pasien</span>
            </p>
        </div>
    </div>

    {{-- AREA KONTEN UTAMA --}}
    <div class="max-w-7xl mx-auto px-4 md:px-8">
        <div class="bg-white rounded-[2rem] border border-slate-100 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.02)] overflow-hidden p-6">
            
            {{-- BARIS NAVIGASI TAB & FILTER PENCARIAN --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5 mb-6">
                
                {{-- Navigasi Tab (Pending vs Verified) --}}
                <div class="flex bg-slate-100 p-1 rounded-xl w-full md:w-auto self-start">
                    <a href="{{ route('bidan.pemeriksaan.index', ['tab' => 'pending', 'search' => request('search')]) }}" 
                       class="flex-1 md:flex-none text-center px-5 py-2.5 rounded-lg text-xs font-black uppercase tracking-wider transition-all {{ $tab === 'pending' ? 'bg-white text-teal-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                        <i class="fas fa-clock mr-1.5"></i> Belum Diperiksa
                    </a>
                    <a href="{{ route('bidan.pemeriksaan.index', ['tab' => 'verified', 'search' => request('search')]) }}" 
                       class="flex-1 md:flex-none text-center px-5 py-2.5 rounded-lg text-xs font-black uppercase tracking-wider transition-all {{ $tab === 'verified' ? 'bg-white text-teal-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                        <i class="fas fa-check-circle mr-1.5"></i> Selesai Diperiksa
                    </a>
                </div>

                {{-- Form Filter Pencarian Warga --}}
                <form action="{{ route('bidan.pemeriksaan.index') }}" method="GET" class="w-full md:w-80 flex gap-2">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="w-full bg-slate-50 border border-slate-200 text-xs font-bold rounded-xl pl-9 pr-4 py-3 text-slate-700 placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:bg-white transition-all" 
                               placeholder="Cari Nama Pasien atau NIK...">
                    </div>
                    @if(request('search'))
                        <a href="{{ route('bidan.pemeriksaan.index', ['tab' => $tab]) }}" class="px-3 bg-slate-100 border border-slate-200 hover:bg-slate-200 text-slate-500 rounded-xl flex items-center justify-center text-xs transition-colors" title="Bersihkan Pencarian">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </form>
            </div>

            {{-- TABEL DATA ANTRIAN PEMERIKSAAN --}}
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse whitespace-nowrap min-w-[800px]">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] uppercase tracking-widest text-slate-400 font-black">
                            <th class="p-4 w-12 text-center">No</th>
                            <th class="p-4">Tanggal Kunjungan</th>
                            <th class="p-4">Nama Pasien</th>
                            <th class="p-4">Kategori Demografi</th>
                            <th class="p-4 text-center">Status Validasi</th>
                            <th class="p-4 text-center">Tindakan Medis</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-xs">
                        @forelse($pemeriksaans as $index => $item)
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                {{-- Nomor Urut Urutan Halaman --}}
                                <td class="p-4 text-center font-bold text-slate-400">
                                    {{ $pemeriksaans->firstItem() + $index }}
                                </td>
                                
                                {{-- Tanggal Input Data --}}
                                <td class="p-4">
                                    <span class="font-black text-slate-700">
                                        {{ \Carbon\Carbon::parse($item->tanggal_kunjungan)->translatedFormat('d F Y') }}
                                    </span>
                                </td>
                                
                                {{-- Detail Nama Pasien --}}
                                <td class="p-4">
                                    <div class="flex flex-col">
                                        <span class="font-black text-slate-800 text-sm">{{ $item->kunjungan->pasien->nama_lengkap ?? 'Nama Tidak Terdata' }}</span>
                                        <span class="text-[10px] text-slate-400 font-bold mt-0.5 uppercase tracking-wider">NIK: {{ $item->kunjungan->pasien->nik ?? '-' }}</span>
                                    </div>
                                </td>
                                
                                {{-- Badge Warna Tiap Kategori Warga --}}
                                <td class="p-4">
                                    @php
                                        $kat = strtolower($item->kategori_pasien);
                                        $badgeStyle = match($kat) {
                                            'balita' => 'bg-teal-50 text-teal-700 border-teal-100',
                                            'remaja' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                                            'ibu hamil', 'ibu_hamil' => 'bg-rose-50 text-rose-700 border-rose-100',
                                            'lansia' => 'bg-amber-50 text-amber-700 border-amber-100',
                                            default => 'bg-slate-100 text-slate-600 border-slate-200'
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider border {{ $badgeStyle }}">
                                        {{ $item->kategori_pasien }}
                                    </span>
                                </td>
                                
                                {{-- Status Verifikasi Alur Data --}}
                                <td class="p-4 text-center">
                                    @if($item->status_verifikasi === 'verified')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 font-bold border border-emerald-100">
                                            <i class="fas fa-check-circle text-[10px]"></i> Selesai Periksa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-600 font-bold border border-amber-100 animate-pulse">
                                            <i class="fas fa-clock text-[10px]"></i> Menunggu Validasi
                                        </span>
                                    @endif
                                </td>
                                
                                {{-- TOMBOL AKSI HORIZONTAL RAPIH --}}
                                <td class="p-4">
                                    <div class="flex items-center justify-center gap-2">
                                        @if($item->status_verifikasi !== 'verified')
                                            {{-- Jika Pending: Tombol Periksa Validasi --}}
                                            <a href="{{ route('bidan.pemeriksaan.validasi', $item->id) }}" 
                                               class="h-8 px-3 rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-500 hover:text-white flex items-center justify-center gap-1.5 font-black uppercase tracking-widest text-[10px] border border-teal-200 hover:border-teal-500 hover:shadow-md transition-all" 
                                               title="Periksa Medis Pasien">
                                                <i class="fas fa-file-medical-chart text-xs"></i> Periksa Pasien
                                            </a>
                                            
                                            {{-- Tombol Hapus Antrian Darurat --}}
                                            <form action="{{ route('bidan.pemeriksaan.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus antrian pemeriksaan warga ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white border border-rose-100 hover:border-rose-500 flex items-center justify-center transition-all" title="Batalkan Antrian">
                                                    <i class="fas fa-trash-can"></i>
                                                </button>
                                            </form>
                                        @else
                                            {{-- Jika Verified: Tombol Lihat Detail Rekam Medis Saja (Read Only) --}}
                                            <a href="{{ route('bidan.pemeriksaan.show', $item->id) }}" 
                                               class="h-8 px-3 rounded-lg bg-slate-50 text-slate-600 hover:bg-slate-800 hover:text-white flex items-center justify-center gap-1.5 font-black uppercase tracking-widest text-[10px] border border-slate-200 hover:border-slate-800 hover:shadow-md transition-all" 
                                               title="Lihat Detail Pemeriksaan">
                                                <i class="fas fa-eye text-xs"></i> Lihat Hasil Arsip
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            {{-- State Jika Ruang Tunggu Kosong --}}
                            <tr>
                                <td colspan="6" class="p-16 text-center">
                                    <div class="w-16 h-16 bg-slate-50 text-slate-300 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4">
                                        <i class="fas fa-hospital-user"></i>
                                    </div>
                                    <h3 class="text-sm font-black text-slate-700">Ruang Tunggu Kosong</h3>
                                    <p class="text-xs text-slate-400 mt-1">Tidak ada data antrian pasien yang cocok atau menunggu diperiksa saat ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- FOOTER PAGINATION YANG RAPIH --}}
            @if($pemeriksaans->hasPages())
                <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        Menampilkan <span class="text-slate-700">{{ $pemeriksaans->firstItem() }}</span> - <span class="text-slate-700">{{ $pemeriksaans->lastItem() }}</span> dari <span class="text-slate-700">{{ $pemeriksaans->total() }}</span> Antrian
                    </p>
                    <div class="pagination-custom text-xs">
                        {{ $pemeriksaans->links() }}
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .animate-fade-in { animation: fadeIn 0.5s ease forwards; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>
@endpush