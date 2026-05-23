<table class="data-table">
    <thead>
        <tr>
            <th style="width: 26px;">No</th>
            <th style="width: 115px;">Nama Balita</th>
            <th style="width: 70px;">Usia</th>
            <th style="width: 120px;">Orang Tua</th>
            <th style="width: 48px;">BB</th>
            <th style="width: 52px;">TB/PB</th>
            <th style="width: 45px;">LK</th>
            <th style="width: 45px;">LILA</th>
            <th style="width: 80px;">Status Gizi</th>
            <th style="width: 110px;">Imunisasi Terakhir</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <tr>
                <td class="center">{{ $row['no'] }}</td>
                <td class="bold">{{ $row['nama'] }}</td>
                <td>{{ $row['usia'] }}</td>
                <td>{{ $row['orang_tua'] }}</td>
                <td class="center">{{ $row['bb'] }}</td>
                <td class="center">{{ $row['tb'] }}</td>
                <td class="center">{{ $row['lk'] }}</td>
                <td class="center">{{ $row['lila'] }}</td>
                <td class="center">{{ $row['status_gizi'] }}</td>
                <td>{{ $row['imunisasi'] }}</td>
                <td>{{ $row['keterangan'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>