<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Traits\DetectsUserPeran;
use App\Services\AnalisisRemajaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RemajaController extends Controller
{
    use DetectsUserPeran;

    protected $analisisService;

    public function __construct(AnalisisRemajaService $analisisService)
    {
        $this->analisisService = $analisisService;
    }

    public function show($id)
    {
        try {
            $user = Auth::user();
            $ctx  = $this->getUserContext($user);

            // Validasi: Apakah remaja ini milik user yang login?
            $remaja = ($ctx['remaja'] && $ctx['remaja']->id == $id) ? $ctx['remaja'] : null;

            if (!$remaja) {
                return redirect()->route('user.monitoring.index')->with('error', 'Akses ditolak atau data tidak ditemukan.');
            }

            // Ambil riwayat pemeriksaan terverifikasi
            $riwayat = $remaja->kunjungans()
                ->with('pemeriksaan')
                ->whereHas('pemeriksaan', fn($q) => $q->where('status_verifikasi', 'verified'))
                ->latest()
                ->get();

            // Ambil pemeriksaan terakhir untuk analisis ringkas
            $terakhir = $riwayat->first()?->pemeriksaan;
            $analisis = null;

            if ($terakhir && $terakhir->berat_badan && $terakhir->tinggi_badan) {
                $imtValue = $terakhir->berat_badan / (($terakhir->tinggi_badan / 100) ** 2);
                $analisis = $this->analisisService->analisisIMT($remaja->usia_tahun, $remaja->jenis_kelamin, $imtValue);
            }

            return view('user.remaja.show', compact('remaja', 'riwayat', 'analisis'));

        } catch (\Exception $e) {
            Log::error('Error pada RemajaController@show: ' . $e->getMessage());
            return redirect()->route('user.monitoring.index')->with('error', 'Gagal memuat detail data remaja.');
        }
    }
}