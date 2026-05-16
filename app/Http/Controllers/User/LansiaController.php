<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Traits\DetectsUserPeran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LansiaController extends Controller
{
    use DetectsUserPeran;

    public function show($id)
    {
        try {
            $user = Auth::user();
            $ctx  = $this->getUserContext($user);

            $lansia = ($ctx['lansia'] && $ctx['lansia']->id == $id) ? $ctx['lansia'] : null;

            if (!$lansia) {
                return redirect()->route('user.monitoring.index')->with('error', 'Data lansia tidak ditemukan.');
            }

            $riwayat = $lansia->kunjungans()
                ->with('pemeriksaan')
                ->whereHas('pemeriksaan', fn($q) => $q->where('status_verifikasi', 'verified'))
                ->latest()
                ->get();

            return view('user.lansia.show', compact('lansia', 'riwayat'));

        } catch (\Exception $e) {
            Log::error('Error LansiaController: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat rekam medis lansia.');
        }
    }
}