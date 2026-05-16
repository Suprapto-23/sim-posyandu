<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Notifikasi;

/**
 * NotifikasiController (User/Warga)
 *
 * PERBAIKAN [BUG-6]:
 * View notifikasi/index.blade.php membutuhkan variabel:
 * - $notifikasis  (sudah ada)
 * - $filter       (sudah ada)
 * - $allCount     (BARU — jumlah total semua notif)
 * - $unreadCount  (BARU — jumlah belum dibaca, untuk badge)
 * Controller lama tidak mengirim $allCount dan $unreadCount
 * sehingga badge "Belum Dibaca" selalu kosong.
 */
class NotifikasiController extends Controller
{
    /**
     * Halaman Kotak Masuk.
     * Route: GET /user/notifikasi → user.notifikasi.index
     */
    public function index(Request $request)
    {
        $user   = Auth::user();
        $filter = $request->get('filter', 'semua');

        try {
            $query = Notifikasi::where('user_id', $user->id)->latest();

            // Filter tab
            if ($filter === 'belum') {
                $query->where('is_read', false);
            } elseif ($filter === 'sudah') {
                $query->where('is_read', true);
            }

            $notifikasis = $query->paginate(15)->withQueryString();

            // [BUG-6 FIX] Tambahkan allCount dan unreadCount
            $allCount    = Notifikasi::where('user_id', $user->id)->count();
            $unreadCount = Notifikasi::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            return view('user.notifikasi.index', compact(
                'notifikasis',
                'filter',
                'allCount',
                'unreadCount'
            ));

        } catch (\Throwable $e) {
            Log::warning('NotifikasiController::index error: ' . $e->getMessage());

            $notifikasis = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            $allCount    = 0;
            $unreadCount = 0;

            return view('user.notifikasi.index', compact('notifikasis', 'filter', 'allCount', 'unreadCount'))
                ->with('error', 'Gagal memuat pesan.');
        }
    }

    /**
     * AJAX polling — Mengembalikan jumlah unread dan HTML list dropdown.
     * Route: GET /user/notifikasi/fetch → user.notifikasi.fetch
     */
    public function fetchRecent()
    {
        try {
            $userId = Auth::id();

            // 1. Ambil jumlah unread
            $unreadCount = Notifikasi::where('user_id', $userId)
                ->where('is_read', false)
                ->count();

            // 2. Ambil 5 notifikasi terbaru untuk ditampilkan di dropdown
            $recentNotifs = Notifikasi::where('user_id', $userId)
                ->latest()
                ->take(5)
                ->get();

            // 3. Bangun string HTML untuk menggantikan loading spinner
            $html = '';
            if ($recentNotifs->isEmpty()) {
                $html = '
                    <div class="py-8 text-center flex flex-col items-center justify-center bg-white">
                        <i class="fas fa-inbox text-2xl text-slate-200 mb-2"></i>
                        <p class="text-[11px] font-medium text-slate-400">Tidak ada pesan baru.</p>
                    </div>';
            } else {
                foreach ($recentNotifs as $notif) {
                    // Penanda CSS jika pesan belum dibaca
                    $bgClass = $notif->is_read ? 'bg-white' : 'bg-teal-50/40';
                    $dotIcon = !$notif->is_read 
                        ? '<span class="w-2 h-2 rounded-full bg-rose-500 shrink-0 mt-1.5 shadow-[0_0_5px_rgba(244,63,94,0.5)]"></span>' 
                        : '<span class="w-2 h-2 rounded-full bg-slate-200 shrink-0 mt-1.5"></span>';
                    
                    $time = $notif->created_at->diffForHumans();
                    
                    // Format HTML per baris notifikasi (mengikuti gaya desain UI Anda)
                    $html .= '
                        <a href="'. route('user.notifikasi.index') .'" class="flex items-start gap-3 p-4 border-b border-teal-50 hover:bg-slate-50 transition-colors duration-200 '. $bgClass .'">
                            '. $dotIcon .'
                            <div class="flex-1 min-w-0">
                                <h4 class="text-[12.5px] font-black text-slate-800 leading-tight truncate font-poppins">'. htmlspecialchars($notif->judul) .'</h4>
                                <p class="text-[11px] text-slate-500 mt-1 line-clamp-2 leading-relaxed">'. htmlspecialchars($notif->pesan) .'</p>
                                <span class="text-[9px] font-bold text-slate-400 mt-1.5 block uppercase tracking-wider"><i class="far fa-clock mr-1"></i>'. $time .'</span>
                            </div>
                        </a>
                    ';
                }
            }

            // 4. Siapkan data untuk trigger SweetAlert Toast di UI
            $latestTitle = $recentNotifs->first()->judul ?? 'Pesan Baru';
            $latestBody  = $recentNotifs->first()->pesan ?? 'Anda memiliki pesan baru.';

            // 5. Kembalikan semua data dalam format JSON
            return response()->json([
                'unreadCount'  => $unreadCount,
                'html'         => $html,
                'latest_title' => $latestTitle,
                'latest_body'  => $latestBody
            ]);

        } catch (\Throwable $e) {
            Log::warning('NotifikasiController::fetchRecent error: ' . $e->getMessage());
            return response()->json([
                'unreadCount' => 0, 
                'html' => '<div class="py-6 text-center text-rose-500 text-xs">Gagal memuat pesan.</div>'
            ]);
        }
    }

    /**
     * Tandai satu notifikasi sudah dibaca.
     * Route: POST /user/notifikasi/{id}/read → user.notifikasi.read
     */
    public function markRead(Request $request, $id)
    {
        try {
            Notifikasi::where('user_id', Auth::id())
                ->where('id', $id)
                ->update(['is_read' => true]);
        } catch (\Throwable $e) {
            Log::warning('NotifikasiController::markRead error: ' . $e->getMessage());
        }
        return back();
    }

    /**
     * Tandai semua notifikasi sudah dibaca.
     * Route: POST /user/notifikasi/mark-all-read → user.notifikasi.markall
     */
    public function markAllRead()
    {
        try {
            Notifikasi::where('user_id', Auth::id())
                ->where('is_read', false)
                ->update(['is_read' => true]);
        } catch (\Throwable $e) {
            Log::warning('NotifikasiController::markAllRead error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses permintaan.');
        }
        return back()->with('success', 'Semua pesan telah ditandai dibaca.');
    }
}