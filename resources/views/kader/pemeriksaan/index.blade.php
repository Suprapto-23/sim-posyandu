@extends('layouts.kader')
@section('title', 'Log Rekam Medis - Nexus EMR')
@section('page-name', 'Manajemen Antropometri')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    :root {
        --nexus-emerald: #059669;
        --nexus-emerald-light: #ecfdf5;
        --nexus-slate: #64748b;
        --nexus-dark: #0f172a;
        --glass-bg: rgba(255, 255, 255, 0.75);
        --glass-border: rgba(255, 255, 255, 0.5);
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #f8fafc;
    }

    .animate-up { opacity: 0; animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    /* Decorative Medical Aura */
    .nexus-blur-aura {
        position: fixed; width: 500px; height: 500px; border-radius: 50%;
        filter: blur(100px); z-index: -1; opacity: 0.12; pointer-events: none;
    }
    .aura-emerald { top: -50px; right: -50px; background: var(--nexus-emerald); }
    .aura-teal { bottom: -50px; left: -50px; background: #14b8a6; }

    /* Glassmorphism Container */
    .nexus-glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid var(--glass-border);
        border-radius: 28px;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    /* Filter & Search Bar */
    .nexus-search-bar {
        background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0;
        display: flex; align-items: center; padding: 5px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }
    .nexus-search-input {
        flex: 1; border: none; padding: 10px 15px; outline: none; background: transparent;
        font-weight: 500; font-size: 13px; color: var(--nexus-dark);
    }
    .nexus-search-btn {
        background: var(--nexus-emerald); color: white; padding: 10px 20px;
        border-radius: 12px; font-weight: 700; font-size: 12px; border: none;
        cursor: pointer; transition: all 0.2s;
    }
    .nexus-search-btn:hover { background: #047857; }

    .nexus-select {
        background: #ffffff; border: 1px solid #e2e8f0; padding: 12px 16px;
        border-radius: 16px; font-weight: 600; font-size: 13px; color: var(--nexus-dark);
        outline: none; cursor: pointer; appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 12px center; background-size: 16px;
        padding-right: 40px;
    }

    /* Modern Table Design */
    .nexus-table-wrapper { overflow-x: auto; width: 100%; }
    .nexus-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .nexus-table th {
        background: rgba(248, 250, 252, 0.6); padding: 16px 24px;
        text-align: left; font-size: 11px; font-weight: 800; color: var(--nexus-slate);
        text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .nexus-table td {
        padding: 18px 24px; border-bottom: 1px solid #f1f5f9;
        font-size: 14px; font-weight: 600; color: var(--nexus-dark);
        vertical-align: middle; transition: background 0.2s;
    }
    .nexus-table tr:last-child td { border-bottom: none; }
    .nexus-table tbody tr:hover td { background: rgba(236, 253, 245, 0.4); }

    /* Identity Cell */
    .cell-identity { display: flex; flex-direction: column; gap: 4px; }
    .cell-name { font-weight: 800; color: var(--nexus-dark); font-size: 14px; }
    .cell-nik { font-family: monospace; font-size: 11px; color: #94a3b8; font-weight: 500; }

    /* Category Pill */
    .cat-pill {
        display: inline-flex; items-center; gap: 6px; padding: 6px 12px;
        border-radius: 10px; font-size: 11px; font-weight: 800; text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .cat-balita { background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
    .cat-remaja { background: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }
    .cat-lansia { background: #fce7f3; color: #be185d; border: 1px solid #fbcfe8; }

    /* Status Badges */
    .status-badge {
        display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px;
        border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .status-pending { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; } /* Gold */
    .status-verified { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; } /* Emerald */
    .status-rejected { background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; } /* Rose */

    /* Action Buttons */
    .action-group { display: flex; gap: 8px; }
    .btn-action {
        width: 36px; height: 36px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
        transition: all 0.2s; border: 1px solid transparent; cursor: pointer; text-decoration: none;
    }
    .btn-show { background: #f8fafc; color: #64748b; border-color: #e2e8f0; }
    .btn-show:hover { background: #e2e8f0; color: #0f172a; }
    .btn-edit { background: #fffbeb; color: #d97706; border-color: #fde68a; }
    .btn-edit:hover { background: #fef3c7; transform: translateY(-2px); }
    .btn-del { background: #fff1f2; color: #e11d48; border-color: #fecdd3; }
    .btn-del:hover { background: #ffe4e6; transform: translateY(-2px); }
    
    /* Tombol Tambah Utama */
    .btn-nexus-primary {
        background: var(--nexus-emerald); color: white; padding: 14px 24px;
        border-radius: 16px; font-weight: 800; text-transform: uppercase;
        letter-spacing: 0.5px; font-size: 12px; display: inline-flex; align-items: center; gap: 8px;
        box-shadow: 0 8px 20px rgba(5, 150, 105, 0.25); transition: all 0.3s ease; text-decoration: none;
    }
    .btn-nexus-primary:hover { background: #047857; transform: translateY(-2px); box-shadow: 0 12px 24px rgba(5, 150, 105, 0.35); color: white; }

    /* SweetAlert Custom Nexus Style */
    .swal2-backdrop-show { background: rgba(15, 23, 42, 0.5) !important; backdrop-filter: blur(8px) !important; }
    .nexus-swal-popup { border-radius: 28px !important; padding: 2.5rem 2rem !important; background: rgba(255, 255, 255, 0.95) !important; backdrop-filter: blur(20px) !important; border: 1px solid rgba(255, 255, 255, 0.8) !important; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15) !important; width: 26em !important; }
    .nexus-swal-title { font-family: 'Plus Jakarta Sans', sans-serif !important; font-size: 1.35rem !important; font-weight: 800 !important; color: #0f172a !important; margin-bottom: 0.5rem !important; }
    .nexus-swal-text { font-family: 'Plus Jakarta Sans', sans-serif !important; color: #64748b !important; font-size: 0.9rem !important; line-height: 1.6 !important; font-weight: 500 !important; }
    .nexus-swal-actions { gap: 12px !important; margin-top: 2rem !important; width: 100% !important; }
    .swal-btn-danger { flex: 1; background-color: #e11d48 !important; color: white !important; border-radius: 14px !important; padding: 14px 24px !important; font-weight: 800 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; text-transform: uppercase !important; box-shadow: 0 4px 14px 0 rgba(225, 29, 72, 0.3) !important; border: 1px solid transparent !important; transition: all 0.2s ease !important; }
    .swal-btn-danger:hover { background-color: #be123c !important; transform: translateY(-2px) !important; box-shadow: 0 6px 20px rgba(225, 29, 72, 0.4) !important; }
    .swal-btn-cancel { flex: 1; background-color: #ffffff !important; color: #64748b !important; border: 1px solid #cbd5e1 !important; border-radius: 14px !important; padding: 14px 24px !important; font-weight: 800 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; text-transform: uppercase !important; transition: all 0.2s ease !important; }
    .swal-btn-cancel:hover { background-color: #f8fafc !important; color: #0f172a !important; border-color: #94a3b8 !important; }
</style>
@endpush

@section('content')
<div class="max-w-[1200px] mx-auto animate-up pb-20 relative px-4 mt-6">
    <div class="nexus-blur-aura aura-emerald"></div>
    <div class="nexus-blur-aura aura-teal"></div>

    {{-- HEADER CONTROLS --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Log <span class="text-emerald-600">Rekam Medis</span></h1>
            <p class="text-slate-500 font-medium text-sm mt-1">Kelola data antropometri fisik dan pantau status validasi Bidan.</p>
        </div>
        <div>
            <a href="{{ route('kader.pemeriksaan.create') }}" class="btn-nexus-primary">
                <i class="fas fa-plus-circle text-lg"></i> Input Pengukuran Fisik
            </a>
        </div>
    </div>

    {{-- FILTER ENGINE --}}
    <div class="nexus-glass-card mb-8 p-4">
        <form action="{{ route('kader.pemeriksaan.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            
            <div class="nexus-search-bar flex-1">
                <i class="fas fa-search text-slate-400 ml-3"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="nexus-search-input" placeholder="Cari nama warga atau NIK...">
                <button type="submit" class="nexus-search-btn">CARI</button>
            </div>

            <select name="kategori" class="nexus-select w-full md:w-48" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                <option value="balita" {{ request('kategori') == 'balita' ? 'selected' : '' }}>Balita (12-59 Bln)</option>
                <option value="remaja" {{ request('kategori') == 'remaja' ? 'selected' : '' }}>Remaja</option>
                <option value="lansia" {{ request('kategori') == 'lansia' ? 'selected' : '' }}>Lansia</option>
            </select>

            <select name="status" class="nexus-select w-full md:w-48" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Menunggu Validasi</option>
                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>✅ Tervalidasi</option>
                <option value="direvisi" {{ request('status') == 'direvisi' ? 'selected' : '' }}>❌ Revisi/Ditolak</option>
            </select>
            
            @if(request('search') || request('kategori') || request('status'))
                <a href="{{ route('kader.pemeriksaan.index') }}" class="flex items-center justify-center px-4 py-3 rounded-2xl bg-rose-50 text-rose-600 font-bold text-xs hover:bg-rose-100 transition-colors border border-rose-100">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
            @endif
        </form>
    </div>

    {{-- DATA TABLE --}}
    <div class="nexus-glass-card">
        <div class="nexus-table-wrapper">
            <table class="nexus-table">
                <thead>
                    <tr>
                        <th>Identitas Warga</th>
                        <th>Kategori</th>
                        <th>Tanggal Periksa</th>
                        <th>Pengukuran Utama</th>
                        <th>Status Medis</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pemeriksaans as $pem)
                    <tr>
                        <td>
                            <div class="cell-identity">
                                <span class="cell-name">{{ $pem->kunjungan->pasien->nama_lengkap ?? 'Data Terhapus' }}</span>
                                <span class="cell-nik"><i class="fas fa-id-card mr-1"></i> {{ $pem->kunjungan->pasien->nik ?? '-' }}</span>
                            </div>
                        </td>
                        <td>
                            @if($pem->kategori_pasien === 'balita')
                                <span class="cat-pill cat-balita"><i class="fas fa-child"></i> Balita</span>
                            @elseif($pem->kategori_pasien === 'remaja')
                                <span class="cat-pill cat-remaja"><i class="fas fa-user-graduate"></i> Remaja</span>
                            @elseif($pem->kategori_pasien === 'lansia')
                                <span class="cat-pill cat-lansia"><i class="fas fa-wheelchair"></i> Lansia</span>
                            @else
                                <span class="cat-pill" style="background:#f1f5f9; color:#64748b;">Lainnya</span>
                            @endif
                        </td>
                        <td>
                            <div class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($pem->tanggal_periksa)->translatedFormat('d M Y') }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-1">{{ $pem->created_at->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="text-center px-3 py-1 bg-slate-50 rounded-lg border border-slate-100">
                                    <div class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider mb-0.5">Berat</div>
                                    <div class="text-sm font-bold text-slate-800">{{ $pem->berat_badan ?? '-' }}<span class="text-[10px] text-emerald-500 ml-0.5">kg</span></div>
                                </div>
                                <div class="text-center px-3 py-1 bg-slate-50 rounded-lg border border-slate-100">
                                    <div class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider mb-0.5">Tinggi</div>
                                    <div class="text-sm font-bold text-slate-800">{{ $pem->tinggi_badan ?? '-' }}<span class="text-[10px] text-emerald-500 ml-0.5">cm</span></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $status = strtolower($pem->status_verifikasi);
                                $badgeClass = 'status-pending';
                                $icon = 'fa-clock';
                                $text = 'Menunggu';

                                if (in_array($status, ['verified', 'tervalidasi', 'approved'])) {
                                    $badgeClass = 'status-verified';
                                    $icon = 'fa-check-circle';
                                    $text = 'Tervalidasi';
                                } elseif (in_array($status, ['ditolak', 'rejected', 'direvisi'])) {
                                    $badgeClass = 'status-rejected';
                                    $icon = 'fa-exclamation-circle';
                                    $text = 'Revisi';
                                }
                            @endphp
                            <span class="status-badge {{ $badgeClass }}">
                                <i class="fas {{ $icon }}"></i> {{ $text }}
                            </span>
                        </td>
                        <td>
                            <div class="action-group justify-end">
                                <a href="{{ route('kader.pemeriksaan.show', $pem->id) }}" class="btn-action btn-show" title="Lihat Detail">
                                    <i class="fas fa-file-medical"></i>
                                </a>
                                
                                {{-- Kunci Tombol Edit jika sudah di-ACC Bidan --}}
                                @if(!in_array($status, ['verified', 'tervalidasi', 'approved']))
                                    <a href="{{ route('kader.pemeriksaan.edit', $pem->id) }}" class="btn-action btn-edit" title="Koreksi Data">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    
                                    <form action="{{ route('kader.pemeriksaan.destroy', $pem->id) }}" method="POST" class="delete-form inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn-action btn-del btn-delete" title="Hapus Data">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @else
                                    <div class="btn-action" style="background:#f1f5f9; color:#cbd5e1; cursor:not-allowed;" title="Terkunci (Sudah ACC)">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                    <div class="btn-action" style="background:#f1f5f9; color:#cbd5e1; cursor:not-allowed;" title="Terkunci (Sudah ACC)">
                                        <i class="fas fa-ban"></i>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-16">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-folder-open text-4xl text-slate-300"></i>
                                </div>
                                <h3 class="text-lg font-bold text-slate-700 mb-1">Rekam Medis Kosong</h3>
                                <p class="text-sm font-medium text-slate-500">Belum ada data pemeriksaan yang sesuai dengan filter Anda.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Custom Pagination --}}
        @if($pemeriksaans->hasPages())
        <div class="p-6 border-t border-slate-100 bg-white/50">
            {{ $pemeriksaans->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // SWEETALERT2 NEXUS THEME UNTUK DELETE CONFIRMATION
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('.delete-form');
                
                Swal.fire({
                    title: 'Hapus Rekam Medis?',
                    html: `Data pengukuran fisik ini akan <b class="text-rose-500 font-bold">dihapus secara permanen</b> dari sistem. Aksi ini tidak dapat dibatalkan.`,
                    icon: 'warning',
                    iconColor: '#f43f5e', 
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Ya, Hapus',
                    cancelButtonText: 'Batalkan',
                    reverseButtons: true, 
                    buttonsStyling: false, 
                    customClass: { 
                        popup: 'nexus-swal-popup animate-up', 
                        title: 'nexus-swal-title',
                        htmlContainer: 'nexus-swal-text',
                        actions: 'nexus-swal-actions',
                        confirmButton: 'swal-btn-danger',
                        cancelButton: 'swal-btn-cancel'
                    },
                    showClass: { popup: '' }, // override default swal anim to use our custom animate-up
                    hideClass: { popup: 'swal2-hide' }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menghapus Data...',
                            text: 'Sistem sedang membersihkan rekam medis.',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            customClass: { popup: 'nexus-swal-popup', title: 'nexus-swal-title', htmlContainer: 'nexus-swal-text' },
                            willOpen: () => { Swal.showLoading(); }
                        });
                        form.submit();
                    }
                });
            });
        });

        // SUCCESS/ERROR TOAST NOTIFICATIONS (Gaya Nexus)
        @if(session('success'))
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
                customClass: { popup: 'nexus-glass-card border border-emerald-200 !rounded-2xl !p-3' }
            });
            Toast.fire({
                icon: 'success',
                title: '<span class="text-sm font-bold text-slate-800 ml-1">{{ session('success') }}</span>'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                title: 'Perhatian Sistem',
                text: '{{ session('error') }}',
                icon: 'error',
                iconColor: '#f43f5e',
                confirmButtonText: 'Mengerti',
                buttonsStyling: false,
                customClass: { 
                    popup: 'nexus-swal-popup animate-up', 
                    title: 'nexus-swal-title',
                    htmlContainer: 'nexus-swal-text',
                    confirmButton: 'swal-btn-danger w-full mt-4'
                }
            });
        @endif
    });
</script>
@endpush
@endsection