<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Preview Laporan {{ ucfirst($jenis_laporan ?? 'Posyandu') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        :root {
            --emerald: #059669;
            --emerald-dark: #047857;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--slate-900);
            background:
                radial-gradient(circle at 10% 0%, rgba(16, 185, 129, .18), transparent 32%),
                radial-gradient(circle at 90% 18%, rgba(14, 165, 233, .15), transparent 34%),
                linear-gradient(135deg, #ecfdf5 0%, #f8fafc 48%, #eff6ff 100%);
        }

        .page {
            max-width: 1280px;
            margin: 0 auto;
            padding: 26px 22px 88px;
        }

        .toolbar {
            position: sticky;
            top: 16px;
            z-index: 30;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
            padding: 12px;
            border-radius: 26px;
            background: rgba(255, 255, 255, .86);
            border: 1px solid rgba(15, 23, 42, .08);
            box-shadow: 0 22px 55px -38px rgba(15, 23, 42, .55);
            backdrop-filter: blur(18px);
        }

        .toolbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .toolbar-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 18px;
            color: var(--emerald-dark);
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
        }

        .toolbar-kicker {
            margin: 0 0 4px;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--emerald-dark);
        }

        .toolbar-title {
            margin: 0;
            font-size: 16px;
            font-weight: 900;
            color: var(--slate-900);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 15px;
            border-radius: 14px;
            border: 0;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
            text-decoration: none;
            transition: .18s ease;
        }

        .btn:active { transform: scale(.97); }

        .btn-soft {
            color: var(--slate-700);
            background: #ffffff;
            border: 1px solid var(--slate-200);
            box-shadow: 0 10px 22px -18px rgba(15, 23, 42, .55);
        }

        .btn-soft:hover { background: var(--slate-50); }

        .btn-primary {
            color: #ffffff;
            background: var(--emerald);
            box-shadow: 0 14px 26px -18px rgba(5, 150, 105, .95);
        }

        .btn-primary:hover { background: var(--emerald-dark); }

        .paper {
            width: 100%;
            max-width: 1120px;
            min-height: 720px;
            margin: 0 auto;
            padding: 42px;
            border-radius: 30px;
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, .08);
            box-shadow: 0 34px 90px -60px rgba(15, 23, 42, .65);
        }

        .kop {
            text-align: center;
            padding-bottom: 16px;
            border-bottom: 3px solid var(--slate-900);
        }

        .kop h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: .24em;
            text-transform: uppercase;
            color: var(--slate-900);
        }

        .kop p {
            margin: 7px 0 0;
            font-size: 12px;
            font-weight: 700;
            color: var(--slate-600);
        }

        .heading {
            margin: 30px 0 26px;
            text-align: center;
        }

        .heading h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: .10em;
            text-transform: uppercase;
            color: var(--slate-900);
        }

        .period-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
            padding: 7px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
            color: var(--emerald-dark);
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
            border-radius: 18px;
            border: 1px solid var(--slate-200);
            background: #ffffff;
        }

        table.data-table {
            width: 100%;
            min-width: 980px;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 11px;
        }

        table.data-table th,
        table.data-table td {
            padding: 10px 8px;
            border-right: 1px solid var(--slate-200);
            border-bottom: 1px solid var(--slate-200);
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        table.data-table th:last-child,
        table.data-table td:last-child { border-right: 0; }

        table.data-table tr:last-child td { border-bottom: 0; }

        table.data-table th {
            text-align: center;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--slate-700);
            background: linear-gradient(180deg, #f8fafc, #ecfdf5);
        }

        table.data-table td {
            color: var(--slate-800);
            background: #ffffff;
        }

        table.data-table tr:nth-child(even) td { background: #fbfdfc; }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: 800; }

        .empty-state {
            min-height: 235px;
            display: grid;
            place-items: center;
            padding: 34px;
            border-radius: 22px;
            border: 1.5px dashed var(--slate-300);
            background:
                radial-gradient(circle at top, rgba(16, 185, 129, .12), transparent 42%),
                linear-gradient(135deg, #f8fafc, #ffffff);
            text-align: center;
        }

        .empty-icon {
            width: 62px;
            height: 62px;
            display: grid;
            place-items: center;
            margin: 0 auto 14px;
            border-radius: 22px;
            color: var(--emerald-dark);
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
        }

        .empty-state h3 {
            margin: 0;
            font-size: 15px;
            font-weight: 900;
            color: var(--slate-900);
        }

        .empty-state p {
            max-width: 460px;
            margin: 8px auto 0;
            font-size: 12px;
            font-weight: 650;
            line-height: 1.7;
            color: var(--slate-500);
        }

        .signature {
            display: flex;
            justify-content: flex-end;
            margin-top: 48px;
        }

        .signature-box {
            width: 260px;
            text-align: center;
        }

        .signature-date {
            margin: 0 0 72px;
            font-size: 13px;
            font-weight: 650;
            color: var(--slate-800);
        }

        .signature-name {
            margin: 0;
            padding-bottom: 5px;
            border-bottom: 1px solid var(--slate-900);
            font-size: 13px;
            font-weight: 900;
            color: var(--slate-900);
        }

        .signature-role {
            margin: 5px 0 0;
            font-size: 11px;
            font-weight: 700;
            color: var(--slate-500);
        }

        .download-form {
            margin: 0;
        }

        @media (max-width: 820px) {
            .page { padding: 16px 12px 72px; }

            .toolbar {
                align-items: stretch;
                flex-direction: column;
                border-radius: 22px;
            }

            .toolbar-actions { justify-content: stretch; }

            .btn { flex: 1; }

            .paper {
                padding: 26px 18px;
                border-radius: 24px;
            }

            .kop h1 {
                font-size: 16px;
                letter-spacing: .16em;
            }

            .heading h2 { font-size: 15px; }
        }

        @media print {
            body { background: #ffffff; }

            .no-print { display: none !important; }

            .page {
                max-width: none;
                padding: 0;
            }

            .paper {
                max-width: none;
                min-height: auto;
                padding: 0;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .table-wrap {
                overflow: visible;
                border-radius: 0;
            }

            table.data-table {
                min-width: 0;
                font-size: 9px;
            }

            table.data-table th,
            table.data-table td {
                padding: 6px 4px;
            }
        }
    </style>
</head>

<body>
@php
    $jenisLabel = match ($jenis_laporan ?? '') {
        'balita' => 'Balita',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
        default => ucfirst($jenis_laporan ?? 'Posyandu'),
    };

    $tanggalCetak = isset($dicetak_pada) ? $dicetak_pada->translatedFormat('d F Y') : '-';
    $judulLaporan = $title ?? 'Laporan Pemeriksaan ' . $jenisLabel;

    $periodeBulan = request('periode_bulan');
    $periodeTahun = request('periode_tahun');
@endphp

<main class="page">
    <div class="toolbar no-print">
        <div class="toolbar-left">
            <div class="toolbar-icon">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <path d="M14 2v6h6"></path>
                    <path d="M9 13h6"></path>
                    <path d="M9 17h6"></path>
                    <path d="M9 9h2"></path>
                </svg>
            </div>

            <div>
                <p class="toolbar-kicker">Preview Laporan</p>
                <p class="toolbar-title">{{ $judulLaporan }}</p>
            </div>
        </div>

        <div class="toolbar-actions">
            <a href="{{ route('kader.laporan.index') }}" class="btn btn-soft">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M19 12H5"></path>
                    <path d="m12 19-7-7 7-7"></path>
                </svg>
                Kembali
            </a>

            <button type="button" onclick="window.print()" class="btn btn-soft">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M6 9V3h12v6"></path>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <path d="M6 14h12v7H6z"></path>
                </svg>
                Print
            </button>

            <form action="{{ route('kader.laporan.preview') }}" method="POST" class="download-form">
                @csrf
                <input type="hidden" name="jenis_laporan" value="{{ $jenis_laporan }}">
                <input type="hidden" name="mode" value="download">

                @if($periodeBulan)
                    <input type="hidden" name="periode_bulan" value="{{ $periodeBulan }}">
                @endif

                @if($periodeTahun)
                    <input type="hidden" name="periode_tahun" value="{{ $periodeTahun }}">
                @endif

                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M12 3v12"></path>
                        <path d="m7 10 5 5 5-5"></path>
                        <path d="M5 21h14"></path>
                    </svg>
                    Unduh PDF
                </button>
            </form>
        </div>
    </div>

    <section class="paper">
        <header class="kop">
            <h1>{{ $posyandu['nama'] ?? 'Posyandu Desa Bantarkulon' }}</h1>
            <p>{{ $posyandu['alamat'] ?? '-' }} | Telepon: {{ $posyandu['telepon'] ?? '-' }}</p>
        </header>

        <section class="heading">
            <h2>Laporan Pemeriksaan {{ $jenisLabel }}</h2>

            <div class="period-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <rect x="3" y="4" width="18" height="18" rx="3"></rect>
                    <path d="M16 2v4M8 2v4M3 10h18"></path>
                </svg>
                Periode: {{ $periode['label'] ?? '-' }}
            </div>
        </section>

        @if(isset($rows) && count($rows) > 0)
            <div class="table-wrap">
                @include("kader.laporan.templates.tables.{$jenis_laporan}", ['rows' => $rows])
            </div>
        @else
            <div class="empty-state">
                <div>
                    <div class="empty-icon">
                        <svg width="29" height="29" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <path d="M9 13h6"></path>
                        </svg>
                    </div>

                    <h3>Tidak ada rekapitulasi data pada periode ini.</h3>
                    <p>
                        Data pemeriksaan untuk kategori {{ $jenisLabel }} pada {{ $periode['label'] ?? 'periode yang dipilih' }}
                        belum tersedia. Cek kembali kategori atau periode laporan dari halaman arsip.
                    </p>
                </div>
            </div>
        @endif

        <footer class="signature">
            <div class="signature-box">
                <p class="signature-date">Dicetak pada, {{ $tanggalCetak }}</p>
                <p class="signature-name">{{ $dicetak_oleh ?? 'Kader Posyandu' }}</p>
                <p class="signature-role">Kader Posyandu</p>
            </div>
        </footer>
    </section>
</main>
</body>
</html>