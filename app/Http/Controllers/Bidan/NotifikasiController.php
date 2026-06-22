<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NotifikasiController extends Controller
{
    /**
     * Menampilkan Halaman Utama Pusat Notifikasi Bidan
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'semua');
        
        $query = Notifikasi::where('user_id', Auth::id())->latest();

        if ($filter == 'belum_dibaca' || $filter == 'belum') {
            $query->belumDibaca(); 
        } elseif ($filter == 'sudah') {
            $query->where('is_read', true);
        }

        $notifikasis = $query->paginate(5)->withQueryString();
        $unreadCount = Notifikasi::where('user_id', Auth::id())->belumDibaca()->count();
                        
        return view('bidan.notifikasi.index', compact('notifikasis', 'filter', 'unreadCount'));
    }

    /**
     * AJAX fetch untuk lonceng notifikasi Bidan
     * Mengembalikan format yang sama dengan User (items array)
     */
    public function fetchRecent()
    {
        try {
            $userId = Auth::id();

            $unreadCount = Notifikasi::where('user_id', $userId)->belumDibaca()->count();

            $recentNotifs = Notifikasi::where('user_id', $userId)
                ->latest()
                ->limit(5)
                ->get();

            $items = $recentNotifs->map(function ($notif) {
                $isRead = (bool) $notif->is_read;
                $tipe = $notif->tipe ?? $this->guessType($notif);

                return [
                    'id' => $notif->id,
                    'judul' => $notif->judul ?: 'Pemberitahuan',
                    'pesan' => Str::limit($notif->pesan ?? '', 80),
                    'tipe' => $tipe,
                    'icon' => $this->typeIcon($tipe),
                    'link' => $notif->link ?: route('bidan.notifikasi.index'),
                    'is_read' => $isRead,
                    'waktu' => $notif->created_at ? $notif->created_at->diffForHumans() : '-',
                ];
            })->values();

            $latestUnread = Notifikasi::where('user_id', $userId)
                ->belumDibaca()
                ->latest()
                ->first();

            return response()->json([
                'unreadCount' => $unreadCount,
                'items' => $items,
                'latest_title' => $latestUnread?->judul,
                'latest_body' => $latestUnread?->pesan,
            ]);

        } catch (\Throwable $e) {
            Log::error('Bidan NotifikasiController fetch error: ' . $e->getMessage());
            return response()->json([
                'unreadCount' => 0,
                'items' => [],
                'latest_title' => null,
                'latest_body' => null,
            ]);
        }
    }

    /**
     * Eksekusi: Tandai Semua Dibaca
     */
    public function markAllRead()
    {
        Notifikasi::where('user_id', Auth::id())
                  ->belumDibaca()
                  ->update(['is_read' => true]);
                  
        return back()->with('success', 'Semua laporan telah ditandai dibaca.');
    }

    // ========== PRIVATE HELPERS ==========

    private function guessType(Notifikasi $item): string
    {
        $text = Str::lower(($item->judul ?? '') . ' ' . ($item->pesan ?? ''));

        if (str_contains($text, 'jadwal') || str_contains($text, 'agenda')) {
            return 'jadwal';
        }
        if (str_contains($text, 'imunisasi') || str_contains($text, 'vaksin')) {
            return 'imunisasi';
        }
        if (str_contains($text, 'pemeriksaan') || str_contains($text, 'rekam medis')) {
            return 'pemeriksaan';
        }
        if (str_contains($text, 'import') || str_contains($text, 'data')) {
            return 'import';
        }
        return 'info';
    }

    private function typeIcon(string $type): string
    {
        return match ($type) {
            'jadwal' => 'fa-calendar-check',
            'imunisasi' => 'fa-syringe',
            'pemeriksaan' => 'fa-stethoscope',
            'import' => 'fa-file-excel',
            default => 'fa-bell',
        };
    }
}