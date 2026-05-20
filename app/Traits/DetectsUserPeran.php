<?php

namespace App\Traits;

use App\Models\Balita;
use App\Models\Remaja;
use App\Models\Lansia;
use Illuminate\Support\Facades\Log;

/**
 * DetectsUserPeran
 *
 * Trait ini adalah SATU-SATUNYA tempat untuk:
 * 1. Mendeteksi NIK user dari berbagai sumber
 * 2. Menentukan "peran" user berdasarkan data di database
 * 3. Mengambil entitas pasien berdasarkan NIK
 *
 * STATUS: PUBLIC agar bisa diakses dengan aman dari file Blade (View).
 */
trait DetectsUserPeran
{
    /**
     * Ambil NIK user dari berbagai sumber (users.nik, profiles.nik, username numerik).
     * Urutan prioritas: users.nik → profiles.nik → username numerik → null
     */
    public function detectNik($user): ?string
    {
        // Sumber 1: Kolom nik langsung di tabel users (paling reliable)
        if (!empty($user->nik)) {
            return $user->nik;
        }

        // Sumber 2: Tabel profiles (jika user update NIK via profil)
        if ($user->relationLoaded('profile') || $user->profile) {
            if (!empty($user->profile->nik)) {
                return $user->profile->nik;
            }
        }

        // Sumber 3: Email format NIK@posyandu.user (pola login warga)
        // Email user warga biasanya: 3326032302040001@posyandu.user
        if (!empty($user->email) && str_contains($user->email, '@posyandu.user')) {
            $extracted = explode('@', $user->email)[0];
            if (is_numeric($extracted) && strlen($extracted) === 16) {
                return $extracted;
            }
        }

        return null;
    }

    /**
     * Deteksi semua peran user berdasarkan NIK-nya di database.
     * Return array peran + referensi ke entitas terkait.
     * * VISIBILITAS: public (Agar bisa dipanggil via Anonymous class di Sidebar Blade)
     */
    public function getUserContext($user = null): array
    {
        if (!$user) {
            $user = auth()->user();
        }

        if (!$user) {
            return [
                'nik' => null, 'peran' => ['umum'], 
                'balitas' => collect(), 'remaja' => null, 
                'lansia' => null, 'is_multi_peran' => false
            ];
        }

        $nik   = $this->detectNik($user);
        $peran = [];

        $balitas = collect();
        $remaja  = null;
        $lansia  = null;

        if ($nik) {
            try {
                // Cek orang tua (NIK cocok dengan nik_ibu atau nik_ayah di balitas)
                // Juga cek user_id (jika balita sudah di-sync ke akun)
                $balitas = Balita::where(function ($q) use ($nik, $user) {
                    $q->where('nik_ibu', $nik)
                      ->orWhere('nik_ayah', $nik)
                      ->orWhere('nik', $nik)      // balita itu sendiri yang login
                      ->orWhere('user_id', $user->id);
                })->orderBy('tanggal_lahir', 'desc')->get();

                if ($balitas->isNotEmpty()) {
                    $peran[] = 'orang_tua';
                }
            } catch (\Throwable $e) {
                Log::warning('DetectsUserPeran: balita check error - ' . $e->getMessage());
            }

            try {
                $remaja = Remaja::where('nik', $nik)
                    ->orWhere('user_id', $user->id)
                    ->first();

                if ($remaja) {
                    $peran[] = 'remaja';
                }
            } catch (\Throwable $e) {
                Log::warning('DetectsUserPeran: remaja check error - ' . $e->getMessage());
            }

            try {
                $lansia = Lansia::where('nik', $nik)
                    ->orWhere('user_id', $user->id)
                    ->first();

                if ($lansia) {
                    $peran[] = 'lansia';
                }
            } catch (\Throwable $e) {
                Log::warning('DetectsUserPeran: lansia check error - ' . $e->getMessage());
            }
        }

        // Jika tidak ada peran yang cocok
        if (empty($peran)) {
            $peran[] = 'umum';
        }

        return [
            'nik'           => $nik,
            'peran'         => $peran,
            'balitas'       => $balitas,
            'remaja'        => $remaja,
            'lansia'        => $lansia,
            'is_multi_peran'=> count($peran) > 1,
        ];
    }

    /**
     * Shortcut: ambil peran utama (peran pertama yang terdeteksi).
     * Digunakan untuk redirect otomatis di HomeController.
     * Prioritas: orang_tua > remaja > lansia  > umum
     */
    public function getPeranUtama($user): string
    {
        $ctx = $this->getUserContext($user);
        $prioritas = ['orang_tua', 'remaja', 'lansia', 'umum'];

        foreach ($prioritas as $p) {
            if (in_array($p, $ctx['peran'])) {
                return $p;
            }
        }

        return 'umum';
    }
}