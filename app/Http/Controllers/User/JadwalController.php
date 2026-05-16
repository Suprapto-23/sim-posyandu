<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\JadwalPosyandu;
use App\Traits\DetectsUserPeran;
use Carbon\Carbon;

/**
 * JadwalController (User/Warga)
 * * Optimalisasi:
 * - Penambahan Try-Catch untuk mencegah "White Screen of Death" jika database bermasalah.
 * - Logika Hak Akses yang lebih ringkas.
 * - Pengurutan cerdas: Jadwal terdekat muncul paling atas.
 */
class JadwalController extends Controller
{
    use DetectsUserPeran;

    /**
     * Menampilkan daftar jadwal posyandu yang relevan bagi warga.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Mengambil context user (siapa saja anggota keluarga yang terdaftar)
            $ctx = $this->getUserContext($user);

            // 1. Menentukan Hak Akses Jadwal
            // Secara default, semua warga bisa melihat jadwal kategori 'semua'
            $hakAkses = ['semua'];

            // Cek peran dari trait DetectsUserPeran
            if (in_array('orang_tua', $ctx['peran'])) $hakAkses[] = 'balita';
            if (in_array('remaja',    $ctx['peran'])) $hakAkses[] = 'remaja';
            if (in_array('lansia',    $ctx['peran'])) $hakAkses[] = 'lansia';
            if (in_array('bumil',     $ctx['peran'])) $hakAkses[] = 'ibu_hamil';

            // 2. Filter Tab (Standarisasi 'filter')
            $filterTarget = $request->get('filter', 'semua');
            $validFilters = ['semua', 'balita', 'remaja', 'lansia', 'ibu_hamil'];
            
            if (!in_array($filterTarget, $validFilters)) {
                $filterTarget = 'semua';
            }

            // 3. Query Utama dengan Eager Loading (Jika diperlukan)
            $query = JadwalPosyandu::where('status', 'aktif')
                ->whereIn('target_peserta', $hakAkses);

            if ($filterTarget !== 'semua') {
                $query->where('target_peserta', $filterTarget);
            }

            // 4. Pengurutan Cerdas (Smart Ordering)
            // - Prioritas 1: Jadwal hari ini dan masa depan (Urutan Mendatang/ASC)
            // - Prioritas 2: Jadwal yang sudah lewat (Urutan Terbaru/DESC)
            $query->orderByRaw("
                CASE WHEN tanggal >= CURDATE() THEN 0 ELSE 1 END ASC,
                CASE WHEN tanggal >= CURDATE() THEN tanggal ELSE NULL END ASC,
                CASE WHEN tanggal < CURDATE() THEN tanggal ELSE NULL END DESC,
                waktu_mulai ASC
            ");

            $jadwalKegiatan = $query->paginate(9)->withQueryString();

            // 5. Kalkulasi Badge Count untuk Tabs di UI
            // Gunakan clone agar tidak merusak query utama (Efisien)
            $base = JadwalPosyandu::where('status', 'aktif')->whereIn('target_peserta', $hakAkses);

            $summary = [
                'semua'     => (clone $base)->count(),
                'balita'    => (clone $base)->where('target_peserta', 'balita')->count(),
                'remaja'    => (clone $base)->where('target_peserta', 'remaja')->count(),
                'lansia'    => (clone $base)->where('target_peserta', 'lansia')->count(),
                'ibu_hamil' => (clone $base)->where('target_peserta', 'ibu_hamil')->count(),
                'mendatang' => (clone $base)->whereDate('tanggal', '>=', Carbon::today())->count(),
            ];

            return view('user.jadwal.index', compact(
                'jadwalKegiatan',
                'hakAkses',
                'filterTarget',
                'summary'
            ));

        } catch (\Exception $e) {
            // Catat error ke file log untuk debugging tanpa mengganggu user
            Log::error('Gagal memuat halaman Jadwal User: ' . $e->getMessage());

            return redirect()->route('user.dashboard')
                ->with('error', 'Gagal memuat jadwal. Silakan coba beberapa saat lagi.');
        }
    }
}