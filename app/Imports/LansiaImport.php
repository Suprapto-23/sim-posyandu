<?php

namespace App\Imports;

use App\Models\Lansia;
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

class LansiaImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithBatchInserts, WithChunkReading
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

        if (Lansia::where('nik', $nik)->exists()) {
            return null;
        }

        $jenisKelamin = $this->normalizeGender($row['jenis_kelamin'] ?? null, $rowNumber);
        $tanggalLahir = $this->parseDate($row['tanggal_lahir'] ?? null, $rowNumber);
        $linkedUser = $this->findLinkedUser($nik, $namaLengkap);

        $data = [
            'kode_lansia' => $this->generateKodeLansia(),
            'nik' => $nik,
            'nama_lengkap' => $namaLengkap,
            'jenis_kelamin' => $jenisKelamin,
            'tempat_lahir' => $this->cleanText($row['tempat_lahir'] ?? null) ?: '-',
            'tanggal_lahir' => $tanggalLahir,
            'alamat' => $this->cleanText($row['alamat_lengkap'] ?? null) ?: '-',
            'penyakit_bawaan' => $this->cleanText($row['riwayat_penyakit'] ?? null) ?: null,
            'created_by' => auth()->id(),
        ];

        if (Schema::hasColumn('lansias', 'user_id')) {
            $data['user_id'] = $linkedUser?->id;
        }

        if (Schema::hasColumn('lansias', 'telepon_keluarga')) {
            $data['telepon_keluarga'] = $this->cleanPhone(
                $row['telepon_keluarga']
                ?? $row['no_hp_keluarga']
                ?? $row['no_hp']
                ?? null
            );
        }

        if (Schema::hasColumn('lansias', 'golongan_darah')) {
            $data['golongan_darah'] = $this->cleanBloodType($row['golongan_darah'] ?? null);
        }

        return new Lansia($data);
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
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            $value = number_format($value, 0, '', '');
        }

        $phone = preg_replace('/[^0-9+]/', '', trim((string) $value));

        return $phone !== '' ? $phone : null;
    }

    private function cleanBloodType($value): ?string
    {
        $value = strtoupper(trim((string) $value));

        if ($value === '') {
            return null;
        }

        return in_array($value, ['A', 'B', 'AB', 'O'], true) ? $value : null;
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
            throw new \RuntimeException("Baris {$rowNumber}: tanggal_lahir tidak valid. Gunakan format YYYY-MM-DD, contoh 1958-03-12.");
        }
    }

    private function generateKodeLansia(): string
    {
        do {
            $kode = 'LNS-' . now('Asia/Jakarta')->format('ymd') . '-' . random_int(1000, 9999);
        } while (Lansia::where('kode_lansia', $kode)->exists());

        return $kode;
    }

    private function findLinkedUser(string $nik, string $namaLengkap): ?User
    {
        $nik = preg_replace('/[^0-9]/', '', $nik);
        $namaLengkap = trim($namaLengkap);

        $userQuery = User::query();
        $hasUserCondition = false;

        $userQuery->where(function ($q) use ($nik, $namaLengkap, &$hasUserCondition) {
            if ($nik !== '' && Schema::hasColumn('users', 'nik')) {
                $q->where('nik', $nik);
                $hasUserCondition = true;
            }

            if ($nik !== '' && Schema::hasColumn('users', 'username')) {
                $method = $hasUserCondition ? 'orWhere' : 'where';
                $q->{$method}('username', $nik);
                $hasUserCondition = true;
            }

            if ($nik !== '' && Schema::hasColumn('users', 'email')) {
                $method = $hasUserCondition ? 'orWhere' : 'where';
                $q->{$method}('email', $nik);
                $hasUserCondition = true;
            }

            if ($namaLengkap !== '' && Schema::hasColumn('users', 'name')) {
                $method = $hasUserCondition ? 'orWhere' : 'where';
                $q->{$method}('name', 'like', "%{$namaLengkap}%");
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

        $profileQuery->where(function ($q) use ($nik, $namaLengkap, &$hasProfileCondition) {
            if ($nik !== '' && Schema::hasColumn('profiles', 'nik')) {
                $q->where('nik', $nik);
                $hasProfileCondition = true;
            }

            if ($nik !== '' && Schema::hasColumn('profiles', 'no_ktp')) {
                $method = $hasProfileCondition ? 'orWhere' : 'where';
                $q->{$method}('no_ktp', $nik);
                $hasProfileCondition = true;
            }

            if ($namaLengkap !== '' && Schema::hasColumn('profiles', 'full_name')) {
                $method = $hasProfileCondition ? 'orWhere' : 'where';
                $q->{$method}('full_name', 'like', "%{$namaLengkap}%");
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