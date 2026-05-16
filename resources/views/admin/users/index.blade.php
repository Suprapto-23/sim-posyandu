@extends('layouts.admin')
@section('title', 'Manajemen User Warga')
@section('page-name', 'Data Warga')

@section('content')
<style>
    .animate-pop-in { animation: popIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
    @keyframes popIn { 0% { opacity: 0; transform: scale(0.95) translateY(10px); } 100% { opacity: 1; transform: scale(1) translateY(0); } }
    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
    .delay-300 { animation-delay: 0.3s; }
</style>

<div class="max-w-6xl mx-auto space-y-8">

    {{-- Hero Section Premium --}}
    <div class="bg-gradient-to-br from-blue-600 to-sky-400 rounded-[2.5rem] p-10 relative overflow-hidden shadow-[0_20px_40px_-10px_rgba(59,130,246,0.3)] border border-white/20 flex flex-col items-center justify-center text-center group animate-pop-in">
        <div class="absolute inset-0 opacity-20 pointer-events-none" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-white/10 blur-[80px] rounded-full pointer-events-none transition-all duration-700 group-hover:bg-white/20"></div>

        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md border border-white/30 text-white text-[11px] font-black px-4 py-1.5 rounded-full mb-4 uppercase tracking-widest shadow-sm">
                <i class="fas fa-users"></i> Manajemen Data Warga
            </div>
            <h2 class="text-3xl md:text-4xl font-black text-white mb-3 font-poppins tracking-tight text-shadow-sm">Daftar Akun Warga</h2>
            <p class="text-blue-50 text-sm font-medium max-w-lg mx-auto mb-8 leading-relaxed">Kelola seluruh akun warga Posyandu. Gunakan NIK 16 digit yang valid sebagai kredensial utama untuk sinkronisasi rekam medis.</p>
            
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-blue-600 font-black px-7 py-3.5 rounded-xl transition-all shadow-lg hover:shadow-xl hover:-translate-y-1 smooth-route">
                <i class="fas fa-plus"></i> Daftarkan Warga Baru
            </a>
        </div>
    </div>

    {{-- Info Kredensial --}}
    @if(session('generated_password') || session('reset_password'))
    <div class="bg-blue-50 border border-blue-200 rounded-[1.5rem] p-6 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4 animate-pop-in delay-100">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl shrink-0 shadow-sm border border-blue-200">
                <i class="fas fa-key"></i>
            </div>
            <div>
                <h4 class="text-blue-800 font-black text-sm mb-1 uppercase tracking-widest">Kredensial Login Tersedia!</h4>
                <p class="text-xs font-medium text-blue-600">Berikan password ini kepada: <strong class="text-blue-800">{{ session('user_name') ?? session('reset_name') }}</strong> (NIK: {{ session('user_nik') ?? session('reset_nik') }})</p>
            </div>
        </div>
        <div class="bg-white border border-blue-200 rounded-xl px-5 py-3 flex items-center gap-4 shadow-sm">
            <code class="text-xl font-mono font-black text-blue-600 tracking-wider" id="passwordText">{{ session('generated_password') ?? session('reset_password') }}</code>
            <button onclick="copyPassword()" class="text-xs bg-blue-50 hover:bg-blue-500 hover:text-white text-blue-600 border border-blue-100 px-3 py-1.5 rounded-lg font-bold transition-all shadow-sm" title="Copy Password">
                <i class="fas fa-copy"></i>
            </button>
        </div>
    </div>
    @endif

    {{-- Mini Stats Cards --}}
    @if(isset($stats))
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 animate-pop-in delay-100">
        <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl"><i class="fas fa-users"></i></div>
            <div>
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Warga</div>
                <div class="text-3xl font-black text-slate-700 font-poppins">{{ $stats['total'] ?? 0 }}</div>
            </div>
        </div>
        <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-2xl"><i class="fas fa-user-check"></i></div>
            <div>
                <div class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1">Akun Aktif</div>
                <div class="text-3xl font-black text-slate-700 font-poppins">{{ $stats['aktif'] ?? 0 }}</div>
            </div>
        </div>
        <div class="bg-white rounded-3xl border border-slate-100 p-6 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center text-2xl"><i class="fas fa-user-lock"></i></div>
            <div>
                <div class="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-1">Nonaktif</div>
                <div class="text-3xl font-black text-slate-700 font-poppins">{{ $stats['nonaktif'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    @endif

    {{-- Alert Success --}}
    @if(session('success') && !session('generated_password') && !session('reset_password'))
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center justify-center text-center gap-3 text-emerald-700 font-bold shadow-sm animate-pop-in delay-100">
        <i class="fas fa-check-circle text-xl"></i> {{ session('success') }}
    </div>
    @endif

    {{-- ✨ FITUR BARU: FILTER PILLS (TABS KATEGORI) --}}
    <div class="flex flex-wrap items-center gap-2.5 animate-pop-in delay-200">
        <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest mr-1"><i class="fas fa-filter"></i> Filter:</span>
        
        <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-full text-xs font-black tracking-wide transition-all duration-300 {{ !request('kategori') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/30' : 'bg-white text-slate-500 hover:bg-blue-50 hover:text-blue-600 border border-slate-200' }}">
            Semua Warga
        </a>
        <a href="{{ route('admin.users.index', ['kategori' => 'balita', 'search' => request('search')]) }}" class="px-5 py-2.5 rounded-full text-xs font-black tracking-wide transition-all duration-300 flex items-center gap-2 {{ request('kategori') == 'balita' ? 'bg-sky-500 text-white shadow-md shadow-sky-500/30' : 'bg-white text-slate-500 hover:bg-sky-50 hover:text-sky-600 border border-slate-200' }}">
            <i class="fas fa-baby text-sm"></i> Orang Tua Balita
        </a>
        <a href="{{ route('admin.users.index', ['kategori' => 'bumil', 'search' => request('search')]) }}" class="px-5 py-2.5 rounded-full text-xs font-black tracking-wide transition-all duration-300 flex items-center gap-2 {{ request('kategori') == 'bumil' ? 'bg-pink-500 text-white shadow-md shadow-pink-500/30' : 'bg-white text-slate-500 hover:bg-pink-50 hover:text-pink-600 border border-slate-200' }}">
            <i class="fas fa-female text-sm"></i> Ibu Hamil
        </a>
        <a href="{{ route('admin.users.index', ['kategori' => 'remaja', 'search' => request('search')]) }}" class="px-5 py-2.5 rounded-full text-xs font-black tracking-wide transition-all duration-300 flex items-center gap-2 {{ request('kategori') == 'remaja' ? 'bg-indigo-500 text-white shadow-md shadow-indigo-500/30' : 'bg-white text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 border border-slate-200' }}">
            <i class="fas fa-user-graduate text-sm"></i> Remaja
        </a>
        <a href="{{ route('admin.users.index', ['kategori' => 'lansia', 'search' => request('search')]) }}" class="px-5 py-2.5 rounded-full text-xs font-black tracking-wide transition-all duration-300 flex items-center gap-2 {{ request('kategori') == 'lansia' ? 'bg-orange-500 text-white shadow-md shadow-orange-500/30' : 'bg-white text-slate-500 hover:bg-orange-50 hover:text-orange-600 border border-slate-200' }}">
            <i class="fas fa-wheelchair text-sm"></i> Lansia
        </a>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden animate-pop-in delay-300">
        <div class="px-8 py-6 border-b border-slate-50 flex flex-col sm:flex-row justify-between items-center gap-4">
            
            <div class="flex flex-col">
                <h3 class="text-lg font-black text-slate-800 font-poppins flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm shadow-inner"><i class="fas fa-list"></i></div>
                    Direktori Warga
                </h3>
                @if(request('kategori'))
                    <p class="text-[11px] font-bold text-slate-400 mt-1 uppercase tracking-widest ml-[52px]">Total: <span class="text-blue-500">{{ $users->total() }}</span> Data {{ ucfirst(request('kategori')) }}</p>
                @endif
            </div>
            
            <form method="GET" class="flex relative w-full sm:w-auto">
                <input type="hidden" name="kategori" value="{{ request('kategori') }}">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIK / Nama..." class="w-full sm:w-80 bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm font-medium focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm">
            </form>
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
                <tbody class="text-sm font-medium text-slate-600">
                    @forelse($users ?? [] as $u)
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors text-center">
                        
                       {{-- Kolom 1: Nama Warga --}}
<td class="py-4 px-6 text-left">
    <div class="flex items-center gap-3 w-max">
        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-black shadow-sm shrink-0 border border-blue-200/50">
            {{ strtoupper(substr($u->profile->full_name ?? $u->name, 0, 1)) }}
        </div>
        <div class="flex flex-col justify-center">
            <div class="font-bold text-slate-800 leading-tight">{{ $u->profile->full_name ?? $u->name }}</div>
            <div class="text-[10px] font-black text-blue-400 uppercase tracking-widest mt-0.5">Warga Aktif</div>
        </div>
    </div>
</td>

                        {{-- Kolom 2: NIK --}}
                        <td class="py-4 px-6">
                            <span class="font-mono text-xs font-bold bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 text-slate-500 tracking-wider">
                                {{ $u->nik ?? $u->profile?->nik ?? '-' }}
                            </span>
                        </td>

                        {{-- Kolom 3: Kontak Telepon --}}
                        <td class="py-4 px-6 text-slate-500">
                            {{ $u->profile?->telepon ?? '-' }}
                        </td>

                        {{-- Kolom 4: Status Akun --}}
                        <td class="py-4 px-6">
                            @if($u->status === 'active')
                                <span class="bg-emerald-50 text-emerald-600 border border-emerald-100 px-3 py-1 rounded-full text-[10px] font-black tracking-widest uppercase"><i class="fas fa-check-circle mr-1"></i> Aktif</span>
                            @else
                                <span class="bg-rose-50 text-rose-500 border border-rose-100 px-3 py-1 rounded-full text-[10px] font-black tracking-widest uppercase"><i class="fas fa-ban mr-1"></i> Nonaktif</span>
                            @endif
                        </td>

                        {{-- Kolom 5: Aksi --}}
                        <td class="py-4 px-6">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.users.show', $u->id) }}" class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-500 hover:text-white flex items-center justify-center transition-all smooth-route" title="Lihat Detail"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.users.edit', $u->id) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center transition-all smooth-route" title="Edit Data"><i class="fas fa-edit"></i></a>
                                
                                {{-- Tombol Reset --}}
                                <form action="{{ route('admin.users.reset-password', $u->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Reset password warga ini ke default?')" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-600 hover:text-white flex items-center justify-center transition-all" title="Reset Password"><i class="fas fa-key"></i></button>
                                </form>

                                <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Hapus data warga beserta rekam medisnya secara permanen?')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white flex items-center justify-center transition-all" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 text-slate-300 mb-4 border border-slate-100"><i class="fas fa-users-slash text-3xl opacity-50"></i></div>
                            <h4 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Pencarian Kosong</h4>
                            <p class="text-xs font-medium text-slate-400">Sistem tidak dapat menemukan data warga pada kategori ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if(isset($users) && $users->hasPages())
    <div class="mt-6 flex justify-center pb-8">
        {{ $users->withQueryString()->links() }}
    </div>
    @endif

</div>

<script>
    function copyPassword() {
        var passwordText = document.getElementById("passwordText").innerText;
        navigator.clipboard.writeText(passwordText).then(function() {
            Swal.fire({
                toast: true, position: 'top-end', icon: 'success', title: 'Password Disalin!',
                showConfirmButton: false, timer: 2000, timerProgressBar: true
            });
        }, function(err) {
            console.error('Gagal menyalin text: ', err);
        });
    }
</script>
@endsection