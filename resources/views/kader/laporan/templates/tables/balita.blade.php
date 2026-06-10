<table class="data-table">
    <thead>
        <tr>
            <th style="width: 4%;">No</th>
            <th style="width: 7%;">Tgl</th>
            <th style="width: 15%;">Nama Balita</th>
            <th style="width: 8%;">Usia</th>
            <th style="width: 14%;">Nama Ortu</th>
            <th style="width: 6%;">BB</th>
            <th style="width: 6%;">TB/PB</th>
            <th style="width: 6%;">LK</th>
            <th style="width: 6%;">LILA</th>
            <th style="width: 10%;">Status Gizi</th>
            <th style="width: 8%;">Imunisasi</th>
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
                <td>{{ $row['orang_tua'] ?? '-' }}</td>
                <td class="text-center">{{ $row['bb'] ?? '-' }}</td>
                <td class="text-center">{{ $row['tb'] ?? '-' }}</td>
                <td class="text-center">{{ $row['lk'] ?? '-' }}</td>
                <td class="text-center">{{ $row['lila'] ?? '-' }}</td>
                <td class="text-center">{{ $row['status_gizi'] ?? '-' }}</td>
                <td class="text-center">{{ $row['imunisasi'] ?? '-' }}</td>
                <td>{{ $row['keterangan'] ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="12" class="text-center">
                    Tidak ada data pemeriksaan Balita pada periode ini.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>