<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Traits\DetectsUserPeran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IbuHamilController extends Controller
{
    use DetectsUserPeran;

    public function show($id)
    {
        try {
            $user = Auth::user();
            $ctx  = $this->getUserContext($user);

            $bumil = ($ctx['bumil'] && $ctx['bumil']->id == $id) ? $ctx['bumil'] : null;

            if (!$bumil) {
                return redirect()->route('user.monitoring.index')->with('error', 'Data ibu hamil tidak ditemukan.');
            }

            $riwayat = $bumil->kunjungans()
                ->with('pemeriksaan')
                ->whereHas('pemeriksaan', fn($q) => $q->where('status_verifikasi', 'verified'))
                ->latest()
                ->get();

            return view('user.ibu_hamil.show', compact('bumil', 'riwayat'));

        } catch (\Exception $e) {
            Log::error('Error IbuHamilController: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat data kehamilan.');
        }
    }
}