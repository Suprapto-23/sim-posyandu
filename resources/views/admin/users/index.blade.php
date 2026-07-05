@extends('layouts.admin')

@section('title', 'Manajemen User Warga')
@section('page-name', 'Data Warga')
@section('page-title', 'Manajemen Akun Warga')

@php
    use Illuminate\Support\Str;

    $stats = $stats ?? ['total' => 0, 'aktif' => 0, 'nonaktif' => 0];
    $search = request('search', '');
    $status = request('status', 'semua');
    $kategori = request('kategori', 'semua');

    $getName = fn($user) => $user->profile?->full_name ?? $user->name ?? 'Warga';
    $getNik = fn($user) => $user->nik ?? $user->profile?->nik ?? '-';
    $getPhone = fn($user) => $user->profile?->telepon ?? '-';
    $initial = fn($name) => Str::upper(Str::substr(trim((string) $name), 0, 1)) ?: 'W';

    // Helper teks dropdown
    $statusText = $status === 'active' ? 'Aktif Saja' : ($status === 'inactive' ? 'Nonaktif Saja' : 'Semua Status');
    $kategoriText = 'Semua Kategori';
    if($kategori === 'balita') $kategoriText = 'Balita';
    if($kategori === 'remaja') $kategoriText = 'Remaja';
    if($kategori === 'lansia') $kategoriText = 'Lansia';
@endphp

@section('content')
<style>
    .animate-pop-in {
        animation: popIn .5s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }

    @keyframes popIn {
        from { opacity: 0; transform: scale(.95) translateY(10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .delay-100 { animation-delay: .1s; }
    .delay-200 { animation-delay: .2s; }
    .delay-300 { animation-delay: .3s; }

    .hero-grid {
        background-image: radial-gradient(rgba(255,255,255,.45) 1px, transparent 1px);
        background-size: 24px 24px;
    }

    .soft-card {
        background: rgba(255,255,255,.90);
        backdrop-filter: blur(18px);
        transition: all .28s cubic-bezier(.16, 1, .3, 1);
    }

    .soft-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 40px -28px rgba(15,23,42,.35);
    }

    .table-row-soft {
        transition: background .22s ease;
    }

    .table-row-soft:hover {
        background: #f8fafc;
    }

    .action-soft {
        transition: all .22s ease;
    }

    .action-soft:hover {
        transform: translateY(-2px);
    }

    #tableBody {
        transition: opacity 0.3s ease-in-out;
        opacity: 1;
    }

    /* CSS Pagination dari Balita yang membuat bentuknya membulat sempurna */
    .btn-pill {
        border-radius: 9999px;
        transition: all 0.15s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }
    .btn-pill:active { transform: scale(0.95); }
</style>

<!-- Ditambahkan ID adminMain sebagai container delegasi global -->
<div id="adminMain" class="max-w-6xl mx-auto space-y-8 pb-12">

    {{-- HERO SECTION --}}
    <section class="bg-gradient-to-br from-blue-600 via-sky-500 to-emerald-400 rounded-[2.5rem] p-9 md:p-10 relative overflow-hidden shadow-[0_20px_40px_-10px_rgba(59,130,246,.30)] border border-white/20 flex flex-col items-center justify-center text-center group animate-pop-in">
        <div class="hero-grid absolute inset-0 opacity-20 pointer-events-none"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-white/10 blur-[90px] rounded-full pointer-events-none transition-all duration-700 group-hover:bg-white/20"></div>

        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md border border-white/30 text-white text-[11px] font-black px-4 py-1.5 rounded-full mb-4 uppercase tracking-widest shadow-sm">
                <i class="fas fa-users"></i>
                Manajemen Data Warga
            </div>

            <h2 class="text-3xl md:text-4xl font-black text-white mb-3 font-poppins tracking-tight">
                Daftar Akun Warga
            </h2>

            <p class="text-blue-50 text-sm font-medium max-w-lg mx-auto mb-8 leading-relaxed">
                Kelola akun warga Posyandu. NIK digunakan sebagai kredensial utama untuk akses masuk ke sistem.
            </p>

            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-blue-600 font-black px-7 py-3.5 rounded-xl transition-all shadow-lg hover:shadow-xl hover:-translate-y-1">
                <i class="fas fa-plus"></i>
                Daftarkan Warga Baru
            </a>
        </div>
    </section>

    @if(session('generated_password') || session('reset_password'))
        <section class="bg-blue-50 border border-blue-200 rounded-[1.5rem] p-6 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4 animate-pop-in delay-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl shrink-0 shadow-sm border border-blue-200">
                    <i class="fas fa-key"></i>
                </div>
                <div>
                    <h4 class="text-blue-800 font-black text-sm mb-1 uppercase tracking-widest">
                        Kredensial Login Tersedia
                    </h4>
                    <p class="text-xs font-medium text-blue-600">
                        Berikan password ini kepada
                        <strong class="text-blue-800">{{ session('user_name') ?? session('reset_name') ?? 'Warga' }}</strong>
                        dengan NIK {{ session('user_nik') ?? session('reset_nik') ?? '-' }}.
                    </p>
                </div>
            </div>
            <div class="bg-white border border-blue-200 rounded-xl px-5 py-3 flex items-center gap-4 shadow-sm">
                <code class="text-xl font-mono font-black text-blue-600 tracking-wider" id="passwordText">
                    {{ session('generated_password') ?? session('reset_password') }}
                </code>
                <button type="button" onclick="copyPassword()" class="action-soft text-xs bg-blue-50 hover:bg-blue-500 hover:text-white text-blue-600 border border-blue-100 px-3 py-1.5 rounded-lg font-bold shadow-sm" title="Copy Password">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </section>
    @endif

    {{-- KARTU DATA --}}
    <section class="grid grid-cols-1 md:grid-cols-3 gap-5 animate-pop-in delay-100">
        <div class="soft-card rounded-3xl border border-slate-100 p-6 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Warga</div>
                <div class="text-3xl font-black text-slate-700 font-poppins">{{ number_format($stats['total'] ?? 0) }}</div>
            </div>
        </div>

        <div class="soft-card rounded-3xl border border-slate-100 p-6 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-2xl">
                <i class="fas fa-user-check"></i>
            </div>
            <div>
                <div class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1">Akun Aktif</div>
                <div class="text-3xl font-black text-slate-700 font-poppins">{{ number_format($stats['aktif'] ?? 0) }}</div>
            </div>
        </div>

        <div class="soft-card rounded-3xl border border-slate-100 p-6 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center text-2xl">
                <i class="fas fa-user-lock"></i>
            </div>
            <div>
                <div class="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-1">Nonaktif</div>
                <div class="text-3xl font-black text-slate-700 font-poppins">{{ number_format($stats['nonaktif'] ?? 0) }}</div>
            </div>
        </div>
    </section>

    @if(session('success') && !session('generated_password') && !session('reset_password'))
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center justify-center text-center gap-3 text-emerald-700 font-bold shadow-sm animate-pop-in delay-100">
            <i class="fas fa-check-circle text-xl"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- FILTER BAR (Custom UI Dropdown) --}}
    <section class="flex flex-wrap items-center justify-between gap-4 animate-pop-in delay-200 z-20 relative">
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest mr-1">
                <i class="fas fa-filter"></i> Filter:
            </span>

            {{-- Custom Dropdown: Status Akun --}}
            <div class="relative custom-dropdown" data-name="status">
                <input type="hidden" id="statusSelect" value="{{ $status }}">
                <button type="button" class="dropdown-toggle bg-white border border-slate-200 text-slate-600 text-[11px] font-black tracking-wide rounded-full px-5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer shadow-sm hover:bg-slate-50 transition-all w-40 flex justify-between items-center">
                    <span class="dropdown-label">{{ $statusText }}</span>
                    <i class="fas fa-chevron-down text-slate-400 ml-2"></i>
                </button>
                <div class="dropdown-menu hidden absolute top-full left-0 mt-2 w-full bg-white border border-slate-100 rounded-2xl shadow-xl z-50 overflow-hidden py-2 transition-all">
                    <div class="dropdown-item px-5 py-2.5 text-[11px] font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors" data-value="semua">Semua Status</div>
                    <div class="dropdown-item px-5 py-2.5 text-[11px] font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors" data-value="active">Aktif Saja</div>
                    <div class="dropdown-item px-5 py-2.5 text-[11px] font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors" data-value="inactive">Nonaktif Saja</div>
                </div>
            </div>

            {{-- Custom Dropdown: Kategori --}}
            <div class="relative custom-dropdown" data-name="kategori">
                <input type="hidden" id="kategoriSelect" value="{{ $kategori }}">
                <button type="button" class="dropdown-toggle bg-white border border-slate-200 text-slate-600 text-[11px] font-black tracking-wide rounded-full px-5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500/20 cursor-pointer shadow-sm hover:bg-slate-50 transition-all w-44 flex justify-between items-center">
                    <span class="dropdown-label">{{ $kategoriText }}</span>
                    <i class="fas fa-chevron-down text-slate-400 ml-2"></i>
                </button>
                <div class="dropdown-menu hidden absolute top-full left-0 mt-2 w-full bg-white border border-slate-100 rounded-2xl shadow-xl z-50 overflow-hidden py-2 transition-all">
                    <div class="dropdown-item px-5 py-2.5 text-[11px] font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors" data-value="semua">Semua Kategori</div>
                    <div class="dropdown-item px-5 py-2.5 text-[11px] font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors" data-value="balita">Balita</div>
                    <div class="dropdown-item px-5 py-2.5 text-[11px] font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors" data-value="remaja">Remaja</div>
                    <div class="dropdown-item px-5 py-2.5 text-[11px] font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors" data-value="lansia">Lansia</div>
                </div>
            </div>
        </div>
    </section>

    {{-- WRAPPER TABEL --}}
    <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden animate-pop-in delay-300 relative z-10">
        
        <div class="px-8 py-6 border-b border-slate-50 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex flex-col">
                <h3 class="text-lg font-black text-slate-800 font-poppins flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm shadow-inner">
                        <i class="fas fa-list"></i>
                    </div>
                    Direktori Warga
                </h3>

                <p class="text-[11px] font-bold text-slate-400 mt-1 uppercase tracking-widest ml-[52px]">
                    Total tampil:
                    <span id="totalDataCount" class="text-blue-500">{{ $users->total() ?? 0 }}</span> Data
                </p>
            </div>

            <div class="flex relative w-full sm:w-auto">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input
                    type="text"
                    id="liveSearchInput"
                    value="{{ $search }}"
                    placeholder="Ketik NIK / Nama..."
                    class="w-full sm:w-80 bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm font-medium focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm"
                >
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">
                        <th class="py-4 px-6 text-left">Informasi Warga</th>
                        <th class="py-4 px-6">NIK KTP</th>
                        <th class="py-4 px-6">Kontak / Telp</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6">Aksi</th>
                    </tr>
                </thead>

                <tbody id="tableBody" class="text-sm font-medium text-slate-600">
                    @forelse($users ?? [] as $user)
                        @php
                            $name = $getName($user);
                            $nik = $getNik($user);
                            $phone = $getPhone($user);
                            $userStatus = $user->status ?? 'inactive';
                        @endphp

                        <tr class="table-row-soft border-b border-slate-50 text-center">
                            <td class="py-4 px-6 text-left">
                                <div class="flex items-center gap-3 w-max">
                                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-black shadow-sm shrink-0 border border-blue-200/50">
                                        {{ $initial($name) }}
                                    </div>
                                    <div class="font-bold text-slate-800">{{ $name }}</div>
                                </div>
                            </td>

                            <td class="py-4 px-6">
                                <span class="font-mono text-xs font-bold bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 text-slate-500 tracking-wider">
                                    {{ $nik }}
                                </span>
                            </td>

                            <td class="py-4 px-6 text-slate-500">
                                {{ $phone }}
                            </td>

                            <td class="py-4 px-6">
                                @if($userStatus === 'active')
                                    <span class="bg-emerald-50 text-emerald-600 border border-emerald-100 px-3 py-1 rounded-full text-[10px] font-black tracking-widest uppercase">
                                        <i class="fas fa-check-circle mr-1"></i> Aktif
                                    </span>
                                @else
                                    <span class="bg-rose-50 text-rose-500 border border-rose-100 px-3 py-1 rounded-full text-[10px] font-black tracking-widest uppercase">
                                        <i class="fas fa-ban mr-1"></i> Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td class="py-4 px-6">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="action-soft w-8 h-8 rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-500 hover:text-white flex items-center justify-center transition-all" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="action-soft w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-all" title="Edit Data">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="inline dynamic-form-toggle">
                                        @csrf
                                        @method('PATCH')
                                        @if($userStatus === 'active')
                                            <button type="button" class="action-soft w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white flex items-center justify-center transition-all" data-name="{{ $name }}" data-action="nonaktifkan" title="Nonaktifkan Akun">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                        @else
                                            <button type="button" class="action-soft w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white flex items-center justify-center transition-all" data-name="{{ $name }}" data-action="aktifkan" title="Aktifkan Akun">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        @endif
                                    </form>

                                    <form action="{{ route('admin.users.reset-password', $user->id) }}" method="POST" class="inline dynamic-form-reset">
                                        @csrf
                                        <button type="button" class="action-soft w-8 h-8 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-600 hover:text-white flex items-center justify-center transition-all" data-name="{{ $name }}" title="Reset Password">
                                            <i class="fas fa-key"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline dynamic-form-delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="action-soft w-8 h-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white flex items-center justify-center transition-all" data-name="{{ $name }}" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 text-slate-300 mb-4 border border-slate-100">
                                    <i class="fas fa-users-slash text-3xl opacity-50"></i>
                                </div>
                                <h4 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">
                                    Pencarian Kosong
                                </h4>
                                <p class="text-xs font-medium text-slate-400">
                                    Sistem tidak dapat menemukan data warga pada filter ini.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION CUSTOM TERBARU --}}
        @if(isset($users) && method_exists($users, 'hasPages') && $users->hasPages())
            @php $users->withQueryString(); @endphp
            <div id="tableFooter" class="border-t border-slate-100 bg-slate-50/50 px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                    Halaman <span class="text-slate-900">{{ $users->currentPage() }}</span> dari <span class="text-slate-900">{{ $users->lastPage() }}</span>
                </p>
                
                <div class="flex items-center gap-2">
                    {{-- Previous Page --}}
                    @if ($users->onFirstPage())
                        <button type="button" disabled class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50 cursor-not-allowed opacity-60">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </button>
                    @else
                        <a href="{{ $users->previousPageUrl() }}" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition-all">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </a>
                    @endif

                    {{-- Page Numbers --}}
                    @php
                        $start = max(1, $users->currentPage() - 2);
                        $end = min($users->lastPage(), $users->currentPage() + 2);
                    @endphp
                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $users->currentPage())
                            <span class="btn-pill w-10 h-10 flex items-center justify-center bg-emerald-500 text-white font-black text-sm shadow-md pointer-events-none">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $users->url($page) }}" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 font-bold text-sm shadow-sm hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition-all">
                                {{ $page }}
                            </a>
                        @endif
                    @endfor

                    {{-- Next Page --}}
                    @if ($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}" class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition-all">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </a>
                    @else
                        <button type="button" disabled class="btn-pill w-10 h-10 flex items-center justify-center border border-slate-100 text-slate-300 bg-slate-50 cursor-not-allowed opacity-60">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<script>
(function() {
    // --- 1. SETUP EVENT DELEGATION GLOBAL ---
    const container = document.getElementById('adminMain') || document;
    
    container.addEventListener('click', function(e) {
        
        // --- HANDLER DROPDOWN FILTER ---
        if (e.target.closest('.dropdown-toggle')) {
            const toggle = e.target.closest('.dropdown-toggle');
            const menu = toggle.nextElementSibling;
            if (menu && menu.classList.contains('dropdown-menu')) {
                e.stopPropagation();
                document.querySelectorAll('.dropdown-menu').forEach(m => { 
                    if (m !== menu && !m.closest('.topbar-right')) m.classList.add('hidden'); 
                });
                menu.classList.toggle('hidden');
            }
            return;
        }

        // --- HANDLER ITEM DROPDOWN ---
        if (e.target.closest('.dropdown-item') && e.target.closest('.custom-dropdown')) {
            const item = e.target.closest('.dropdown-item');
            const dropdown = item.closest('.custom-dropdown');
            
            // Set input hidden value & text label
            dropdown.querySelector('input[type="hidden"]').value = item.dataset.value;
            dropdown.querySelector('.dropdown-label').innerText = item.innerText;
            dropdown.querySelector('.dropdown-menu').classList.add('hidden');
            
            // Trigger fetch data AJAX
            if (typeof window.fetchData === 'function') window.fetchData();
            return;
        }

        // --- HANDLER PAGINATION ---
        const pageLink = e.target.closest('#tableFooter a');
        if (pageLink) {
            e.preventDefault();
            if (typeof window.fetchData === 'function') window.fetchData(pageLink.href);
            return;
        }

        // --- HANDLER TOMBOL AKSI (AKTIFKAN/NONAKTIFKAN) ---
        const toggleBtn = e.target.closest('.dynamic-form-toggle button');
        if (toggleBtn) {
            e.preventDefault();
            const form = toggleBtn.closest('form');
            const name = toggleBtn.dataset.name || 'warga';
            const action = toggleBtn.dataset.action; 
            const isActivating = action === 'aktifkan';

            Swal.fire({
                title: isActivating ? 'Aktifkan Akun?' : 'Nonaktifkan Akun?',
                html: `Anda akan <b>${action}</b> akun milik <b>${name}</b>.`,
                icon: 'question',
                iconColor: isActivating ? '#10b981' : '#f59e0b',
                showCancelButton: true,
                confirmButtonText: isActivating ? 'Ya, Aktifkan' : 'Ya, Nonaktifkan',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                buttonsStyling: false,
                customClass: {
                    confirmButton: `mx-2 text-white font-black px-6 py-3 rounded-2xl transition-all ${isActivating ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-amber-500 hover:bg-amber-600'}`,
                    cancelButton: 'mx-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-black px-6 py-3 rounded-2xl transition-all'
                }
            }).then(result => { if (result.isConfirmed) form.submit(); });
            return;
        }

        // --- HANDLER TOMBOL AKSI (RESET PASSWORD) ---
        const resetBtn = e.target.closest('.dynamic-form-reset button');
        if (resetBtn) {
            e.preventDefault();
            const form = resetBtn.closest('form');
            const name = resetBtn.dataset.name || 'warga';
            
            Swal.fire({
                title: 'Reset Password?',
                html: `Sistem akan membuat password baru untuk <b>${name}</b>.`,
                icon: 'question',
                iconColor: '#f59e0b',
                showCancelButton: true,
                confirmButtonText: 'Ya, Reset',
                cancelButtonText: 'Batalkan',
                reverseButtons: true,
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'mx-2 bg-blue-600 hover:bg-blue-700 text-white font-black px-6 py-3 rounded-2xl transition-all shadow-md',
                    cancelButton: 'mx-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-black px-6 py-3 rounded-2xl transition-all'
                }
            }).then(result => { if (result.isConfirmed) form.submit(); });
            return;
        }

        // --- HANDLER TOMBOL AKSI (HAPUS) ---
        const deleteBtn = e.target.closest('.dynamic-form-delete button');
        if (deleteBtn) {
            e.preventDefault();
            const form = deleteBtn.closest('form');
            const name = deleteBtn.dataset.name || 'warga';
            
            Swal.fire({
                title: 'Hapus Akun Warga?',
                html: `Akun <b>${name}</b> akan dihapus secara permanen. Data rekam medis balita/lansia (jika ada) akan tetap dipertahankan.`,
                icon: 'warning',
                iconColor: '#f43f5e',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batalkan',
                reverseButtons: true,
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'mx-2 bg-rose-500 hover:bg-rose-600 text-white font-black px-6 py-3 rounded-2xl transition-all shadow-md',
                    cancelButton: 'mx-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-black px-6 py-3 rounded-2xl transition-all'
                }
            }).then(result => { if (result.isConfirmed) form.submit(); });
            return;
        }
    });

    // Menutup dropdown jika klik di area kosong layar
    document.addEventListener('click', () => {
        document.querySelectorAll('.custom-dropdown .dropdown-menu').forEach(m => m.classList.add('hidden'));
    });

    // --- 2. LIVE SEARCH BERBASIS DELEGASI ---
    let searchTimeout;
    container.addEventListener('input', function(e) {
        if (e.target.id === 'liveSearchInput') {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (typeof window.fetchData === 'function') window.fetchData();
            }, 300);
        }
    });

    // --- 3. FUNGSI FETCH DATA AJAX (Support Multi-Filter) ---
    window.fetchData = function(targetUrl = null) {
        const tableBody = document.getElementById('tableBody');
        if(!tableBody) return;
        
        tableBody.style.opacity = '0.3'; 

        let url = targetUrl ? new URL(targetUrl) : new URL(window.location.href);
        if (!targetUrl) {
            const searchVal = document.getElementById('liveSearchInput')?.value || '';
            const statusVal = document.getElementById('statusSelect')?.value || 'semua';
            const kategoriVal = document.getElementById('kategoriSelect')?.value || 'semua';
            
            url.searchParams.set('search', searchVal);
            url.searchParams.set('status', statusVal);
            url.searchParams.set('kategori', kategoriVal);
            url.searchParams.delete('page'); 
        }

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                setTimeout(() => {
                    const newBody = doc.getElementById('tableBody');
                    if(newBody) tableBody.innerHTML = newBody.innerHTML;
                    
                    const newCount = doc.getElementById('totalDataCount');
                    if(newCount) document.getElementById('totalDataCount').innerText = newCount.innerText;
                    
                    const newFooter = doc.getElementById('tableFooter');
                    const oldFooter = document.getElementById('tableFooter');
                    
                    if (newFooter && oldFooter) {
                        oldFooter.outerHTML = newFooter.outerHTML;
                    } else if (newFooter && !oldFooter) {
                        document.querySelector('.overflow-x-auto').insertAdjacentHTML('afterend', newFooter.outerHTML);
                    } else if (!newFooter && oldFooter) {
                        oldFooter.remove();
                    }

                    window.history.pushState({}, '', url);
                    tableBody.style.opacity = '1';
                }, 150);
            });
    };
})();

// --- FUNGSI COPY PASSWORD ---
window.copyPassword = function() {
    const passwordText = document.getElementById('passwordText');
    if (!passwordText) return;
    navigator.clipboard.writeText(passwordText.innerText).then(function() {
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Password Disalin', showConfirmButton: false, timer: 2000, timerProgressBar: true });
    });
}

// Trigger Alert Flash Session Bawaan Controller
@if(session('warning'))
    Swal.fire({ icon: 'warning', title: 'Perhatian', text: @json(session('warning')), confirmButtonText: 'Mengerti', buttonsStyling: false, customClass: { confirmButton: 'bg-slate-900 text-white font-black px-6 py-3 rounded-2xl' } });
@endif
@if($errors->has('system'))
    Swal.fire({ icon: 'error', title: 'Sistem Menolak Aksi', html: @json($errors->first('system')), confirmButtonText: 'Tutup', buttonsStyling: false, customClass: { confirmButton: 'bg-rose-500 hover:bg-rose-600 text-white font-black px-6 py-3 rounded-2xl transition-all shadow-md' } });
@endif
</script>
@endsection