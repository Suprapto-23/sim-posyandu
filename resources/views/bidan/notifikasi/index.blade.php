@extends('layouts.bidan')

@section('title', 'Notifikasi')
@section('page-name', 'Notifikasi')
@section('page-title', 'Kotak Notifikasi Bidan')

@section('content')
<div class="space-y-7 pb-10">
    <div class="rounded-[2rem] bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-600 p-6 md:p-8 text-white shadow-xl shadow-emerald-900/20 relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-44 h-44 rounded-full bg-white/10 blur-2xl"></div>
        <div class="absolute -left-10 bottom-0 w-36 h-36 rounded-full bg-cyan-200/20 blur-2xl"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-5">
            <div>
                <p class="text-sm font-bold text-emerald-50/80 mb-2">Pusat Informasi Bidan</p>
                <h1 class="text-2xl md:text-3xl font-black tracking-tight">Notifikasi Sistem</h1>
                <p class="mt-2 text-sm md:text-base text-emerald-50/85 max-w-2xl">
                    Pantau informasi jadwal, pemeriksaan, imunisasi, dan aktivitas Posyandu yang berkaitan dengan akun Bidan.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="rounded-2xl bg-white/15 border border-white/20 px-4 py-3 backdrop-blur-xl">
                    <p class="text-[11px] uppercase tracking-[0.18em] text-emerald-50/70 font-black">Belum Dibaca</p>
                    <p class="text-2xl font-black">{{ $unreadCount ?? 0 }}</p>
                </div>

                @if (($unreadCount ?? 0) > 0)
                    <form method="POST" action="{{ route('bidan.notifikasi.markall') }}">
                        @csrf
                        <button
                            type="submit"
                            class="px-4 py-3 rounded-2xl bg-white text-emerald-700 text-sm font-black hover:bg-emerald-50 transition-all duration-300 shadow-lg"
                        >
                            Tandai Dibaca
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-800 font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-rose-800 font-semibold">
            {{ session('error') }}
        </div>
    @endif

    @php
        $activeFilter = $filter ?? request('filter', 'semua');

        $filters = [
            'semua' => 'Semua',
            'belum_dibaca' => 'Belum Dibaca',
            'sudah' => 'Sudah Dibaca',
        ];
    @endphp

    <div class="flex flex-wrap items-center gap-3">
        @foreach ($filters as $key => $label)
            <a
                href="{{ route('bidan.notifikasi.index', ['filter' => $key]) }}"
                class="px-5 py-3 rounded-2xl text-sm font-black border transition-all duration-300
                    {{ $activeFilter === $key
                        ? 'bg-emerald-600 text-white border-emerald-600 shadow-lg shadow-emerald-900/15'
                        : 'bg-white/80 text-slate-600 border-slate-200 hover:border-emerald-300 hover:text-emerald-700'
                    }}"
            >
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="rounded-[2rem] bg-white/85 border border-slate-200/70 shadow-xl shadow-slate-200/70 overflow-hidden backdrop-blur-xl">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-black text-slate-800">Daftar Notifikasi</h2>
                <p class="text-sm text-slate-500">Informasi terbaru untuk akun Bidan.</p>
            </div>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse ($notifikasis as $notif)
                @php
                    $isUnread = !($notif->is_read ?? false);
                    $tipe = $notif->tipe ?? 'info';

                    $theme = match ($tipe) {
                        'jadwal' => [
                            'icon' => 'fa-calendar-check',
                            'bg' => 'bg-indigo-50',
                            'text' => 'text-indigo-600',
                            'border' => 'border-indigo-100',
                            'label' => 'Jadwal',
                        ],
                        'imunisasi' => [
                            'icon' => 'fa-syringe',
                            'bg' => 'bg-emerald-50',
                            'text' => 'text-emerald-600',
                            'border' => 'border-emerald-100',
                            'label' => 'Imunisasi',
                        ],
                        'pemeriksaan' => [
                            'icon' => 'fa-stethoscope',
                            'bg' => 'bg-sky-50',
                            'text' => 'text-sky-600',
                            'border' => 'border-sky-100',
                            'label' => 'Pemeriksaan',
                        ],
                        'import' => [
                            'icon' => 'fa-file-excel',
                            'bg' => 'bg-amber-50',
                            'text' => 'text-amber-600',
                            'border' => 'border-amber-100',
                            'label' => 'Import',
                        ],
                        default => [
                            'icon' => 'fa-bell',
                            'bg' => 'bg-slate-50',
                            'text' => 'text-slate-600',
                            'border' => 'border-slate-100',
                            'label' => 'Informasi',
                        ],
                    };
                @endphp

                <div class="p-5 md:p-6 {{ $isUnread ? 'bg-emerald-50/40' : 'bg-white' }}">
                    <div class="flex gap-4">
                        <div class="w-12 h-12 rounded-2xl {{ $theme['bg'] }} {{ $theme['text'] }} {{ $theme['border'] }} border flex items-center justify-center shrink-0">
                            <i class="fa-solid {{ $theme['icon'] }}"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-[15px] md:text-base font-black text-slate-800">
                                            {{ $notif->judul ?? 'Notifikasi' }}
                                        </h3>

                                        @if ($isUnread)
                                            <span class="px-2.5 py-1 rounded-full bg-emerald-600 text-white text-[10px] font-black uppercase tracking-wide">
                                                Baru
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mt-1 text-xs font-bold {{ $theme['text'] }}">
                                        {{ $theme['label'] }}
                                    </p>
                                </div>

                                <p class="text-xs text-slate-400 font-semibold shrink-0">
                                    {{ optional($notif->created_at)->diffForHumans() ?? '-' }}
                                </p>
                            </div>

                            <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                                {{ $notif->pesan ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center">
                    <div class="mx-auto w-16 h-16 rounded-3xl bg-slate-100 text-slate-400 flex items-center justify-center mb-4">
                        <i class="fa-solid fa-bell-slash text-xl"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-800">Belum Ada Notifikasi</h3>
                    <p class="mt-2 text-sm text-slate-500">
                        Kotak notifikasi masih kosong. Sistem akhirnya memilih diam, sebuah pencapaian kecil.
                    </p>
                </div>
            @endforelse
        </div>

        @if ($notifikasis->hasPages())
            <div class="px-6 py-5 border-t border-slate-100 bg-slate-50/70">
                {{ $notifikasis->links() }}
            </div>
        @endif
    </div>
</div>
@endsection