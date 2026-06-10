<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%;">No</th>
            <th style="width: 7%;">Tgl</th>
            <th style="width: 13%;">Nama Lansia</th>
            <th style="width: 7%;">Usia</th>
            <th style="width: 9%;">Kemandirian</th>
            <th style="width: 5%;">BB</th>
            <th style="width: 5%;">TB</th>
            <th style="width: 5%;">IMT</th>
            <th style="width: 5%;">LP</th>
            <th style="width: 7%;">TD</th>
            <th style="width: 6%;">GDS</th>
            <th style="width: 6%;">Koles</th>
            <th style="width: 6%;">Asam Urat</th>
            <th style="width: 15%;">Riwayat / Keluhan</th>
        </tr>
    </thead>

    <tbody>
        @forelse($rows as $row)
            <tr>
                <td class="text-center">{{ $row['no'] ?? '-' }}</td>
                <td class="text-center">{{ $row['tanggal'] ?? '-' }}</td>
                <td class="font-bold">{{ $row['nama'] ?? '-' }}</td>
                <td class="text-center">{{ $row['usia'] ?? '-' }}</td>
                <td class="text-center">{{ $row['kemandirian'] ?? '-' }}</td>
                <td class="text-center">{{ $row['bb'] ?? '-' }}</td>
                <td class="text-center">{{ $row['tb'] ?? '-' }}</td>
                <td class="text-center">{{ $row['imt'] ?? '-' }}</td>
                <td class="text-center">{{ $row['lp'] ?? '-' }}</td>
                <td class="text-center">{{ $row['td'] ?? '-' }}</td>
                <td class="text-center">{{ $row['gds'] ?? '-' }}</td>
                <td class="text-center">{{ $row['kolesterol'] ?? '-' }}</td>
                <td class="text-center">{{ $row['asam_urat'] ?? '-' }}</td>
                <td>{{ $row['riwayat_keluhan'] ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="14" class="text-center">
                    Tidak ada data pemeriksaan Lansia pada periode ini.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>