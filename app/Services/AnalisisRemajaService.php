<?php

namespace App\Services;

use App\Models\ReferensiNilaiRemaja;
use Illuminate\Support\Facades\Log;

class AnalisisRemajaService
{
    /**
     * Menganalisis Indeks Massa Tubuh (IMT) Remaja (Terisolasi dari Error DB)
     */
    public function analisisIMT(int $usiaTahun, string $jenisKelamin, float $nilaiImt): array
    {
        // 1. Proteksi Awal: Jika Kader lupa mengisi Tinggi/Berat Badan (IMT = 0)
        if ($nilaiImt <= 0) {
            return $this->fallbackResponse('Data tinggi atau berat badan belum lengkap.');
        }

        // Standarisasi input jenis kelamin (Ambil huruf depannya saja: 'L' atau 'P')
        $jk = strtoupper(substr($jenisKelamin, 0, 1));

        // 2. Coba ambil dari Database (Diisolasi khusus agar jika gagal tidak merusak sistem)
        try {
            $referensi = ReferensiNilaiRemaja::where('jenis_pemeriksaan', 'imt')
                ->where('usia_tahun', $usiaTahun)
                ->where('jenis_kelamin', $jk)
                ->get();
            
            if ($referensi->isNotEmpty()) {
                foreach ($referensi as $ref) {
                    if ($ref->kategori === 'kurus' && $nilaiImt <= $ref->nilai_max) {
                        return $this->formatRespon('kurus', 'IMT menunjukkan status kurus. Perlu perbaikan gizi.', 'Tingkatkan asupan makanan bergizi, konsumsi makanan tinggi protein dan kalori.');
                    } elseif ($ref->kategori === 'normal' && $nilaiImt >= $ref->nilai_min && $nilaiImt <= $ref->nilai_max) {
                        return $this->formatRespon('normal', 'IMT dalam batas normal.', 'Pertahankan pola makan sehat dan aktivitas fisik.');
                    } elseif ($ref->kategori === 'gemuk' && $nilaiImt >= $ref->nilai_min) {
                        return $this->formatRespon('gemuk', 'IMT menunjukkan status gemuk/obesitas.', 'Perbanyak aktivitas fisik, kurangi makanan tinggi gula dan lemak.');
                    }
                }
            }
        } catch (\Throwable $e) {
            // Jika tabel referensi tidak ada atau error, HANYA dicatat di log.
            // Sistem AKAN MELANJUTKAN ke rumus WHO di bawah tanpa memunculkan error ke Bidan.
            Log::warning("Tabel Referensi Remaja Error (Beralih ke WHO): " . $e->getMessage());
        }

        // 3. RUMUS BACKUP STANDAR WHO (Pasti Berhasil & Berjalan Otomatis)
        if ($nilaiImt < 18.5) {
            return $this->formatRespon('kurus', 'IMT di bawah standar normal (Kurus).', 'Perbanyak asupan protein, karbohidrat, dan konsultasi gizi.');
        } elseif ($nilaiImt >= 18.5 && $nilaiImt <= 24.9) {
            return $this->formatRespon('normal', 'Pertumbuhan dan gizi sangat baik (Normal).', 'Pertahankan pola makan bergizi dan olahraga teratur.');
        } else {
            return $this->formatRespon('gemuk', 'IMT berlebih (Risiko Obesitas).', 'Kurangi makanan manis/berlemak, tingkatkan aktivitas fisik harian.');
        }
    }
    
    public function analisisHemoglobin(string $jenisKelamin, float $nilaiHb): array
    {
        $jk = strtoupper(substr($jenisKelamin, 0, 1));
        
        if ($jk === 'L') { 
            if ($nilaiHb >= 14 && $nilaiHb <= 18) return $this->formatRespon('normal', 'Kadar Hemoglobin Normal.', 'Pertahankan asupan zat besi dari daging dan sayur hijau.');
            if ($nilaiHb < 14) return $this->formatRespon('rendah', 'Hemoglobin rendah (Indikasi Anemia).', 'Konsumsi Tablet Tambah Darah (TTD) dan makanan tinggi zat besi.');
            return $this->formatRespon('tinggi', 'Hemoglobin di atas normal.', 'Perbanyak minum air putih dan konsultasikan ke dokter jika pusing.');
        } else { 
            if ($nilaiHb >= 12 && $nilaiHb <= 16) return $this->formatRespon('normal', 'Kadar Hemoglobin Normal.', 'Pertahankan asupan zat besi dari daging dan sayur hijau.');
            if ($nilaiHb < 12) return $this->formatRespon('rendah', 'Hemoglobin rendah (Indikasi Anemia).', 'Wajib konsumsi Tablet Tambah Darah (TTD) secara rutin.');
            return $this->formatRespon('tinggi', 'Hemoglobin di atas normal.', 'Perbanyak minum air putih dan konsultasi ke fasilitas kesehatan.');
        }
    }

    private function formatRespon(string $kategori, string $pesan, string $rekomendasi): array
    {
        return [
            'kategori'    => $kategori,
            'pesan'       => $pesan,
            'rekomendasi' => $rekomendasi
        ];
    }

    private function fallbackResponse(string $pesan): array
    {
        return [
            'kategori'    => 'tidak_terdefinisi',
            'pesan'       => $pesan,
            'rekomendasi' => 'Harap isi angka dengan benar saat pemeriksaan.'
        ];
    }
}