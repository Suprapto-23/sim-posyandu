<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AnalisisIbuHamilService
{
    /**
     * Menganalisis Kondisi Kehamilan secara Komprehensif (Antenatal Care)
     */
    public function analisisKomprehensif($pemeriksaan, $dataBumil = null): array
    {
        try {
            // Analisis per komponen
            $analisisLila  = $this->cekLILA((float) ($pemeriksaan->lila ?? 0));
            $analisisTensi = $this->cekTensi($pemeriksaan->tekanan_darah ?? '0/0');
            $analisisDjj   = $this->cekDJJ((float) ($pemeriksaan->djj ?? 0));

            // Penentuan Kesimpulan & Triase Kegawatdaruratan
            $statusUtama = 'Kehamilan Sehat';
            $pesanMedis = 'Kondisi ibu dan janin terpantau baik. Lanjutkan konsumsi vitamin dan asupan gizi seimbang.';
            $tingkatRisiko = 'Rendah';

            // Logika Prioritas Risiko (Triase)
            if ($analisisTensi['status'] === 'Hipertensi / Risiko Preeklampsia') {
                $statusUtama = 'Risiko Preeklampsia';
                $pesanMedis = 'Tekanan darah ibu sangat tinggi! Segera rujuk ke Puskesmas/RS untuk mencegah kejang kehamilan.';
                $tingkatRisiko = 'Tinggi';
            } elseif ($analisisDjj['status'] === 'Gawat Janin') {
                $statusUtama = 'Gawat Janin (Fetal Distress)';
                $pesanMedis = 'Detak Jantung Janin tidak normal. Segera lakukan pemeriksaan USG darurat!';
                $tingkatRisiko = 'Tinggi';
            } elseif ($analisisLila['status'] === 'Risiko KEK') {
                $statusUtama = 'Risiko KEK (Kekurangan Energi Kronis)';
                $pesanMedis = 'Ibu mengalami kekurangan energi kronis. Berisiko bayi lahir dengan berat badan rendah (BBLR).';
                $tingkatRisiko = 'Sedang';
            }

            return [
                'lila'  => $analisisLila,
                'tensi' => $analisisTensi,
                'djj'   => $analisisDjj,
                'kesimpulan' => [
                    'status' => $statusUtama,
                    'pesan'  => $pesanMedis,
                    'risiko' => $tingkatRisiko
                ]
            ];

        } catch (\Exception $e) {
            Log::error("Error Analisis Ibu Hamil Service: " . $e->getMessage());
            return $this->fallbackError();
        }
    }

    /**
     * Menghitung Hari Perkiraan Lahir (HPL) menggunakan Rumus Naegele
     */
    public function hitungHPL(string $hpht): ?string
    {
        try {
            $date = Carbon::parse($hpht);
            // Rumus Naegele: HPHT + 7 Hari, Bulan - 3, Tahun + 1
            $date->addDays(7)->subMonths(3)->addYear();
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Cek Risiko KEK dari Lingkar Lengan Atas (Standar Kemenkes: 23.5 cm)
     */
    private function cekLILA(float $lila): array
    {
        if ($lila <= 0) return ['status' => 'Belum Diukur', 'pesan' => '-'];

        if ($lila < 23.5) {
            return ['status' => 'Risiko KEK', 'pesan' => 'LILA < 23.5 cm. Berikan Makanan Tambahan (PMT) Ibu Hamil.'];
        }
        return ['status' => 'Normal', 'pesan' => 'Status gizi ibu baik (Tidak KEK).'];
    }

    /**
     * Cek Tensi untuk Kewaspadaan Preeklampsia
     */
    private function cekTensi(string $tensi): array
    {
        if (empty($tensi) || $tensi === '0/0' || $tensi === '-') {
            return ['status' => 'Belum Diukur', 'pesan' => '-'];
        }

        $parts = explode('/', $tensi);
        $sistolik = (int) ($parts[0] ?? 0);
        $diastolik = (int) ($parts[1] ?? 0);

        if ($sistolik >= 140 || $diastolik >= 90) {
            return ['status' => 'Hipertensi / Risiko Preeklampsia', 'pesan' => "Tensi $tensi. Waspada keracunan kehamilan!"];
        } elseif ($sistolik < 90) {
            return ['status' => 'Hipotensi', 'pesan' => "Tensi $tensi. Ibu berisiko pusing/pingsan, perbanyak istirahat."];
        }
        return ['status' => 'Normal', 'pesan' => 'Tekanan darah aman terkendali.'];
    }

    /**
     * Cek Detak Jantung Janin (DJJ)
     */
    private function cekDJJ(float $djj): array
    {
        if ($djj <= 0) return ['status' => 'Belum Diukur', 'pesan' => '-'];

        if ($djj >= 120 && $djj <= 160) {
            return ['status' => 'Normal', 'pesan' => 'Detak jantung janin kuat dan teratur.'];
        }
        return ['status' => 'Gawat Janin', 'pesan' => "DJJ $djj bpm berada di luar rentang aman (120-160 bpm)."];
    }

    /**
     * Fallback System
     */
    private function fallbackError(): array
    {
        return [
            'lila'  => ['status' => 'Menunggu Data', 'pesan' => '-'],
            'tensi' => ['status' => 'Menunggu Data', 'pesan' => '-'],
            'djj'   => ['status' => 'Menunggu Data', 'pesan' => '-'],
            'kesimpulan' => [
                'status' => 'Analisis Terhambat',
                'pesan'  => 'Pastikan data Tensi, LILA, dan DJJ telah diinput dengan benar.',
                'risiko' => 'Tidak Diketahui'
            ]
        ];
    }
}