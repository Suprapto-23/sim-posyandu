@extends('layouts.bidan')

@section('title', 'Kelola Jadwal Posyandu')
@section('page-name', 'Manajemen Jadwal')

@push('styles')
<style>
    /* ANIMASI MASUK HALUS */
    .fade-in-up { opacity: 0; animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes slideUpFade { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    /* NEXUS TABLE ROW (PRESISI & ELEGAN) */
    .nexus-table-row { 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        border-bottom: 1px solid #f1f5f9; 
        background-color: #ffffff; 
    }
    .nexus-table-row:last-child { border-bottom: none; }
    .nexus-table-row:hover { 
        background-color: #f8fafc; 
        transform: translateY(-2px) scale(1.002);
        box-shadow: 0 10px 25px -5px rgba(6, 182, 212, 0.08); 
        z-index: 10; position: relative;
        border-color: transparent;
        border-radius: 16px;
    }

    /* KARTU TANGGAL MINI (CLEAN) */
    .date-card { transition: all 0.3s ease; }
    .nexus-table-row:hover .date-card { border-color: #bae6fd; background-color: #f0f9ff; }

    /* SWEETALERT NEXUS ULTIMATE */
    .swal2-popup.nexus-swal {
        border-radius: 36px !important; padding: 3rem 2.5rem !important;
        background: rgba(255, 255, 255, 0.98) !important; backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(255,255,255,0.9) !important; box-shadow: 0 40px 80px -15px rgba(15, 23, 42, 0.15) !important;
    }
    .swal2-title.nexus-title { font-family: 'Poppins', sans-serif !important; font-weight: 800 !important; font-size: 24px !important; color: #0f172a !important; margin-bottom: 0.5rem !important; }
    .swal2-html-container.nexus-text { font-weight: 500 !important; font-size: 14px !important; color: #64748b !important; line-height: 1.6 !important; }

    /* MODIFIKASI IKON SWEETALERT */
    .swal2-icon.swal2-success { border-color: #10b981 !important; color: #10b981 !important; background-color: #ecfdf5 !important; box-shadow: 0 0 0 10px #f0fdf4 !important; margin-bottom: 2rem !important; }
    .swal2-icon.swal2-success [class^=swal2-success-line] { background-color: #10b981 !important; }
    .swal2-icon.swal2-success .swal2-success-ring { border-color: rgba(16, 185, 129, 0.15) !important; }
    .swal2-icon.swal2-warning { border-color: #f43f5e !important; color: #f43f5e !important; background-color: #fff1f2 !important; box-shadow: 0 0 0 10px #fff1f2 !important; margin-bottom: 2rem !important; }

    /* KUSTOMISASI SCROLLBAR TABEL */
    .custom-scrollbar::-webkit-scrollbar { height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    
    #toast-container, .toast, .alert-success, .alert-danger { display: none !important; }
</style>
@endpush

@section('content')

{{-- =================================================================
     LOADER SISTEM NEXUS
     ================================================================= --}}
<div id="smoothLoader" class="fixed inset-0 bg-slate-50/90 backdrop-blur-sm z-[9999] flex flex-col items-center justify-center transition-all duration-300 opacity-0 pointer-events-none">
    <div class="relative w-16 h-16 flex items-center justify-center mb-5">
        <div class="absolute inset-0 border-4 border-cyan-100 rounded-full"></div>
        <div class="absolute inset-0 border-4 border-cyan-600 rounded-full border-t-transparent animate-spin"></div>
        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm">
            <i class="fas fa-calendar-alt text-cyan-600 text-lg animate-pulse"></i>
        </div>
    </div>
    <div class="bg-white px-5 py-2.5 rounded-full shadow-lg border border-slate-100 flex items-center gap-3">
        <div class="w-2 h-2 rounded-full bg-cyan-500 animate-ping"></div>
        <p class="text-[10.5px] font-black text-cyan-700 uppercase tracking-[0.2em] font-poppins" id="loaderText">MEMUAT DATA...</p>
    </div>
</div>

<div class="max-w-[1250px] mx-auto fade-in-up pb-20">

    {{-- =================================================================
         1. HERO HEADER (Ringkas & Mewah)
         ================================================================= --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-8 bg-white p-6 md:px-10 md:py-8 rounded-[32px] border border-slate-200 shadow-sm relative overflow-hidden">
        <div class="absolute right-0 top-0 w-80 h-80 bg-cyan-50/80 rounded-bl-full pointer-events-none transition-transform duration-700 hover:scale-110"></div>
        
        <div class="flex items-center gap-6 relative z-10">
            <div class="w-16 h-16 rounded-[20px] bg-gradient-to-tr from-cyan-500 to-blue-600 text-white flex items-center justify-center text-3xl shadow-[0_10px_25px_rgba(6,182,212,0.3)] shrink-0 border border-cyan-400">
                <i class="far fa-calendar-check"></i>
            </div>
            <div>
                <h1 class="text-[24px] font-black text-slate-800 tracking-tight font-poppins mb-1">Agenda Posyandu Anda</h1>
                <p class="text-slate-500 font-medium text-[13px] leading-relaxed max-w-lg">Susun jadwal pelayanan klinis Posyandu bulanan Anda. Sistem akan otomatis mendistribusikan notifikasi ke warga terkait.</p>
            </div>
        </div>
        
        <a href="{{ route('bidan.jadwal.create') }}" class="smooth-route inline-flex items-center justify-center gap-3 px-8 py-4 bg-slate-900 text-white font-bold text-[12px] uppercase tracking-widest rounded-2xl hover:bg-cyan-600 hover:shadow-[0_15px_30px_-5px_rgba(6,182,212,0.4)] hover:-translate-y-1 transition-all duration-300 shrink-0 relative z-10 w-full sm:w-auto">
            <i class="fas fa-plus-circle text-lg text-cyan-400"></i> Buat Agenda Baru
        </a>
    </div>

    {{-- =================================================================
         2. TABEL DATA (Presisi Piksel & Responsif)
         ================================================================= --}}
    <div class="bg-white rounded-[32px] border border-slate-100 shadow-[0_10px_40px_-10px_rgba(15,23,42,0.05)] flex flex-col overflow-hidden relative z-10">
        
        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-[12px] bg-cyan-100/50 text-cyan-600 flex items-center justify-center shadow-inner text-lg border border-cyan-100"><i class="fas fa-list-ul"></i></div>
                <div>
                    <h3 class="font-black text-slate-800 text-[16px] font-poppins tracking-tight">Daftar Agenda Tersimpan</h3>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Disusun Berdasarkan Tanggal Terdekat</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar flex-1 p-3">
            <table class="w-full text-left border-collapse min-w-[1050px]">
                <thead>
                    <tr>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 w-16 text-center">No</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Waktu Pelaksanaan</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Rincian Kegiatan</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Distribusi Sasaran</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Status</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right pr-8">Manajemen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwals as $index => $jadwal)
                    @php
                        $isToday = \Carbon\Carbon::parse($jadwal->tanggal)->isToday();
                    @endphp
                    <tr class="nexus-table-row group {{ $isToday ? 'bg-cyan-50/30' : '' }}">
                        
                        {{-- NO --}}
                        <td class="px-6 py-5 text-[13px] font-bold text-slate-400 align-middle text-center">
                            {{ $jadwals->firstItem() + $index }}
                        </td>
                        
                        {{-- WAKTU & TANGGAL --}}
                        <td class="px-6 py-5 align-middle">
                            <div class="flex items-center gap-4">
                                <div class="date-card w-14 h-14 rounded-[16px] {{ $isToday ? 'bg-cyan-100 border-cyan-200' : 'bg-slate-50 border-slate-200' }} border flex flex-col items-center justify-center shrink-0 shadow-sm">
                                    <span class="text-[9px] font-black {{ $isToday ? 'text-cyan-700' : 'text-slate-500' }} uppercase tracking-widest">{{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('M') }}</span>
                                    <span class="text-[20px] font-black {{ $isToday ? 'text-cyan-800' : 'text-slate-800' }} leading-none mt-0.5">{{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d') }}</span>
                                </div>
                                <div>
                                    <p class="font-black text-slate-800 text-[14px] mb-1 font-poppins flex items-center gap-2">
                                        {{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('l, Y') }}
                                        @if($isToday) <span class="px-2 py-0.5 bg-rose-100 text-rose-600 text-[9px] uppercase tracking-widest rounded-md animate-pulse">Hari Ini</span> @endif
                                    </p>
                                    <p class="text-[12px] font-bold text-slate-500 flex items-center gap-1.5">
                                        <i class="far fa-clock text-cyan-500"></i> {{ date('H:i', strtotime($jadwal->waktu_mulai)) }} - {{ date('H:i', strtotime($jadwal->waktu_selesai)) }} WIB
                                    </p>
                                </div>
                            </div>
                        </td>

                        {{-- RINCIAN KEGIATAN --}}
                        <td class="px-6 py-5 align-middle max-w-[280px]">
                            <p class="font-black text-slate-800 text-[14.5px] mb-1.5 font-poppins leading-tight group-hover:text-cyan-700 transition-colors">{{ $jadwal->judul }}</p>
                            <p class="text-[12px] font-medium text-slate-500 line-clamp-2 mb-2">{{ $jadwal->deskripsi ?? 'Tidak ada deskripsi tambahan.' }}</p>
                            <p class="text-[11px] font-bold text-slate-600 flex items-center gap-1.5 bg-slate-50 px-2 py-1 rounded border border-slate-100 inline-flex">
                                <i class="fas fa-map-marker-alt text-rose-400"></i> {{ $jadwal->lokasi }}
                            </p>
                        </td>

                        {{-- DISTRIBUSI SASARAN --}}
                        <td class="px-6 py-5 align-middle">
                            <div class="flex flex-col items-start gap-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black rounded-lg border border-indigo-100 uppercase tracking-widest">
                                    <i class="fas fa-tags text-indigo-400 text-[10px]"></i> {{ $jadwal->kategori }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded-lg border border-emerald-100 uppercase tracking-widest">
                                    <i class="fas fa-bullseye text-emerald-400 text-[10px]"></i> {{ ucwords(str_replace('_', ' ', $jadwal->target_peserta)) }}
                                </span>
                            </div>
                        </td>

                        {{-- STATUS --}}
                        <td class="px-6 py-5 text-center align-middle">
                            @php
                                $statusConf = match(strtolower($jadwal->status)) {
                                    'aktif' => ['bg-cyan-50 text-cyan-700 border-cyan-200', 'Agenda Aktif', 'fa-check-circle text-cyan-500', 'animate-pulse'],
                                    'selesai' => ['bg-slate-50 text-slate-500 border-slate-200', 'Selesai', 'fa-flag-checkered text-slate-400', ''],
                                    'dibatalkan' => ['bg-rose-50 text-rose-600 border-rose-200', 'Dibatalkan', 'fa-times-circle text-rose-500', ''],
                                    default => ['bg-slate-50 text-slate-600 border-slate-200', $jadwal->status, 'fa-info-circle text-slate-400', '']
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border {{ $statusConf[0] }} shadow-sm">
                                <i class="fas {{ $statusConf[2] }} {{ $statusConf[3] }}"></i> {{ $statusConf[1] }}
                            </span>
                        </td>

                        {{-- AKSI / MANAJEMEN --}}
                        <td class="px-6 py-5 text-right align-middle pr-8">
                            <div class="flex items-center justify-end gap-2 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <a href="{{ route('bidan.jadwal.edit', $jadwal->id) }}" class="smooth-route w-10 h-10 rounded-[12px] bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-amber-50 hover:text-amber-600 hover:border-amber-300 transition-all shadow-sm" title="Edit Jadwal">
                                    <i class="fas fa-pen text-[13px]"></i>
                                </a>
                                <form action="{{ route('bidan.jadwal.destroy', $jadwal->id) }}" method="POST" class="m-0 p-0">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="confirmDelete(this)" class="w-10 h-10 rounded-[12px] bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-rose-50 hover:text-rose-600 hover:border-rose-300 transition-all shadow-sm" title="Batalkan/Hapus Jadwal">
                                        <i class="fas fa-trash-alt text-[13px]"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-24 bg-slate-50/30">
                            <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center text-slate-300 mx-auto mb-5 border-2 border-dashed border-slate-200 shadow-sm"><i class="fas fa-calendar-times text-4xl"></i></div>
                            <h4 class="font-black text-slate-800 text-[18px] font-poppins mb-1.5">Agenda Masih Kosong</h4>
                            <p class="text-[13.5px] font-medium text-slate-500 max-w-sm mx-auto leading-relaxed">Anda belum merencanakan agenda posyandu. Klik tombol <b class="text-cyan-600">Buat Agenda Baru</b> untuk memulai.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($jadwals) && $jadwals->hasPages())
        <div class="px-8 py-5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest hidden sm:block">
                Menampilkan <span class="text-slate-800">{{ $jadwals->firstItem() }}</span> - <span class="text-slate-800">{{ $jadwals->lastItem() }}</span> dari <span class="text-slate-800">{{ $jadwals->total() }}</span> Agenda
            </p>
            <div class="pagination-wrapper text-xs">
                {{ $jadwals->links() }}
            </div>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const showLoader = (text = 'MEMUAT SISTEM...') => {
        const loader = document.getElementById('smoothLoader');
        if(loader) {
            document.getElementById('loaderText').innerText = text;
            loader.style.display = 'flex';
            // Paksa browser membaca DOM ulang agar animasi CSS ter-trigger
            void loader.offsetWidth; 
            loader.classList.remove('opacity-0', 'pointer-events-none');
            loader.classList.add('opacity-100');
        }
    };
    
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

    document.querySelectorAll('.smooth-route, .pagination-wrapper a').forEach(link => {
        link.addEventListener('click', function(e) {
            if(!this.classList.contains('target-blank') && this.target !== '_blank' && !e.ctrlKey && !e.metaKey) {
                showLoader('MEMUAT HALAMAN...');
            }
        });
    });

    // ====================================================================
    // SWEETALERT NEXUS: KONFIRMASI HAPUS
    // ====================================================================
    function confirmDelete(button) {
        const form = button.closest('form');
        
        Swal.fire({
            title: 'Hapus Agenda?',
            text: "Jadwal ini akan dibatalkan dan notifikasi akan ditarik dari sistem warga.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash-alt mr-1.5"></i> Ya, Hapus',
            cancelButtonText: 'Batal',
            buttonsStyling: false,
            customClass: {
                popup: 'nexus-swal',
                title: 'nexus-title',
                htmlContainer: 'nexus-text',
                confirmButton: 'bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-400 hover:to-rose-500 text-white px-8 py-3.5 rounded-[14px] font-bold text-[12px] uppercase tracking-widest transition-all shadow-[0_10px_20px_rgba(244,63,94,0.3)] hover:-translate-y-0.5 outline-none border-none mx-2',
                cancelButton: 'bg-slate-100 hover:bg-slate-200 text-slate-600 px-8 py-3.5 rounded-[14px] font-bold text-[12px] uppercase tracking-widest transition-all outline-none border-none mx-2'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                showLoader('MENGHAPUS AGENDA...');
                form.submit();
            }
        });
    }

    // ====================================================================
    // SWEETALERT NEXUS: NOTIFIKASI SUKSES & ERROR
    // ====================================================================
    @if(session('success'))
        document.querySelectorAll('.alert, .toast').forEach(el => el.remove());

        Swal.fire({
            title: 'Tindakan Berhasil!',
            html: {!! json_encode(session('success')) !!},
            icon: 'success',
            showConfirmButton: true,
            confirmButtonText: '<i class="fas fa-check-circle mr-1.5"></i> Mengerti',
            buttonsStyling: false,
            customClass: {
                popup: 'nexus-swal',
                title: 'nexus-title',
                htmlContainer: 'nexus-text',
                confirmButton: 'bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white px-10 py-3.5 rounded-[14px] font-bold text-[12px] uppercase tracking-widest transition-all shadow-[0_10px_20px_rgba(16,185,129,0.3)] hover:-translate-y-0.5 outline-none border-none mt-2'
            }
        });
    @endif

    @if(session('error'))
        document.querySelectorAll('.alert, .toast').forEach(el => el.remove());
        
        Swal.fire({
            title: 'Terjadi Kesalahan!',
            html: {!! json_encode(session('error')) !!},
            icon: 'error',
            showConfirmButton: true,
            confirmButtonText: '<i class="fas fa-times-circle mr-1.5"></i> Tutup',
            buttonsStyling: false,
            customClass: {
                popup: 'nexus-swal',
                title: 'nexus-title',
                htmlContainer: 'nexus-text',
                confirmButton: 'bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-400 hover:to-rose-500 text-white px-10 py-3.5 rounded-[14px] font-bold text-[12px] uppercase tracking-widest transition-all shadow-[0_10px_20px_rgba(244,63,94,0.3)] hover:-translate-y-0.5 outline-none border-none mt-2'
            }
        });
    @endif
</script>
@endpush