@extends('layouts.user')

@section('title', 'Pesan Bidan')
@section('page_title', 'Pesan Bidan')

@php
    use Illuminate\Support\Facades\Route;

    $filters = $filters ?? [
        'filter' => $filter ?? 'semua',
        'search' => '',
    ];

    $counts = $counts ?? [
        'semua' => $allCount ?? 0,
        'belum' => $unreadCount ?? 0,
        'sudah' => $readCount ?? 0,
    ];

    $notifikasiCards = collect($notifikasiCards ?? []);

    $indexRoute = Route::has('user.notifikasi.index')
        ? route('user.notifikasi.index')
        : url('/user/notifikasi');

    $markAllRoute = Route::has('user.notifikasi.markall')
        ? route('user.notifikasi.markall')
        : '#';

    $readRoute = function ($id) {
        return Route::has('user.notifikasi.read')
            ? route('user.notifikasi.read', $id)
            : '#';
    };

    $tabs = [
        [
            'key' => 'semua',
            'label' => 'Semua Pesan',
            'count' => $counts['semua'] ?? 0,
            'tone' => 'emerald',
            'icon' => 'fa-inbox',
        ],
        [
            'key' => 'belum',
            'label' => 'Belum Dibaca',
            'count' => $counts['belum'] ?? 0,
            'tone' => 'rose',
            'icon' => 'fa-envelope',
        ],
        [
            'key' => 'sudah',
            'label' => 'Sudah Dibaca',
            'count' => $counts['sudah'] ?? 0,
            'tone' => 'sky',
            'icon' => 'fa-envelope-open',
        ],
    ];

    $toneMap = [
        'emerald' => [
            'icon' => 'border-emerald-100 bg-emerald-50 text-emerald-600',
            'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'line' => 'bg-emerald-400',
            'button' => 'text-emerald-700 hover:bg-emerald-50',
            'active' => 'border-emerald-500 bg-emerald-600 text-white shadow-[0_12px_24px_rgba(16,185,129,.22)]',
            'idle' => 'border-emerald-100 bg-white/78 text-emerald-700 hover:bg-emerald-50',
        ],
        'rose' => [
            'icon' => 'border-rose-100 bg-rose-50 text-rose-500',
            'badge' => 'border-rose-200 bg-rose-50 text-rose-700',
            'line' => 'bg-rose-400',
            'button' => 'text-rose-700 hover:bg-rose-50',
            'active' => 'border-rose-500 bg-rose-500 text-white shadow-[0_12px_24px_rgba(244,63,94,.18)]',
            'idle' => 'border-rose-100 bg-white/78 text-rose-700 hover:bg-rose-50',
        ],
        'sky' => [
            'icon' => 'border-sky-100 bg-sky-50 text-sky-500',
            'badge' => 'border-sky-200 bg-sky-50 text-sky-700',
            'line' => 'bg-sky-400',
            'button' => 'text-sky-700 hover:bg-sky-50',
            'active' => 'border-sky-500 bg-sky-500 text-white shadow-[0_12px_24px_rgba(14,165,233,.18)]',
            'idle' => 'border-sky-100 bg-white/78 text-sky-700 hover:bg-sky-50',
        ],
        'amber' => [
            'icon' => 'border-amber-100 bg-amber-50 text-amber-500',
            'badge' => 'border-amber-200 bg-amber-50 text-amber-700',
            'line' => 'bg-amber-400',
            'button' => 'text-amber-700 hover:bg-amber-50',
            'active' => 'border-amber-500 bg-amber-500 text-white shadow-[0_12px_24px_rgba(245,158,11,.18)]',
            'idle' => 'border-amber-100 bg-white/78 text-amber-700 hover:bg-amber-50',
        ],
        'violet' => [
            'icon' => 'border-violet-100 bg-violet-50 text-violet-500',
            'badge' => 'border-violet-200 bg-violet-50 text-violet-700',
            'line' => 'bg-violet-400',
            'button' => 'text-violet-700 hover:bg-violet-50',
            'active' => 'border-violet-500 bg-violet-500 text-white shadow-[0_12px_24px_rgba(139,92,246,.18)]',
            'idle' => 'border-violet-100 bg-white/78 text-violet-700 hover:bg-violet-50',
        ],
        'slate' => [
            'icon' => 'border-slate-100 bg-slate-50 text-slate-500',
            'badge' => 'border-slate-200 bg-slate-50 text-slate-600',
            'line' => 'bg-slate-300',
            'button' => 'text-slate-700 hover:bg-slate-50',
            'active' => 'border-slate-500 bg-slate-600 text-white shadow-[0_12px_24px_rgba(100,116,139,.18)]',
            'idle' => 'border-slate-100 bg-white/78 text-slate-700 hover:bg-slate-50',
        ],
    ];
@endphp

@push('styles')
<style>
    .nt-page {
        background:
            radial-gradient(circle at 8% 8%, rgba(16,185,129,.14), transparent 28%),
            radial-gradient(circle at 92% 12%, rgba(14,165,233,.11), transparent 26%),
            radial-gradient(circle at 76% 88%, rgba(245,158,11,.11), transparent 28%),
            linear-gradient(135deg,#f8fafc 0%,#ecfdf5 42%,#eff6ff 100%);
    }

    .nt-enter {
        opacity: 0;
        animation: ntEnter .36s cubic-bezier(.16,1,.3,1) forwards;
    }

    .nt-enter-2 {
        opacity: 0;
        animation: ntEnter .36s cubic-bezier(.16,1,.3,1) .07s forwards;
    }

    @keyframes ntEnter {
        from {
            opacity: 0;
            transform: translateY(12px) scale(.99);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .nt-glass {
        border: 1px solid rgba(255,255,255,.76);
        background: rgba(255,255,255,.70);
        backdrop-filter: blur(20px);
        box-shadow: 0 16px 46px rgba(15,23,42,.055);
    }

    .nt-card {
        border: 1px solid rgba(226,232,240,.78);
        background: rgba(255,255,255,.74);
        backdrop-filter: blur(16px);
        box-shadow: 0 10px 28px rgba(15,23,42,.04);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .nt-card:hover {
        transform: translateY(-2px);
        border-color: rgba(16,185,129,.28);
        box-shadow: 0 16px 38px rgba(15,23,42,.065);
    }

    @media (prefers-reduced-motion: reduce) {
        .nt-enter,
        .nt-enter-2 {
            animation: none;
            opacity: 1;
        }

        .nt-card,
        .nt-card:hover {
            transition: none;
            transform: none;
        }
    }
</style>
@endpush

@section('content')
<div class="nt-page -mx-4 -my-4 min-h-[calc(100vh-96px)] px-4 py-5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-5">

        <section class="nt-enter nt-glass relative overflow-hidden rounded-[30px] p-5 sm:p-6">
            <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-emerald-300/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 left-8 h-56 w-56 rounded-full bg-sky-300/14 blur-3xl"></div>

            <div class="relative grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_420px] xl:items-center">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50/90 px-3.5 py-2 text-[10px] font-black uppercase tracking-[0.18em] text-emerald-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Kotak Masuk Warga
                    </span>

                    <h1 class="mt-4 text-3xl font-black tracking-tight text-slate-800 sm:text-4xl">
                        Pesan Bidan
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm font-semibold leading-7 text-slate-600">
                        Pusat informasi jadwal, pemeriksaan, imunisasi, dan pemberitahuan Posyandu untuk warga.
                    </p>

                    <div class="mt-5 grid grid-cols-3 gap-3">
                        <div class="rounded-[20px] border border-emerald-100 bg-white/70 px-3 py-3">
                            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-400">Semua</p>
                            <p class="mt-1 text-2xl font-black text-slate-800">{{ $counts['semua'] ?? 0 }}</p>
                        </div>

                        <div class="rounded-[20px] border border-rose-100 bg-white/70 px-3 py-3">
                            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-400">Baru</p>
                            <p class="mt-1 text-2xl font-black text-rose-600">{{ $counts['belum'] ?? 0 }}</p>
                        </div>

                        <div class="rounded-[20px] border border-sky-100 bg-white/70 px-3 py-3">
                            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-400">Dibaca</p>
                            <p class="mt-1 text-2xl font-black text-sky-600">{{ $counts['sudah'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <form action="{{ $indexRoute }}" method="GET" class="rounded-[26px] border border-white/80 bg-gradient-to-br from-emerald-50/95 via-white/82 to-sky-50/90 p-5 shadow-[0_14px_40px_rgba(15,23,42,0.055)] backdrop-blur-xl">
                    <label for="search" class="block text-[10px] font-black uppercase tracking-[0.18em] text-emerald-700">
                        Cari Pesan
                    </label>

                    <input type="hidden" name="filter" value="{{ $filters['filter'] ?? 'semua' }}">

                    <input type="text"
                           id="search"
                           name="search"
                           value="{{ $filters['search'] ?? '' }}"
                           autocomplete="off"
                           placeholder="Cari judul atau isi pesan..."
                           class="mt-3 h-12 w-full rounded-2xl border border-emerald-100 bg-white/78 px-4 text-sm font-bold text-slate-700 outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">

                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <button type="submit"
                                class="h-11 rounded-2xl bg-emerald-600 px-4 text-xs font-black uppercase tracking-[0.12em] text-white shadow-[0_12px_26px_rgba(16,185,129,.22)] transition hover:bg-emerald-700">
                            Terapkan
                        </button>

                        <a href="{{ $indexRoute }}"
                           class="inline-flex h-11 items-center justify-center rounded-2xl border border-emerald-100 bg-white/82 px-4 text-xs font-black uppercase tracking-[0.12em] text-emerald-700 transition hover:bg-emerald-50">
                            Reset
                        </a>
                    </div>

                    @if(($counts['belum'] ?? 0) > 0)
                        <form></form>
                    @endif
                </form>
            </div>
        </section>

        @if(session('success') || session('error'))
            <section class="nt-enter-2 rounded-[24px] border p-4 shadow-sm backdrop-blur-xl {{ session('error') ? 'border-rose-200 bg-rose-50/85 text-rose-800' : 'border-emerald-200 bg-emerald-50/85 text-emerald-800' }}">
                <p class="text-sm font-black">
                    {{ session('error') ?: session('success') }}
                </p>
            </section>
        @endif

        <section class="nt-enter-2 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap gap-3">
                @foreach($tabs as $tab)
                    @php
                        $isActive = ($filters['filter'] ?? 'semua') === $tab['key'];
                        $tone = $toneMap[$tab['tone']] ?? $toneMap['emerald'];

                        $query = array_filter([
                            'filter' => $tab['key'],
                            'search' => filled($filters['search'] ?? null) ? $filters['search'] : null,
                        ]);

                        $tabUrl = $indexRoute . '?' . http_build_query($query);
                    @endphp

                    <a href="{{ $tabUrl }}"
                       class="smooth-route inline-flex h-11 items-center gap-2 rounded-2xl border px-4 text-xs font-black uppercase tracking-[0.12em] transition {{ $isActive ? $tone['active'] : $tone['idle'] }}">
                        <i class="fas {{ $tab['icon'] }}"></i>
                        <span>{{ $tab['label'] }}</span>
                        <span class="rounded-full bg-white/30 px-2 py-0.5 text-[10px]">
                            {{ $tab['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            @if(($counts['belum'] ?? 0) > 0)
                <form action="{{ $markAllRoute }}" method="POST">
                    @csrf

                    <button type="submit"
                            class="inline-flex h-11 items-center justify-center rounded-2xl bg-emerald-600 px-5 text-xs font-black uppercase tracking-[0.12em] text-white shadow-[0_12px_26px_rgba(16,185,129,.22)] transition hover:bg-emerald-700">
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </section>

        <section class="nt-enter-2 space-y-4">
            @forelse($notifikasiCards as $card)
                @php
                    $tone = $toneMap[$card['tone']] ?? $toneMap['slate'];
                @endphp

                <article class="nt-card group relative overflow-hidden rounded-[28px] p-4 sm:p-5 {{ !$card['is_read'] ? 'ring-1 ring-emerald-200/80' : '' }}">
                    <div class="absolute left-0 top-0 h-full w-1.5 {{ !$card['is_read'] ? $tone['line'] : 'bg-slate-200' }}"></div>

                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_180px] xl:items-center">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[20px] border text-lg {{ $tone['icon'] }}">
                                <i class="fas {{ $card['icon'] }}"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[0.10em] {{ $tone['badge'] }}">
                                        {{ $card['label'] }}
                                    </span>

                                    <span class="rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[0.10em] {{ $card['is_read'] ? 'border-slate-200 bg-slate-50 text-slate-600' : 'border-rose-200 bg-rose-50 text-rose-700' }}">
                                        {{ $card['is_read'] ? 'Sudah Dibaca' : 'Baru' }}
                                    </span>
                                </div>

                                <h2 class="mt-3 line-clamp-1 text-lg font-black text-slate-800">
                                    {{ $card['judul'] }}
                                </h2>

                                <p class="mt-2 text-sm font-semibold leading-6 text-slate-600">
                                    {{ $card['pesan'] }}
                                </p>

                                <p class="mt-3 text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $card['tanggal'] }} • {{ $card['waktu'] }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3">
                            @if(!$card['is_read'])
                                <form action="{{ $readRoute($card['id']) }}" method="POST">
                                    @csrf

                                    <button type="submit"
                                            class="inline-flex h-10 w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 text-xs font-black uppercase tracking-[0.12em] text-white shadow-[0_12px_26px_rgba(16,185,129,.20)] transition hover:bg-emerald-700">
                                        Mengerti
                                    </button>
                                </form>
                            @endif

                            @if(($card['link'] ?? '#') !== '#')
                                <a href="{{ $card['link'] }}"
                                   class="smooth-route inline-flex h-10 w-full items-center justify-center rounded-2xl border border-slate-100 bg-white/82 px-4 text-xs font-black uppercase tracking-[0.12em] transition {{ $tone['button'] }}">
                                    Buka
                                </a>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-[28px] border border-dashed border-emerald-300 bg-emerald-50/65 p-10 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[24px] bg-white text-2xl text-emerald-500 shadow-sm">
                        <i class="fas fa-check-double"></i>
                    </div>

                    <h3 class="mt-5 text-xl font-black text-slate-800">
                        Kotak Masuk Bersih
                    </h3>

                    <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-600">
                        Belum ada pesan Bidan atau pemberitahuan Posyandu pada filter ini.
                    </p>
                </div>
            @endforelse
        </section>

        @if(method_exists($notifikasis, 'links') && $notifikasis->hasPages())
            <section class="nt-enter-2 rounded-[24px] border border-white/75 bg-white/70 px-4 py-4 shadow-sm backdrop-blur-xl">
                {{ $notifikasis->links() }}
            </section>
        @endif
    </div>
</div>
@endsection