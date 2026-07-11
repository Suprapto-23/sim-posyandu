<?php

namespace App\Imports;

use App\Models\Lansia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    private int $headingRow;

    /**
     * Menerima baris header dinamis yang disuntikkan oleh ImportController
     */
    public function __construct(int $headingRow = 3)
    {
        $this->headingRow = $headingRow;
    }

    public function headingRow(): int
    {
        return $this->headingRow;
    }

    public function model(array $row)
    {
        // 1. BLOKIR GHOST ROWS OTOMATIS (Mencegah iterasi pada baris kosong)
        $isEmpty = collect($row)->filter(fn($val) => trim((string)$val) !== '')->isEmpty();
        if ($isEmpty) {
            return null;
        }

        $rowNumber = $this->getRowNumber();

        try {
            // 2. SANITASI NIK CERDAS (Otomatis perbaiki notasi E Excel)
            $nik = $this->cleanNik($row['nik'] ?? null);
            
            if (empty($nik)) {
                Log::warning("Import Lansia: Baris {$rowNumber} dilewati karena NIK kosong atau format salah.");
                return null;
            }

            // 3. CEK DUPLIKASI
            if (Lansia::where('nik', $nik)->exists()) {
                return null;
            }

            // 4. EKSTRAKSI DATA WAJIB
            $namaLengkap = $this->cleanText($row['nama_lengkap'] ?? $row['nama'] ?? '');
            $tanggalLahir = $this->parseExcelDate($row['tanggal_lahir'] ?? $row['tgl_lahir'] ?? null);
            
            if (!$namaLengkap || !$tanggalLahir) {
                Log::warning("Import Lansia: Baris {$rowNumber} dilewati (Nama atau Tanggal Lahir tidak valid).");
                return null;
            }

            // 5. PERHITUNGAN MEDIS OTOMATIS
            $beratBadan = $this->cleanDecimal($row['berat_badan'] ?? $row['bb'] ?? null);
            $tinggiBadan = $this->cleanDecimal($row['tinggi_badan'] ?? $row['tb'] ?? null);
            $imt = $this->calculateImt($beratBadan, $tinggiBadan);

            // Fuzzy Match: Riwayat Penyakit (kader sering menggunakan nama kolom yang berbeda)
            $riwayatPenyakit = $this->firstFilled([
                $row['riwayat_penyakit'] ?? null,
                $row['penyakit_bawaan'] ?? null,
                $row['penyakit'] ?? null,
            ]);

            // 6. PENYUSUNAN DATA AMAN
            $data = [
                'nik'                 => $nik,
                'nama_lengkap'        => $namaLengkap,
                'jenis_kelamin'       => $this->normalizeGender($row['jenis_kelamin'] ?? $row['jk'] ?? null),
                'tempat_lahir'        => $this->cleanText($row['tempat_lahir'] ?? null) ?: '-',
                'tanggal_lahir'       => $tanggalLahir,
                'alamat'              => $this->firstFilled([$row['alamat_lengkap'] ?? null, $row['alamat'] ?? null]) ?: '-',
                'berat_badan'         => $beratBadan,
                'tinggi_badan'        => $tinggiBadan,
                'imt'                 => $imt,
                'lingkar_perut'       => $this->cleanDecimal($row['lingkar_perut'] ?? $row['lp'] ?? null),
                'tekanan_darah'       => $this->cleanTensi($row['tekanan_darah'] ?? $row['tensi'] ?? null),
                'gula_darah'          => $this->cleanDecimal($row['gula_darah'] ?? null),
                'kolesterol'          => $this->cleanDecimal($row['kolesterol'] ?? null),
                'asam_urat'           => $this->cleanDecimal($row['asam_urat'] ?? null),
                'tingkat_kemandirian' => $this->normalizeKemandirian($row['tingkat_kemandirian'] ?? $row['kemandirian'] ?? null),
                'penyakit_bawaan'     => $riwayatPenyakit,
                'keluhan'             => $this->cleanText($row['keluhan'] ?? null),
                'created_by'          => auth()->id(),
            ];

            // 7. SCHEMA AUTO-ADAPT (Cegah error query SQL jika kolom belum ada)
            if (Schema::hasColumn('lansias', 'kode_lansia')) {
                $data['kode_lansia'] = $this->generateKodeLansia();
            }

            if (Schema::hasColumn('lansias', 'user_id')) {
                $linkedUser = $this->findLinkedUser($nik, $namaLengkap);
                $data['user_id'] = $linkedUser?->id;
            }

            if (Schema::hasColumn('lansias', 'telepon_keluarga')) {
                $data['telepon_keluarga'] = $this->cleanPhone($row['telepon_keluarga'] ?? $row['no_hp_keluarga'] ?? $row['no_hp'] ?? null);
            }

            if (Schema::hasColumn('lansias', 'golongan_darah')) {
                $data['golongan_darah'] = $this->cleanBloodType($row['golongan_darah'] ?? $row['goldar'] ?? null);
            }

            // 8. INSERT DINAMIS
            $lansia = new Lansia();
            foreach ($data as $column => $value) {
                if (Schema::hasColumn('lansias', $column)) {
                    $lansia->{$column} = $value;
                }
            }

            return $lansia;

        } catch (\Throwable $e) {
            // Isolasi Kegagalan (Fail-Safe)
            Log::error("KADER_IMPORT_LANSIA_BARIS_{$rowNumber}: " . $e->getMessage());
            return null;
        }
    }

    public function batchSize(): int
    {
        return 200;
    }

    public function chunkSize(): int
    {
        return 200;
    }

    /* =======================================================
       HELPER METHODS (Otak Kecerdasan Pemetaan Data)
       ======================================================= */

    private function cleanText($value): ?string
    {
        if ($value === null || trim((string)$value) === '') return null;
        return trim(strip_tags((string) $value));
    }

    private function firstFilled(array $values): ?string
    {
        foreach ($values as $value) {
            $text = $this->cleanText($value);
            if ($text !== null && $text !== '') return $text;
        }
        return null;
    }

    private function cleanNik($value): ?string
    {
        if ($value === null || trim((string) $value) === '') return null;
        
        // Perbaiki notasi E
        if (stripos((string)$value, 'e+') !== false || stripos((string)$value, 'e-') !== false) {
            $value = sprintf('%.0f', (float) $value);
        }

        $nik = preg_replace('/[^0-9]/', '', (string)$value);
        return strlen($nik) === 16 ? $nik : null;
    }

    private function cleanPhone($value): ?string
    {
        if (empty($value)) return null;
        if (stripos((string)$value, 'e+') !== false) $value = sprintf('%.0f', (float) $value);
        $phone = preg_replace('/[^0-9+]/', '', trim((string)$value));
        return $phone !== '' ? $phone : null;
    }

    private function cleanBloodType($value): ?string
    {
        $value = strtoupper(trim((string)$value));
        $value = str_replace([' ', '+', '-'], '', $value);
        return in_array($value, ['A', 'B', 'AB', 'O'], true) ? $value : null;
    }

    private function cleanDecimal($value): ?float
    {
        if ($value === null || trim((string)$value) === '') return null;
        $value = str_replace(',', '.', (string)$value);
        $value = preg_replace('/[^0-9.\-]/', '', $value);
        return is_numeric($value) ? round((float)$value, 2) : null;
    }

    /**
     * Kecerdasan Pembersih Tensi:
     * Otomatis memperbaiki typo kader seperti "120\80", "120 80", "120|80" menjadi "120/80"
     */
    private function cleanTensi($value): ?string
    {
        $value = trim((string)$value);
        if ($value === '') return null;

        // Ganti spasi, backslash, atau pipa dengan garis miring
        $value = str_replace([' ', '\\', '|'], '/', $value);
        // Buang duplikasi garis miring jika ada (misal: 120//80)
        $value = preg_replace('/\/+/', '/', $value);

        if (!preg_match('/^\d{2,3}\/\d{2,3}$/', $value)) {
            return null; // Return null jika format sangat melenceng (jangan throw exception)
        }

        return $value;
    }

    private function normalizeGender($value): string
    {
        $value = strtolower(trim((string) $value));
        return match ($value) {
            'l', 'laki-laki', 'laki laki', 'laki', 'pria' => 'L',
            'p', 'perempuan', 'wanita' => 'P',
            default => 'L', // Fallback default
        };
    }

    private function normalizeKemandirian($value): ?string
    {
        $value = strtolower(trim((string) $value));
        $value = str_replace(['-', ' '], '_', $value);

        if ($value === '') return null;

        return match ($value) {
            'mandiri', 'm' => 'mandiri',
            'bantuan_sebagian', 'bantuan', 'sebagian' => 'bantuan_sebagian',
            'ketergantungan_penuh', 'penuh', 'tergantung_penuh', 'ketergantungan' => 'ketergantungan_penuh',
            default => null, // Biarkan database mengisi dengan defaultnya jika ada
        };
    }

    private function parseExcelDate($value)
    {
        if (empty($value)) return null;

        try {
            if (is_numeric($value)) {
                $dateObj = Date::excelToDateTimeObject($value);
                $carbon = Carbon::instance($dateObj);
            } else {
                $value = trim(str_replace('/', '-', (string)$value));
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                    $carbon = Carbon::createFromFormat('Y-m-d', $value);
                } elseif (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $value)) {
                    $carbon = Carbon::createFromFormat('d-m-Y', $value);
                } else {
                    return null;
                }
            }

            if ($carbon->isFuture()) return null;

            return $carbon->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function calculateImt(?float $beratBadan, ?float $tinggiBadan): ?float
    {
        if (!$beratBadan || !$tinggiBadan || $tinggiBadan <= 0) return null;
        
        $tinggiMeter = $tinggiBadan / 100;
        if ($tinggiMeter <= 0) return null;

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
            if ($user) return $user;
        }

        if (!Schema::hasTable('profiles')) return null;

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

        if ($hasProfileCondition) {
            $profile = $profileQuery->first();
            if ($profile && isset($profile->user_id)) {
                return User::find($profile->user_id);
            }
        }

        return null;
    }
}