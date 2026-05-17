<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AnalisisLansiaService
{
    /**
     * Menganalisis Skrining Penyakit Tidak Menular (PTM) pada Lansia
     * Meliputi: Tensi, Gula Darah, Asam Urat, dan Kolesterol.
     */
    public function analisisKomprehensif($pemeriksaan, string $jenisKelamin = 'L'): array
    {
        try {
            // Standarisasi Input Gender
            $jk = strtoupper(substr($jenisKelamin, 0, 1));

            // Analisis per komponen laboratorium sederhana
            $tensi      = $this->cekTensi($pemeriksaan->tekanan_darah ?? '0/0');
            $gulaDarah  = $this->cekGulaDarah((float) ($pemeriksaan->gula_darah ?? 0));
            $asamUrat   = $this->cekAsamUrat((float) ($pemeriksaan->asam_urat ?? 0), $jk);
            $kolesterol = $this->cekKolesterol((float) ($pemeriksaan->kolesterol ?? 0));

            // Triase Prioritas Klinis (Penentuan Kesimpulan Akhir)
            $statusUtama = 'Terkontrol (Sehat)';
            $pesanMedis = 'Kondisi metabolik lansia dalam batas wajar. Lanjutkan pola hidup sehat dan senam lansia.';
            $tindakan = 'Edukasi Pola Hidup Sehat';

            // Evaluasi dari kondisi yang paling berisiko fatal (Komplikasi Jantung/Stroke)
            if ($tensi['kategori'] === 'Hipertensi' && $gulaDarah['kategori'] === 'Diabetes') {
                $statusUtama = 'Risiko Tinggi Komplikasi';
                $pesanMedis = 'Tekanan darah dan gula darah sangat tinggi. Berisiko komplikasi serius.';
                $tindakan = 'Rujuk Segera ke Puskesmas/Faskes Lanjutan';
            } elseif ($tensi['kategori'] === 'Hipertensi') {
                $statusUtama = 'Indikasi Hipertensi';
                $pesanMedis = $tensi['pesan'];
                $tindakan = 'Rujuk ke Dokter Umum & Edukasi Diet Rendah Garam';
            } elseif ($gulaDarah['kategori'] === 'Diabetes' || $gulaDarah['kategori'] === 'Hipoglikemia') {
                $statusUtama = 'Gangguan Gula Darah';
                $pesanMedis = $gulaDarah['pesan'];
                $tindakan = 'Rujuk Puskesmas & Pantau Diet Rendah Gula';
            } elseif ($kolesterol['kategori'] === 'Tinggi' || $asamUrat['kategori'] === 'Tinggi') {
                $statusUtama = 'Gangguan Metabolik Ringan';
                $pesanMedis = 'Terdapat peningkatan kadar kolesterol atau asam urat. Waspada nyeri sendi.';
                $tindakan = 'Perbaiki Pola Makan (Diet Rendah Purin dan Lemak)';
            }

            return [
                'tensi'      => $tensi,
                'gula_darah' => $gulaDarah,
                'asam_urat'  => $asamUrat,
                'kolesterol' => $kolesterol,
                'kesimpulan' => [
                    'status'   => $statusUtama,
                    'pesan'    => $pesanMedis,
                    'tindakan' => $tindakan
                ]
            ];

        } catch (\Exception $e) {
            Log::error("Error Analisis Lansia Service: " . $e->getMessage());
            return $this->fallbackError();
        }
    }

    /**
     * 1. Cek Tekanan Darah (Risiko Hipertensi)
     */
    private function cekTensi(string $tensi): array
    {
        if (empty($tensi) || $tensi === '0/0' || $tensi === '-') {
            return ['kategori' => 'Belum Diukur', 'pesan' => '-'];
        }
        
        $sistolik = (int) explode('/', $tensi)[0];

        if ($sistolik >= 140) {
            return ['kategori' => 'Hipertensi', 'pesan' => "Tensi $tensi mmHg. Indikasi darah tinggi."];
        } elseif ($sistolik < 90 && $sistolik > 0) {
            return ['kategori' => 'Hipotensi', 'pesan' => "Tensi $tensi mmHg. Darah rendah, berisiko lemas atau pingsan."];
        }
        return ['kategori' => 'Normal', 'pesan' => 'Tekanan darah normal terkendali.'];
    }

    /**
     * 2. Cek Gula Darah Sewaktu (GDS) - Deteksi Diabetes
     */
    private function cekGulaDarah(float $gula): array
    {
        if ($gula <= 0) return ['kategori' => 'Belum Diukur', 'pesan' => '-'];
        
        if ($gula >= 200) {
            return ['kategori' => 'Diabetes', 'pesan' => "Gula sewaktu $gula mg/dL. Indikasi Diabetes Mellitus."];
        } elseif ($gula < 70) {
            return ['kategori' => 'Hipoglikemia', 'pesan' => "Gula $gula mg/dL sangat rendah. Segera berikan cairan manis."];
        }
        return ['kategori' => 'Normal', 'pesan' => 'Kadar gula darah dalam batas aman.'];
    }

    /**
     * 3. Cek Asam Urat - Sensitif terhadap Gender
     */
    private function cekAsamUrat(float $au, string $jk): array
    {
        if ($au <= 0) return ['kategori' => 'Belum Diukur', 'pesan' => '-'];

        // Laki-laki toleransi asam urat lebih tinggi daripada perempuan
        $batasMaksimal = ($jk === 'L') ? 7.0 : 6.0;
        
        if ($au > $batasMaksimal) {
            return ['kategori' => 'Tinggi', 'pesan' => "Kadar $au mg/dL. Berisiko radang sendi (Gout)."];
        }
        return ['kategori' => 'Normal', 'pesan' => 'Asam urat terkendali.'];
    }

    /**
     * 4. Cek Kolesterol Total
     */
    private function cekKolesterol(float $kolesterol): array
    {
        if ($kolesterol <= 0) return ['kategori' => 'Belum Diukur', 'pesan' => '-'];

        if ($kolesterol >= 200) {
            return ['kategori' => 'Tinggi', 'pesan' => "Kadar $kolesterol mg/dL. Berisiko penyumbatan pembuluh darah."];
        }
        return ['kategori' => 'Normal', 'pesan' => 'Kolesterol total dalam batas aman.'];
    }

    /**
     * Fallback Response
     */
    private function fallbackError(): array
    {
        return [
            'tensi'      => ['kategori' => 'Error', 'pesan' => '-'],
            'gula_darah' => ['kategori' => 'Error', 'pesan' => '-'],
            'asam_urat'  => ['kategori' => 'Error', 'pesan' => '-'],
            'kolesterol' => ['kategori' => 'Error', 'pesan' => '-'],
            'kesimpulan' => [
                'status'   => 'Gagal Analisis',
                'pesan'    => 'Format angka laboratorium tidak valid untuk diproses.',
                'tindakan' => 'Pastikan kader memasukkan angka bulat/desimal yang benar.'
            ]
        ];
    }
}