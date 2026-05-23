<?php

namespace App\Imports;

use App\Models\Balita;
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

class BalitaImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithBatchInserts, WithChunkReading
{
    use RemembersRowNumber;

    public function model(array $row)
    {
        $rowNumber = $this->getRowNumber();

        $nikBalita = $this->cleanNik($row['nik_balita'] ?? null, $rowNumber, 'NIK balita');
        $namaLengkap = $this->cleanText($row['nama_lengkap'] ?? null);

        if ($namaLengkap === '') {
            throw new \RuntimeException("Baris {$rowNumber}: nama_lengkap wajib diisi.");
        }

        if (Balita::where('nik', $nikBalita)->exists()) {
            return null;
        }

        $jenisKelamin = $this->normalizeGender($row['jenis_kelamin'] ?? null, $rowNumber);
        $tanggalLahir = $this->parseDate($row['tanggal_lahir'] ?? null, $rowNumber);

        $namaIbu = $this->cleanText($row['nama_ibu'] ?? null);
        $nikIbu = $this->cleanOptionalNik($row['nik_ibu'] ?? null, $rowNumber, 'NIK ibu');

        if ($namaIbu === '') {
            throw new \RuntimeException("Baris {$rowNumber}: nama_ibu wajib diisi.");
        }

        $linkedUser = $this->findLinkedUser($nikIbu, $namaIbu);

        $data = [
            'kode_balita' => $this->generateKodeBalita(),
            'nik' => $nikBalita,
            'nama_lengkap' => $namaLengkap,
            'jenis_kelamin' => $jenisKelamin,
            'tempat_lahir' => $this->cleanText($row['tempat_lahir'] ?? null) ?: '-',
            'tanggal_lahir' => $tanggalLahir,
            'nama_ibu' => $namaIbu,
            'berat_lahir' => $this->cleanDecimal($row['berat_lahir_kg'] ?? null),
            'panjang_lahir' => $this->cleanDecimal($row['panjang_lahir_cm'] ?? null),
            'alamat' => $this->cleanText($row['alamat_lengkap'] ?? null) ?: '-',
            'created_by' => auth()->id(),
        ];

        if (Schema::hasColumn('balitas', 'user_id')) {
            $data['user_id'] = $linkedUser?->id;
        }

        if (Schema::hasColumn('balitas', 'nik_ibu')) {
            $data['nik_ibu'] = $nikIbu;
        }

        if (Schema::hasColumn('balitas', 'nama_ayah')) {
            $data['nama_ayah'] = $this->cleanText($row['nama_ayah'] ?? null) ?: null;
        }

        return new Balita($data);
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

    private function cleanNik($value, int $rowNumber, string $label): string
    {
        if ($value === null || $value === '') {
            throw new \RuntimeException("Baris {$rowNumber}: {$label} wajib diisi.");
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
            throw new \RuntimeException("Baris {$rowNumber}: {$label} harus 16 digit angka. Nilai terbaca: {$value}");
        }

        return $nik;
    }

    private function cleanOptionalNik($value, int $rowNumber, string $label): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return $this->cleanNik($value, $rowNumber, $label);
    }

    private function cleanDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = str_replace(',', '.', trim((string) $value));
        $number = preg_replace('/[^0-9.]/', '', $value);

        return $number !== '' ? (float) $number : null;
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

    private function parseDate($value, int $rowNumber): string
    {
        if ($value === null || $value === '') {
            throw new \RuntimeException("Baris {$rowNumber}: tanggal_lahir wajib diisi.");
        }

        try {
            if (is_numeric($value)) {
                $date = Date::excelToDateTimeObject($value);
                $carbon = Carbon::instance($date);
            } else {
                $value = trim((string) $value);
                $value = str_replace('/', '-', $value);

                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                    $carbon = Carbon::createFromFormat('Y-m-d', $value);
                } elseif (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $value)) {
                    $carbon = Carbon::createFromFormat('d-m-Y', $value);
                } else {
                    throw new \RuntimeException('Format tanggal tidak dikenali.');
                }
            }

            if ($carbon->isFuture()) {
                throw new \RuntimeException('Tanggal lahir tidak boleh melebihi hari ini.');
            }

            return $carbon->format('Y-m-d');
        } catch (\Throwable $e) {
            throw new \RuntimeException("Baris {$rowNumber}: tanggal_lahir tidak valid. Gunakan format YYYY-MM-DD, contoh 2021-05-17.");
        }
    }

    private function generateKodeBalita(): string
    {
        do {
            $kode = 'BLT-' . now('Asia/Jakarta')->format('ymd') . '-' . random_int(1000, 9999);
        } while (Balita::where('kode_balita', $kode)->exists());

        return $kode;
    }

    private function findLinkedUser(?string $nikIbu, string $namaIbu): ?User
    {
        $nikIbu = $nikIbu ? preg_replace('/[^0-9]/', '', $nikIbu) : null;
        $namaIbu = trim($namaIbu);

        $userQuery = User::query();
        $hasUserCondition = false;

        $userQuery->where(function ($q) use ($nikIbu, $namaIbu, &$hasUserCondition) {
            if ($nikIbu && Schema::hasColumn('users', 'nik')) {
                $q->where('nik', $nikIbu);
                $hasUserCondition = true;
            }

            if ($nikIbu && Schema::hasColumn('users', 'username')) {
                $method = $hasUserCondition ? 'orWhere' : 'where';
                $q->{$method}('username', $nikIbu);
                $hasUserCondition = true;
            }

            if ($namaIbu !== '' && Schema::hasColumn('users', 'name')) {
                $method = $hasUserCondition ? 'orWhere' : 'where';
                $q->{$method}('name', 'like', "%{$namaIbu}%");
                $hasUserCondition = true;
            }
        });

        if ($hasUserCondition) {
            $user = $userQuery->first();

            if ($user) {
                return $user;
            }
        }

        if (!Schema::hasTable('profiles')) {
            return null;
        }

        $profileQuery = DB::table('profiles');
        $hasProfileCondition = false;

        $profileQuery->where(function ($q) use ($nikIbu, $namaIbu, &$hasProfileCondition) {
            if ($nikIbu && Schema::hasColumn('profiles', 'nik')) {
                $q->where('nik', $nikIbu);
                $hasProfileCondition = true;
            }

            if ($nikIbu && Schema::hasColumn('profiles', 'no_ktp')) {
                $method = $hasProfileCondition ? 'orWhere' : 'where';
                $q->{$method}('no_ktp', $nikIbu);
                $hasProfileCondition = true;
            }

            if ($namaIbu !== '' && Schema::hasColumn('profiles', 'full_name')) {
                $method = $hasProfileCondition ? 'orWhere' : 'where';
                $q->{$method}('full_name', 'like', "%{$namaIbu}%");
                $hasProfileCondition = true;
            }
        });

        if (!$hasProfileCondition) {
            return null;
        }

        $profile = $profileQuery->first();

        if ($profile && isset($profile->user_id)) {
            return User::find($profile->user_id);
        }

        return null;
    }
}