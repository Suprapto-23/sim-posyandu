<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\JadwalPosyandu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * =========================================================================
 * JADWAL CONTROLLER (NEXUS ENTERPRISE EDITION)
 * =========================================================================
 * Mengelola siklus hidup agenda Posyandu, mulai dari perancangan,
 * pembaruan otomatis status, hingga penyebaran pengumuman (Broadcast).
 */
class JadwalController extends Controller
{
    /**
     * INDEX: DASHBOARD MANAJEMEN AGENDA
     */
    public function index(Request $request)
    {
        // 1. AUTO-MAINTENANCE ENGINE (Lazy Update Status)
        // Mengubah 'aktif' menjadi 'selesai' jika waktu pelaksanaan sudah terlewati secara presisi
        $this->autoUpdateStatus();

        // 2. QUERY BUILDING (Eager Loading & Filtering)
        $search = $request->get('search');
        $status = $request->get('status', 'semua');

        $query = JadwalPosyandu::query()->orderBy('tanggal', 'desc')->orderBy('waktu_mulai', 'desc');

        // Filter Berdasarkan Status (Scopes Model)
        if ($status !== 'semua') {
            $query->where('status', $status);
        }

        // Search Engine (Judul atau Lokasi)
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        $jadwals = $query->paginate(12)->withQueryString();

        // 3. RENDER RESPONSE (Support AJAX Live Search)
        if ($request->ajax()) {
            return view('kader.jadwal.index', compact('jadwals', 'search', 'status'))->render();
        }

        return view('kader.jadwal.index', compact('jadwals', 'search', 'status'));
    }

    /**
     * CREATE & STORE: MEMBANGUN AGENDA BARU
     */
    public function create()
    {
        return view('kader.jadwal.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'          => 'required|string|max:255',
            'kategori'       => 'required|in:kesehatan_ibu_anak,imunisasi,penyuluhan,pemeriksaan_lansia,lainnya',
            'target_peserta' => 'required|in:semua,balita,remaja,lansia',
            'tanggal'        => 'required|date|after_or_equal:today',
            'waktu_mulai'    => 'required',
            'waktu_selesai'  => 'required|after:waktu_mulai',
            'lokasi'         => 'required|string|max:255',
            'deskripsi'      => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();
            $jadwal = JadwalPosyandu::create(array_merge($request->all(), [
                'status'     => 'aktif',
                'created_by' => auth()->id()
            ]));
            DB::commit();

            return $this->jsonResponse('success', 'Agenda berhasil dijadwalkan!', route('kader.jadwal.index'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("STORE_JADWAL_ERROR: " . $e->getMessage());
            return $this->jsonResponse('error', 'Gagal membuat agenda: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * SHOW & EDIT: VIEW DATA
     */
    public function show($id)
    {
        $jadwal = JadwalPosyandu::findOrFail($id);
        return view('kader.jadwal.show', compact('jadwal'));
    }

    public function edit($id)
    {
        $jadwal = JadwalPosyandu::findOrFail($id);
        return view('kader.jadwal.edit', compact('jadwal'));
    }

    /**
     * UPDATE: KOREKSI AGENDA
     */
    public function update(Request $request, $id)
    {
        $jadwal = JadwalPosyandu::findOrFail($id);
        $request->validate([
            'judul'          => 'required|string|max:255',
            'status'         => 'required|in:aktif,selesai,dibatalkan',
            'target_peserta' => 'required',
            'tanggal'        => 'required|date',
            'waktu_mulai'    => 'required',
            'waktu_selesai'  => 'required',
            'lokasi'         => 'required|string|max:255',
        ]);

        try {
            $jadwal->update($request->all());
            return $this->jsonResponse('success', 'Perubahan agenda berhasil disimpan!', route('kader.jadwal.show', $jadwal->id));
        } catch (\Exception $e) {
            return $this->jsonResponse('error', 'Gagal memperbarui data.', null, 500);
        }
    }

    /**
     * DESTROY: PENGHAPUSAN PERMANEN
     */
    public function destroy($id)
    {
        try {
            JadwalPosyandu::findOrFail($id)->delete();
            return redirect()->route('kader.jadwal.index')->with('success', 'Agenda berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data.');
        }
    }

    /**
     * SMART BROADCAST: SEBARKAN PENGUMUMAN KE SMARTPHONE WARGA
     */
    public function broadcast(Request $request, $id)
    {
        try {
            $jadwal = JadwalPosyandu::findOrFail($id);
            if ($jadwal->status !== 'aktif') throw new \Exception("Hanya agenda aktif yang bisa disiarkan.");

            DB::transaction(function() use ($jadwal) {
                $userIds = $this->getTargetUserIds($jadwal->target_peserta);
                
                $notifikasiData = [];
                $waktu = Carbon::now('Asia/Jakarta');
                $tglFormatted = Carbon::parse($jadwal->tanggal)->translatedFormat('d M Y');
                $pesan = "📢 Panggilan Posyandu! Agenda: *{$jadwal->judul}* dilaksanakan pada {$tglFormatted}, jam {$jadwal->waktu_lengkap} di {$jadwal->lokasi}. Mohon kehadiran Bapak/Ibu.";

                foreach (array_unique($userIds) as $uId) {
                    $notifikasiData[] = [
                        'user_id' => $uId, 'judul' => 'Agenda Posyandu Baru', 'pesan' => $pesan,
                        'is_read' => false, 'created_at' => $waktu, 'updated_at' => $waktu
                    ];
                }

                if (!empty($notifikasiData)) DB::table('notifikasis')->insert($notifikasiData);
            });

            return response()->json(['status' => 'success', 'message' => 'Notifikasi telah berhasil dipancarkan ke warga!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * =========================================================================
     * PRIVATE HELPERS (CLEAN CODE ENGINES)
     * =========================================================================
     */

    private function autoUpdateStatus()
    {
        $now = Carbon::now('Asia/Jakarta');
        JadwalPosyandu::where('status', 'aktif')->where(function ($query) use ($now) {
            $query->whereDate('tanggal', '<', $now->toDateString())
                  ->orWhere(function ($q) use ($now) {
                      $q->whereDate('tanggal', '=', $now->toDateString())
                        ->whereTime('waktu_selesai', '<', $now->toTimeString());
                  });
        })->update(['status' => 'selesai']);
    }

    private function getTargetUserIds($target)
    {
        if ($target === 'semua') return User::whereIn('role', ['user', 'warga'])->pluck('id')->toArray();

        $table = match($target) {
            'balita' => 'balitas', 'remaja' => 'remajas', 'lansia' => 'lansias', default => null
        };

        return ($table && \Schema::hasTable($table)) ? DB::table($table)->whereNotNull('user_id')->pluck('user_id')->toArray() : [];
    }

    private function jsonResponse($status, $message, $redirect = null, $code = 200)
    {
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(compact('status', 'message', 'redirect'), $code);
        }
        return $redirect ? redirect($redirect)->with($status, $message) : back()->with($status, $message);
    }
}