<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Pemeriksaan;

class PemeriksaanController extends Controller
{
    /**
     * INDEX: Ruang Tunggu Validasi Bidan (Triase Meja 5)
     */
    public function index(Request $request)
    {
        try {
            $tab = $request->get('tab', 'pending');
            $search = $request->get('search');

            $query = Pemeriksaan::with(['kunjungan.pasien', 'pemeriksa'])->latest();

            if ($tab === 'verified') {
                $query->verified();
            } else {
                $query->pending();
                $tab = 'pending';
            }

            if ($search) {
                $query->whereHas('kunjungan.pasien', function($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%");
                });
            }

            $pemeriksaans = $query->paginate(15)->withQueryString();
            $pendingCount = Pemeriksaan::pending()->count();

            return view('bidan.pemeriksaan.index', compact('pemeriksaans', 'tab', 'pendingCount'));

        } catch (\Exception $e) {
            Log::error('BIDAN_INDEX_ERROR: ' . $e->getMessage());
            abort(500, 'Sistem gagal memuat antrian pemeriksaan.');
        }
    }

    /**
     * SHOW: Ruang Konsultasi (Menampilkan data untuk divalidasi)
     */
    public function show($id)
    {
        $pemeriksaan = Pemeriksaan::with(['kunjungan.pasien', 'pemeriksa'])->findOrFail($id);
        return view('bidan.pemeriksaan.show', compact('pemeriksaan'));
    }

    /**
     * UPDATE: Finalisasi & Pengesahan Medis (DENGAN LOGIKA JEMBATAN IMUNISASI)
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'suhu_tubuh'     => 'nullable|numeric|between:30,45',
            'tekanan_darah'  => 'nullable|string|max:20',
            'status_gizi'    => 'nullable|string|max:50',
            'diagnosa'       => 'required|string|min:5',
            'tindakan'       => 'required|string|min:5',
        ], [
            'diagnosa.required' => 'Hasil diagnosa klinis wajib diisi sebelum pengesahan.',
            'tindakan.required' => 'Saran tindakan atau resep wajib diberikan.',
        ]);

        DB::beginTransaction();
        try {
            $pemeriksaan = Pemeriksaan::with('kunjungan')->findOrFail($id);
            
            $clinicalData = $request->only([
                'suhu_tubuh', 
                'tekanan_darah', 
                'status_gizi', 
                'diagnosa', 
                'tindakan'
            ]);
            
            // Stempel Otoritas Bidan mutlak
            $clinicalData['status_verifikasi'] = 'verified'; 
            $clinicalData['verified_by']       = Auth::id();
            $clinicalData['verified_at']       = Carbon::now();

            $pemeriksaan->update($clinicalData);

            DB::commit();
            
            // ====================================================================
            // LOGIKA JEMBATAN CERDAS (SMART BRIDGE)
            // Jika request datang dari AJAX (fetch API di view show), balas dgn JSON
            // ====================================================================
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Pemeriksaan telah disahkan secara permanen.',
                    'data'    => [
                        'pasien_id'   => $pemeriksaan->pasien_id,
                        'pasien_type' => class_basename($pemeriksaan->kunjungan->pasien_type ?? ''),
                        'kategori'    => $pemeriksaan->kategori_pasien,
                        'nama'        => $pemeriksaan->nama_pasien
                    ]
                ]);
            }
            
            // Fallback jika browser tidak mendukung JS (Standard form submit)
            return redirect()->route('bidan.pemeriksaan.index', ['tab' => 'verified'])
                             ->with('success', 'Pemeriksaan telah disahkan secara permanen ke Rekam Medis (EMR).');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BIDAN_VALIDASI_ERROR: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal memproses validasi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * DESTROY: Penghapusan Data (Emergency Only)
     */
    public function destroy($id)
    {
        try {
            $pem = Pemeriksaan::findOrFail($id);
            $pem->delete();
            return back()->with('success', 'Data antrian berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data.');
        }
    }
}