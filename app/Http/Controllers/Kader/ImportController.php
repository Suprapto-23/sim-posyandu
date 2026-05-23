<?php

namespace App\Http\Controllers\Kader;
use App\Models\Balita;
use App\Models\Remaja;
use App\Models\Lansia;
use Illuminate\Support\Facades\Schema;
use App\Exports\TemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\BalitaImport;
use App\Imports\LansiaImport;
use App\Imports\RemajaImport;
use App\Models\DataImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;



class ImportController extends Controller
{
    private function countDataByType(string $type): int
{
    return match ($type) {
        'balita' => Balita::count(),
        'remaja' => Remaja::count(),
        'lansia' => Lansia::count(),
        default => 0,
    };
}

private function safeUpdateImportLog(DataImport $riwayat, array $data): void
{
    $allowed = [];

    foreach ($data as $column => $value) {
        if (Schema::hasColumn('data_imports', $column)) {
            $allowed[$column] = $value;
        }
    }

    if (!empty($allowed)) {
        $riwayat->update($allowed);
    }
}
    private array $types = [
        'balita' => [
            'label' => 'Balita / Anak',
            'import' => BalitaImport::class,
            'template' => 'Formulir_Massal_KaderCare_BALITA.xlsx',
        ],
        'remaja' => [
            'label' => 'Remaja',
            'import' => RemajaImport::class,
            'template' => 'Formulir_Massal_KaderCare_REMAJA.xlsx',
        ],
        'lansia' => [
            'label' => 'Lansia',
            'import' => LansiaImport::class,
            'template' => 'Formulir_Massal_KaderCare_LANSIA.xlsx',
        ],
    ];

    public function index()
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

    public function create(Request $request)
    {
        $type = $this->normalizeType($request->get('type'));
        $types = $this->types;

        return view('kader.import.create', compact('type', 'types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_data' => 'required|in:balita,remaja,lansia',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
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
            'data_tersimpan' => 0,
            'catatan' => 'File diterima dan sedang diproses oleh sistem.',
            'created_by' => auth()->id(),
        ]);

        try {
            $importClass = $this->makeImportClass($jenisData);

            $jumlahBaris = $this->countExcelRows($importClass, $file);

            if ($jumlahBaris <= 0) {
                throw new \RuntimeException('File Excel kosong atau belum memiliki data setelah baris heading.');
            }

            $jumlahSebelum = $this->countDataByType($jenisData);

Excel::import($importClass, $file);

$jumlahSesudah = $this->countDataByType($jenisData);

$dataBaruTersimpan = max(0, $jumlahSesudah - $jumlahSebelum);
$dataTidakMasuk = max(0, $jumlahBaris - $dataBaruTersimpan);

            $modeText = $isSmartImport ? '[Mode Smart Mapping Aktif]' : '[Mode Standar]';

$this->safeUpdateImportLog($riwayat, [
    'status' => 'completed',
    'total_data' => $jumlahBaris,
    'data_berhasil' => $dataBaruTersimpan,
    'data_gagal' => $dataTidakMasuk,

    // Aman kalau kolom ini masih ada di database lokalmu.
    'data_tersimpan' => $dataBaruTersimpan,

    'catatan' => "{$modeText} Import {ucfirst($jenisData)} selesai. Sistem membaca {$jumlahBaris} baris data dari Excel. Data baru tersimpan: {$dataBaruTersimpan}. Data tidak masuk atau dilewati: {$dataTidakMasuk}. Data dengan NIK yang sudah ada dilewati agar tidak terjadi duplikasi.",
]);

            $riwayat->update([
                'status' => 'completed',
                'data_tersimpan' => $dataBaruTersimpan,
'catatan' => "{$modeText} Import {ucfirst($jenisData)} selesai. Sistem membaca {$jumlahBaris} baris data dari Excel. Data baru tersimpan: {$dataBaruTersimpan}. Data dengan NIK yang sudah ada dilewati agar tidak terjadi duplikasi.",
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Import berhasil. {$jumlahBaris} baris data berhasil diproses.",
                    'redirect' => route('kader.import.history'),
                ]);
            }

            return redirect()
    ->route('kader.import.history')
    ->with('success', "Import berhasil diproses. Data baru tersimpan: {$dataBaruTersimpan} dari {$jumlahBaris} baris terbaca.");
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $firstFailure = $failures[0] ?? null;

            $errorMsg = $firstFailure
                ? 'Gagal di baris ke-' . $firstFailure->row() . ': ' . ($firstFailure->errors()[0] ?? 'Format data tidak valid.')
                : 'Validasi Excel gagal. Periksa kembali format file.';

            $riwayat->update([
                'status' => 'failed',
                'catatan' => "[VALIDATION ERROR]\n{$errorMsg}",
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $errorMsg,
                ], 422);
            }

            return redirect()
                ->route('kader.import.create', ['type' => $jenisData])
                ->withInput()
                ->with('error', $errorMsg);
        } catch (\Throwable $e) {
            Log::error('KADER_IMPORT_STORE_ERROR: ' . $e->getMessage());

            $riwayat->update([
                'status' => 'failed',
                'catatan' => "[SERVER ERROR]\n" . $e->getMessage(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->route('kader.import.create', ['type' => $jenisData])
                ->withInput()
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function history(Request $request)
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

        if (in_array($status, ['processing', 'completed', 'failed'], true)) {
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

    public function show($id)
    {
        $import = DataImport::query()
            ->with('creator')
            ->findOrFail($id);

        return view('kader.import.show', compact('import'));
    }

    public function destroy($id)
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
            Log::error('KADER_IMPORT_DESTROY_ERROR: ' . $e->getMessage());

            return back()->with('error', 'Gagal menghapus riwayat import.');
        }
    }

    public function downloadTemplate(string $type)
    {
        $type = $this->normalizeType($type);

        if (!$type) {
            abort(404, 'Kategori template tidak ditemukan.');
        }

        if (!class_exists(TemplateExport::class)) {
            return back()->with('error', 'File TemplateExport belum tersedia. Pastikan app/Exports/TemplateExport.php sudah dibuat dan composer dump-autoload sudah dijalankan.');
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

        return array_key_exists($type, $this->types) ? $type : null;
    }

    private function makeImportClass(string $type)
    {
        $class = $this->types[$type]['import'] ?? null;

        if (!$class || !class_exists($class)) {
            throw new \RuntimeException("Class import untuk {$type} tidak ditemukan.");
        }

        return new $class();
    }

    private function countExcelRows($importClass, $file): int
    {
        $arrayData = Excel::toArray($importClass, $file);
        $rows = $arrayData[0] ?? [];

        return collect($rows)
            ->filter(function ($row) {
                if (!is_array($row)) {
                    return filled($row);
                }

                return collect($row)
                    ->filter(fn ($cell) => trim((string) $cell) !== '')
                    ->isNotEmpty();
            })
            ->count();
    }
}