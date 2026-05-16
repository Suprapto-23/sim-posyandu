<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        $notifikasis = $query->paginate(15)->withQueryString();
        $unreadCount = Notifikasi::where('user_id', Auth::id())->belumDibaca()->count();
                        
        return view('bidan.notifikasi.index', compact('notifikasis', 'filter', 'unreadCount'));
    }

    /**
     * AJAX fetch untuk lonceng notifikasi Bidan (Mengatasi Loading Muter)
     */
    public function fetchRecent()
    {
        try {
            $userId = Auth::id();

            // Ambil jumlah yang belum dibaca menggunakan Scope Model
            $unreadCount = Notifikasi::where('user_id', $userId)->belumDibaca()->count();

            // Ambil notifikasi terbaru
            $recentNotifs = Notifikasi::where('user_id', $userId)->terbaru()->get();

            $html = '';
            $latestUnread = Notifikasi::where('user_id', $userId)->belumDibaca()->latest()->first();

            if ($recentNotifs->isEmpty()) {
                $html = '
                    <div class="py-8 text-center flex flex-col items-center justify-center bg-white">
                        <i class="fas fa-bell-slash text-3xl text-slate-200 mb-3"></i>
                        <h4 class="text-[13px] font-black text-slate-800 uppercase tracking-widest mb-1">KOTAK MASUK KOSONG</h4>
                        <p class="text-[11.5px] font-medium text-slate-400">Belum ada aktifitas atau laporan baru.</p>
                    </div>';
            } else {
                foreach ($recentNotifs as $notif) {
                    $nexus = $notif->toNexusFormat(); // Menggunakan fungsi dari Model Notifikasi.php
                    
                    // Aksen warna Indigo khusus untuk Bidan
                    $bgClass     = $notif->is_read ? 'bg-white' : 'bg-indigo-50/50';
                    $borderClass = $notif->is_read ? 'border-slate-100' : 'border-indigo-100';
                    $iconBg      = $notif->is_read ? 'bg-slate-100 text-slate-400' : "bg-indigo-100 text-indigo-600";
                    $dotNotif    = !$notif->is_read ? '<span class="absolute top-1/2 -left-1.5 -translate-y-1/2 w-3 h-3 rounded-full bg-indigo-500 border-2 border-white"></span>' : '';
                    
                    // Arahkan link ke route bidan
                    $linkRoute = route('bidan.notifikasi.index');

                    $html .= "
                    <a href=\"{$linkRoute}\" class=\"flex items-start gap-4 p-4 border-b hover:bg-slate-50 transition-all duration-200 relative {$bgClass} {$borderClass}\">
                        {$dotNotif}
                        <div class=\"w-10 h-10 rounded-full flex items-center justify-center shrink-0 {$iconBg}\">
                            <i class=\"{$nexus['icon']} text-lg\"></i>
                        </div>
                        <div class=\"flex-1 min-w-0 pt-0.5\">
                            <div class=\"flex items-center justify-between gap-2 mb-1\">
                                <h4 class=\"text-[13px] font-black text-slate-800 truncate font-poppins\">{$nexus['judul']}</h4>
                                <span class=\"text-[9px] font-black uppercase tracking-widest text-slate-400 whitespace-nowrap pt-0.5\">{$nexus['waktu']}</span>
                            </div>
                            <p class=\"text-[12px] font-medium text-slate-500 line-clamp-1 leading-relaxed\">{$nexus['pesan']}</p>
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
            Log::error('Bidan/NotifikasiController error: ' . $e->getMessage());
            return response()->json([
                'unreadCount' => 0,
                'html' => '
                    <div class="p-6 text-center text-rose-600 bg-rose-50">
                        <i class="fas fa-bug text-3xl mb-3"></i>
                        <h4 class="font-black text-[13px] uppercase tracking-widest mb-2">Error Backend:</h4>
                        <p class="text-[11px] font-medium font-mono text-left bg-white p-3 rounded-lg border border-rose-200">Gagal memuat notifikasi.</p>
                    </div>'
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
}