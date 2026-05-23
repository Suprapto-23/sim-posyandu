<?php

namespace App\Imports;

use App\Models\Remaja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class RemajaImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithBatchInserts, WithChunkReading
{
    use RemembersRowNumber;

    public function model(array $row)
    {
        $rowNumber = $this->getRowNumber();

        $nik = $this->cleanNik($row['nik'] ?? null, $rowNumber);
        $namaLengkap = $this->cleanText($row['nama_lengkap'] ?? null);

        if ($namaLengkap === '') {
            throw new \RuntimeException("Baris {$rowNumber}: nama_lengkap wajib diisi.");
        }

        if (Remaja::where('nik', $nik)->exists()) {
            return null;
        }

        $jenisKelamin = $this->normalizeGender($row['jenis_kelamin'] ?? null, $rowNumber);
        $tanggalLahir = $this->parseDate($row['tanggal_lahir'] ?? null, $rowNumber);

        $linkedUser = $this->findLinkedUser($nik);

        $data = [
            'user_id' => $linkedUser?->id,
            'kode_remaja' => $this->generateKodeRemaja(),
            'nik' => $nik,
            'nama_lengkap' => $namaLengkap,
            'jenis_kelamin' => $jenisKelamin,
            'tempat_lahir' => $this->cleanText($row['tempat_lahir'] ?? null) ?: '-',
            'tanggal_lahir' => $tanggalLahir,
            'sekolah' => $this->cleanText($row['nama_sekolah'] ?? null) ?: null,
            'nama_ortu' => $this->cleanText($row['nama_ortu'] ?? null) ?: null,
            'telepon_ortu' => $this->cleanPhone($row['no_hp_ortu'] ?? null),
            'alamat' => $this->cleanText($row['alamat_lengkap'] ?? null) ?: '-',
            'created_by' => auth()->id(),
        ];

        if (Schema::hasColumn('remajas', 'kelas')) {
            $data['kelas'] = $this->cleanText($row['kelas'] ?? null) ?: null;
        }

        return new Remaja($data);
    }

    public function headingRow(): int
    {
        return 3;
    }

    public function batchSize(): int
    {
        return 200;
    }

    public function chunkSize(): int
    {
        return 200;
    }

    private function cleanText($value): string
    {
        return trim((string) $value);
    }

    private function cleanNik($value, int $rowNumber): string
    {
        if ($value === null || $value === '') {
            throw new \RuntimeException("Baris {$rowNumber}: NIK wajib diisi.");
        }

        if (is_int($value) || is_float($value)) {
            $value = number_format($value, 0, '', '');
        }

        $value = trim((string) $value);

        if (stripos($value, 'e+') !== false || stripos($value, 'e-') !== false) {
            $value = sprintf('%.0f', (float) $value);
        }

        $nik = preg_replace('/[^0-9]/', '', $value);

        if (strlen($nik) !== 16) {
            throw new \RuntimeException("Baris {$rowNumber}: NIK harus 16 digit angka. Nilai terbaca: {$value}");
        }

        return $nik;
    }

    private function cleanPhone($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            $value = number_format($value, 0, '', '');
        }

        $phone = preg_replace('/[^0-9+]/', '', trim((string) $value));

        return $phone !== '' ? $phone : null;
    }

    private function normalizeGender($value, int $rowNumber): string
    {
        $value = strtolower(trim((string) $value));

        return match ($value) {
            'l', 'laki-laki', 'laki laki', 'laki', 'pria', 'cowok' => 'L',
            'p', 'perempuan', 'wanita', 'cewek' => 'P',
            default => throw new \RuntimeException("Baris {$rowNumber}: jenis_kelamin wajib L atau P."),
        };
    }

    private function parseDate($value, int $rowNumber): ?string
    {
        if ($value === null || $value === '') {
            throw new \RuntimeException("Baris {$rowNumber}: tanggal_lahir wajib diisi.");
        }

        try {
            if (is_numeric($value)) {
                $date = Date::excelToDateTimeObject($value);
                return Carbon::instance($date)->format('Y-m-d');
            }

            $value = trim((string) $value);
            $value = str_replace('/', '-', $value);

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                $date = Carbon::createFromFormat('Y-m-d', $value);
            } elseif (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $value)) {
                $date = Carbon::createFromFormat('d-m-Y', $value);
            } else {
                throw new \RuntimeException("Format tanggal tidak dikenali.");
            }

            if ($date->isFuture()) {
                throw new \RuntimeException("Tanggal lahir tidak boleh melebihi hari ini.");
            }

            return $date->format('Y-m-d');
        } catch (\Throwable $e) {
            throw new \RuntimeException("Baris {$rowNumber}: tanggal_lahir tidak valid. Gunakan format YYYY-MM-DD, contoh 2010-08-20.");
        }
    }

    private function generateKodeRemaja(): string
    {
        do {
            $kode = 'RMJ-' . now('Asia/Jakarta')->format('ymd') . '-' . random_int(1000, 9999);
        } while (Remaja::where('kode_remaja', $kode)->exists());

        return $kode;
    }

    private function findLinkedUser(string $nik): ?User
{
    $query = User::query();

    $query->where(function ($q) use ($nik) {
        if (Schema::hasColumn('users', 'nik')) {
            $q->where('nik', $nik);
        }

        if (Schema::hasColumn('users', 'username')) {
            $q->orWhere('username', $nik);
        }

        if (Schema::hasColumn('users', 'email')) {
            $q->orWhere('email', $nik);
        }
    });

    $user = $query->first();

    if ($user) {
        return $user;
    }

    if (Schema::hasTable('profiles')) {
        $profileQuery = DB::table('profiles');

        $profileQuery->where(function ($q) use ($nik) {
            if (Schema::hasColumn('profiles', 'nik')) {
                $q->where('nik', $nik);
            }

            if (Schema::hasColumn('profiles', 'no_ktp')) {
                $q->orWhere('no_ktp', $nik);
            }
        });

        $profile = $profileQuery->first();

        if ($profile && isset($profile->user_id)) {
            return User::find($profile->user_id);
        }
    }

    return null;
}
}