<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NotifikasiController extends Controller
{
    /** Jumlah notifikasi yang dikirim ke dropdown lonceng. */
    private const FEED_LIMIT = 8;

    // =====================================================================
    // HELPER (dipakai juga oleh view kader/notifikasi/_list.blade.php)
    // =====================================================================

    /**
     * Menentukan jenis, ikon, dan label notifikasi.
     * Hanya mengembalikan string. Kelas Tailwind sengaja tetap ada di file Blade
     * supaya ikut ter-scan saat build dan tidak hilang di production.
     */
    public static function describe(Notifikasi $n): array
    {
        $text = Str::lower(($n->tipe ?? '') . ' ' . ($n->judul ?? '') . ' ' . ($n->pesan ?? ''));

        if (Str::contains($text, ['jadwal', 'agenda'])) {
            return ['tone' => 'emerald', 'icon' => 'fas fa-calendar-check', 'label' => 'Jadwal'];
        }
        if (Str::contains($text, ['pemeriksaan', 'validasi', 'rekam medis', 'kesehatan'])) {
            return ['tone' => 'sky', 'icon' => 'fas fa-stethoscope', 'label' => 'Kesehatan'];
        }
        if (Str::contains($text, ['imunisasi', 'vaksin'])) {
            return ['tone' => 'teal', 'icon' => 'fas fa-syringe', 'label' => 'Imunisasi'];
        }
        if (Str::contains($text, ['laporan', 'rekap'])) {
            return ['tone' => 'amber', 'icon' => 'fas fa-file-medical', 'label' => 'Laporan'];
        }

        return ['tone' => 'slate', 'icon' => $n->tipe_icon ?? 'fas fa-bell', 'label' => 'Informasi'];
    }

    /** Hanya izinkan tautan relatif (/...) atau http(s). Mencegah javascript: dari data. */
    public static function safeLink(?string $link): ?string
    {
        $link = trim((string) $link);

        if ($link === '' || $link === '#') {
            return null;
        }

        return preg_match('#^(https?://|/)#i', $link) ? $link : null;
    }

    private function base()
    {
        return Notifikasi::where('user_id', Auth::id());
    }

    private function counters(): array
    {
        return [
            'unread' => $this->base()->belumDibaca()->count(),
            'total'  => $this->base()->count(),
        ];
    }

    private function toFeedItem(Notifikasi $n): array
    {
        $meta = self::describe($n);

        return [
            'id'         => (int) $n->id,
            'title'      => $n->judul ?: 'Informasi Posyandu',
            'message'    => Str::limit(strip_tags((string) $n->pesan), 140),
            'url'        => self::safeLink($n->link) ?? route('kader.notifikasi.index'),
            'is_read'    => (bool) $n->is_read,
            'tone'       => $meta['tone'],
            'icon'       => $meta['icon'],
            'label'      => $meta['label'],
            'time'       => $n->created_at?->copy()->locale('id')->diffForHumans(),
            'created_at' => $n->created_at?->toIso8601String(),
        ];
    }

    // =====================================================================
    // HALAMAN DAFTAR NOTIFIKASI
    // =====================================================================
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'semua');

        $query = $this->base()->orderByDesc('created_at')->orderByDesc('id');

        if ($filter === 'belum_dibaca') {
            $query->belumDibaca();
        }

        $notifikasis = $query->paginate(15)->withQueryString();
        $unreadCount = $this->base()->belumDibaca()->count();

        // Halaman kosong karena item terakhir di halaman ini baru dihapus: pindah ke halaman terakhir.
        if ($notifikasis->isEmpty() && $notifikasis->currentPage() > 1) {
            $lastUrl = $notifikasis->url($notifikasis->lastPage());

            return $request->wantsJson()
                ? response()->json(['redirect' => $lastUrl])
                : redirect()->to($lastUrl);
        }

        $data = compact('notifikasis', 'filter', 'unreadCount');

        // Permintaan AJAX dari halaman (Accept: application/json): kirim potongan HTML daftar saja.
        // Navigasi SPA mengirim Accept: text/html, jadi tetap mendapat halaman penuh.
        if ($request->wantsJson()) {
            return response()
                ->json([
                    'html'   => view('kader.notifikasi._list', $data)->render(),
                    'unread' => $unreadCount,
                    'total'  => $notifikasis->total(),
                ])
                ->header('Cache-Control', 'no-store');
        }

        return view('kader.notifikasi.index', $data);
    }

    // =====================================================================
    // AKSI (mendukung AJAX tanpa reload dan tetap aman bila JS mati)
    // =====================================================================
    public function markAllRead(Request $request)
    {
        $this->base()->belumDibaca()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'Semua notifikasi ditandai dibaca.'] + $this->counters());
        }

        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    public function markAsRead(Request $request, $id)
    {
        $this->base()->findOrFail($id)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true] + $this->counters());
        }

        return back();
    }

    public function destroy(Request $request, $id)
    {
        $this->base()->findOrFail($id)->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'Notifikasi dihapus.'] + $this->counters());
        }

        return back()->with('success', 'Arsip notifikasi berhasil dihapus permanen.');
    }

    // =====================================================================
    // FEED REAL-TIME (dipoll layout tiap beberapa detik)
    // Satu endpoint untuk lonceng DAN halaman notifikasi.
    // =====================================================================
    public function feed()
    {
        $userId = Auth::id();

        $items = Notifikasi::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(self::FEED_LIMIT)
            ->get()
            ->map(fn (Notifikasi $n) => $this->toFeedItem($n))
            ->values();

        return response()
            ->json($this->counters() + [
                'latest_id' => (int) $this->base()->max('id'),
                'items'     => $items,
            ])
            ->header('Cache-Control', 'no-store, max-age=0');
    }

    /**
     * Route lama kader.notifikasi.count hanya mengembalikan {unread}, padahal layout
     * butuh daftar notifikasi. Sekarang disamakan dengan feed() supaya lonceng
     * tetap jalan walaupun hanya route "count" yang terdaftar.
     */
    public function count()
    {
        return $this->feed();
    }

    // =====================================================================
    // LEGACY: tidak dipakai layout saat ini. Dipertahankan agar route lama tidak error.
    // =====================================================================
    public function fetchRecent()
    {
        try {
            $userId = Auth::id();

            $unreadCount  = Notifikasi::where('user_id', $userId)->belumDibaca()->count();
            $latestNotifs = Notifikasi::where('user_id', $userId)->terbaru()->get();
            $latestUnread = Notifikasi::where('user_id', $userId)->belumDibaca()->latest()->first();

            $html = '';

            if ($latestNotifs->isEmpty()) {
                $html .= '
                <div class="flex flex-col items-center justify-center py-10 text-slate-400">
                    <div class="w-14 h-14 bg-slate-50 rounded-[16px] flex items-center justify-center mb-4 shadow-inner border border-slate-100 transform -rotate-3">
                        <i class="fas fa-bell-slash text-2xl opacity-40"></i>
                    </div>
                    <p class="text-[11px] font-black uppercase tracking-widest text-slate-500">Layar Bersih</p>
                    <p class="text-[12px] font-medium text-slate-400 mt-1">Tidak ada sinyal baru.</p>
                </div>';
            } else {
                foreach ($latestNotifs as $n) {
                    $nexus = $n->toNexusFormat();

                    $bgClass     = $nexus['is_read'] ? 'bg-white border-l-4 border-l-transparent' : 'bg-indigo-50/40 border-l-4 border-l-indigo-500';
                    $iconBgClass = $nexus['is_read'] ? 'bg-slate-50 text-slate-400 border-slate-100' : ($nexus['styles'] ?? 'bg-slate-100 text-slate-600 border-slate-200') . ' shadow-sm';
                    $titleClass  = $nexus['is_read'] ? 'text-slate-600' : 'text-slate-900';

                    $route = ($nexus['link'] ?? '#') !== '#' ? $nexus['link'] : route('kader.notifikasi.index');
                    $judul = e($nexus['judul'] ?? $nexus['title'] ?? 'Notifikasi');
                    $pesan = e($nexus['pesan'] ?? $nexus['message'] ?? '');
                    $waktu = e($nexus['waktu'] ?? $nexus['created_at'] ?? '');
                    $icon  = $nexus['icon'] ?? 'fas fa-bell';

                    $html .= "
                    <a href=\"{$route}\" class=\"flex gap-4 px-5 py-4 hover:bg-slate-50 transition-colors border-b border-slate-100/80 {$bgClass} group\">
                        <div class=\"w-11 h-11 rounded-[14px] flex items-center justify-center shrink-0 border {$iconBgClass} transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3\">
                            <i class=\"{$icon} text-[14px]\"></i>
                        </div>
                        <div class=\"flex-1 min-w-0\">
                            <div class=\"flex justify-between items-start mb-1\">
                                <p class=\"text-[13px] font-black font-poppins {$titleClass} truncate pr-3\">{$judul}</p>
                                <span class=\"text-[9px] font-black uppercase tracking-widest text-slate-400 whitespace-nowrap pt-0.5\">{$waktu}</span>
                            </div>
                            <p class=\"text-[12px] font-medium text-slate-500 line-clamp-1 leading-relaxed\">{$pesan}</p>
                        </div>
                    </a>";
                }
            }

            return response()->json([
                'unreadCount'  => $unreadCount,
                'html'         => $html,
                'latest_title' => $latestUnread ? $latestUnread->judul : null,
                'latest_body'  => $latestUnread ? $latestUnread->pesan : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Kader Notifikasi AJAX Error: ' . $e->getMessage());

            return response()->json([
                'unreadCount' => 0,
                'html' => '
                    <div class="p-6 text-center text-rose-600 bg-rose-50 rounded-xl m-4">
                        <i class="fas fa-wifi text-3xl mb-3 opacity-50"></i>
                        <h4 class="font-black text-[13px] uppercase tracking-widest mb-2">Gangguan Sinyal</h4>
                        <p class="text-[11px] font-medium text-rose-500 bg-white p-3 rounded-lg border border-rose-200">
                            Gagal menarik data notifikasi. Sistem akan mencoba kembali secara otomatis.
                        </p>
                    </div>',
            ]);
        }
    }
}