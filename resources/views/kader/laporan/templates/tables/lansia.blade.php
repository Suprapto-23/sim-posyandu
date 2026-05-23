<table class="data-table">
    <thead>
        <tr>
            <th style="width: 26px;">No</th>
            <th style="width: 105px;">Nama Lansia</th>
            <th style="width: 65px;">Usia</th>
            <th style="width: 85px;">Kemandirian</th>
            <th style="width: 42px;">BB</th>
            <th style="width: 42px;">TB</th>
            <th style="width: 40px;">IMT</th>
            <th style="width: 42px;">LP</th>
            <th style="width: 50px;">TD</th>
            <th style="width: 42px;">GDS</th>
            <th style="width: 60px;">Kolesterol</th>
            <th style="width: 55px;">Asam Urat</th>
            <th>Riwayat / Keluhan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <tr>
                <td class="center">{{ $row['no'] }}</td>
                <td class="bold">{{ $row['nama'] }}</td>
                <td>{{ $row['usia'] }}</td>
                <td>{{ $row['kemandirian'] }}</td>
                <td class="center">{{ $row['bb'] }}</td>
                <td class="center">{{ $row['tb'] }}</td>
                <td class="center">{{ $row['imt'] }}</td>
                <td class="center">{{ $row['lp'] }}</td>
                <td class="center">{{ $row['td'] }}</td>
                <td class="center">{{ $row['gds'] }}</td>
                <td class="center">{{ $row['kolesterol'] }}</td>
                <td class="center">{{ $row['asam_urat'] }}</td>
                <td>{{ $row['riwayat_keluhan'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>