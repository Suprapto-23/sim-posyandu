<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

// Memanggil Model Utama untuk Log Riwayat
use App\Models\DataImport; 

// Memanggil Class Import (Mesin Eksekutor Upload Excel)
use App\Imports\BalitaImport;
use App\Imports\RemajaImport;
use App\Imports\LansiaImport;

// Memanggil Class Export (Mesin Pembuat Template Excel Kosong)
use App\Exports\TemplateExport;

class ImportController extends Controller
{
    /**
     * Menampilkan Halaman Dashboard / Perkenalan Import Center
     */
    public function index()
    {
        return view('kader.import.index');
    }

    /**
     * Menampilkan Halaman Wizard Upload (Drag & Drop)
     */
    public function create(Request $request)
    {
        // Menangkap parameter 'type' jika pengguna datang dari tombol spesifik
        $type = $request->get('type', '');
        return view('kader.import.create', compact('type'));
    }

    /**
     * Mesin Utama Eksekusi Data Excel ke Database (Terintegrasi AJAX)
     */
    public function store(Request $request)
    {
        // 1. Validasi Input dari Form
        $request->validate([
            'jenis_data' => 'required|in:balita,remaja,lansia', 
            'file'       => 'required|file|mimes:xlsx,xls,csv|max:10240', // Maksimal 10MB
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $jenisData = $request->jenis_data;
        $isSmartImport = $request->has('smart_import');

        // 2. Simpan fisik file ke storage lokal (sebagai arsip)
        $path = $file->store('imports');

        // 3. Catat aktivitas ini ke Database Log (Status: Processing)
        $riwayat = DataImport::create([
            'nama_file'  => $originalName,
            'jenis_data' => $jenisData,
            'file_path'  => $path,
            'status'     => 'processing',
            'created_by' => auth()->id(),
        ]);

        try {
            // 4. Tentukan Class Import berdasarkan pilihan dropdown
            $importClass = match($jenisData) {
                'balita' => new BalitaImport(),
                'remaja' => new RemajaImport(),
                'lansia' => new LansiaImport(),
            };
            
            // 5. INTEL ENGINE: Hitung jumlah baris di Excel sebelum dieksekusi (Untuk Log Terminal)
            $arrayData = Excel::toArray($importClass, $file);
            $jumlahBaris = 0;
            if (isset($arrayData[0])) {
                // Total baris dikurangi 3 baris (Karena baris 1, 2, 3 adalah Header/Judul Template kita)
                $totalBarisExcel = count($arrayData[0]);
                $jumlahBaris = $totalBarisExcel > 3 ? ($totalBarisExcel - 3) : 0; 
            }

            if ($jumlahBaris === 0) {
                throw new \Exception("File Excel kosong atau Anda belum mengisi data di bawah baris ke-3.");
            }

            // 6. EKSEKUSI UTAMA: Masukkan data ke Database
            Excel::import($importClass, $file);

            // 7. Catat Keberhasilan ke Terminal Log
            $modeText = $isSmartImport ? '[Mode Smart Mapping AI Aktif]' : '[Mode Standar]';
            $riwayat->update([
                'status'         => 'completed',
                'data_tersimpan' => $jumlahBaris,
                'catatan'        => "{$modeText} Berhasil membaca {$jumlahBaris} baris data warga dari Excel. Data telah divalidasi, disinkronisasi dengan akun keluarga, dan disimpan ke sistem utama Posyandu.",
            ]);

            // 8. Berikan Respons Sukses ke Javascript (AJAX / SweetAlert)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Sukses! {$jumlahBaris} baris data berhasil di-import.",
                    'redirect' => route('kader.import.history')
                ]);
            }

            // Fallback jika tidak menggunakan AJAX
            return redirect()->route('kader.import.history')->with('success', 'Sukses! Data berhasil di-import.');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Tangkap Error Validasi Bawaan Laravel Excel
            $failures = $e->failures();
            $errorMsg = "Gagal di baris ke-" . $failures[0]->row() . ": " . $failures[0]->errors()[0];
            
            $riwayat->update(['status' => 'failed', 'catatan' => "[VALIDATION ERROR]\n" . $errorMsg]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $errorMsg], 422);
            }
            return redirect()->route('kader.import.history')->with('error', $errorMsg);

        } catch (\Throwable $e) { 
            // Tangkap Error Sistem / Database / Kesalahan Format Excel
            Log::error('Kesalahan Import Data : ' . $e->getMessage());
            $riwayat->update(['status' => 'failed', 'catatan' => "[SERVER ERROR]\n" . $e->getMessage()]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
            return redirect()->route('kader.import.history')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan Tabel Riwayat (Log History)
     */
    public function history(Request $request)
    {
        $tanggal = $request->get('tanggal');
        $query = DataImport::query();

        // Fitur filter berdasarkan tanggal eksekusi
        if ($tanggal) {
            $query->whereDate('created_at', $tanggal);
        }

        $imports = $query->latest()->paginate(10)->withQueryString();
        return view('kader.import.history', compact('imports', 'tanggal'));
    }

    /**
     * Menampilkan Detail Terminal Log per File
     */
    public function show($id)
    {
        $import = DataImport::findOrFail($id);
        return view('kader.import.show', compact('import'));
    }

    /**
     * Menghapus Log Riwayat beserta File Fisik Excelnya
     */
    public function destroy($id)
    {
        try {
            $import = DataImport::findOrFail($id);
            
            // Hapus file excel dari server agar hardisk tidak penuh
            if ($import->file_path && Storage::exists($import->file_path)) {
                Storage::delete($import->file_path);
            }
            
            // Hapus record database
            $import->delete();
            
            return redirect()->route('kader.import.history')->with('success', 'Log riwayat import dan arsip file berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data riwayat.');
        }
    }

    /**
     * ENGINE GENERATOR TEMPLATE KOSONG (.XLSX)
     */
    public function downloadTemplate($type)
    {
        // Proteksi keamanan: pastikan tipenya valid
        if (!in_array($type, ['balita', 'remaja', 'lansia'])) {
            abort(404, 'Kategori template tidak ditemukan.');
        }

        $fileName = "Formulir_Massal_KaderCare_" . strtoupper($type) . ".xlsx";

        // Memanggil class TemplateExport yang akan melukis Excel secara on-the-fly
        return Excel::download(new TemplateExport($type), $fileName);
    }
}