<?php

namespace App\Imports;

use App\Models\Lansia;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LansiaImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, SkipsEmptyRows
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

        $beratBadan = $this->cleanDecimal($row['berat_badan'] ?? null);
        $tinggiBadan = $this->cleanDecimal($row['tinggi_badan'] ?? null);
        $imt = $this->calculateImt($beratBadan, $tinggiBadan);

        $linkedUser = $this->findLinkedUser($nik, $namaLengkap);

        $riwayatPenyakit = $this->firstFilled([
            $row['riwayat_penyakit'] ?? null,
            $row['penyakit_bawaan'] ?? null,
        ]);

        $data = [
            'nik' => $nik,
            'nama_lengkap' => $namaLengkap,
            'jenis_kelamin' => $jenisKelamin,
            'tempat_lahir' => $this->cleanText($row['tempat_lahir'] ?? null) ?: '-',
            'tanggal_lahir' => $tanggalLahir,
            'alamat' => $this->firstFilled([
                $row['alamat_lengkap'] ?? null,
                $row['alamat'] ?? null,
            ]) ?: '-',
            'berat_badan' => $beratBadan,
            'tinggi_badan' => $tinggiBadan,
            'imt' => $imt,
            'lingkar_perut' => $this->cleanDecimal($row['lingkar_perut'] ?? null),
            'tekanan_darah' => $this->cleanTensi($row['tekanan_darah'] ?? null, $rowNumber),
            'gula_darah' => $this->cleanDecimal($row['gula_darah'] ?? null),
            'kolesterol' => $this->cleanDecimal($row['kolesterol'] ?? null),
            'asam_urat' => $this->cleanDecimal($row['asam_urat'] ?? null),
            'tingkat_kemandirian' => $this->normalizeKemandirian($row['tingkat_kemandirian'] ?? null),
            'penyakit_bawaan' => $riwayatPenyakit,
            'keluhan' => $this->cleanText($row['keluhan'] ?? null) ?: null,
            'created_by' => auth()->id(),
        ];

        if (Schema::hasColumn('lansias', 'kode_lansia')) {
            $data['kode_lansia'] = $this->generateKodeLansia();
        }

        if (Schema::hasColumn('lansias', 'user_id')) {
            $data['user_id'] = $linkedUser?->id;
        }

        if (Schema::hasColumn('lansias', 'telepon_keluarga')) {
            $data['telepon_keluarga'] = $this->cleanPhone(
                $row['telepon_keluarga'] ?? $row['no_hp_keluarga'] ?? $row['no_hp'] ?? null
            );
        }

        if (Schema::hasColumn('lansias', 'golongan_darah')) {
            $data['golongan_darah'] = $this->cleanBloodType($row['golongan_darah'] ?? null);
        }

        $lansia = new Lansia();

        foreach ($data as $column => $value) {
            if (Schema::hasColumn('lansias', $column)) {
                $lansia->{$column} = $value;
            }
        }

        return $lansia;
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

    private function firstFilled(array $values): ?string
    {
        foreach ($values as $value) {
            $text = $this->cleanText($value);

            if ($text !== '') {
                return $text;
            }
        }

        return null;
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

    private function cleanDecimal($value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return round((float) $value, 2);
        }

        $value = trim((string) $value);
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/[^0-9.\-]/', '', $value);

        if ($value === '' || !is_numeric($value)) {
            return null;
        }

        return round((float) $value, 2);
    }

    private function cleanTensi($value, int $rowNumber): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = str_replace([' ', '\\'], ['', '/'], $value);

        if (!preg_match('/^\d{2,3}\/\d{2,3}$/', $value)) {
            throw new \RuntimeException("Baris {$rowNumber}: tekanan_darah harus memakai format 120/80.");
        }

        return $value;
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

    private function normalizeKemandirian($value): ?string
    {
        $value = strtolower(trim((string) $value));
        $value = str_replace(['-', ' '], '_', $value);

        if ($value === '') {
            return null;
        }

        return match ($value) {
            'mandiri', 'm' => 'mandiri',
            'bantuan_sebagian', 'bantuan', 'sebagian' => 'bantuan_sebagian',
            'ketergantungan_penuh', 'penuh', 'tergantung_penuh', 'ketergantungan' => 'ketergantungan_penuh',
            default => null,
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

            if ($carbon->greaterThan(now('Asia/Jakarta')->subYears(45))) {
                throw new \RuntimeException('Usia Lansia/Pra-Lansia minimal 45 tahun.');
            }

            return $carbon->format('Y-m-d');
        } catch (\Throwable $e) {
            throw new \RuntimeException("Baris {$rowNumber}: tanggal_lahir tidak valid. Gunakan format YYYY-MM-DD, contoh 1958-03-12.");
        }
    }

    private function calculateImt(?float $beratBadan, ?float $tinggiBadan): ?float
    {
        if (!$beratBadan || !$tinggiBadan || $tinggiBadan <= 0) {
            return null;
        }

        $tinggiMeter = $tinggiBadan / 100;

        if ($tinggiMeter <= 0) {
            return null;
        }

        return round($beratBadan / ($tinggiMeter * $tinggiMeter), 2);
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