<?php

namespace App\Imports;

use App\Models\Remaja;
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

class RemajaImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, SkipsEmptyRows
{
    use RemembersRowNumber;

    private int $headingRow;

    /**
     * Menerima baris header dinamis dari algoritma ImportController
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
        // 1. FILTER GHOST ROWS (Otomatis buang baris kosong ber-format)
        $isEmpty = collect($row)->filter(fn($val) => trim((string)$val) !== '')->isEmpty();
        if ($isEmpty) {
            return null;
        }

        $rowNumber = $this->getRowNumber();

        try {
            // 2. SANITASI NIK (Perbaiki notasi Eksponensial Excel)
            $nik = $this->cleanNik($row['nik'] ?? null);
            
            if (empty($nik)) {
                Log::warning("Import Remaja: Baris {$rowNumber} dilewati karena NIK kosong atau tidak valid.");
                return null;
            }

            // 3. ANTI-DUPLIKASI OTOMATIS
            if (Remaja::where('nik', $nik)->exists()) {
                return null;
            }

            // 4. EKSTRAKSI DATA WAJIB (Fuzzy Matching pada nama kolom)
            $namaLengkap = $this->cleanText($row['nama_lengkap'] ?? $row['nama'] ?? $row['nama_remaja'] ?? '');
            $tanggalLahir = $this->parseExcelDate($row['tanggal_lahir'] ?? $row['tgl_lahir'] ?? null);
            
            if (!$namaLengkap || !$tanggalLahir) {
                Log::warning("Import Remaja: Baris {$rowNumber} dilewati (Nama atau Tanggal Lahir kosong).");
                return null;
            }

            // 5. PENYUSUNAN DATA UTAMA
            $data = [
                'nik'           => $nik,
                'nama_lengkap'  => $namaLengkap,
                'jenis_kelamin' => $this->normalizeGender($row['jenis_kelamin'] ?? $row['jk'] ?? null),
                'tempat_lahir'  => $this->cleanText($row['tempat_lahir'] ?? null) ?: '-',
                'tanggal_lahir' => $tanggalLahir,
                'alamat'        => $this->firstFilled([$row['alamat_lengkap'] ?? null, $row['alamat'] ?? null]) ?: '-',
                
                // Tambahan opsional yang biasa ada di form Remaja
                'nama_ayah'     => $this->cleanText($row['nama_ayah'] ?? null),
                'nama_ibu'      => $this->cleanText($row['nama_ibu'] ?? null),
                
                // Metrik Fisik Awal (jika diizinkan masuk ke tabel master)
                'berat_badan'   => $this->cleanDecimal($row['berat_badan'] ?? $row['bb'] ?? null),
                'tinggi_badan'  => $this->cleanDecimal($row['tinggi_badan'] ?? $row['tb'] ?? null),
                'tekanan_darah' => $this->cleanTensi($row['tekanan_darah'] ?? $row['tensi'] ?? null),
                
                'created_by'    => auth()->id(),
            ];

            // 6. SCHEMA AUTO-ADAPT (Injeksi aman ke kolom database yang tersedia)
            if (Schema::hasColumn('remajas', 'kode_remaja')) {
                $data['kode_remaja'] = $this->generateKodeRemaja();
            }

            if (Schema::hasColumn('remajas', 'user_id')) {
                $linkedUser = $this->findLinkedUser($nik, $namaLengkap);
                $data['user_id'] = $linkedUser?->id;
            }

            // Deteksi otomatis jika kolom telepon bernama 'telepon' atau 'telepon_keluarga'
            $nomorHp = $this->cleanPhone($row['telepon'] ?? $row['no_hp'] ?? $row['telepon_keluarga'] ?? null);
            if (Schema::hasColumn('remajas', 'telepon')) {
                $data['telepon'] = $nomorHp;
            } elseif (Schema::hasColumn('remajas', 'telepon_keluarga')) {
                $data['telepon_keluarga'] = $nomorHp;
            }

            if (Schema::hasColumn('remajas', 'golongan_darah')) {
                $data['golongan_darah'] = $this->cleanBloodType($row['golongan_darah'] ?? $row['goldar'] ?? null);
            }
            
            if (Schema::hasColumn('remajas', 'sekolah')) {
                $data['sekolah'] = $this->cleanText($row['sekolah'] ?? $row['asal_sekolah'] ?? null);
            }

            // 7. EKSEKUSI INSERT 
            $remaja = new Remaja();
            foreach ($data as $column => $value) {
                if (Schema::hasColumn('remajas', $column)) {
                    $remaja->{$column} = $value;
                }
            }

            return $remaja;

        } catch (\Throwable $e) {
            // Isolasi Kegagalan - 1 baris error tidak akan membatalkan baris lainnya
            Log::error("KADER_IMPORT_REMAJA_BARIS_{$rowNumber}: " . $e->getMessage());
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
       HELPER METHODS (Kecerdasan Sanitasi Data)
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

    private function cleanTensi($value): ?string
    {
        $value = trim((string)$value);
        if ($value === '') return null;
        
        $value = str_replace([' ', '\\', '|'], '/', $value);
        $value = preg_replace('/\/+/', '/', $value);

        if (!preg_match('/^\d{2,3}\/\d{2,3}$/', $value)) {
            return null; 
        }
        return $value;
    }

    private function normalizeGender($value): string
    {
        $value = strtolower(trim((string) $value));
        return match ($value) {
            'l', 'laki-laki', 'laki laki', 'laki', 'pria', 'putra' => 'L',
            'p', 'perempuan', 'wanita', 'cewek', 'putri' => 'P',
            default => 'L', 
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

    private function generateKodeRemaja(): string
    {
        do {
            $kode = 'RMJ-' . now('Asia/Jakarta')->format('ymd') . '-' . random_int(1000, 9999);
        } while (Remaja::where('kode_remaja', $kode)->exists());
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