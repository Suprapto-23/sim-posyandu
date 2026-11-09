@extends('layouts.kader')

@section('title', 'Pengukuran Fisik')
@section('page-name', 'Pengukuran Fisik')
@section('page-title', 'Pengukuran Fisik')

@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;
    use App\Models\Pemeriksaan;

    Carbon::setLocale('id');
    $tz    = 'Asia/Jakarta';
    $today = now($tz);

    $kategori = $kategori ?? request('kategori', '');
    $search   = $search ?? request('search', '');
    $status   = $status ?? request('status', '');
    $mode     = ($mode ?? request('mode', 'today')) === 'history' ? 'history' : 'today';
    $periode  = $periode ?? request('periode', $today->format('Y-m'));

    $kategoriAktif = ['balita', 'remaja', 'lansia'];
    $totalData = method_exists($pemeriksaans, 'total') ? $pemeriksaans->total() : $pemeriksaans->count();
    if (method_exists($pemeriksaans, 'withQueryString')) {
        $pemeriksaans->withQueryString();
    }

    $modeOptions = ['today' => ['Hari Ini', 'fa-calendar-day'], 'history' => ['Riwayat', 'fa-clock-rotate-left']];
    $kategoriOptions = ['' => 'Semua', 'balita' => 'Balita', 'remaja' => 'Remaja', 'lansia' => 'Lansia'];
    $statusOptions = ['' => 'Semua Status', 'pending' => 'Menunggu Review', 'ditolak' => 'Perlu Revisi', 'verified' => 'Tervalidasi'];
    $statusList = collect($statusOptions)->map(fn ($l, $v) => ['v' => (string) $v, 'l' => $l])->values();

    $normalizeStatus = function ($v) {
        $v = strtolower((string) ($v ?? 'pending'));

        return match (true) {
            in_array($v, ['verified', 'terverifikasi', 'valid', 'approved', 'disetujui', 'selesai'], true) => 'verified',
            in_array($v, ['ditolak', 'revisi', 'perlu_revisi', 'needs_revision', 'rejected', 'dikembalikan'], true) => 'ditolak',
            default => 'pending',
        };
    };

    $kategoriMeta = fn ($v) => match (strtolower((string) $v)) {
        'balita' => ['label' => 'Balita', 'icon' => 'fa-child-reaching', 'tone' => 'teal'],
        'remaja' => ['label' => 'Remaja', 'icon' => 'fa-user-graduate', 'tone' => 'amber'],
        'lansia' => ['label' => 'Lansia', 'icon' => 'fa-person-cane', 'tone' => 'blue'],
        default  => ['label' => 'Umum', 'icon' => 'fa-user', 'tone' => 'slate'],
    };

    $statusMeta = fn ($v) => match ($normalizeStatus($v)) {
        'verified' => ['label' => 'Tervalidasi', 'icon' => 'fa-circle-check', 'tone' => 'emerald', 'editable' => false],
        'ditolak'  => ['label' => 'Perlu Revisi', 'icon' => 'fa-rotate-left', 'tone' => 'rose', 'editable' => true],
        default    => ['label' => 'Menunggu', 'icon' => 'fa-clock', 'tone' => 'amber', 'editable' => true],
    };

    $metric = fn ($v, $unit = '') => ($v === null || $v === '') ? '-' : trim($v . ' ' . $unit);

    $formatTanggal = function ($item) {
        $t = $item->tanggal_periksa ?? $item->kunjungan?->tanggal_kunjungan ?? $item->created_at ?? null;
        return $t ? Carbon::parse($t)->translatedFormat('d M Y') : '-';
    };

    // Ringkasan: satu query agregat (bukan get() semua baris)
    $range = null;
    if ($mode === 'history' && preg_match('/^\d{4}-\d{2}$/', (string) $periode)) {
        try {
            $d = Carbon::createFromFormat('!Y-m', $periode, $tz)->startOfMonth();
            $range = [$d->copy(), $d->copy()->endOfMonth()];
        } catch (\Throwable $e) {
        }
    }

    $summary = Pemeriksaan::query()
        ->whereIn('kategori_pasien', $kategoriAktif)
        ->when($mode === 'today', fn ($q) => $q->whereBetween('tanggal_periksa', [$today->copy()->startOfDay(), $today->copy()->endOfDay()]))
        ->when($range, fn ($q) => $q->whereBetween('tanggal_periksa', $range))
        ->when(in_array($kategori, $kategoriAktif, true), fn ($q) => $q->where('kategori_pasien', $kategori))
        ->selectRaw('kategori_pasien, status_verifikasi, COUNT(*) as total')
        ->groupBy('kategori_pasien', 'status_verifikasi')
        ->get();

    $byStatus = $summary->groupBy(fn ($r) => $normalizeStatus($r->status_verifikasi))->map(fn ($g) => $g->sum('total'));
    $byKat    = $summary->groupBy(fn ($r) => strtolower((string) $r->kategori_pasien))->map(fn ($g) => $g->sum('total'));

    $stats = [
        ['Menunggu review', $byStatus['pending'] ?? 0, 'fa-clock', 'amber'],
        ['Perlu revisi', $byStatus['ditolak'] ?? 0, 'fa-rotate-left', 'rose'],
        ['Tervalidasi', $byStatus['verified'] ?? 0, 'fa-circle-check', 'emerald'],
        ['Total balita', $byKat['balita'] ?? 0, 'fa-child-reaching', 'teal'],
        ['Total remaja', $byKat['remaja'] ?? 0, 'fa-user-graduate', 'amber'],
        ['Total lansia', $byKat['lansia'] ?? 0, 'fa-person-cane', 'blue'],
    ];

    $activeFilterCount = collect([$kategori, $search, $status])->filter(fn ($v) => filled($v))->count();
    $filterCaption = $activeFilterCount > 0 ? $activeFilterCount . ' filter aktif' : 'Semua data';
    $createUrl = route('kader.pemeriksaan.create', ['kategori' => $kategori ?: 'balita']);
@endphp

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
    [x-cloak] { display: none !important; }

    body {
        background-color: #f1f5f9;
        background-image:
            radial-gradient(at 0% 0%, #d1fae5 0, transparent 50%),
            radial-gradient(at 100% 0%, #cffafe 0, transparent 50%),
            radial-gradient(at 100% 100%, #d1fae5 0, transparent 50%);
        background-attachment: fixed;
    }

    /* ===== Token: skala tipografi tunggal (12 / 14 / 18 / 28 px) ===== */
    .pk, .pk-dialog {
        --ink: #0f172a; --mute: #64748b; --line: #e2e8f0; --teal: #0d9488;
        --fs-cap: .75rem; --fs-body: .875rem; --fs-title: 1.125rem; --fs-num: 1.75rem;
    }
    .pk {
        display: grid; gap: 1.25rem; max-width: 1400px; margin-inline: auto;
        padding: 1rem 1rem 6rem;
        font-size: var(--fs-body); line-height: 1.5; font-weight: 500; color: var(--ink);
        animation: pkIn .4s ease both;
    }
    @media (min-width: 640px) { .pk { padding: 1.5rem 1.5rem 6rem; } }
    @media (min-width: 1024px) { .pk { padding: 2rem 2rem 6rem; } }
    @keyframes pkIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { .pk { animation: none; } }
    .pk [hidden] { display: none !important; }

    .tone-teal    { --t-bg: #f0fdfa; --t-bd: #99f6e4; --t-fg: #0f766e; }
    .tone-emerald { --t-bg: #ecfdf5; --t-bd: #a7f3d0; --t-fg: #047857; }
    .tone-amber   { --t-bg: #fffbeb; --t-bd: #fde68a; --t-fg: #b45309; }
    .tone-blue    { --t-bg: #eff6ff; --t-bd: #bfdbfe; --t-fg: #1d4ed8; }
    .tone-rose    { --t-bg: #fff1f2; --t-bd: #fecdd3; --t-fg: #be123c; }
    .tone-slate   { --t-bg: #f8fafc; --t-bd: #e2e8f0; --t-fg: #475569; }

    .pk-card {
        background: rgba(255, 255, 255, .85); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, .9); border-radius: 1.5rem;
        box-shadow: 0 10px 30px -12px rgba(15, 23, 42, .08);
    }
    .pk-label { font-size: var(--fs-cap); font-weight: 600; color: var(--mute); }
    .pk-title { font-size: var(--fs-title); font-weight: 800; line-height: 1.3; }
    .pk-num   { font-size: var(--fs-num); font-weight: 800; line-height: 1.1; font-variant-numeric: tabular-nums; }

    .pk-pill {
        display: inline-flex; align-items: center; gap: .375rem; padding: .25rem .75rem; border-radius: 999px;
        font-size: var(--fs-cap); font-weight: 700; white-space: nowrap;
        background: var(--t-bg); border: 1px solid var(--t-bd); color: var(--t-fg);
    }
    .pk-ico {
        display: grid; place-items: center; flex: none; width: 2.5rem; height: 2.5rem; border-radius: 1rem;
        font-size: 1rem; background: var(--t-bg); border: 1px solid var(--t-bd); color: var(--t-fg);
    }

    .pk-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: .5rem; height: 2.75rem; padding: 0 1.25rem;
        border-radius: 999px; font-size: var(--fs-body); font-weight: 700; white-space: nowrap; cursor: pointer;
        text-decoration: none; transition: transform .15s, background .2s, color .2s;
    }
    .pk-btn:active { transform: scale(.97); }
    .pk-btn-white { background: #fff; color: #0f766e; }
    .pk-btn-white:hover { background: #f0fdfa; }
    .pk-btn-ghost { background: rgba(0, 0, 0, .12); color: #fff; border: 1px solid rgba(255, 255, 255, .35); }
    .pk-btn-ghost:hover { background: rgba(0, 0, 0, .2); }
    .pk-btn-soft { background: #f0fdfa; color: #0f766e; border: 1px solid #99f6e4; }
    .pk-btn-soft:hover { background: #14b8a6; color: #fff; }
    .pk-btn-line { background: #fff; color: #334155; border: 1px solid var(--line); }
    .pk-btn-line:hover { background: #f8fafc; }
    .pk-btn-danger { background: linear-gradient(90deg, #f43f5e, #e11d48); color: #fff; }
    .pk-btn:focus-visible, .pk-ib:focus-visible, .pk-opt:focus-visible { outline: 2px solid #14b8a6; outline-offset: 2px; }

    /* Alert */
    .pk-alert { display: flex; align-items: center; gap: .75rem; padding: 1rem 1.25rem; border-radius: 1.5rem; background: var(--t-bg); border: 1px solid var(--t-bd); color: var(--t-fg); }
    .pk-alert > i { font-size: 1.25rem; }
    .pk-alert strong { display: block; font-weight: 800; }

    /* Hero */
    .pk-hero {
        position: relative; overflow: hidden; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.5rem;
        padding: clamp(1.25rem, 3vw, 2.5rem); border-radius: 2rem; color: #fff;
        background: linear-gradient(110deg, #14b8a6, #2dd4bf 55%, #34d399);
        border: 3px solid rgba(255, 255, 255, .5); box-shadow: 0 20px 40px -15px rgba(20, 184, 166, .45);
    }
    .pk-hero::before { content: ""; position: absolute; inset: 0; pointer-events: none; background: radial-gradient(rgba(255, 255, 255, .12) 1.5px, transparent 1.5px) 0 0 / 20px 20px; }
    .pk-hero > * { position: relative; }
    .pk-hero-text { flex: 1 1 22rem; }
    .pk-hero-text h1 { margin: .75rem 0 .5rem; font-size: clamp(1.5rem, 1rem + 1.6vw, 2.25rem); font-weight: 800; line-height: 1.2; letter-spacing: -.02em; }
    .pk-hero-text p { max-width: 34rem; color: #f0fdfa; }
    .pk-hero-actions { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 1.25rem; }
    .pk-eyebrow { display: inline-flex; align-items: center; gap: .5rem; padding: .25rem .75rem; border-radius: 999px; background: rgba(255, 255, 255, .2); border: 1px solid rgba(255, 255, 255, .3); font-size: var(--fs-cap); font-weight: 700; }
    .pk-eyebrow i { width: .5rem; height: .5rem; border-radius: 50%; background: #a7f3d0; }
    .pk-total { display: flex; flex-direction: column; align-items: center; gap: .625rem; min-width: 13rem; padding: 1.25rem; color: var(--ink); }
    .pk-big { display: flex; align-items: center; gap: .75rem; }

    /* Stats */
    .pk-stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; }
    @media (min-width: 768px) { .pk-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; } }
    .pk-stat { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: 1rem 1.25rem; min-width: 0; }
    .pk-stat-l { font-weight: 600; color: var(--mute); }
    .pk-stat .pk-num { margin-top: .25rem; }

    /* Filter */
    .pk-filter { position: relative; z-index: 30; display: flex; flex-wrap: wrap; gap: .75rem; }
    .pk-filter-left, .pk-filter-right { display: flex; flex-wrap: wrap; gap: .75rem; }
    .pk-filter-right { flex: 1 1 26rem; justify-content: flex-end; }
    .pk-seg {
        display: inline-flex; gap: .25rem; padding: .25rem; max-width: 100%; overflow-x: auto; scrollbar-width: none;
        background: rgba(241, 245, 249, .9); border: 1px solid rgba(226, 232, 240, .8); border-radius: 1rem; box-shadow: inset 0 1px 2px rgba(15, 23, 42, .05);
    }
    .pk-seg::-webkit-scrollbar { display: none; }
    .pk-seg label { position: relative; flex: none; cursor: pointer; }
    .pk-seg input { position: absolute; opacity: 0; pointer-events: none; }
    .pk-seg span { display: flex; align-items: center; justify-content: center; gap: .375rem; height: 2.25rem; padding: 0 1rem; border-radius: .75rem; font-weight: 700; color: var(--mute); white-space: nowrap; transition: background .2s, color .2s; }
    .pk-seg input:checked + span { background: #fff; color: var(--teal); box-shadow: 0 1px 3px rgba(15, 23, 42, .1); }
    .pk-seg input:focus-visible + span { outline: 2px solid #14b8a6; }

    .pk-field {
        display: flex; align-items: center; justify-content: space-between; gap: .5rem; width: 100%; height: 2.75rem; padding: 0 1rem;
        background: #fff; border: 1px solid var(--line); border-radius: 1rem; box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        font-size: var(--fs-body); font-weight: 600; color: var(--ink); outline: none; transition: border-color .2s, box-shadow .2s;
    }
    .pk-field:hover { border-color: #5eead4; }
    .pk-field:focus, .pk-field:focus-visible { border-color: #14b8a6; box-shadow: 0 0 0 4px rgba(20, 184, 166, .12); }
    .pk-field i { color: #94a3b8; font-size: var(--fs-cap); }
    .pk-search { position: relative; flex: 1 1 12rem; max-width: 20rem; min-width: 0; }
    .pk-search > i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: var(--fs-cap); pointer-events: none; }
    .pk-search input { padding-left: 2.5rem; font-weight: 500; }
    .pk-search input::placeholder { color: #94a3b8; }
    .pk-search input::-webkit-search-cancel-button { -webkit-appearance: none; height: 14px; width: 14px; cursor: pointer; opacity: .6; background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%2394a3b8"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>') no-repeat 50% 50%; }
    .pk-dd { position: relative; flex: 0 0 11rem; }
    .pk-menu { position: absolute; z-index: 50; top: calc(100% + .5rem); left: 0; right: 0; padding: .375rem; background: #fff; border: 1px solid #f1f5f9; border-radius: 1rem; box-shadow: 0 20px 40px -10px rgba(15, 23, 42, .18); }
    .pk-menu.is-month { left: auto; width: 16rem; padding: 1rem; border-radius: 1.25rem; }
    .pk-opt { display: block; width: 100%; padding: .625rem 1rem; border-radius: .75rem; text-align: left; font-size: var(--fs-body); font-weight: 600; color: #475569; cursor: pointer; }
    .pk-opt:not(:disabled):hover { background: #f8fafc; color: var(--teal); }
    .pk-opt.is-on { background: #f0fdfa; color: var(--teal); }
    .pk-opt:disabled { opacity: .35; cursor: not-allowed; }
    .pk-yr { display: flex; align-items: center; justify-content: space-between; padding-bottom: .75rem; margin-bottom: .75rem; border-bottom: 1px solid #f1f5f9; font-weight: 800; }
    .pk-yr button { width: 2rem; height: 2rem; border-radius: 50%; background: #f8fafc; color: #64748b; font-size: var(--fs-cap); }
    .pk-yr button:disabled { opacity: .3; cursor: not-allowed; }
    .pk-months { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; margin-bottom: .5rem; }
    .pk-months .pk-opt { padding: .5rem 0; text-align: center; font-weight: 700; background: #f8fafc; }
    .pk-months .pk-opt.is-on { background: linear-gradient(135deg, #2dd4bf, #10b981); color: #fff; }
    @media (max-width: 639px) {
        .pk-filter-left, .pk-filter-right, .pk-seg { width: 100%; }
        .pk-seg label { flex: 1; }
        .pk-dd, .pk-search { flex: 1 1 100%; max-width: none; }
        .pk-menu.is-month { left: 0; right: 0; width: auto; }
    }

    /* Daftar */
    .pk-section { padding: 1rem; }
    @media (min-width: 640px) { .pk-section { padding: 1.5rem; } }
    .pk-head-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: 1.25rem; }
    .pk-list { container-type: inline-size; display: flex; flex-direction: column; gap: .75rem; }
    .pk-th { display: none; }
    .pk-row {
        display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem 1rem; padding: 1rem;
        background: rgba(255, 255, 255, .7); border: 1px solid rgba(226, 232, 240, .8); border-radius: 1.25rem;
        transition: background .2s, box-shadow .2s;
    }
    .pk-row:hover { background: #fff; box-shadow: 0 8px 25px rgba(20, 184, 166, .1); }
    .pk-row > [data-label]::before { content: attr(data-label); display: block; margin-bottom: .125rem; font-size: var(--fs-cap); font-weight: 600; color: var(--mute); }
    .pk-who { grid-column: 1 / -1; order: 0; display: flex; align-items: center; gap: .75rem; min-width: 0; }
    .pk-c-date { order: 1; } .pk-c-status { order: 2; }
    .pk-c-fisik { grid-column: 1 / -1; order: 3; } .pk-c-spec { grid-column: 1 / -1; order: 4; }
    .pk-note { grid-column: 1 / -1; order: 5; } .pk-actions { grid-column: 1 / -1; order: 6; }
    .pk-av { display: grid; place-items: center; flex: none; width: 2.5rem; height: 2.5rem; border-radius: 50%; font-weight: 800; background: var(--t-bg); border: 1px solid var(--t-bd); color: var(--t-fg); }
    .pk-who b { display: block; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pk-sub { display: flex; flex-wrap: wrap; gap: 0 .5rem; font-size: var(--fs-cap); font-weight: 600; color: var(--mute); }
    .pk-sub em { font-style: normal; font-weight: 700; color: var(--t-fg); }
    .pk-kv { display: flex; flex-wrap: wrap; gap: .125rem 1rem; }
    .pk-kv span { color: var(--mute); } .pk-kv b { color: var(--ink); font-weight: 700; }
    .pk-chips { display: flex; flex-wrap: wrap; gap: .375rem; }
    .pk-chip { display: inline-flex; gap: .25rem; padding: .125rem .5rem; border-radius: .5rem; background: #f8fafc; border: 1px solid #f1f5f9; font-size: var(--fs-cap); font-weight: 600; color: var(--mute); }
    .pk-chip b { font-weight: 700; color: var(--t-fg); }
    .pk-note { display: flex; gap: .625rem; padding: .75rem 1rem; border-radius: 1rem; background: var(--t-bg); border: 1px solid var(--t-bd); color: var(--t-fg); }
    .pk-note > i { margin-top: .2rem; }
    .pk-note b { display: block; font-size: var(--fs-cap); font-weight: 700; }
    .pk-actions { display: flex; gap: .5rem; }
    .pk-actions form { display: contents; }
    .pk-ib {
        display: inline-flex; align-items: center; justify-content: center; gap: .375rem; flex: 1; height: 2.5rem; border-radius: 999px;
        background: #fff; border: 1px solid var(--line); color: #475569; font-size: var(--fs-body); font-weight: 700; cursor: pointer; transition: background .2s, color .2s, border-color .2s;
    }
    .pk-ib i { font-size: var(--fs-cap); }
    .pk-ib:hover { border-color: #99f6e4; background: #f0fdfa; color: var(--teal); }
    .pk-ib.is-amber:hover { border-color: #fde68a; background: #fffbeb; color: #b45309; }
    .pk-ib.is-rose { background: #fff1f2; border-color: #fecdd3; color: #e11d48; }
    .pk-ib.is-rose:hover { background: #e11d48; border-color: #e11d48; color: #fff; }
    .pk-ib.is-lock { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }

    @container (min-width: 560px) {
        .pk-row { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .pk-c-fisik { grid-column: span 2; }
    }
    @container (min-width: 900px) {
        .pk-th, .pk-row { grid-template-columns: minmax(12rem, 2fr) 6.5rem 7.5rem minmax(10rem, 2fr) 7.5rem 8rem; gap: .75rem; align-items: center; }
        .pk-th { display: grid; padding: 0 calc(1rem + 1px); font-size: var(--fs-cap); font-weight: 600; color: var(--mute); }
        .pk-th span:last-child { text-align: right; }
        .pk-row { padding: .75rem 1rem; }
        .pk-row > [data-label]::before { display: none; }
        .pk-who, .pk-c-date, .pk-c-fisik, .pk-c-spec, .pk-c-status, .pk-actions { grid-column: auto; }
        .pk-c-date { order: 1; } .pk-c-fisik { order: 2; } .pk-c-spec { order: 3; } .pk-c-status { order: 4; }
        .pk-actions { order: 5; justify-content: flex-end; }
        .pk-note { grid-column: 1 / -1; order: 6; }
        .pk-ib { flex: none; width: 2.25rem; height: 2.25rem; padding: 0; }
        .pk-ib span { display: none; }
    }

    .pk-empty { display: grid; justify-items: center; gap: .5rem; padding: 3rem 1rem; text-align: center; }
    .pk-empty .pk-ico { width: 4.5rem; height: 4.5rem; border-radius: 50%; font-size: 1.75rem; margin-bottom: .5rem; }
    .pk-empty p { max-width: 24rem; color: var(--mute); }
    .is-loading { opacity: .5; pointer-events: none; transition: opacity .2s; }

    /* Pagination */
    .pk-pager { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid #f1f5f9; }
    .pk-pg { display: flex; flex-wrap: wrap; gap: .375rem; }
    .pk-pg a, .pk-pg span { display: grid; place-items: center; min-width: 2.25rem; height: 2.25rem; padding: 0 .5rem; border-radius: 999px; border: 1px solid var(--line); background: #fff; font-weight: 700; color: #334155; }
    .pk-pg a:hover { color: var(--teal); border-color: #99f6e4; }
    .pk-pg .is-on { background: #14b8a6; border-color: #14b8a6; color: #fff; }
    .pk-pg .is-off { opacity: .4; }

    /* Dialog hapus */
    .pk-dialog { width: min(24rem, calc(100vw - 2rem)); padding: 0; border: 0; background: transparent; margin: auto; }
    .pk-dialog::backdrop { background: rgba(15, 23, 42, .45); backdrop-filter: blur(4px); }
    .pk-dialog-box { padding: 2rem; text-align: center; background: #fff; border-radius: 2rem; box-shadow: 0 30px 60px -15px rgba(15, 23, 42, .35); color: var(--ink); font-size: var(--fs-body); }
    .pk-dialog-box .pk-ico { width: 4rem; height: 4rem; margin: 0 auto 1rem; border-radius: 50%; font-size: 1.5rem; }
    .pk-dialog-box p { margin: .5rem 0 1.5rem; color: var(--mute); }
    .pk-dialog-box div { display: flex; gap: .75rem; }
    .pk-dialog-box .pk-btn { flex: 1; }
</style>
@endpush

@section('content')
<div class="pk">

    @if(session('success') || session('error') || $errors->any())
        @php $ok = (bool) session('success'); @endphp
        <div class="pk-alert tone-{{ $ok ? 'emerald' : 'rose' }}" role="alert">
            <i class="fa-solid {{ $ok ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i>
            <div>
                <strong>{{ $ok ? 'Berhasil' : 'Peringatan' }}</strong>
                <p>{{ session('success') ?? session('error') ?? 'Ada input yang belum valid. Perbaiki dulu.' }}</p>
            </div>
        </div>
    @endif

    {{-- HERO --}}
    <section class="pk-hero">
        <div class="pk-hero-text">
            <span class="pk-eyebrow"><i></i> Measurement Center</span>
            <h1>Pengukuran Fisik Posyandu</h1>
            <p>Kader mencatat data dasar seperti berat badan, tinggi badan, lingkar tubuh, tensi, dan catatan awal. Validasi klinis tetap dilakukan Bidan.</p>
            <div class="pk-hero-actions">
                <a href="{{ $createUrl }}" class="pk-btn pk-btn-white"><i class="fa-solid fa-plus"></i> Input Pengukuran</a>
                <a href="{{ route('kader.dashboard') }}" class="pk-btn pk-btn-ghost"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            </div>
        </div>
        <div id="hero-stats" class="pk-card pk-total">
            <span class="pk-label">Total data ditemukan</span>
            <div class="pk-big">
                <span class="pk-ico tone-teal"><i class="fa-solid fa-database"></i></span>
                <span class="pk-num">{{ number_format($totalData) }}</span>
            </div>
            <span class="pk-pill tone-teal"><i class="fa-solid fa-filter"></i> {{ $filterCaption }}</span>
        </div>
    </section>

    {{-- STATISTIK --}}
    <section id="stats" class="pk-stats">
        @foreach($stats as [$label, $value, $icon, $tone])
            <div class="pk-card pk-stat tone-{{ $tone }}">
                <div>
                    <p class="pk-stat-l">{{ $label }}</p>
                    <p class="pk-num">{{ number_format($value) }}</p>
                </div>
                <span class="pk-ico"><i class="fa-solid {{ $icon }}"></i></span>
            </div>
        @endforeach
    </section>

    {{-- FILTER --}}
    <form id="filterForm" method="GET" action="{{ route('kader.pemeriksaan.index') }}" class="pk-filter">
        <div class="pk-filter-left">
            <div class="pk-seg" role="radiogroup" aria-label="Rentang waktu">
                @foreach($modeOptions as $key => [$label, $icon])
                    <label>
                        <input type="radio" name="mode" value="{{ $key }}" @checked($mode === $key)>
                        <span><i class="fa-solid {{ $icon }}"></i> {{ $label }}</span>
                    </label>
                @endforeach
            </div>
            <div class="pk-seg" role="radiogroup" aria-label="Kategori sasaran">
                @foreach($kategoriOptions as $key => $label)
                    <label>
                        <input type="radio" name="kategori" value="{{ $key }}" @checked($kategori === (string) $key)>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="pk-filter-right">
            {{-- Pemilih bulan (bulan depan dikunci) --}}
            <div id="periodeWrapper" class="pk-dd" @if($mode !== 'history') hidden @endif x-data="monthPicker(@js($periode))" @click.outside="open = false">
                <input type="hidden" name="periode" value="{{ $periode }}" :value="selectedPeriod">
                <button type="button" class="pk-field" @click="open = !open" :aria-expanded="open">
                    <span x-text="formattedPeriod">{{ $periode }}</span>
                    <i class="fa-regular fa-calendar"></i>
                </button>
                <div class="pk-menu is-month" x-show="open" x-cloak x-transition.opacity>
                    <div class="pk-yr">
                        <button type="button" @click="year--" aria-label="Tahun sebelumnya"><i class="fa-solid fa-chevron-left"></i></button>
                        <span x-text="year"></span>
                        <button type="button" @click="nextYear()" :disabled="year >= currentYear" aria-label="Tahun berikutnya"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>
                    <div class="pk-months">
                        <template x-for="(m, i) in months" :key="i">
                            <button type="button" class="pk-opt" :class="{ 'is-on': year === selectedYear && i + 1 === selectedMonth }" :disabled="isFuture(i + 1)" @click="pick(i + 1)" x-text="m"></button>
                        </template>
                    </div>
                    <button type="button" class="pk-opt" @click="pick(currentMonth, currentYear)"><i class="fa-solid fa-clock-rotate-left"></i> Bulan ini</button>
                </div>
            </div>

            <div class="pk-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" name="search" value="{{ $search }}" class="pk-field" autocomplete="off" placeholder="Cari nama atau NIK..." aria-label="Cari nama atau NIK">
            </div>

            <div class="pk-dd" x-data="{ open: false, val: @js((string) $status), opts: @js($statusList), get label() { return (this.opts.find(o => o.v === this.val) || this.opts[0]).l; } }" @click.outside="open = false">
                <input type="hidden" name="status" :value="val" value="{{ $status }}">
                <button type="button" class="pk-field" @click="open = !open" :aria-expanded="open">
                    <span x-text="label">{{ $statusOptions[$status] ?? 'Semua Status' }}</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="pk-menu" x-show="open" x-cloak x-transition.opacity>
                    <template x-for="o in opts" :key="o.v || 'all'">
                        <button type="button" class="pk-opt" :class="{ 'is-on': val === o.v }" @click="val = o.v; open = false; $nextTick(() => window.submitFilter())" x-text="o.l"></button>
                    </template>
                </div>
            </div>
        </div>
    </form>

    {{-- DAFTAR --}}
    <section id="data-table-section" class="pk-card pk-section">
        <div class="pk-head-row">
            <div>
                <h2 class="pk-title">Riwayat Pengukuran Fisik</h2>
                <p class="pk-meta pk-label">Menampilkan {{ number_format($totalData) }} data</p>
            </div>
            <a href="{{ $createUrl }}" class="pk-btn pk-btn-soft"><i class="fa-solid fa-plus"></i> Tambah Data</a>
        </div>

        @if($pemeriksaans->count())
            <div class="pk-list">
                <div class="pk-th" aria-hidden="true">
                    <span>Sasaran</span><span>Tanggal</span><span>Fisik dasar</span><span>Pemeriksaan spesifik</span><span>Status</span><span>Aksi</span>
                </div>

                @foreach($pemeriksaans as $item)
                    @php
                        $pasien = $item->kunjungan?->pasien;
                        $nama = $pasien->nama_lengkap ?? $pasien->nama ?? $item->nama_pasien ?? 'Tanpa Nama';
                        $nik  = $pasien->nik ?? $item->nik_pasien ?? '-';
                        $km = $kategoriMeta($item->kategori_pasien);
                        $sm = $statusMeta($item->status_verifikasi ?? null);
                        $note = $item->catatan_validasi ?? $item->catatan_bidan ?? $item->catatan_review ?? null;
                        $spec = match (strtolower((string) $item->kategori_pasien)) {
                            'balita' => ['L. Kepala' => $metric($item->lingkar_kepala, 'cm'), 'LiLA' => $metric($item->lingkar_lengan, 'cm')],
                            'remaja' => ['IMT' => $metric($item->imt), 'Tensi' => $metric($item->tekanan_darah), 'LiLA' => $metric($item->lingkar_lengan, 'cm'), 'LP' => $metric($item->lingkar_perut, 'cm')],
                            'lansia' => ['IMT' => $metric($item->imt), 'Tensi' => $metric($item->tekanan_darah), 'LP' => $metric($item->lingkar_perut, 'cm'), 'Kemandirian' => $item->tingkat_kemandirian ? Str::headline($item->tingkat_kemandirian) : '-'],
                            default => [],
                        };
                    @endphp

                    <article class="pk-row tone-{{ $km['tone'] }}">
                        <div class="pk-who">
                            <span class="pk-av">{{ Str::upper(Str::substr($nama, 0, 1)) }}</span>
                            <div class="min-w-0">
                                <b>{{ $nama }}</b>
                                <div class="pk-sub"><span>{{ $nik }}</span><em>{{ $km['label'] }}</em></div>
                            </div>
                        </div>

                        <div class="pk-c-date" data-label="Tanggal">{{ $formatTanggal($item) }}</div>

                        <div class="pk-c-fisik" data-label="Fisik dasar">
                            <div class="pk-kv">
                                <div><span>BB</span> <b>{{ $metric($item->berat_badan, 'kg') }}</b></div>
                                <div><span>TB</span> <b>{{ $metric($item->tinggi_badan, 'cm') }}</b></div>
                            </div>
                        </div>

                        <div class="pk-c-spec" data-label="Pemeriksaan spesifik">
                            <div class="pk-chips">
                                @foreach($spec as $k => $v)
                                    <span class="pk-chip">{{ $k }} <b>{{ $v }}</b></span>
                                @endforeach
                            </div>
                        </div>

                        <div class="pk-c-status" data-label="Status">
                            <span class="pk-pill tone-{{ $sm['tone'] }}"><i class="fa-solid {{ $sm['icon'] }}"></i> {{ $sm['label'] }}</span>
                        </div>

                        @if($note)
                            <div class="pk-note tone-{{ $sm['tone'] }}">
                                <i class="fa-solid fa-notes-medical"></i>
                                <div><b>Catatan Bidan</b>{{ $note }}</div>
                            </div>
                        @endif

                        <div class="pk-actions">
                            <a href="{{ route('kader.pemeriksaan.show', $item->id) }}" class="pk-ib" title="Detail" aria-label="Detail"><i class="fa-solid fa-eye"></i><span>Detail</span></a>
                            @if($sm['editable'])
                                <a href="{{ route('kader.pemeriksaan.edit', $item->id) }}" class="pk-ib is-amber" title="Edit" aria-label="Edit"><i class="fa-solid fa-pen"></i><span>Edit</span></a>
                                <form action="{{ route('kader.pemeriksaan.destroy', $item->id) }}" method="POST" data-delete-form>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="pk-ib is-rose" title="Hapus" aria-label="Hapus"><i class="fa-solid fa-trash"></i><span>Hapus</span></button>
                                </form>
                            @else
                                <span class="pk-ib is-lock" title="Dikunci" aria-label="Dikunci"><i class="fa-solid fa-lock"></i><span>Dikunci</span></span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            @if(method_exists($pemeriksaans, 'hasPages') && $pemeriksaans->hasPages())
                @php
                    $cur = $pemeriksaans->currentPage();
                    $last = $pemeriksaans->lastPage();
                @endphp
                <nav class="pk-pager" aria-label="Halaman">
                    <p class="pk-label">Menampilkan {{ $pemeriksaans->firstItem() }}–{{ $pemeriksaans->lastItem() }} dari {{ number_format($totalData) }} data</p>
                    <div class="pk-pg">
                        @if($pemeriksaans->onFirstPage())
                            <span class="is-off"><i class="fa-solid fa-chevron-left"></i></span>
                        @else
                            <a href="{{ $pemeriksaans->previousPageUrl() }}" aria-label="Sebelumnya"><i class="fa-solid fa-chevron-left"></i></a>
                        @endif
                        @for($p = max(1, $cur - 2); $p <= min($last, $cur + 2); $p++)
                            @if($p === $cur)
                                <span class="is-on" aria-current="page">{{ $p }}</span>
                            @else
                                <a href="{{ $pemeriksaans->url($p) }}">{{ $p }}</a>
                            @endif
                        @endfor
                        @if($pemeriksaans->hasMorePages())
                            <a href="{{ $pemeriksaans->nextPageUrl() }}" aria-label="Berikutnya"><i class="fa-solid fa-chevron-right"></i></a>
                        @else
                            <span class="is-off"><i class="fa-solid fa-chevron-right"></i></span>
                        @endif
                    </div>
                </nav>
            @endif
        @else
            <div class="pk-empty">
                <span class="pk-ico tone-slate"><i class="fa-solid fa-folder-open"></i></span>
                <h3 class="pk-title">Belum ada data</h3>
                <p>{{ $mode === 'today' && $activeFilterCount === 0 ? 'Belum ada pengukuran hari ini. Buka tab Riwayat untuk melihat bulan lain, atau input pengukuran baru agar masuk antrean review Bidan.' : 'Tidak ada data yang cocok. Ubah filter atau input pengukuran baru.' }}</p>
            </div>
        @endif
    </section>
</div>

{{-- Dialog hapus --}}
<dialog id="pkDelete" class="pk-dialog" aria-labelledby="pkDeleteTitle">
    <div class="pk-dialog-box">
        <span class="pk-ico tone-rose"><i class="fa-solid fa-triangle-exclamation"></i></span>
        <h3 id="pkDeleteTitle" class="pk-title">Hapus data?</h3>
        <p>Data yang belum direview Bidan akan dihapus permanen.</p>
        <div>
            <button type="button" id="pkCancel" class="pk-btn pk-btn-line">Batal</button>
            <button type="button" id="pkConfirm" class="pk-btn pk-btn-danger"><i class="fa-solid fa-trash"></i> Hapus</button>
        </div>
    </div>
</dialog>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('monthPicker', (initial) => {
        const now = new Date(), cy = now.getFullYear(), cm = now.getMonth() + 1;
        let [y, m] = String(initial || '').split('-').map(Number);
        if (!y || !m || y > cy || (y === cy && m > cm)) { y = cy; m = cm; }

        return {
            open: false,
            months: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'],
            currentYear: cy, currentMonth: cm,
            year: y, selectedYear: y, selectedMonth: m,

            get selectedPeriod() { return `${this.selectedYear}-${String(this.selectedMonth).padStart(2, '0')}`; },
            get formattedPeriod() { return `${this.months[this.selectedMonth - 1]} ${this.selectedYear}`; },

            isFuture(mo) { return this.year > cy || (this.year === cy && mo > cm); },
            nextYear() { if (this.year < cy) this.year++; },

            pick(mo, yr = this.year) {
                this.year = yr;
                if (this.isFuture(mo)) return;
                this.selectedMonth = mo;
                this.selectedYear = yr;
                this.open = false;
                const r = document.querySelector('#filterForm input[name="mode"][value="history"]');
                if (r) r.checked = true;
                this.$nextTick(() => window.submitFilter());
            },
        };
    });
});

(function () {
    'use strict';

    const SWAP = ['hero-stats', 'stats', 'data-table-section'];
    let ctrl, timer;

    async function load(url) {
        if (ctrl) ctrl.abort();
        const mine = ctrl = new AbortController();
        const box = document.getElementById('data-table-section');
        if (box) box.classList.add('is-loading');

        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: mine.signal });
            if (!res.ok) throw new Error(res.status);
            const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
            SWAP.forEach((id) => {
                const a = document.getElementById(id), b = doc.getElementById(id);
                if (a && b) a.innerHTML = b.innerHTML;
            });
            history.pushState({}, '', url);
        } catch (e) {
            if (e.name !== 'AbortError') window.location.href = url;
        } finally {
            if (ctrl === mine && box) box.classList.remove('is-loading');
        }
    }

    window.submitFilter = function () {
        const form = document.getElementById('filterForm');
        if (!form) return;
        const p = new URLSearchParams(new FormData(form));
        [...p.keys()].forEach((k) => { if (!p.get(k)) p.delete(k); });
        if (p.get('mode') !== 'history') p.delete('periode');
        const url = new URL(form.action);
        url.search = p.toString();
        load(url);
    };

    document.addEventListener('input', (e) => {
        if (e.target.matches('#filterForm input[name="search"]')) {
            clearTimeout(timer);
            timer = setTimeout(window.submitFilter, 450);
        }
    });

    document.addEventListener('change', (e) => {
        if (!e.target.closest('#filterForm')) return;
        if (e.target.name === 'mode') {
            const w = document.getElementById('periodeWrapper');
            if (w) w.hidden = e.target.value !== 'history';
        }
        if (e.target.name === 'mode' || e.target.name === 'kategori') window.submitFilter();
    });

    document.addEventListener('submit', (e) => {
        if (e.target.id === 'filterForm') { e.preventDefault(); window.submitFilter(); }
    });

    document.addEventListener('click', (e) => {
        const a = e.target.closest('.pk-pager a');
        if (!a) return;
        e.preventDefault();
        load(new URL(a.href));
        const sec = document.getElementById('data-table-section');
        if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    window.addEventListener('popstate', () => window.location.reload());

    // Dialog hapus
    const dlg = document.getElementById('pkDelete');
    const ok = document.getElementById('pkConfirm');
    let target = null;

    document.addEventListener('submit', (e) => {
        const f = e.target.closest('[data-delete-form]');
        if (!f || !dlg) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        target = f;
        dlg.showModal();
    }, true);

    if (dlg) {
        dlg.addEventListener('click', (e) => { if (e.target === dlg) dlg.close(); });
        dlg.addEventListener('close', () => {
            target = null;
            ok.disabled = false;
            ok.innerHTML = '<i class="fa-solid fa-trash"></i> Hapus';
        });
        document.getElementById('pkCancel').addEventListener('click', () => dlg.close());
        ok.addEventListener('click', () => {
            if (!target) return dlg.close();
            ok.disabled = true;
            ok.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghapus...';
            HTMLFormElement.prototype.submit.call(target);
        });
    }
})();
</script>
@endpush