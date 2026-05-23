@extends('layouts.kader')

@section('title', 'Data Balita')
@section('page-name', 'Data Balita')

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;

    Carbon::setLocale('id');

    $routeHas = fn ($name) => Route::has($name);

    $search = $search ?? request('search', '');
    $statusAkun = $statusAkun ?? request('status_akun', 'semua');

    $statusOptions = [
        'semua' => 'Semua Status',
        'terhubung' => 'Terhubung Akun',
        'belum' => 'Belum Terhubung',
    ];

    $genderLabel = fn ($jk) => $jk === 'L' ? 'Laki-laki' : ($jk === 'P' ? 'Perempuan' : '-');
@endphp

@push('styles')
<style>
    .balita-page {
        font-family: "Plus Jakarta Sans", Inter, system-ui, sans-serif;
        position: relative;
        isolation: isolate;
    }

    .balita-page::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background:
            radial-gradient(circle at 8% 8%, rgba(16,185,129,.13), transparent 28%),
            radial-gradient(circle at 92% 12%, rgba(245,158,11,.10), transparent 26%),
            radial-gradient(circle at 50% 100%, rgba(14,165,233,.08), transparent 32%),
            linear-gradient(135deg, #f8fffc 0%, #f8fafc 58%, #fffaf0 100%);
    }

    .glass-panel {
        border: 1px solid rgba(255,255,255,.78);
        background: rgba(255,255,255,.64);
        backdrop-filter: blur(18px);
        box-shadow: 0 18px 42px rgba(15,23,42,.06);
    }

    .hero-panel {
        border: 1px solid rgba(167,243,208,.72);
        background:
            radial-gradient(circle at 12% 18%, rgba(16,185,129,.16), transparent 32%),
            radial-gradient(circle at 88% 16%, rgba(245,158,11,.13), transparent 32%),
            linear-gradient(135deg, rgba(255,255,255,.72), rgba(236,253,245,.70));
        backdrop-filter: blur(18px);
        box-shadow: 0 18px 42px rgba(15,23,42,.06);
    }

    .input-premium {
        border: 1px solid rgba(226,232,240,.9);
        background: rgba(255,255,255,.72);
        outline: none;
        transition: all .3s ease-in-out;
    }

    .input-premium:focus {
        border-color: rgba(16,185,129,.42);
        box-shadow: 0 0 0 4px rgba(16,185,129,.08);
        background: rgba(255,255,255,.86);
    }

    .card-hover {
        transition: all .3s ease-in-out;
    }

    .card-hover:hover {
        transform: translateY(-2px);
        border-color: rgba(16,185,129,.24);
        box-shadow: 0 20px 46px rgba(15,23,42,.075);
    }

    .scroll-soft {
        max-height: 660px;
        overflow: auto;
        overscroll-behavior: contain;
    }

    .scroll-soft::-webkit-scrollbar {
        width: 7px;
        height: 7px;
    }

    .scroll-soft::-webkit-scrollbar-track {
        background: rgba(241,245,249,.8);
        border-radius: 999px;
    }

    .scroll-soft::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #10b981, #f59e0b);
        border-radius: 999px;
    }
</style>
@endpush

@section('content')
<div class="balita-page space-y-5">

    {{-- HERO --}}
    <section class="hero-panel rounded-[30px] p-5 sm:p-6">
        <div class="grid gap-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/80 px-4 py-2 text-[10px] font-black uppercase tracking-[.14em] text-emerald-700">
                    <i class="fa-solid fa-child-reaching"></i>
                    Database Balita
                </div>

                <h1 class="text-2xl font-black tracking-[-.04em] text-slate-900 sm:text-3xl">
                    Data Balita
                </h1>

                <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-slate-500">
                    Kelola data sasaran Balita untuk kebutuhan absensi, pengukuran fisik, imunisasi, rekam kesehatan, dan laporan Posyandu.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @if($routeHas('kader.import.create'))
                    <a href="{{ route('kader.import.create', ['type' => 'balita']) }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border border-amber-100 bg-amber-50/80 px-5 py-3 text-sm font-black text-amber-700 backdrop-blur-md transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-amber-100/80">
                        <i class="fa-solid fa-file-import"></i>
                        Import
                    </a>
                @endif

                @if($routeHas('kader.data.balita.create'))
                    <a href="{{ route('kader.data.balita.create') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-[0_14px_28px_rgba(5,150,105,.18)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                        <i class="fa-solid fa-plus"></i>
                        Tambah Balita
                    </a>
                @endif
            </div>
        </div>
    </section>

    {{-- STATS --}}
    <section class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <div class="glass-panel card-hover rounded-[26px] p-5">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50/90 text-emerald-700">
                <i class="fa-solid fa-users"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Total Balita</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $statTotal ?? 0 }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Seluruh data sasaran</p>
        </div>

        <div class="glass-panel card-hover rounded-[26px] p-5">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50/90 text-emerald-700">
                <i class="fa-solid fa-link"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Terhubung Akun</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $statTerhubung ?? 0 }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Akun warga tersedia</p>
        </div>

        <div class="glass-panel card-hover rounded-[26px] p-5">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-amber-50/90 text-amber-700">
                <i class="fa-solid fa-unlink"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Belum Terhubung</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $statBelumTerhubung ?? 0 }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Perlu sinkron akun</p>
        </div>

        <div class="glass-panel card-hover rounded-[26px] p-5">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-sky-50/90 text-sky-700">
                <i class="fa-solid fa-calendar-plus"></i>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[.13em] text-slate-400">Bulan Ini</p>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $statBulanIni ?? 0 }}</h2>
            <p class="mt-1 text-xs font-bold text-slate-400">Data baru tercatat</p>
        </div>
    </section>

    {{-- FILTER --}}
    <section class="glass-panel rounded-[30px] p-4 sm:p-5">
        <form method="GET" action="{{ route('kader.data.balita.index') }}" class="grid grid-cols-1 gap-3 xl:grid-cols-[1fr_220px_auto]">
            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Cari Balita</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-xs text-slate-300"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        class="input-premium h-12 w-full rounded-2xl pl-10 pr-4 text-sm font-bold text-slate-700"
                        placeholder="Cari nama, NIK, nama orang tua, atau alamat..."
                    >
                </div>
            </div>

            <div>
                <label class="mb-2 block text-xs font-black uppercase tracking-[.12em] text-slate-400">Status Akun</label>
                <select name="status_akun" class="input-premium h-12 w-full rounded-2xl px-4 text-sm font-bold text-slate-700">
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" {{ $statusAkun === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="h-12 rounded-2xl bg-emerald-600 px-5 text-sm font-black text-white shadow-[0_12px_24px_rgba(5,150,105,.18)] transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                    <i class="fa-solid fa-filter mr-1"></i>
                    Filter
                </button>

                <a href="{{ route('kader.data.balita.index') }}"
                   class="grid h-12 w-12 place-items-center rounded-2xl border border-white/70 bg-white/60 text-slate-500 backdrop-blur-md transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-50 hover:text-emerald-700">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </section>

    {{-- LIST --}}
    <section class="glass-panel rounded-[30px] p-4 sm:p-5">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-black text-slate-900">Daftar Balita</h2>
                <p class="mt-1 text-xs font-bold text-slate-400">
                    Menampilkan data Balita berdasarkan filter aktif.
                </p>
            </div>

            <span class="w-fit rounded-full border border-emerald-100 bg-emerald-50/80 px-3 py-1 text-[10px] font-black uppercase tracking-[.12em] text-emerald-700">
                {{ $items->total() ?? 0 }} Data
            </span>
        </div>

        @if(isset($items) && $items->count())
            <form method="POST" action="{{ route('kader.data.balita.bulk-delete') }}" id="bulkDeleteForm">
                @csrf
                @method('DELETE')

                <div class="mb-4 hidden rounded-[24px] border border-rose-100 bg-rose-50/80 p-4" id="bulkActionBar">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-black text-rose-700">Mode hapus massal aktif</p>
                            <p class="mt-1 text-xs font-bold text-rose-500">
                                <span id="selectedCount">0</span> data dipilih.
                            </p>
                        </div>

                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-600 px-5 py-3 text-sm font-black text-white transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-rose-700"
                                onclick="return confirm('Hapus data Balita yang dipilih? Data yang sudah punya riwayat pelayanan tidak dapat dihapus.');">
                            <i class="fa-solid fa-trash"></i>
                            Hapus Terpilih
                        </button>
                    </div>
                </div>

                <div class="scroll-soft">
                    <div class="space-y-3">
                        @foreach($items as $item)
                            @php
                                $tanggalLahir = $item->tanggal_lahir ? Carbon::parse($item->tanggal_lahir) : null;
                                $usiaText = '-';

                                if ($tanggalLahir) {
                                    $diff = $tanggalLahir->diff(now('Asia/Jakarta'));
                                    $usiaText = $diff->y > 0
                                        ? $diff->y . ' tahun ' . $diff->m . ' bulan'
                                        : $diff->m . ' bulan ' . $diff->d . ' hari';
                                }

                                $jkClass = $item->jenis_kelamin === 'L'
                                    ? 'border-sky-100 bg-sky-50/80 text-sky-700'
                                    : 'border-pink-100 bg-pink-50/80 text-pink-700';

                                $akunTerhubung = filled($item->user_id);
                                $pemeriksaan = $item->pemeriksaan_terakhir;
                            @endphp

                            <article class="card-hover rounded-[26px] border border-white/70 bg-white/56 p-4 backdrop-blur-md">
                                <div class="grid gap-4 xl:grid-cols-[36px_1.2fr_1fr_1fr_1fr_auto] xl:items-center">

                                    <div class="flex items-center">
                                        <input
                                            type="checkbox"
                                            name="ids[]"
                                            value="{{ $item->id }}"
                                            class="bulk-checkbox h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                        >
                                    </div>

                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-emerald-50/90 text-emerald-700">
                                            <span class="text-sm font-black">
                                                {{ strtoupper(substr($item->nama_lengkap ?? 'B', 0, 1)) }}
                                            </span>
                                        </div>

                                        <div class="min-w-0">
                                            <div class="mb-1 flex flex-wrap items-center gap-2">
                                                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50/80 px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] text-emerald-700">
                                                    <i class="fa-solid fa-child-reaching"></i>
                                                    Balita
                                                </span>

                                                <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[.08em] {{ $jkClass }}">
                                                    <i class="fa-solid {{ $item->jenis_kelamin === 'L' ? 'fa-mars' : 'fa-venus' }}"></i>
                                                    {{ $genderLabel($item->jenis_kelamin) }}
                                                </span>
                                            </div>

                                            <h3 class="truncate text-base font-black text-slate-900">
                                                {{ $item->nama_lengkap }}
                                            </h3>

                                            <p class="mt-1 text-xs font-bold text-slate-400">
                                                NIK {{ $item->nik ?? '-' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Lahir</p>
                                        <p class="mt-1 text-sm font-black text-slate-900">
                                            {{ $item->tempat_lahir ?? '-' }}
                                        </p>
                                        <p class="mt-1 text-xs font-bold text-slate-400">
                                            {{ $tanggalLahir ? $tanggalLahir->translatedFormat('d F Y') : '-' }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Orang Tua</p>
                                        <p class="mt-1 text-sm font-black text-slate-900">
                                            {{ $item->nama_ibu ?? '-' }}
                                        </p>
                                        <p class="mt-1 text-xs font-bold text-slate-400">
                                            {{ $item->nama_ayah ? 'Ayah: ' . $item->nama_ayah : 'Ayah belum diisi' }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[.12em] text-slate-400">Ringkasan</p>
                                        <p class="mt-1 text-sm font-black text-slate-900">
                                            {{ $usiaText }}
                                        </p>
                                        <p class="mt-1 text-xs font-bold text-slate-400">
                                            BB lahir {{ $item->berat_lahir ?? '-' }} kg, PB {{ $item->panjang_lahir ?? '-' }} cm
                                        </p>

                                        @if($pemeriksaan)
                                            <p class="mt-1 text-xs font-bold text-emerald-600">
                                                Pemeriksaan terakhir tersedia
                                            </p>
                                        @else
                                            <p class="mt-1 text-xs font-bold text-slate-400">
                                                Belum ada pemeriksaan
                                            </p>
                                        @endif
                                    </div>

                                    <div class="flex flex-wrap justify-start gap-2 xl:justify-end">
                                        @if($akunTerhubung)
                                            <span class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-100 bg-emerald-50/80 px-4 py-2 text-xs font-black text-emerald-700">
                                                <i class="fa-solid fa-link"></i>
                                                Akun
                                            </span>
                                        @else
                                            @if($routeHas('kader.data.balita.sync'))
                                                <form method="POST" action="{{ route('kader.data.balita.sync', $item->id) }}">
                                                    @csrf
                                                    <button type="submit"
                                                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-amber-100 bg-amber-50/80 px-4 py-2 text-xs font-black text-amber-700 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-amber-100/80">
                                                        <i class="fa-solid fa-rotate"></i>
                                                        Sinkron Akun
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        @if($routeHas('kader.data.balita.show'))
                                            <a href="{{ route('kader.data.balita.show', $item->id) }}"
                                               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2 text-xs font-black text-white transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                                                <i class="fa-solid fa-eye"></i>
                                                Detail
                                            </a>
                                        @endif

                                        @if($routeHas('kader.data.balita.edit'))
                                            <a href="{{ route('kader.data.balita.edit', $item->id) }}"
                                               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-2 text-xs font-black text-slate-600 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-slate-100">
                                                <i class="fa-solid fa-pen"></i>
                                                Edit
                                            </a>
                                        @endif

                                        @if($routeHas('kader.data.balita.destroy'))
                                            <form method="POST"
                                                  action="{{ route('kader.data.balita.destroy', $item->id) }}"
                                                  onsubmit="return confirm('Hapus data Balita ini? Data yang sudah memiliki riwayat pelayanan tidak dapat dihapus.');">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-rose-100 bg-rose-50/80 px-4 py-2 text-xs font-black text-rose-700 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-rose-100/80">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </form>

            @if($items->hasPages())
                <div class="mt-5">
                    {{ $items->links() }}
                </div>
            @endif
        @else
            <div class="rounded-[28px] border border-dashed border-slate-200 bg-slate-50/70 p-10 text-center">
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-white/60 text-slate-400 backdrop-blur-md">
                    <i class="fa-solid fa-child-reaching text-xl"></i>
                </div>

                <h3 class="mt-4 text-lg font-black text-slate-900">Data Balita Kosong</h3>
                <p class="mx-auto mt-2 max-w-md text-sm font-bold leading-6 text-slate-400">
                    Belum ada data Balita yang cocok dengan filter saat ini. Tambahkan manual atau gunakan import Excel.
                </p>

                <div class="mt-5 flex flex-col justify-center gap-3 sm:flex-row">
                    @if($routeHas('kader.data.balita.create'))
                        <a href="{{ route('kader.data.balita.create') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-black text-white transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-emerald-700">
                            <i class="fa-solid fa-plus"></i>
                            Tambah Balita
                        </a>
                    @endif

                    @if($routeHas('kader.import.create'))
                        <a href="{{ route('kader.import.create', ['type' => 'balita']) }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl border border-amber-100 bg-amber-50/80 px-5 py-3 text-sm font-black text-amber-700 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:bg-amber-100/80">
                            <i class="fa-solid fa-file-import"></i>
                            Import Excel
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = document.querySelectorAll('.bulk-checkbox');
    const bar = document.getElementById('bulkActionBar');
    const count = document.getElementById('selectedCount');

    const updateBulkBar = () => {
        const selected = document.querySelectorAll('.bulk-checkbox:checked').length;

        if (!bar || !count) {
            return;
        }

        count.textContent = selected;
        bar.classList.toggle('hidden', selected === 0);
    };

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkBar);
    });

    updateBulkBar();
});
</script>
@endpush