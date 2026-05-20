<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Traits\DetectsUserPeran;
use App\Models\JadwalPosyandu;
use App\Models\Notifikasi;
use App\Models\Pemeriksaan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * DashboardController (User/Warga)
 *
 * Dashboard "universal" — ditampilkan jika:
 * - User punya multi-peran (orang_tua sekaligus lansia, dst.)
 * - User belum terdaftar (umum) → minta isi NIK
 *
 * Untuk user single-peran, HomeController sudah redirect
 * ke halaman spesifik mereka (balita/remaja/lansia) SEBELUM
 * mencapai dashboard ini. Jadi dashboard ini adalah fallback.
 *
 * CATATAN: Tidak ada auto-detection sistem pakar di sini.
 * Semua data yang ditampilkan adalah data yang sudah divalidasi bidan.
 */
class DashboardController extends Controller
{
    use DetectsUserPeran;

    public function index()
    {
        $user = Auth::user();

        // Gunakan trait untuk deteksi context
        $ctx = $this->getUserContext($user);

        $peranUser  = $ctx['peran'];
        $nikUser    = $ctx['nik'];
        $dataAnak   = $ctx['balitas'];
        $dataRemaja = $ctx['remaja'];
        $dataLansia = $ctx['lansia'];

        // Grafik pertumbuhan untuk balita pertama (jika ada)
        $grafikData = [];
        if ($dataAnak->isNotEmpty()) {
            $grafikData = $this->getGrafikBalita($dataAnak->first()->id);
        }

        // Jadwal posyandu terdekat (disesuaikan target peserta)
        $jadwalTerdekat = collect();
        try {
            $jadwalTerdekat = $this->buildJadwalQuery($peranUser)->take(5)->get();
        } catch (\Throwable $e) {
            Log::warning('Dashboard jadwal error: ' . $e->getMessage());
        }

        // Notifikasi terbaru (5 terakhir)
        $notifikasiTerbaru          = collect();
        $totalNotifikasiBelumDibaca = 0;
        try {
            if (Schema::hasTable('notifikasis')) {
                $notifikasiTerbaru = Notifikasi::where('user_id', $user->id)
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn($n) => [
                        'id'       => $n->id,
                        'judul'    => $n->judul ?? 'Pemberitahuan',
                        'pesan'    => \Illuminate\Support\Str::limit($n->pesan ?? '', 80),
                        'waktu'    => $n->created_at->diffForHumans(),
                        // PERBAIKAN: gunakan is_read (boolean), BUKAN read_at
                        'is_read'  => (bool) $n->is_read,
                    ]);

                $totalNotifikasiBelumDibaca = Notifikasi::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count();
            }
        } catch (\Throwable $e) {
            Log::warning('Dashboard notifikasi error: ' . $e->getMessage());
        }

        // Pesan error jika NIK belum terdaftar
        $pesanError = null;
        if (in_array('umum', $peranUser)) {
            $pesanError = empty($nikUser)
                ? 'NIK belum diisi. Lengkapi profil Anda agar data kesehatan dari Posyandu dapat ditampilkan.'
                : 'NIK ' . $nikUser . ' belum terdaftar di Posyandu. Hubungi kader untuk pendaftaran.';
        }

        return view('user.dashboard', compact(
            'user',
            'peranUser',
            'nikUser',
            'dataAnak',
            'dataRemaja',
            'dataLansia',
            'grafikData',
            'jadwalTerdekat',
            'notifikasiTerbaru',
            'totalNotifikasiBelumDibaca',
            'pesanError'
        ));
    }

    /**
     * AJAX: polling notifikasi (dipanggil setiap N detik dari frontend).
     * WAJIB return JSON — jangan redirect agar tidak loop.
     */
    public function getStats()
    {
        if (!request()->expectsJson() && !request()->ajax()) {
            return response()->json(['status' => 'error'], 400);
        }

        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['status' => 'unauthenticated', 'unread_count' => 0]);
            }

            $count = 0;
            if (Schema::hasTable('notifikasis')) {
                $count = Notifikasi::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count();
            }

            return response()->json(['status' => 'success', 'unread_count' => $count]);

        } catch (\Throwable $e) {
            Log::error('getStats error: ' . $e->getMessage());
            return response()->json(['status' => 'success', 'unread_count' => 0]);
        }
    }

    // ─── Private Helpers ────────────────────────────────────────────────────

    private function buildJadwalQuery(array $peranUser)
    {
        $targets = ['semua'];
        if (in_array('orang_tua', $peranUser)) $targets[] = 'balita';
        if (in_array('remaja', $peranUser))    $targets[] = 'remaja';
        if (in_array('lansia', $peranUser))    $targets[] = 'lansia';

        return JadwalPosyandu::where('status', 'aktif')
            ->whereIn('target_peserta', $targets)
            ->orderBy('tanggal', 'desc');
    }

    private function getGrafikBalita(int $balitaId): array
    {
        try {
            $riwayat = Pemeriksaan::where('pasien_id', $balitaId)
                ->where('kategori_pasien', 'balita')
                ->where('status_verifikasi', 'verified')
                ->orderBy('tanggal_periksa', 'asc')
                ->take(12)
                ->get();

            if ($riwayat->isEmpty()) return [];

            return [
                'labels' => $riwayat->map(fn($i) => Carbon::parse($i->tanggal_periksa)->format('M y'))->toArray(),
                'berat'  => $riwayat->pluck('berat_badan')->map(fn($v) => (float) $v)->toArray(),
                'tinggi' => $riwayat->pluck('tinggi_badan')->map(fn($v) => (float) $v)->toArray(),
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }
}