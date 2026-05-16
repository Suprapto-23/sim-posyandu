<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Traits\DetectsUserPeran; // Tambahkan Trait
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MonitoringController extends Controller
{
    use DetectsUserPeran; // Aktifkan Trait

    /**
     * Menampilkan Dasbor Monitoring Kesehatan Terpadu Warga.
     * Terintegrasi dengan Trait agar membaca seluruh relasi keluarga (NIK Ayah/Ibu)
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            // 1. Gunakan Trait Cerdas untuk mendapatkan SEMUA relasi demografi pengguna ini
            $ctx = $this->getUserContext($user);

            // 2. Ambil data yang sudah dikumpulkan oleh Trait dan muat relasi rekam medisnya (Eager Loading)
            $balitas = $ctx['balitas']->load([
                'kunjungans' => function($query) {
                    $query->latest('tanggal_kunjungan');
                },
                'kunjungans.pemeriksaan',
                'pemeriksaan_terakhir'
            ]);

            $remajas = collect();
            if ($ctx['remaja']) {
                $remajas->push($ctx['remaja']->load([
                    'kunjungans' => function($query) { $query->latest('tanggal_kunjungan'); },
                    'kunjungans.pemeriksaan',
                    'pemeriksaan_terakhir'
                ]));
            }

            $ibuHamils = collect();
            if ($ctx['bumil']) {
                $ibuHamils->push($ctx['bumil']->load([
                    'kunjungans' => function($query) { $query->latest('tanggal_kunjungan'); },
                    'kunjungans.pemeriksaan',
                    'pemeriksaan_terakhir'
                ]));
            }

            $lansias = collect();
            if ($ctx['lansia']) {
                $lansias->push($ctx['lansia']->load([
                    'kunjungans' => function($query) { $query->latest('tanggal_kunjungan'); },
                    'kunjungans.pemeriksaan',
                    'pemeriksaan_terakhir'
                ]));
            }

            // 3. FLAG KETERSEDIAAN DATA (Untuk UI Empty State)
            $hasData = $balitas->isNotEmpty() || $ibuHamils->isNotEmpty() || $remajas->isNotEmpty() || $lansias->isNotEmpty();

            return view('user.monitoring.index', compact(
                'balitas', 
                'ibuHamils', 
                'remajas', 
                'lansias',
                'hasData'
            ));

        } catch (\Exception $e) {
            // Catat error secara diam-diam
            Log::error('Error pada MonitoringController@index: ' . $e->getMessage());
            
            return redirect()
                ->route('user.dashboard')
                ->with('error', 'Sistem sedang memproses pembaruan. Gagal memuat data pemantauan saat ini.');
        }
    }
}