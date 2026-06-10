<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%;">No</th>
            <th style="width: 7%;">Tgl</th>
            <th style="width: 15%;">Nama Remaja</th>
            <th style="width: 8%;">Usia</th>
            <th style="width: 13%;">Sekolah / Kelas</th>
            <th style="width: 6%;">BB</th>
            <th style="width: 6%;">TB</th>
            <th style="width: 6%;">IMT</th>
            <th style="width: 6%;">LP</th>
            <th style="width: 6%;">LILA</th>
            <th style="width: 7%;">TD</th>
            <th style="width: 6%;">GDS</th>
            <th style="width: 10%;">Keterangan</th>
        </tr>
    </thead>

    <tbody>
        @forelse($rows as $row)
            <tr>
                <td class="text-center">{{ $row['no'] ?? '-' }}</td>
                <td class="text-center">{{ $row['tanggal'] ?? '-' }}</td>
                <td class="font-bold">{{ $row['nama'] ?? '-' }}</td>
                <td class="text-center">{{ $row['usia'] ?? '-' }}</td>
                <td>{{ $row['sekolah_kelas'] ?? '-' }}</td>
                <td class="text-center">{{ $row['bb'] ?? '-' }}</td>
                <td class="text-center">{{ $row['tb'] ?? '-' }}</td>
                <td class="text-center">{{ $row['imt'] ?? '-' }}</td>
                <td class="text-center">{{ $row['lp'] ?? '-' }}</td>
                <td class="text-center">{{ $row['lila'] ?? '-' }}</td>
                <td class="text-center">{{ $row['td'] ?? '-' }}</td>
                <td class="text-center">{{ $row['gds'] ?? '-' }}</td>
                <td>{{ $row['keterangan'] ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="13" class="text-center">
                    Tidak ada data pemeriksaan Remaja pada periode ini.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>