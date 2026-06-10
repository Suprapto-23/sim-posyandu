<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pemeriksaan {{ ucfirst($jenis_laporan) }}</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 18px 22px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #111827;
            line-height: 1.35;
            margin: 0;
        }

        .kop {
            text-align: center;
            border-bottom: 2px solid #111827;
            padding-bottom: 7px;
            margin-bottom: 12px;
        }

        .kop h1 {
            margin: 0 0 3px;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 1.8px;
            text-transform: uppercase;
        }

        .kop p {
            margin: 0;
            font-size: 9px;
            color: #374151;
        }

        .judul {
            text-align: center;
            margin-bottom: 12px;
        }

        .judul h2 {
            margin: 0 0 4px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .7px;
        }

        .judul p {
            margin: 0;
            color: #059669;
            font-size: 9px;
            font-weight: 700;
        }

        .meta {
            width: 100%;
            margin-bottom: 8px;
            border-collapse: collapse;
        }

        .meta td {
            padding: 0 0 3px;
            border: none;
            font-size: 8.5px;
            color: #374151;
        }

        .meta .right {
            text-align: right;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: auto;
        }

        table.data-table thead {
            display: table-header-group;
        }

        table.data-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #6b7280;
            padding: 4px 3px;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        table.data-table th {
            background: #e5e7eb;
            color: #111827;
            font-size: 7.5px;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
        }

        table.data-table td {
            font-size: 8px;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .font-bold {
            font-weight: 700;
        }

        .empty {
            margin-top: 16px;
            padding: 28px 16px;
            border: 1px dashed #9ca3af;
            text-align: center;
            color: #6b7280;
            font-weight: 700;
        }

        .footer {
            width: 100%;
            margin-top: 28px;
            page-break-inside: avoid;
        }

        .ttd {
            width: 230px;
            float: right;
            text-align: center;
            color: #111827;
        }

        .ttd .date {
            margin: 0 0 46px;
        }

        .ttd .name {
            margin: 0;
            padding-bottom: 2px;
            border-bottom: 1px solid #111827;
            font-weight: 800;
        }

        .ttd .role {
            margin: 3px 0 0;
            color: #4b5563;
            font-size: 8px;
        }

        .notes {
            margin-top: 10px;
            color: #4b5563;
            font-size: 8px;
        }

        .notes span {
            display: inline-block;
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <div class="kop">
        <h1>{{ $posyandu['nama'] ?? 'Posyandu Desa Bantarkulon' }}</h1>
        <p>{{ $posyandu['alamat'] ?? '-' }} | Telepon: {{ $posyandu['telepon'] ?? '-' }}</p>
    </div>

    <div class="judul">
        <h2>Laporan Pemeriksaan {{ ucfirst($jenis_laporan) }}</h2>
        <p>Periode: {{ $periode['label'] ?? '-' }}</p>
    </div>

    <table class="meta">
        <tr>
            <td>
                Total Pemeriksaan:
                <strong>{{ collect($summary ?? [])->firstWhere('label', 'Pemeriksaan')['value'] ?? count($rows ?? []) }}</strong>
                data
            </td>
            <td class="right">
                Dicetak: {{ isset($dicetak_pada) ? $dicetak_pada->translatedFormat('d F Y') : '-' }}
            </td>
        </tr>
    </table>

    @if(isset($rows) && count($rows) > 0)
        @include("kader.laporan.templates.tables.{$jenis_laporan}", ['rows' => $rows])

        @if(!empty($notes))
            <div class="notes">
                @foreach($notes as $note)
                    <span>{{ $note }}</span>
                @endforeach
            </div>
        @endif
    @else
        <div class="empty">
            Tidak ada rekapitulasi data pada periode ini.
        </div>
    @endif

    <div class="footer">
        <div class="ttd">
            <p class="date">Dicetak pada, {{ isset($dicetak_pada) ? $dicetak_pada->translatedFormat('d F Y') : '-' }}</p>
            <p class="name">{{ $dicetak_oleh ?? 'Kader Posyandu' }}</p>
            <p class="role">Otoritas Kader Posyandu</p>
        </div>
    </div>
</body>
</html>