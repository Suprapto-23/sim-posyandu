@extends('layouts.user')

@section('title', 'Pantau Kesehatan')
@section('page_title', 'Pantau Kesehatan')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    Carbon::setLocale('id');

    $routeTo = function ($names, $params = []) {
        foreach ((array) $names as $name) {
            if (Route::has($name)) {
                return route($name, $params);
            }
        }

        return '#';
    };

    $counts = $counts ?? [
        'total' => 0,
        'balita' => isset($balitas) ? $balitas->count() : 0,
        'remaja' => isset($remajas) ? $remajas->count() : 0,
        'lansia' => isset($lansias) ? $lansias->count() : 0,
    ];

    $formatDate = fn ($value, $format = 'd M Y') => $value
        ? Carbon::parse($value)->translatedFormat($format)
        : '-';

    $numberValue = function ($value, $unit = '') {
        if (blank($value)) {
            return '-';
        }

        $value = rtrim(rtrim((string) $value, '0'), '.');

        return trim($value . ' ' . $unit);
    };

    $ageText = function ($tanggalLahir) {
        if (blank($tanggalLahir)) {
            return '-';
        }

        $diff = Carbon::parse($tanggalLahir)->diff(now());

        return $diff->y > 0 ? $diff->y . ' tahun' : $diff->m . ' bulan';
    };

    $genderText = fn ($value) => match ($value) {
        'L' => 'Laki-laki',
        'P' => 'Perempuan',
        default => 'Belum diisi',
    };

    $lastCheckDate = function ($item) use ($formatDate) {
        $date = data_get($item, 'pemeriksaan_terakhir.tanggal_periksa')
            ?? data_get($item, 'pemeriksaan_terakhir.created_at')
            ?? data_get($item, 'updated_at');

        return $formatDate($date);
    };

    $getImt = function ($item) {
        if (filled($item->imt ?? null)) {
            return (float) $item->imt;
        }

        if (blank($item->berat_badan ?? null) || blank($item->tinggi_badan ?? null)) {
            return null;
        }

        $meter = ((float) $item->tinggi_badan) / 100;

        if ($meter <= 0) {
            return null;
        }

        return round(((float) $item->berat_badan) / ($meter * $meter), 2);
    };

    $imtLabel = function ($imt) {
        if (blank($imt)) {
            return '-';
        }

        return match (true) {
            $imt < 18.5 => 'Kurus',
            $imt < 25 => 'Normal',
            $imt < 30 => 'Berlebih',
            default => 'Obesitas',
        };
    };

    $balitaShowRoute = fn ($id) => $routeTo(['user.balita.show', 'user.monitoring.balita.show'], [$id]);
    $remajaShowRoute = fn ($id) => $routeTo(['user.remaja.show', 'user.monitoring.remaja.show'], [$id]);
    $lansiaShowRoute = fn ($id) => $routeTo(['user.lansia.show', 'user.monitoring.lansia.show'], [$id]);

    $cards = collect();

    foreach(($balitas ?? collect()) as $item) {
        $cards->push([
            'kategori' => 'Balita',
            'nama' => $item->nama_lengkap ?? '-',
            'meta' => $ageText($item->tanggal_lahir ?? null) . ' • ' . $genderText($item->jenis_kelamin ?? null),
            'caption' => 'Pertumbuhan dan KMS',
            'href' => $balitaShowRoute($item->id),
            'icon' => 'child',
            'tone' => 'rose',
            'search' => Str::lower('balita ' . ($item->nama_lengkap ?? '') . ' ' . ($item->nik ?? '')),
            'metrics' => [
                ['label' => 'BB', 'value' => $numberValue($item->berat_badan ?? data_get($item, 'pemeriksaan_terakhir.berat_badan'), 'kg')],
                ['label' => 'TB', 'value' => $numberValue($item->tinggi_badan ?? data_get($item, 'pemeriksaan_terakhir.tinggi_badan'), 'cm')],
                ['label' => 'Update', 'value' => $lastCheckDate($item)],
            ],
        ]);
    }

    foreach(($remajas ?? collect()) as $item) {
        $imt = $getImt($item);

        $cards->push([
            'kategori' => 'Remaja',
            'nama' => $item->nama_lengkap ?? '-',
            'meta' => $ageText($item->tanggal_lahir ?? null) . ' • ' . ($item->sekolah ?: 'Sekolah belum diisi'),
            'caption' => 'IMT dan kesehatan remaja',
            'href' => $remajaShowRoute($item->id),
            'icon' => 'user-graduate',
            'tone' => 'sky',
            'search' => Str::lower('remaja ' . ($item->nama_lengkap ?? '') . ' ' . ($item->nik ?? '')),
            'metrics' => [
                ['label' => 'BB', 'value' => $numberValue($item->berat_badan ?? data_get($item, 'pemeriksaan_terakhir.berat_badan'), 'kg')],
                ['label' => 'IMT', 'value' => filled($imt) ? $imt . ' ' . $imtLabel($imt) : '-'],
                ['label' => 'Update', 'value' => $lastCheckDate($item)],
            ],
        ]);
    }

    foreach(($lansias ?? collect()) as $item) {
        $imt = $getImt($item);

        $cards->push([
            'kategori' => 'Lansia',
            'nama' => $item->nama_lengkap ?? '-',
            'meta' => $ageText($item->tanggal_lahir ?? null) . ' • ' . ucwords(str_replace('_', ' ', $item->tingkat_kemandirian ?: 'Belum diisi')),
            'caption' => 'Tensi dan pemeriksaan dasar',
            'href' => $lansiaShowRoute($item->id),
            'icon' => 'heart-pulse',
            'tone' => 'amber',
            'search' => Str::lower('lansia ' . ($item->nama_lengkap ?? '') . ' ' . ($item->nik ?? '')),
            'metrics' => [
                ['label' => 'Tensi', 'value' => $item->tekanan_darah ?: '-'],
                ['label' => 'Gula', 'value' => $numberValue($item->gula_darah, 'mg/dL')],
                ['label' => 'IMT', 'value' => filled($imt) ? $imt . ' ' . $imtLabel($imt) : '-'],
            ],
        ]);
    }

    $toneMap = [
        'rose' => [
            'icon' => 'border-rose-100 bg-rose-50 text-rose-500',
            'badge' => 'border-rose-200 bg-rose-50 text-rose-700',
            'button' => 'bg-rose-500 hover:bg-rose-600 shadow-rose-500/20',
        ],
        'sky' => [
            'icon' => 'border-sky-100 bg-sky-50 text-sky-500',
            'badge' => 'border-sky-200 bg-sky-50 text-sky-700',
            'button' => 'bg-sky-500 hover:bg-sky-600 shadow-sky-500/20',
        ],
        'amber' => [
            'icon' => 'border-amber-100 bg-amber-50 text-amber-500',
            'badge' => 'border-amber-200 bg-amber-50 text-amber-700',
            'button' => 'bg-amber-500 hover:bg-amber-600 shadow-amber-500/20',
        ],
    ];
@endphp

@push('styles')
<style>
    .monitoring-enter {
        opacity: 0;
        animation: monitoringEnter .36s cubic-bezier(.16, 1, .3, 1) forwards;
    }

    .monitoring-enter-2 {
        opacity: 0;
        animation: monitoringEnter .36s cubic-bezier(.16, 1, .3, 1) .06s forwards;
    }

    @keyframes monitoringEnter {
        from {
            opacity: 0;
            transform: translateY(12px) scale(.99);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .monitoring-page {
        background:
            radial-gradient(circle at 8% 8%, rgba(16,185,129,.14), transparent 28%),
            radial-gradient(circle at 90% 12%, rgba(14,165,233,.11), transparent 26%),
            radial-gradient(circle at 76% 88%, rgba(245,158,11,.11), transparent 28%),
            linear-gradient(135deg,#f8fafc 0%,#ecfdf5 44%,#eff6ff 100%);
    }

    .monitoring-glass {
        border: 1px solid rgba(255,255,255,.76);
        background: rgba(255,255,255,.68);
        backdrop-filter: blur(20px);
        box-shadow: 0 16px 46px rgba(15,23,42,.055);
    }

    .monitoring-card {
        border: 1px solid rgba(226,232,240,.78);
        background: rgba(255,255,255,.72);
        backdrop-filter: blur(16px);
        box-shadow: 0 10px 28px rgba(15,23,42,.04);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .monitoring-card:hover {
        transform: translateY(-2px);
        border-color: rgba(16,185,129,.28);
        box-shadow: 0 16px 38px rgba(15,23,42,.065);
    }

    .is-hidden-by-search {
        display: none;
    }

    @media (prefers-reduced-motion: reduce) {
        .monitoring-enter,
        .monitoring-enter-2 {
            animation: none;
            opacity: 1;
        }

        .monitoring-card,
        .monitoring-card:hover {
            transition: none;
            transform: none;
        }
    }
</style>
@endpush

@section('content')
<div class="monitoring-page -mx-4 -my-4 min-h-[calc(100vh-96px)] px-4 py-5 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-5">

        <section class="monitoring-enter monitoring-glass relative overflow-hidden rounded-[30px] p-5 sm:p-6">
            <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-emerald-300/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 left-8 h-56 w-56 rounded-full bg-sky-300/14 blur-3xl"></div>

            <div class="relative grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_390px] xl:items-center">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50/90 px-3.5 py-2 text-[10px] font-black uppercase tracking-[0.18em] text-emerald-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Kesehatan Terpadu
                    </span>

                    <h1 class="mt-4 text-3xl font-black tracking-tight text-slate-800 sm:text-4xl">
                        Pemantauan Keluarga
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm font-semibold leading-7 text-slate-600">
                        Ringkasan kesehatan Balita, Remaja, dan Lansia yang terhubung dengan akun warga.
                    </p>

                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-[20px] border border-emerald-100 bg-white/70 px-3 py-3">
                            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-400">Total</p>
                            <p class="mt-1 text-2xl font-black text-slate-800">{{ $counts['total'] }}</p>
                        </div>

                        <div class="rounded-[20px] border border-rose-100 bg-white/70 px-3 py-3">
                            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-400">Balita</p>
                            <p class="mt-1 text-2xl font-black text-rose-600">{{ $counts['balita'] }}</p>
                        </div>

                        <div class="rounded-[20px] border border-sky-100 bg-white/70 px-3 py-3">
                            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-400">Remaja</p>
                            <p class="mt-1 text-2xl font-black text-sky-600">{{ $counts['remaja'] }}</p>
                        </div>

                        <div class="rounded-[20px] border border-amber-100 bg-white/70 px-3 py-3">
                            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-slate-400">Lansia</p>
                            <p class="mt-1 text-2xl font-black text-amber-600">{{ $counts['lansia'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-[26px] border border-white/80 bg-gradient-to-br from-emerald-50/95 via-white/82 to-sky-50/90 p-5 shadow-[0_14px_40px_rgba(15,23,42,0.055)] backdrop-blur-xl">
                    <label for="familySearch" class="block text-[10px] font-black uppercase tracking-[0.18em] text-emerald-700">
                        Cari Data Keluarga
                    </label>

                    <div class="relative mt-3">
                        <input type="text"
                               id="familySearch"
                               autocomplete="off"
                               placeholder="Ketik nama, NIK, atau kategori..."
                               class="h-12 w-full rounded-2xl border border-emerald-100 bg-white/78 px-4 pr-11 text-sm font-bold text-slate-700 outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-500/10">

                        <div class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-emerald-500">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>

                    <p class="mt-3 text-xs font-semibold leading-5 text-slate-500">
                        Pencarian berjalan di browser, jadi ringan dan tidak membebani server.
                    </p>
                </div>
            </div>
        </section>

        @if($hasData)
            <section class="monitoring-enter-2 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3" id="familyGrid">
                @foreach($cards as $card)
                    @php $tone = $toneMap[$card['tone']]; @endphp

                    <article class="monitoring-card family-card group rounded-[26px] p-4"
                             data-search="{{ $card['search'] }}">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[18px] border text-lg {{ $tone['icon'] }}">
                                <i class="fas fa-{{ $card['icon'] }}"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[0.10em] {{ $tone['badge'] }}">
                                        {{ $card['kategori'] }}
                                    </span>

                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.10em] text-slate-600">
                                        {{ $card['caption'] }}
                                    </span>
                                </div>

                                <h3 class="mt-3 line-clamp-1 text-lg font-black text-slate-800">
                                    {{ $card['nama'] }}
                                </h3>

                                <p class="mt-1 line-clamp-1 text-sm font-semibold text-slate-500">
                                    {{ $card['meta'] }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-2">
                            @foreach($card['metrics'] as $metric)
                                <div class="rounded-[18px] border border-slate-100 bg-slate-50/70 px-3 py-3">
                                    <p class="text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">
                                        {{ $metric['label'] }}
                                    </p>
                                    <p class="mt-1 truncate text-sm font-black text-slate-800">
                                        {{ $metric['value'] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>

                        <a href="{{ $card['href'] }}"
                           class="smooth-route mt-4 inline-flex h-10 w-full items-center justify-center rounded-2xl text-xs font-black uppercase tracking-[0.12em] text-white shadow-lg transition {{ $tone['button'] }}">
                            Lihat Detail
                        </a>
                    </article>
                @endforeach
            </section>

            <section id="notFoundState" class="hidden rounded-[28px] border border-dashed border-emerald-300 bg-emerald-50/65 p-8 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-[22px] bg-white text-xl text-emerald-500 shadow-sm">
                    <i class="fas fa-search"></i>
                </div>

                <h3 class="mt-4 text-base font-black text-slate-800">
                    Data Tidak Ditemukan
                </h3>

                <p class="mx-auto mt-1 max-w-md text-sm font-semibold leading-6 text-slate-500">
                    Tidak ada data keluarga yang cocok dengan kata kunci tersebut.
                </p>
            </section>
        @else
            <section class="monitoring-enter-2 rounded-[28px] border border-dashed border-emerald-300 bg-emerald-50/65 p-10 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[24px] bg-white text-2xl text-emerald-500 shadow-sm">
                    <i class="fas fa-user-shield"></i>
                </div>

                <h3 class="mt-5 text-xl font-black text-slate-800">
                    Belum Ada Rekam Medis
                </h3>

                <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-6 text-slate-600">
                    Data pemantauan akan muncul setelah akun warga tersinkron dengan data Balita, Remaja, atau Lansia di Posyandu.
                </p>
            </section>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('familySearch');
        const cards = Array.from(document.querySelectorAll('.family-card'));
        const notFoundState = document.getElementById('notFoundState');

        if (!searchInput || !cards.length) return;

        searchInput.addEventListener('input', function () {
            const keyword = this.value.trim().toLowerCase();
            let visible = 0;

            cards.forEach(function (card) {
                const haystack = card.dataset.search || '';
                const matched = !keyword || haystack.includes(keyword);

                card.classList.toggle('is-hidden-by-search', !matched);

                if (matched) visible++;
            });

            if (notFoundState) {
                notFoundState.classList.toggle('hidden', visible > 0);
            }
        });
    });
</script>
@endpush