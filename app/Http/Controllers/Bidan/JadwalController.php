<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Models\JadwalPosyandu;
use App\Models\Notifikasi;
use App\Models\User;
use App\Models\Balita;

class JadwalController extends Controller
{
    public function index()
    {
        $jadwals = JadwalPosyandu::orderBy('tanggal', 'desc')->paginate(10);
        return view('bidan.jadwal.index', compact('jadwals'));
    }

    public function create()
    {
        return view('bidan.jadwal.create');
    }

    public function store(Request $request)
    {
        // Validasi disesuaikan dengan target peserta yang tersisa (KIA)
        $request->validate([
            'judul'          => 'required|string|max:191',
            'tanggal'        => 'required|date',
            'waktu_mulai'    => 'required',
            'waktu_selesai'  => 'required',
            'lokasi'         => 'required|string',
            'kategori'       => 'required|in:imunisasi,pemeriksaan,posyandu',
            'target_peserta' => 'required|in:semua,balita', 
        ]);

        DB::beginTransaction();
        try {
            JadwalPosyandu::create([
                'judul'          => $request->judul,
                'deskripsi'      => $request->deskripsi,
                'tanggal'        => $request->tanggal,
                'waktu_mulai'    => $request->waktu_mulai,
                'waktu_selesai'  => $request->waktu_selesai,
                'lokasi'         => $request->lokasi,
                'kategori'       => $request->kategori,
                'target_peserta' => $request->target_peserta,
                'status'         => 'aktif',
                'created_by'     => Auth::id()
            ]);

            // Eksekusi Mesin Broadcast Cerdas
            $this->kirimNotifikasiCerdas($request);

            DB::commit();
            return redirect()->route('bidan.jadwal.index')
                ->with('success', 'Jadwal diterbitkan! Notifikasi instruksi ke Kader dan undangan ke Warga telah berhasil didistribusikan.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('BIDAN_JADWAL_STORE_ERROR: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Sistem gagal menerbitkan jadwal: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $jadwal = JadwalPosyandu::findOrFail($id);
        return view('bidan.jadwal.edit', compact('jadwal'));
    }

    public function update(Request $request, $id)
    {
        $jadwal = JadwalPosyandu::findOrFail($id);
        
        $request->validate([
            'judul'         => 'required|string|max:191',
            'tanggal'       => 'required|date',
            'waktu_mulai'   => 'required',
            'waktu_selesai' => 'required',
            'status'        => 'required|in:aktif,selesai,dibatalkan',
        ]);
        
        $jadwal->update($request->except(['_token', '_method']));
        
        // Opsional Logika Skripsi Tingkat Lanjut: 
        // Jika status diubah jadi 'dibatalkan', otomatis kirim notif darurat ke warga & kader.
        if ($request->status == 'dibatalkan') {
             $this->kirimNotifBatal($jadwal);
        }
        
        return redirect()->route('bidan.jadwal.index')
            ->with('success', 'Perubahan agenda jadwal berhasil disimpan ke sistem.');
    }

    public function destroy($id)
    {
        JadwalPosyandu::findOrFail($id)->delete();
        return redirect()->route('bidan.jadwal.index')
            ->with('success', 'Agenda jadwal telah dicabut secara permanen dari sistem.');
    }

    /**
     * ========================================================================
     * MESIN BROADCAST CERDAS (CONTEXT-AWARE NOTIFICATION)
     * Mengirim pesan yang berbeda antara Kader (Instruksi) dan Warga (Undangan)
     * ========================================================================
     */
    private function kirimNotifikasiCerdas($request)
    {
        $now = now();
        $notifData = [];
        $tanggalFormat = Carbon::parse($request->tanggal)->translatedFormat('d F Y');
        $kategoriTeks = ucwords(str_replace('_', ' ', $request->kategori));

        // ---------------------------------------------------------
        // 1. DISTRIBUSI KE WARGA (UNDANGAN)
        // ---------------------------------------------------------
        $wargaUsers = collect();

        if ($request->target_peserta == 'semua') {
            $wargaUsers = User::where('role', 'user')->where('status', 'active')->pluck('id');
        } elseif ($request->target_peserta == 'balita') {
            $nikOrtu = Balita::pluck('nik_ibu')->merge(Balita::pluck('nik_ayah'))->filter()->unique();
            $wargaUsers = User::whereIn('nik', $nikOrtu)->pluck('id');
        } 

        $judulWarga = "Jadwal {$kategoriTeks} Baru!";
        $pesanWarga = "Halo! Jangan lupa hadir pada agenda {$kategoriTeks} yang akan dilaksanakan pada {$tanggalFormat} di {$request->lokasi}. {$request->deskripsi}";

        foreach ($wargaUsers->unique() as $userId) {
            $notifData[] = [
                'user_id'    => $userId,
                'judul'      => $judulWarga,
                'pesan'      => $pesanWarga,
                'tipe'       => 'jadwal',
                'is_read'    => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // ---------------------------------------------------------
        // 2. DISTRIBUSI KE KADER (INSTRUKSI TUGAS)
        // ---------------------------------------------------------
        $kaderUsers = User::where('role', 'kader')->pluck('id');
        $judulKader = "Instruksi: Persiapan " . $request->judul;
        $pesanKader = "Bidan telah menetapkan agenda {$kategoriTeks} pada {$tanggalFormat}. Mohon rekan-rekan Kader segera berkoordinasi untuk mempersiapkan lokasi di {$request->lokasi} beserta peralatan Meja 1 hingga 4.";

        foreach ($kaderUsers as $kaderId) {
            $notifData[] = [
                'user_id'    => $kaderId,
                'judul'      => $judulKader,
                'pesan'      => $pesanKader,
                'tipe'       => 'jadwal', // atau bisa diganti 'sistem'
                'is_read'    => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // ---------------------------------------------------------
        // 3. EKSEKUSI BATCH INSERT (High Performance)
        // ---------------------------------------------------------
        if (!empty($notifData)) {
            $chunks = array_chunk($notifData, 500);
            foreach ($chunks as $chunk) {
                Notifikasi::insert($chunk);
            }
        }
    }

    /**
     * Broadcast pembatalan jadwal (Opsional tapi mematikan untuk presentasi)
     */
    private function kirimNotifBatal($jadwal)
    {
        $allUsers = collect();
        // Tarik semua user dan kader yang relevan (Disederhanakan untuk contoh)
        $allUsers = User::whereIn('role', ['user', 'kader'])->where('status', 'active')->pluck('id');
        
        $notifData = [];
        $now = now();
        foreach ($allUsers as $userId) {
            $notifData[] = [
                'user_id'    => $userId,
                'judul'      => "Peringatan: Jadwal Dibatalkan!",
                'pesan'      => "Mohon maaf, agenda {$jadwal->judul} pada tanggal " . Carbon::parse($jadwal->tanggal)->translatedFormat('d F Y') . " di {$jadwal->lokasi} terpaksa dibatalkan/ditunda. Tunggu informasi selanjutnya.",
                'tipe'       => 'jadwal',
                'is_read'    => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        
        if (!empty($notifData)) {
            Notifikasi::insert($notifData);
        }
    }
}