<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Traits\DetectsUserPeran;
use App\Models\Balita;
use App\Models\Pemeriksaan;
use App\Models\Imunisasi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BalitaController extends Controller
{
    use DetectsUserPeran;

    /**
     * Menampilkan Buku KIA Digital (Detail Tumbuh Kembang & Imunisasi).
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $ctx  = $this->getUserContext($user);

            // 1. Validasi Kepemilikan Data (Keamanan Lapis Pertama)
            $balita = $ctx['balitas']->find($id);
            
            if (!$balita) {
                return redirect()->route('user.monitoring.index')
                    ->with('error', 'Akses ditolak. Data balita tidak ditemukan atau bukan milik Anda.');
            }

            // 2. Kalkulasi Usia Presisi
            $tanggalLahir = Carbon::parse($balita->tanggal_lahir);
            $diff         = $tanggalLahir->diff(now());
            $usia_tahun   = $diff->y;
            $usia_bulan   = $diff->m;
            $usia_hari    = $diff->d;

            $totalBulan = ($usia_tahun * 12) + $usia_bulan;
            $balita->kategori_medis = $totalBulan < 12 ? 'Bayi' : 'Balita';

            // 3. Ambil Riwayat Pemeriksaan (Urut Lama -> Baru untuk Grafik KMS)
            $riwayatPemeriksaanAsc = Pemeriksaan::where('pasien_id', $balita->id)
                ->whereIn('kategori_pasien', ['balita', 'bayi'])
                ->where('status_verifikasi', 'verified')
                ->orderBy('tanggal_periksa', 'asc')
                ->get();

            // 4. Transformasi Data Instan untuk Chart.js / ApexCharts
            $grafikData = [
                'labels' => [],
                'berat'  => [],
                'tinggi' => [],
                'lk'     => []
            ];

            if ($riwayatPemeriksaanAsc->isNotEmpty()) {
                $grafikData = [
                    'labels' => $riwayatPemeriksaanAsc->map(fn($p) => Carbon::parse($p->tanggal_periksa)->translatedFormat('d M Y'))->toArray(),
                    'berat'  => $riwayatPemeriksaanAsc->pluck('berat_badan')->map(fn($v) => $v ? (float)$v : null)->toArray(),
                    'tinggi' => $riwayatPemeriksaanAsc->pluck('tinggi_badan')->map(fn($v) => $v ? (float)$v : null)->toArray(),
                    'lk'     => $riwayatPemeriksaanAsc->pluck('lingkar_kepala')->map(fn($v) => $v ? (float)$v : null)->toArray(),
                ];
            }

            // 5. Ambil Riwayat Imunisasi (Eager Load kunjungan & Gunakan class constant)
            $riwayatImunisasi = Imunisasi::with('kunjungan')
                ->whereHas('kunjungan', function ($q) use ($balita) {
                    $q->where('pasien_id', $balita->id)
                      ->where('pasien_type', Balita::class); // Lebih aman dan kebal refactor dibanding string biasa
                })
                ->orderBy('tanggal_imunisasi', 'desc')
                ->get();

            // 6. Reverse Array untuk Tabel (Urut Baru -> Lama untuk List View)
            $riwayatPemeriksaanDesc = $riwayatPemeriksaanAsc->reverse();

            return view('user.balita.show', compact(
                'balita', 
                'riwayatPemeriksaanDesc', 
                'riwayatImunisasi',
                'grafikData', 
                'usia_tahun', 
                'usia_bulan', 
                'usia_hari',
                'totalBulan'
            ));

        } catch (\Exception $e) {
            // 7. Penanganan Error Senyap (Sistem tetap berjalan, tapi terekam di background)
            Log::error('Error pada BalitaController@show: ' . $e->getMessage());
            
            return redirect()->route('user.monitoring.index')
                ->with('error', 'Terjadi kesalahan saat memuat data Buku KIA. Silakan coba lagi nanti.');
        }
    }
}