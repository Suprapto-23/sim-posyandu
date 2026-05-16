<?php

namespace App\Services;

use App\Models\ReferensiNilaiRemaja;
use Illuminate\Support\Facades\Log;

class AnalisisRemajaService
{
    /**
     * Menganalisis Indeks Massa Tubuh (IMT) Remaja secara matematis
     */
    public function analisisIMT(int $usiaTahun, string $jenisKelamin, float $nilaiImt): array
    {
        try {
            $referensi = ReferensiNilaiRemaja::where('jenis_pemeriksaan', 'imt')
                ->where('usia_tahun', $usiaTahun)
                ->where('jenis_kelamin', $jenisKelamin)
                ->get();
            
            foreach ($referensi as $ref) {
                if ($ref->kategori === 'kurus' && $nilaiImt <= $ref->nilai_max) {
                    return [
                        'kategori'    => 'kurus',
                        'pesan'       => 'IMT menunjukkan status kurus. Perlu perbaikan gizi.',
                        'rekomendasi' => 'Tingkatkan asupan makanan bergizi, konsumsi makanan tinggi protein dan kalori.'
                    ];
                } elseif ($ref->kategori === 'normal' && $nilaiImt >= $ref->nilai_min && $nilaiImt <= $ref->nilai_max) {
                    return [
                        'kategori'    => 'normal',
                        'pesan'       => 'IMT dalam batas normal.',
                        'rekomendasi' => 'Pertahankan pola makan sehat dan aktivitas fisik.'
                    ];
                } elseif ($ref->kategori === 'gemuk' && $nilaiImt >= $ref->nilai_min) {
                    return [
                        'kategori'    => 'gemuk',
                        'pesan'       => 'IMT menunjukkan status gemuk.',
                        'rekomendasi' => 'Perbanyak aktivitas fisik, kurangi makanan tinggi gula dan lemak.'
                    ];
                }
            }
            
            return $this->fallbackResponse('Data IMT di luar batas referensi wajar.');

        } catch (\Exception $e) {
            Log::error("Error Analisis IMT Remaja: " . $e->getMessage());
            return $this->fallbackResponse('Terjadi kesalahan sistem saat mengambil referensi.');
        }
    }
    
    public function analisisHemoglobin(string $jenisKelamin, float $nilaiHb): array
    {
        $jk = strtoupper($jenisKelamin);
        if ($jk === 'L') { 
            if ($nilaiHb >= 14 && $nilaiHb <= 18) return ['kategori' => 'normal', 'pesan' => 'Hemoglobin normal'];
            if ($nilaiHb < 14) return ['kategori' => 'rendah', 'pesan' => 'Hemoglobin rendah (Indikasi Anemia)'];
            return ['kategori' => 'tinggi', 'pesan' => 'Hemoglobin tinggi'];
        } else { 
            if ($nilaiHb >= 12 && $nilaiHb <= 16) return ['kategori' => 'normal', 'pesan' => 'Hemoglobin normal'];
            if ($nilaiHb < 12) return ['kategori' => 'rendah', 'pesan' => 'Hemoglobin rendah (Indikasi Anemia)'];
            return ['kategori' => 'tinggi', 'pesan' => 'Hemoglobin tinggi'];
        }
    }

    private function fallbackResponse(string $pesan): array
    {
        return [
            'kategori'    => 'tidak_terdefinisi',
            'pesan'       => $pesan,
            'rekomendasi' => 'Konsultasikan langsung dengan Bidan atau Dokter.'
        ];
    }
}