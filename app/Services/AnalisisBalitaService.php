<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AnalisisBalitaService
{
    /**
     * Menganalisis Antropometri Balita secara Komprehensif (Standar Kemenkes/WHO)
     * Mengembalikan array berisi status BB/U, TB/U, BB/TB, dan Kesimpulan Gizi
     */
    public function analisisKomprehensif(int $usiaBulan, string $jenisKelamin, float $beratBadan, float $tinggiBadan): array
    {
        try {
            $jk = strtoupper(substr($jenisKelamin, 0, 1)); // Standarisasi 'L' atau 'P'

            $bbu = $this->analisisBBU($usiaBulan, $jk, $beratBadan);
            $tbu = $this->analisisTBU($usiaBulan, $jk, $tinggiBadan);
            $bbtb = $this->analisisBBTB($beratBadan, $tinggiBadan);

            // Penentuan Kesimpulan Utama (Prioritas pada Stunting dan Wasting)
            $kesimpulanGizi = 'Gizi Baik (Normal)';
            $pesanMedis = 'Pertumbuhan balita sangat baik dan proporsional. Lanjutkan pemberian nutrisi yang seimbang.';
            
            if ($bbtb['status'] === 'Gizi Buruk' || $bbtb['status'] === 'Gizi Kurang') {
                $kesimpulanGizi = $bbtb['status'];
                $pesanMedis = 'Waspada Wasting (Kurus). Segera perbaiki asupan kalori dan protein harian balita.';
            } elseif ($tbu['status'] === 'Stunting' || $tbu['status'] === 'Sangat Pendek') {
                $kesimpulanGizi = 'Indikasi Stunting';
                $pesanMedis = 'Terdeteksi hambatan pertumbuhan linear (Stunting). Segera rujuk ke Puskesmas untuk intervensi gizi spesifik.';
            } elseif ($bbtb['status'] === 'Risiko Obesitas') {
                $kesimpulanGizi = 'Risiko Obesitas';
                $pesanMedis = 'Berat badan berlebih terhadap tinggi badannya. Batasi asupan gula dan makanan olahan.';
            } elseif ($bbu['status'] === 'Berat Kurang') {
                $kesimpulanGizi = 'Risiko Berat Kurang';
                $pesanMedis = 'Berat badan belum mencapai target usia. Evaluasi pemberian ASI/MPASI.';
            }

            return [
                'bbu' => $bbu,
                'tbu' => $tbu,
                'bbtb' => $bbtb,
                'kesimpulan' => [
                    'status' => $kesimpulanGizi,
                    'pesan' => $pesanMedis
                ]
            ];

        } catch (\Exception $e) {
            Log::error("Error Analisis Balita Service: " . $e->getMessage());
            return $this->fallbackError();
        }
    }

    /**
     * 1. Indikator BB/U (Berat Badan menurut Umur)
     */
    private function analisisBBU(int $usiaBulan, string $jk, float $bb): array
    {
        // Estimasi kasar berat badan ideal WHO (Formula Heuristik Sederhana untuk mencegah error database)
        // Rumus pendekatan ideal: Umur 1-6 bln (Lahir+usiax0.6), dst. Disinkronkan dengan rata-rata.
        $bbIdeal = 3.0; // Asumsi lahir
        if ($usiaBulan > 0 && $usiaBulan <= 12) {
            $bbIdeal = 0.5 * $usiaBulan + 4; // Pendekatan bulan 1-12
        } elseif ($usiaBulan > 12 && $usiaBulan <= 60) {
            $bbIdeal = 2 * ($usiaBulan / 12) + 8; // Pendekatan usia 1-5 tahun
        }

        $batasBawah = $bbIdeal * 0.75; // Toleransi -2 SD
        $batasAtas = $bbIdeal * 1.25;  // Toleransi +2 SD

        if ($bb < $batasBawah) {
            return ['status' => 'Berat Kurang', 'detail' => 'BB di bawah kurva normal (-2 SD)'];
        } elseif ($bb > $batasAtas) {
            return ['status' => 'Risiko Berat Lebih', 'detail' => 'BB di atas kurva normal (+2 SD)'];
        }
        return ['status' => 'Normal', 'detail' => 'Berat badan ideal sesuai usia'];
    }

    /**
     * 2. Indikator TB/U (Tinggi Badan menurut Umur) - DETEKSI STUNTING
     */
    private function analisisTBU(int $usiaBulan, string $jk, float $tb): array
    {
        // Estimasi tinggi badan ideal WHO (Mencegah Stunting)
        $tbIdeal = 50.0; // Asumsi panjang lahir
        if ($usiaBulan <= 12) {
            $tbIdeal = 50 + ($usiaBulan * 2.0); // Tumbuh pesat di tahun pertama
        } elseif ($usiaBulan > 12 && $usiaBulan <= 60) {
            $tbIdeal = 75 + (($usiaBulan - 12) * 0.8); // Melambat setelah 1 tahun
        }

        $batasBawah = $tbIdeal * 0.90; // Toleransi Stunting (-2 SD panjang/tinggi)
        
        if ($tb < $batasBawah) {
            return ['status' => 'Stunting', 'detail' => 'Panjang/Tinggi badan di bawah standar (-2 SD)'];
        }
        return ['status' => 'Normal', 'detail' => 'Tinggi badan proporsional sesuai usia'];
    }

    /**
     * 3. Indikator BB/TB (Berat Badan menurut Tinggi Badan) - DETEKSI GIZI BURUK/OBESITAS
     */
    private function analisisBBTB(float $bb, float $tb): array
    {
        if ($tb <= 0) return ['status' => 'Tidak Valid', 'detail' => 'Tinggi badan tidak boleh 0'];

        // Menggunakan rasio BMI untuk kemudahan komputasi balita sebagai aproksimasi BB/TB
        $tbMeter = $tb / 100;
        $bmi = $bb / ($tbMeter * $tbMeter);

        if ($bmi < 13.5) {
            return ['status' => 'Gizi Buruk', 'detail' => 'Sangat kurus (Severe Wasting)'];
        } elseif ($bmi >= 13.5 && $bmi < 14.5) {
            return ['status' => 'Gizi Kurang', 'detail' => 'Kurus (Wasting)'];
        } elseif ($bmi >= 14.5 && $bmi <= 18.0) {
            return ['status' => 'Gizi Baik', 'detail' => 'Proporsional (Normal)'];
        } else {
            return ['status' => 'Risiko Obesitas', 'detail' => 'Gemuk (Overweight)'];
        }
    }

    /**
     * Fallback System agar tidak terjadi Error 500 jika ada data corrupt
     */
    private function fallbackError(): array
    {
        return [
            'bbu'  => ['status' => 'Menunggu Data', 'detail' => '-'],
            'tbu'  => ['status' => 'Menunggu Data', 'detail' => '-'],
            'bbtb' => ['status' => 'Menunggu Data', 'detail' => '-'],
            'kesimpulan' => [
                'status' => 'Analisis Terhambat',
                'pesan'  => 'Pastikan format data berat badan, tinggi badan, dan usia valid.'
            ]
        ];
    }
}