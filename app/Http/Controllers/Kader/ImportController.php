<?php

namespace App\Http\Controllers\Kader;

use App\Exports\TemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\BalitaImport;
use App\Imports\LansiaImport;
use App\Imports\RemajaImport;
use App\Models\Balita;
use App\Models\DataImport;
use App\Models\Lansia;
use App\Models\Remaja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

// Tambahan Namespace untuk Pendeteksi Header Dinamis
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithLimit;

class ImportController extends Controller
{
    private array $types = [
        'balita' => [
            'label' => 'Balita / Anak',
            'import' => BalitaImport::class,
            'model' => Balita::class,
            'template' => 'Formulir_Massal_KaderCare_BALITA.xlsx',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'import' => RemajaImport::class,
            'model' => Remaja::class,
            'template' => 'Formulir_Massal_KaderCare_REMAJA.xlsx',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'import' => LansiaImport::class,
            'model' => Lansia::class,
            'template' => 'Formulir_Massal_KaderCare_LANSIA.xlsx',
        ],
    ];

    public function index(): View
    {
        $statTotal = DataImport::count();
        $statBerhasil = DataImport::where('status', 'completed')->count();
        $statGagal = DataImport::where('status', 'failed')->count();
        $statProcessing = DataImport::where('status', 'processing')->count();

        $latestImports = DataImport::query()
            ->with('creator')
            ->latest()
            ->take(5)
            ->get();

        $types = $this->types;

        return view('kader.import.index', compact(
            'statTotal',
            'statBerhasil',
            'statGagal',
            'statProcessing',
            'latestImports',
            'types'
        ));
    }

    public function create(Request $request): View
    {
        $type = $this->normalizeType($request->get('type'));
        $types = $this->types;

        return view('kader.import.create', compact('type', 'types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_data' => ['required', 'in:balita,remaja,lansia'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'jenis_data.required' => 'Jenis data wajib dipilih.',
            'jenis_data.in' => 'Jenis data tidak valid.',
            'file.required' => 'File Excel wajib diunggah.',
            'file.file' => 'File upload tidak valid.',
            'file.mimes' => 'Format file harus xlsx, xls, atau csv.',
            'file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        $jenisData = $validated['jenis_data'];
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $isSmartImport = $request->boolean('smart_import');

        $path = $file->store('imports');

        $riwayat = DataImport::create([
            'nama_file' => $originalName,
            'jenis_data' => $jenisData,
            'file_path' => $path,
            'status' => 'processing',
            'total_data' => 0,
            'data_berhasil' => 0,
            'data_gagal' => 0,
            'catatan' => 'File diterima dan sedang diproses oleh sistem.',
            'created_by' => auth()->id(),
        ]);

        try {
            // [MODIFIKASI]: Cari tahu baris ke berapa yang mengandung NIK & Nama
            $headerRow = $this->findHeaderRow($file);
            
            // [MODIFIKASI]: Suntikkan baris dinamis ke dalam Class Import
            $importClass = $this->makeImportClass($jenisData, $headerRow);

            $jumlahBaris = $this->countExcelRows($importClass, $file);

            if ($jumlahBaris <= 0) {
                throw new \RuntimeException('File Excel kosong atau belum memiliki data valid setelah baris heading.');
            }

            $jumlahSebelum = $this->countDataByType($jenisData);

            Excel::import($importClass, $file);

            $jumlahSesudah = $this->countDataByType($jenisData);

            $dataBerhasil = max(0, $jumlahSesudah - $jumlahSebelum);
            $dataGagal = max(0, $jumlahBaris - $dataBerhasil);
            
            $modeText = $isSmartImport ? '[Mode Smart Mapping Aktif]' : '[Mode Standar]';
            $jenisDataLabel = $this->types[$jenisData]['label'] ?? ucfirst($jenisData);

            // [MODIFIKASI]: Menambahkan informasi di log bahwa sistem mendeteksi header secara dinamis
            $riwayat->update([
                'status' => 'completed',
                'total_data' => $jumlahBaris,
                'data_berhasil' => $dataBerhasil,
                'data_gagal' => $dataGagal,
                'catatan' => "{$modeText} Import {$jenisDataLabel} selesai (Header terdeteksi otomatis di Baris {$headerRow}). Sistem membaca {$jumlahBaris} baris data dari Excel. Data baru tersimpan: {$dataBerhasil}. Data tidak masuk atau dilewati: {$dataGagal}. Data dengan NIK duplikat diabaikan.",
            ]);

            return redirect()
                ->route('kader.import.history')
                ->with('success', "Import berhasil diproses. Data tersimpan: {$dataBerhasil} dari {$jumlahBaris} baris terbaca.");
        } catch (ValidationException $e) {
            $errorMsg = $this->formatValidationError($e);

            $riwayat->update([
                'status' => 'failed',
                'total_data' => $riwayat->total_data ?: 0,
                'data_berhasil' => 0,
                'data_gagal' => $riwayat->total_data ?: 0,
                'catatan' => "[VALIDATION ERROR]\n{$errorMsg}",
            ]);

            return redirect()
                ->route('kader.import.create', ['type' => $jenisData])
                ->withInput()
                ->with('error', $errorMsg);
        } catch (\Throwable $e) {
            Log::error('KADER_IMPORT_STORE_ERROR', [
                'message' => $e->getMessage(),
                'jenis_data' => $jenisData,
                'file' => $originalName,
                'user_id' => auth()->id(),
                'line' => $e->getLine(),
                'path' => $e->getFile(),
            ]);

            $riwayat->update([
                'status' => 'failed',
                'data_gagal' => $riwayat->total_data ?: 0,
                'catatan' => "[SERVER ERROR]\n" . $e->getMessage(),
            ]);

            return redirect()
                ->route('kader.import.create', ['type' => $jenisData])
                ->withInput()
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function history(Request $request): View
    {
        $tanggal = $request->get('tanggal');
        $jenisData = $request->get('jenis_data', 'semua');
        $status = $request->get('status', 'semua');
        $search = trim((string) $request->get('search', ''));

        $query = DataImport::query()
            ->with('creator')
            ->latest();

        if ($tanggal) {
            $query->whereDate('created_at', $tanggal);
        }

        if (array_key_exists($jenisData, $this->types)) {
            $query->where('jenis_data', $jenisData);
        }

        if (in_array($status, ['pending', 'processing', 'completed', 'failed'], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_file', 'like', "%{$search}%")
                    ->orWhere('jenis_data', 'like', "%{$search}%")
                    ->orWhere('catatan', 'like', "%{$search}%");
            });
        }

        $imports = $query->paginate(10)->withQueryString();
        $types = $this->types;

        return view('kader.import.history', compact(
            'imports',
            'tanggal',
            'jenisData',
            'status',
            'search',
            'types'
        ));
    }

    public function show($id): View
    {
        $import = DataImport::query()
            ->with('creator')
            ->findOrFail($id);

        return view('kader.import.show', compact('import'));
    }

    public function destroy($id): RedirectResponse
    {
        try {
            $import = DataImport::findOrFail($id);

            if ($import->file_path && Storage::exists($import->file_path)) {
                Storage::delete($import->file_path);
            }

            $import->delete();

            return redirect()
                ->route('kader.import.history')
                ->with('success', 'Riwayat import dan arsip file berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('KADER_IMPORT_DESTROY_ERROR', [
                'message' => $e->getMessage(),
                'import_id' => $id,
            ]);

            return back()->with('error', 'Gagal menghapus riwayat import.');
        }
    }

    public function downloadTemplate(string $type): BinaryFileResponse|RedirectResponse
    {
        $type = $this->normalizeType($type);

        if (!$type) {
            abort(404, 'Kategori template tidak ditemukan.');
        }

        return Excel::download(
            new TemplateExport($type),
            $this->types[$type]['template']
        );
    }

    private function normalizeType(?string $type): ?string
    {
        if (!$type) {
            return null;
        }

        $type = strtolower(trim($type));

        return array_key_exists($type, $this->types) ? $type : null;
    }

    /**
     * [FITUR BARU]: Algoritma Pendeteksi Header Dinamis
     * Menganalisis 15 baris pertama untuk mencari di mana kolom data sebenarnya dimulai.
     */
    private function findHeaderRow($file): int
    {
        try {
            // Gunakan class anonymous + WithLimit agar memori server aman
            // meskipun user mengunggah excel berisi puluhan ribu baris.
            $importLimit = new class implements ToArray, WithLimit {
                public function array(array $array) {}
                public function limit(): int {
                    return 15;
                }
            };
            
            $arrayData = Excel::toArray($importLimit, $file);
            $rows = $arrayData[0] ?? [];

            foreach ($rows as $index => $row) {
                // Konversi seluruh cell di baris tersebut menjadi satu string kecil tanpa spasi
                $rowString = strtolower(implode('', array_map(fn($val) => trim((string)$val), $row)));
                
                // Cari kata kunci wajib yang pasti ada di template ('nik' dan 'nama')
                if (str_contains($rowString, 'nik') && (str_contains($rowString, 'nama') || str_contains($rowString, 'lengkap'))) {
                    return $index + 1; // Baris Excel dimulai dari angka 1
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Pendeteksian header dinamis gagal, fallback ke default: ' . $e->getMessage());
        }

        return 3; // Fallback ke baris 3 jika file terlalu berantakan
    }

    /**
     * [MODIFIKASI]: Menambahkan argumen $headerRow untuk disuntikkan ke class Import
     */
    private function makeImportClass(string $type, int $headerRow = 3)
    {
        $class = $this->types[$type]['import'] ?? null;

        if (!$class || !class_exists($class)) {
            throw new \RuntimeException("Class import untuk {$type} tidak ditemukan.");
        }

        return new $class($headerRow);
    }

    private function countExcelRows($importClass, $file): int
    {
        $arrayData = Excel::toArray($importClass, $file);
        $rows = $arrayData[0] ?? [];

        return collect($rows)
            ->filter(function ($row) {
                if (!is_array($row)) {
                    return trim((string) $row) !== '';
                }

                return collect($row)
                    ->filter(fn ($cell) => trim((string) $cell) !== '')
                    ->isNotEmpty();
            })
            ->count();
    }

    private function countDataByType(string $type): int
    {
        $model = $this->types[$type]['model'] ?? null;

        if (!$model || !class_exists($model)) {
            return 0;
        }

        return $model::count();
    }

    private function formatValidationError(ValidationException $e): string
    {
        $failures = $e->failures();
        $firstFailure = $failures[0] ?? null;

        if (!$firstFailure) {
            return 'Validasi Excel gagal. Periksa kembali format file.';
        }

        return 'Gagal di baris ke-' . $firstFailure->row() . ': ' . ($firstFailure->errors()[0] ?? 'Format data tidak valid.');
    }
}