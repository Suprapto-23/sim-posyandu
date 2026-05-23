<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Laporan Posyandu' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            font-family: Arial, Helvetica, sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        .toolbar {
            max-width: 1180px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            border: 1px solid #dbeafe;
            background: #ffffff;
            color: #0369a1;
            padding: 10px 14px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
        }

        .btn.primary {
            border-color: #059669;
            background: #059669;
            color: #ffffff;
        }

        .page {
            max-width: 1180px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .08);
        }

        .header {
            padding: 28px 32px;
            border-bottom: 1px solid #e2e8f0;
            background: linear-gradient(135deg, #ecfdf5, #f8fafc 60%, #fffbeb);
        }

        .brand-row {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            align-items: flex-start;
        }

        .brand h1 {
            margin: 0;
            font-size: 23px;
            letter-spacing: -.02em;
            color: #0f172a;
        }

        .brand .subtitle {
            margin: 8px 0 0;
            font-size: 13px;
            color: #64748b;
            line-height: 1.6;
            max-width: 700px;
        }

        .brand .posyandu {
            margin: 0 0 10px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: #047857;
        }

        .meta {
            text-align: right;
            font-size: 12px;
            color: #475569;
            line-height: 1.8;
            min-width: 270px;
        }

        .meta strong {
            color: #0f172a;
        }

        .section {
            padding: 24px 32px;
        }

        .section + .section {
            border-top: 1px solid #e2e8f0;
        }

        .section-title {
            margin: 0 0 14px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .14em;
            color: #475569;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
        }

        .summary-card {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 16px;
            padding: 14px;
            min-height: 92px;
        }

        .summary-card .label {
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: #64748b;
        }

        .summary-card .value {
            margin-top: 8px;
            font-size: 26px;
            font-weight: 900;
            color: #0f172a;
        }

        .summary-card .note {
            margin-top: 4px;
            font-size: 11px;
            color: #64748b;
            line-height: 1.5;
        }

        .date-group {
            margin-top: 18px;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
        }

        .date-header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            padding: 13px 16px;
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
        }

        .date-header h3 {
            margin: 0;
            font-size: 13px;
            font-weight: 900;
            color: #0f172a;
        }

        .date-header span {
            font-size: 11px;
            font-weight: 800;
            color: #64748b;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 980px;
        }

        thead {
            background: #f8fafc;
        }

        th {
            padding: 11px 9px;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: .10em;
            color: #475569;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        td {
            padding: 10px 9px;
            font-size: 11.5px;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
            line-height: 1.55;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .empty {
            padding: 36px;
            text-align: center;
            color: #64748b;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 16px;
            font-size: 13px;
            font-weight: 700;
        }

        .notes {
            margin: 0;
            padding-left: 18px;
            color: #475569;
            font-size: 12px;
            line-height: 1.7;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 260px;
            gap: 24px;
            align-items: end;
        }

        .signature-box {
            text-align: center;
            font-size: 12px;
            color: #334155;
        }

        .signature-space {
            height: 72px;
        }

        .signature-name {
            font-weight: 800;
            color: #0f172a;
            border-top: 1px solid #94a3b8;
            padding-top: 8px;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .toolbar {
                display: none;
            }

            .page {
                max-width: none;
                border: none;
                border-radius: 0;
                box-shadow: none;
            }

            .header {
                background: #ffffff;
            }

            .section {
                page-break-inside: avoid;
                padding: 18px 22px;
            }

            .date-group {
                page-break-inside: avoid;
            }

            table {
                min-width: 100%;
            }

            th, td {
                font-size: 9px;
                padding: 7px 6px;
            }

            .summary-grid {
                grid-template-columns: repeat(5, 1fr);
            }

            .summary-card .value {
                font-size: 20px;
            }
        }

        @media (max-width: 900px) {
            body {
                padding: 12px;
            }

            .brand-row,
            .signature-grid {
                display: grid;
                grid-template-columns: 1fr;
            }

            .meta {
                text-align: left;
                min-width: 0;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .header,
            .section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('kader.laporan.index') }}" class="btn">Kembali</a>
        <button type="button" onclick="window.print()" class="btn primary">Cetak / Simpan PDF</button>
    </div>

    <main class="page">
        <header class="header">
            <div class="brand-row">
                <div class="brand">
                    <p class="posyandu">{{ $posyandu['nama'] ?? 'Posyandu' }}</p>
                    <h1>{{ $title ?? 'Laporan Pemeriksaan Posyandu' }}</h1>
                    <p class="subtitle">{{ $subtitle ?? 'Laporan pemeriksaan sasaran Posyandu.' }}</p>
                </div>

                <div class="meta">
                    <div><strong>Periode:</strong> {{ $periode['label'] ?? '-' }}</div>
                    <div><strong>Alamat:</strong> {{ $posyandu['alamat'] ?? '-' }}</div>
                    <div><strong>Dicetak oleh:</strong> {{ $dicetak_oleh ?? '-' }}</div>
                    <div><strong>Tanggal cetak:</strong> {{ isset($dicetak_pada) ? $dicetak_pada->translatedFormat('d F Y H:i') . ' WIB' : '-' }}</div>
                </div>
            </div>
        </header>

        <section class="section">
            <h2 class="section-title">Ringkasan Laporan</h2>

            @if(!empty($summary))
                <div class="summary-grid">
                    @foreach($summary as $item)
                        <div class="summary-card">
                            <div class="label">{{ $item['label'] ?? '-' }}</div>
                            <div class="value">{{ $item['value'] ?? 0 }}</div>
                            <div class="note">{{ $item['note'] ?? '' }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty">Belum ada ringkasan laporan.</div>
            @endif
        </section>

        <section class="section">
            <h2 class="section-title">Data Pemeriksaan</h2>

            @if(!empty($groups) && count($groups))
                @foreach($groups as $group)
                    <div class="date-group">
                        <div class="date-header">
                            <h3>Tanggal Pemeriksaan: {{ $group['date_label'] ?? '-' }}</h3>
                            <span>{{ count($group['rows'] ?? []) }} data</span>
                        </div>

                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        @foreach($columns as $column)
                                            <th>{{ $column }}</th>
                                        @endforeach
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach(($group['rows'] ?? []) as $row)
                                        <tr>
                                            @foreach($row as $cell)
                                                <td>{{ $cell }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty">
                    Tidak ada data pemeriksaan pada periode laporan yang dipilih.
                </div>
            @endif
        </section>

        @if(!empty($notes))
            <section class="section">
                <h2 class="section-title">Keterangan Kolom</h2>
                <ul class="notes">
                    @foreach($notes as $note)
                        <li>{{ $note }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="section">
            <div class="signature-grid">
                <div>
                    <h2 class="section-title">Validasi Laporan</h2>
                    <p style="margin: 0; color:#64748b; font-size:12px; line-height:1.7;">
                        Laporan ini dihasilkan berdasarkan data pemeriksaan yang tercatat pada sistem PosyanduCare sesuai periode yang dipilih.
                    </p>
                </div>

                <div class="signature-box">
                    <div>Kader Posyandu</div>
                    <div class="signature-space"></div>
                    <div class="signature-name">{{ $dicetak_oleh ?? 'Kader Posyandu' }}</div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>