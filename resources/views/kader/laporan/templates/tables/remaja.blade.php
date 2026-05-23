<table class="data-table">
    <thead>
        <tr>
            <th style="width: 26px;">No</th>
            <th style="width: 115px;">Nama Remaja</th>
            <th style="width: 70px;">Usia</th>
            <th style="width: 115px;">Sekolah / Kelas</th>
            <th style="width: 48px;">BB</th>
            <th style="width: 48px;">TB</th>
            <th style="width: 42px;">IMT</th>
            <th style="width: 42px;">LP</th>
            <th style="width: 42px;">LILA</th>
            <th style="width: 52px;">TD</th>
            <th style="width: 45px;">GDS</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <tr>
                <td class="center">{{ $row['no'] }}</td>
                <td class="bold">{{ $row['nama'] }}</td>
                <td>{{ $row['usia'] }}</td>
                <td>{{ $row['sekolah_kelas'] }}</td>
                <td class="center">{{ $row['bb'] }}</td>
                <td class="center">{{ $row['tb'] }}</td>
                <td class="center">{{ $row['imt'] }}</td>
                <td class="center">{{ $row['lp'] }}</td>
                <td class="center">{{ $row['lila'] }}</td>
                <td class="center">{{ $row['td'] }}</td>
                <td class="center">{{ $row['gds'] }}</td>
                <td>{{ $row['keterangan'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>