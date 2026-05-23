<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Laporan Pemeriksaan Posyandu' }}</title>

    <style>
        @page {
            margin: 18px 22px 22px 22px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #111827;
            line-height: 1.45;
        }

        .kop {
            text-align: center;
            border-bottom: 2px solid #111827;
            padding-bottom: 8px;
            margin-bottom: 4px;
        }

        .kop .instansi {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .kop .nama-posyandu {
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .kop .alamat {
            font-size: 9.5px;
            font-style: italic;
            color: #374151;
            margin-top: 3px;
        }

        .garis-tipis {
            border-bottom: 1px solid #111827;
            margin-bottom: 12px;
        }

        .judul {
            text-align: center;
            margin: 12px 0 10px;
        }

        .judul h1 {
            margin: 0;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .judul .subjudul {
            margin-top: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #374151;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 12px;
        }

        .info-table td {
            padding: 3px 0;
            font-size: 9.5px;
            vertical-align: top;
        }

        .info-table .label {
            width: 95px;
            font-weight: bold;
            color: #111827;
        }

        .info-table .separator {
            width: 10px;
            text-align: center;
        }

        .info-table .right-label {
            width: 95px;
            font-weight: bold;
            color: #111827;
            padding-left: 30px;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin: 12px 0 7px;
            color: #111827;
        }

        .date-label {
            font-size: 10px;
            font-weight: bold;
            margin: 10px 0 5px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: auto;
        }

        table.data-table thead {
            display: table-header-group;
        }

        table.data-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        table.data-table th {
            border: 1px solid #4b5563;
            background: #e5e7eb;
            color: #111827;
            padding: 5px 4px;
            font-size: 7.5px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            vertical-align: middle;
        }

        table.data-table td {
            border: 1px solid #4b5563;
            padding: 5px 4px;
            font-size: 8px;
            vertical-align: top;
        }

        table.data-table td.center {
            text-align: center;
        }

        table.data-table td.bold {
            font-weight: bold;
        }

        .empty {
            border: 1px solid #6b7280;
            padding: 18px;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            color: #374151;
            margin-top: 8px;
        }

        .notes {
            margin-top: 12px;
            font-size: 8.5px;
            color: #374151;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .notes ul {
            margin: 0;
            padding-left: 14px;
        }

        .notes li {
            margin-bottom: 2px;
        }

        .signature-table {
            width: 100%;
            margin-top: 28px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 10px;
            padding: 0 20px;
        }

        .signature-space {
            height: 62px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .nip {
            margin-top: 2px;
            font-size: 9px;
        }

        .footer {
            position: fixed;
            bottom: -8px;
            left: 0;
            right: 0;
            font-size: 7.5px;
            color: #6b7280;
            text-align: center;
        }

        .page-number:after {
            content: counter(page);
        }
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('id');

        $totalRows = 0;

        foreach (($groups ?? []) as $group) {
            $totalRows += count($group['rows'] ?? []);
        }

        $tanggalCetak = isset($dicetak_pada)
            ? $dicetak_pada->translatedFormat('d F Y')
            : now('Asia/Jakarta')->translatedFormat('d F Y');

        $waktuCetak = isset($dicetak_pada)
            ? $dicetak_pada->translatedFormat('d F Y H:i') . ' WIB'
            : now('Asia/Jakarta')->translatedFormat('d F Y H:i') . ' WIB';

        $namaPosyandu = $posyandu['nama'] ?? 'Posyandu Desa Bantar Kulon';
        $alamatPosyandu = $posyandu['alamat'] ?? 'Desa Bantar Kulon, Kecamatan Lebakbarang, Kabupaten Pekalongan';
    @endphp

    <div class="kop">
        <div class="instansi">Pemerintah Kabupaten Pekalongan</div>
        <div class="nama-posyandu">{{ $namaPosyandu }}</div>
        <div class="alamat">{{ $alamatPosyandu }}</div>
    </div>
    <div class="garis-tipis"></div>

    <div class="judul">
        <h1>{{ $title ?? 'Laporan Pemeriksaan Posyandu' }}</h1>
        <div class="subjudul">
            Periode {{ $periode['label'] ?? '-' }}
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Jenis Laporan</td>
            <td class="separator">:</td>
            <td>{{ $label ?? '-' }}</td>

            <td class="right-label">Dibuat Tanggal</td>
            <td class="separator">:</td>
            <td>{{ $tanggalCetak }}</td>
        </tr>
        <tr>
            <td class="label">Total Data</td>
            <td class="separator">:</td>
            <td>{{ $totalRows }} data pemeriksaan</td>

            <td class="right-label">Dicetak Oleh</td>
            <td class="separator">:</td>
            <td>{{ $dicetak_oleh ?? 'Kader Posyandu' }}</td>
        </tr>
        <tr>
            <td class="label">Waktu Cetak</td>
            <td class="separator">:</td>
            <td>{{ $waktuCetak }}</td>

            <td class="right-label">Sistem</td>
            <td class="separator">:</td>
            <td>PosyanduCare</td>
        </tr>
    </table>

    <div class="section-title">Data Pemeriksaan</div>

    @if(!empty($groups) && count($groups))
        @foreach($groups as $group)
            <div class="date-label">
                Tanggal Pemeriksaan: {{ $group['date_label'] ?? '-' }}
                <span style="font-weight: normal;">({{ count($group['rows'] ?? []) }} data)</span>
            </div>

            @include('kader.laporan.templates.tables.' . $jenis_laporan, [
                'rows' => $group['rows'] ?? []
            ])
        @endforeach
    @else
        <div class="empty">
            Tidak ada data pemeriksaan pada periode laporan yang dipilih.
        </div>
    @endif

    @if(!empty($notes))
        <div class="notes">
            <div class="notes-title">Keterangan</div>
            <ul>
                @foreach($notes as $note)
                    <li>{{ $note }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <table class="signature-table">
        <tr>
            <td>
                <div>Mengetahui,</div>
                <div>Kepala Desa Bantar Kulon</div>
                <div class="signature-space"></div>
                <div class="signature-name">( ........................................ )</div>
                <div class="nip">NIP. ........................................</div>
            </td>

            <td>
                <div>Bantar Kulon, {{ $tanggalCetak }}</div>
                <div>Kader Posyandu</div>
                <div class="signature-space"></div>
                <div class="signature-name">( {{ $dicetak_oleh ?? 'Kader Posyandu' }} )</div>
                <div class="nip">NIP. ........................................</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dokumen ini dihasilkan otomatis oleh sistem PosyanduCare | Halaman <span class="page-number"></span>
    </div>
</body>
</html>