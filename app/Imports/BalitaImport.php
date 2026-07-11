<?php

namespace App\Imports;

use App\Models\Balita;
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

class BalitaImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, SkipsEmptyRows
{
    use RemembersRowNumber;

    private int $headingRow;

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
        // 1. FILTER GHOST ROWS (Otomatis buang baris kosong)
        $isEmpty = collect($row)->filter(fn($val) => trim((string)$val) !== '')->isEmpty();
        if ($isEmpty) {
            return null;
        }

        $rowNumber = $this->getRowNumber();

        try {
            // 2. SANITASI NIK CERDAS (Otomatis perbaiki format eksponensial Excel e.g., 3.32E+15)
            $nik = $this->cleanNik($row['nik'] ?? null, $rowNumber);
            $nikIbu = $this->cleanNik($row['nik_ibu'] ?? $row['nik_ortu'] ?? null, $rowNumber, false); // NIK Ibu opsional
            
            // Proteksi Data Utama
            if (empty($nik)) {
                Log::warning("Import Balita: Baris {$rowNumber} dilewati karena NIK anak kosong atau tidak valid.");
                return null;
            }

            // 3. ANTI-DUPLIKASI OTOMATIS
            if (Balita::where('nik', $nik)->exists()) {
                return null; // Skip jika data sudah ada agar tidak error duplikat
            }

            // 4. FUZZY MATCHING (Menangkap variasi nama kolom Excel dari Kader)
            $namaAnak = $this->cleanText($row['nama_anak'] ?? $row['nama_lengkap'] ?? $row['nama'] ?? '');
            $namaIbu = $this->cleanText($row['nama_ibu'] ?? $row['nama_ortu'] ?? '');
            $namaAyah = $this->cleanText($row['nama_ayah'] ?? '');
            $jenisKelamin = $this->normalizeGender($row['jenis_kelamin'] ?? $row['jk'] ?? null);
            $tanggalLahir = $this->parseExcelDate($row['tanggal_lahir'] ?? $row['tgl_lahir'] ?? null);
            
            if (!$namaAnak || !$tanggalLahir) {
                Log::warning("Import Balita: Baris {$rowNumber} dilewati karena Nama Anak atau Tanggal Lahir kosong.");
                return null;
            }

            // 5. PENYUSUNAN DATA UTAMA
            $data = [
                'nik'           => $nik,
                'nik_ibu'       => $nikIbu,
                'nama_lengkap'  => $namaAnak,
                'nama_ibu'      => $namaIbu ?: '-',
                'nama_ayah'     => $namaAyah ?: '-',
                'jenis_kelamin' => $jenisKelamin,
                'tempat_lahir'  => $this->cleanText($row['tempat_lahir'] ?? null) ?: '-',
                'tanggal_lahir' => $tanggalLahir,
                'alamat'        => $this->cleanText($row['alamat'] ?? $row['alamat_lengkap'] ?? null) ?: '-',
                'rt'            => $this->cleanText($row['rt'] ?? null),
                'rw'            => $this->cleanText($row['rw'] ?? null),
                'berat_lahir'   => $this->parseNumeric($row['berat_lahir'] ?? $row['berat_badan_lahir'] ?? null),
                'tinggi_lahir'  => $this->parseNumeric($row['tinggi_lahir'] ?? $row['panjang_badan_lahir'] ?? null),
                'created_by'    => auth()->id(),
            ];

            // 6. SCHEMA AUTO-ADAPT (Mencegah error SQL jika tabel Balita diubah di masa depan)
            if (Schema::hasColumn('balitas', 'kode_balita')) {
                $data['kode_balita'] = $this->generateKodeBalita();
            }

            if (Schema::hasColumn('balitas', 'user_id')) {
                // Pintar mencari akun user_id berdasarkan NIK Ibu atau Nama Ibu
                $linkedUser = $this->findLinkedUser($nikIbu ?: $nik, $namaIbu ?: $namaAnak);
                $data['user_id'] = $linkedUser?->id;
            }

            if (Schema::hasColumn('balitas', 'telepon_keluarga')) {
                $data['telepon_keluarga'] = $this->cleanPhone($row['telepon_keluarga'] ?? $row['no_hp'] ?? $row['telepon'] ?? null);
            }

            if (Schema::hasColumn('balitas', 'golongan_darah')) {
                $data['golongan_darah'] = $this->cleanBloodType($row['golongan_darah'] ?? $row['goldar'] ?? null);
            }

            if (Schema::hasColumn('balitas', 'buku_kia')) {
                $data['buku_kia'] = $this->normalizeBoolean($row['buku_kia'] ?? $row['kia'] ?? null);
            }

            if (Schema::hasColumn('balitas', 'imd')) {
                $data['imd'] = $this->normalizeBoolean($row['imd'] ?? null);
            }

            // 7. INSERT AMAN
            $balita = new Balita();
            foreach ($data as $column => $value) {
                // Hanya suntikkan data jika kolomnya benar-benar ada di database saat ini
                if (Schema::hasColumn('balitas', $column)) {
                    $balita->{$column} = $value;
                }
            }

            return $balita;

        } catch (\Throwable $e) {
            // Isolasi kegagalan 1 baris agar tidak membatalkan sisa ratusan baris lainnya
            Log::error("KADER_IMPORT_BALITA_BARIS_{$rowNumber}: " . $e->getMessage());
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

    private function cleanNik($value, int $rowNumber, bool $isRequired = true): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return $isRequired ? null : null;
        }

        // Tangani notasi eksponensial (misal: 3.32609E+15)
        if (stripos((string)$value, 'e+') !== false || stripos((string)$value, 'e-') !== false) {
            $value = sprintf('%.0f', (float) $value);
        }

        $nik = preg_replace('/[^0-9]/', '', (string)$value);

        // Abaikan NIK jika ternyata bukan 16 digit, namun jangan matikan program
        if (strlen($nik) !== 16) {
            return null; 
        }

        return $nik;
    }

    private function cleanText($value): ?string
    {
        if ($value === null || trim((string)$value) === '') return null;
        return trim(strip_tags((string)$value));
    }

    private function parseNumeric($value): ?float
    {
        if ($value === null || trim((string)$value) === '') return null;
        
        // Konversi koma ke titik untuk format desimal Indonesia
        $value = str_replace(',', '.', (string)$value);
        $value = preg_replace('/[^0-9.\-]/', '', $value); // Buang huruf tak sengaja terketik
        
        return is_numeric($value) ? round((float)$value, 2) : null;
    }

    private function parseExcelDate($value)
    {
        if (empty($value)) return null;

        try {
            // Jika terbaca sebagai Serial Number Excel
            if (is_numeric($value)) {
                $dateObj = Date::excelToDateTimeObject($value);
                return Carbon::instance($dateObj)->format('Y-m-d');
            }
            
            // Jika terbaca sebagai String Biasa
            $value = trim(str_replace('/', '-', (string)$value));
            
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
            } elseif (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $value)) {
                return Carbon::createFromFormat('d-m-Y', $value)->format('Y-m-d');
            }
            
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function normalizeGender($value): string
    {
        $value = strtolower(trim((string) $value));
        return match ($value) {
            'l', 'laki-laki', 'laki laki', 'laki', 'pria', 'cowok', 'putra' => 'L',
            'p', 'perempuan', 'wanita', 'cewek', 'putri' => 'P',
            default => 'L', // Fallback default
        };
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
        $value = str_replace([' ', '+', '-'], '', $value); // Normalisasi
        return in_array($value, ['A', 'B', 'AB', 'O'], true) ? $value : null;
    }

    private function normalizeBoolean($value): ?bool
    {
        if ($value === null) return null;
        $value = strtolower(trim((string)$value));
        if (in_array($value, ['ya', 'y', '1', 'true', 'ada', 'punya'], true)) return true;
        if (in_array($value, ['tidak', 't', '0', 'false', 'belum', 'tidak ada'], true)) return false;
        return null;
    }

    private function generateKodeBalita(): string
    {
        do {
            $kode = 'BAL-' . now('Asia/Jakarta')->format('ymd') . '-' . random_int(1000, 9999);
        } while (Balita::where('kode_balita', $kode)->exists());
        return $kode;
    }

    private function findLinkedUser(string $nikOrEmail, string $namaLengkap): ?User
    {
        $query = User::query();
        $hasCondition = false;

        $query->where(function ($q) use ($nikOrEmail, $namaLengkap, &$hasCondition) {
            if (!empty($nikOrEmail) && Schema::hasColumn('users', 'nik')) {
                $q->where('nik', $nikOrEmail);
                $hasCondition = true;
            }
            if (!empty($nikOrEmail) && Schema::hasColumn('users', 'email')) {
                $method = $hasCondition ? 'orWhere' : 'where';
                $q->{$method}('email', $nikOrEmail);
                $hasCondition = true;
            }
            if (!empty($namaLengkap) && Schema::hasColumn('users', 'name')) {
                $method = $hasCondition ? 'orWhere' : 'where';
                $q->{$method}('name', 'like', "%{$namaLengkap}%");
                $hasCondition = true;
            }
        });

        return $hasCondition ? $query->first() : null;
    }
}