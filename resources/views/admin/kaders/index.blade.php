@extends('layouts.admin')

@section('title', 'Manajemen Akun Kader')
@section('page-name', 'Data Kader')
@section('page-title', 'Manajemen Akun Kader')

@php
    use Illuminate\Support\Str;

    $stats = $stats ?? ['total' => 0, 'aktif' => 0, 'nonaktif' => 0];
    $search = request('search', '');
    $totalData = isset($kaders) ? (method_exists($kaders, 'total') ? $kaders->total() : $kaders->count()) : 0;

    $getName = fn($kader) => $kader->profile?->full_name ?? $kader->name ?? 'Kader';
    $getNik = fn($kader) => $kader->nik ?? $kader->profile?->nik ?? '-';
    $getPhone = fn($kader) => $kader->profile?->telepon ?? '-';
    $getJabatan = fn($kader) => $kader->kader?->jabatan ?? 'Kader Posyandu';
    $initial = fn($name) => Str::upper(Str::substr(trim((string) $name), 0, 1)) ?: 'K';
@endphp

@section('content')
<style>
    .animate-pop-in { animation: popIn .45s cubic-bezier(.16, 1, .3, 1) forwards; opacity: 0; }
    @keyframes popIn { from { opacity: 0; transform: scale(.96) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    .delay-100 { animation-delay: .1s; }
    .delay-200 { animation-delay: .2s; }
    .hero-grid { background-image: radial-gradient(rgba(255,255,255,.45) 1px, transparent 1px); background-size: 24px 24px; }
    .soft-card { background: rgba(255,255,255,.9); backdrop-filter: blur(18px); transition: all .25s ease; }
    .soft-card:hover { transform: translateY(-3px); box-shadow: 0 18px 40px -28px rgba(15,23,42,.35); }
    .row-soft { transition: background .22s ease; }
    .row-soft:hover { background: #f8fafc; }
    .action-soft { transition: all .2s ease; }
    .action-soft:hover { transform: translateY(-2px); }
</style>

<div class="max-w-6xl mx-auto space-y-8 pb-12">

    <section class="bg-gradient-to-br from-blue-600 via-sky-500 to-emerald-400 rounded-[2.5rem] p-9 md:p-10 relative overflow-hidden shadow-[0_20px_40px_-10px_rgba(59,130,246,.3)] border border-white/20 text-center animate-pop-in">
        <div class="hero-grid absolute inset-0 opacity-20 pointer-events-none"></div>
        <div class="absolute left-1/2 top-1/2 w-72 h-72 -translate-x-1/2 -translate-y-1/2 bg-white/10 blur-[90px] rounded-full"></div>

        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md border border-white/30 text-white text-[11px] font-black px-4 py-1.5 rounded-full mb-4 uppercase tracking-widest">
                <i class="fas fa-user-nurse"></i> Operator Posyandu
            </div>

            <h2 class="text-3xl md:text-4xl font-black text-white mb-3 font-poppins tracking-tight">
                Daftar Akun Kader
            </h2>

            <p class="text-blue-50 text-sm font-medium max-w-lg mx-auto mb-8 leading-relaxed">
                Kelola akun Kader untuk data sasaran, absensi, pengukuran fisik, dan laporan bulanan.
            </p>

            <a href="{{ route('admin.kaders.create') }}" class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-blue-600 font-black px-7 py-3.5 rounded-xl transition-all shadow-lg hover:-translate-y-1">
                <i class="fas fa-plus"></i> Tambah Kader Baru
            </a>
        </div>
    </section>

    @if(session('generated_password') || session('reset_password'))
        <section class="bg-blue-50 border border-blue-200 rounded-[1.5rem] p-6 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4 animate-pop-in delay-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-white text-blue-600 flex items-center justify-center text-xl border border-blue-100">
                    <i class="fas fa-key"></i>
                </div>
                <div>
                    <h4 class="text-blue-800 font-black text-sm uppercase tracking-widest">Kredensial Login Tersedia</h4>
                    <p class="text-xs font-medium text-blue-600 mt-1">Password baru berhasil dibuat.</p>
                </div>
            </div>

            <div class="bg-white border border-blue-200 rounded-xl px-5 py-3 flex items-center gap-4 shadow-sm">
                <code id="passwordText" class="text-xl font-mono font-black text-blue-600 tracking-wider">{{ session('generated_password') ?? session('reset_password') }}</code>
                <button type="button" onclick="copyPassword()" class="text-xs bg-blue-50 hover:bg-blue-500 hover:text-white text-blue-600 border border-blue-100 px-3 py-1.5 rounded-lg font-bold transition-all">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </section>
    @endif

    <section class="grid grid-cols-1 md:grid-cols-3 gap-5 animate-pop-in delay-100">
        <div class="soft-card rounded-3xl border border-slate-100 p-6 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl"><i class="fas fa-user-nurse"></i></div>
            <div>
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Kader</div>
                <div class="text-3xl font-black text-slate-700 font-poppins">{{ number_format($stats['total'] ?? 0) }}</div>
            </div>
        </div>

        <div class="soft-card rounded-3xl border border-slate-100 p-6 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-2xl"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1">Kader Aktif</div>
                <div class="text-3xl font-black text-slate-700 font-poppins">{{ number_format($stats['aktif'] ?? 0) }}</div>
            </div>
        </div>

        <div class="soft-card rounded-3xl border border-slate-100 p-6 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center text-2xl"><i class="fas fa-ban"></i></div>
            <div>
                <div class="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-1">Nonaktif</div>
                <div class="text-3xl font-black text-slate-700 font-poppins">{{ number_format($stats['nonaktif'] ?? 0) }}</div>
            </div>
        </div>
    </section>

    @if(session('success') && !session('generated_password') && !session('reset_password'))
        <section class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center justify-center text-center gap-3 text-emerald-700 font-bold shadow-sm animate-pop-in delay-100">
            <i class="fas fa-check-circle text-xl"></i> {{ session('success') }}
        </section>
    @endif

    <section class="bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden animate-pop-in delay-200">
        <div class="px-8 py-6 border-b border-slate-50 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <h3 class="text-lg font-black text-slate-800 font-poppins flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm shadow-inner"><i class="fas fa-list"></i></div>
                    Direktori Kader
                </h3>
                <p class="text-[11px] font-bold text-slate-400 mt-1 uppercase tracking-widest ml-[52px]">
                    Total tampil: <span class="text-blue-500">{{ $totalData }}</span> Data
                </p>
            </div>

            <form method="GET" action="{{ route('admin.kaders.index') }}" class="flex relative w-full sm:w-auto">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama / NIK / email..." class="w-full sm:w-80 bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-2.5 text-sm font-medium focus:bg-white focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all shadow-sm">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100 text-[11px] font-black text-slate-400 uppercase tracking-widest text-center">
                        <th class="py-4 px-6 text-left">Profil Kader</th>
                        <th class="py-4 px-6">Email</th>
                        <th class="py-4 px-6">NIK</th>
                        <th class="py-4 px-6">Jabatan</th>
                        <th class="py-4 px-6">Kontak</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6">Aksi</th>
                    </tr>
                </thead>

                <tbody class="text-sm font-medium text-slate-600">
                    @forelse($kaders ?? [] as $kader)
                        @php
                            $name = $getName($kader);
                            $status = $kader->status ?? 'inactive';
                        @endphp

                        <tr class="row-soft border-b border-slate-50 text-center">
                            <td class="py-4 px-6 text-left">
                                <div class="flex items-center gap-3 w-max">
                                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-black shadow-sm shrink-0 border border-blue-200/50">
                                        {{ $initial($name) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $name }}</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">Operator Posyandu</div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-4 px-6 text-slate-500">{{ $kader->email ?? '-' }}</td>
                            <td class="py-4 px-6"><span class="font-mono text-xs font-bold bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 text-slate-500">{{ $getNik($kader) }}</span></td>
                            <td class="py-4 px-6"><span class="text-xs font-bold bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-100 text-blue-600">{{ $getJabatan($kader) }}</span></td>
                            <td class="py-4 px-6 text-slate-500">{{ $getPhone($kader) }}</td>

                            <td class="py-4 px-6">
                                @if($status === 'active')
                                    <span class="bg-emerald-50 text-emerald-600 border border-emerald-100 px-3 py-1 rounded-full text-[10px] font-black tracking-widest uppercase"><i class="fas fa-check-circle mr-1"></i> Aktif</span>
                                @else
                                    <span class="bg-rose-50 text-rose-500 border border-rose-100 px-3 py-1 rounded-full text-[10px] font-black tracking-widest uppercase"><i class="fas fa-ban mr-1"></i> Nonaktif</span>
                                @endif
                            </td>

                            <td class="py-4 px-6">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.kaders.show', $kader->id) }}" class="action-soft w-8 h-8 rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-500 hover:text-white flex items-center justify-center"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.kaders.edit', $kader->id) }}" class="action-soft w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white flex items-center justify-center"><i class="fas fa-edit"></i></a>

                                    <form action="{{ route('admin.kaders.reset-password', $kader->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Reset password kader ini?')" class="action-soft w-8 h-8 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-600 hover:text-white flex items-center justify-center"><i class="fas fa-key"></i></button>
                                    </form>

                                    <form action="{{ route('admin.kaders.destroy', $kader->id) }}" method="POST" class="inline delete-form">
    @csrf
    @method('DELETE')
    <button type="submit"
            class="action-soft w-8 h-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white flex items-center justify-center"
            data-name="{{ $name }}"
            title="Hapus">
        <i class="fas fa-trash-alt"></i>
    </button>
</form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 text-slate-300 mb-4 border border-slate-100"><i class="fas fa-user-nurse text-3xl opacity-50"></i></div>
                                <h4 class="text-sm font-black text-slate-400 uppercase tracking-widest">Belum Ada Data Kader</h4>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if(isset($kaders) && $kaders->hasPages())
        <div class="mt-6 flex justify-center pb-8">{{ $kaders->withQueryString()->links() }}</div>
    @endif
</div>

<script>
    function copyPassword() {
        const text = document.getElementById('passwordText');
        if (!text) return;
        navigator.clipboard.writeText(text.innerText);
    }
</script>
<script>
    document.querySelectorAll('.delete-form').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            const name = this.querySelector('button')?.dataset?.name || 'kader';

            Swal.fire({
                title: 'Hapus Akun Kader?',
                html: `Akun <b>${name}</b> akan dihapus jika tidak memiliki data operasional yang masih terhubung.`,
                icon: 'warning',
                iconColor: '#f43f5e',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batalkan',
                reverseButtons: true,
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'mx-2 bg-rose-500 hover:bg-rose-600 text-white font-black px-6 py-3 rounded-2xl transition-all',
                    cancelButton: 'mx-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-black px-6 py-3 rounded-2xl transition-all'
                }
            }).then(result => {
                if (result.isConfirmed) this.submit();
            });
        });
    });
</script>
@endsection