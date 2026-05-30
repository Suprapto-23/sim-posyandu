<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\Imunisasi;
use App\Models\Kunjungan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ImunisasiController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $search = trim((string) $request->get('search', ''));

            $query = Imunisasi::query()
                ->with(['kunjungan.pasien', 'kunjungan.petugas'])
                ->latest('tanggal_imunisasi')
                ->latest('id');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('jenis_imunisasi', 'like', "%{$search}%")
                        ->orWhere('vaksin', 'like', "%{$search}%")
                        ->orWhere('dosis', 'like', "%{$search}%")
                        ->orWhereHas('kunjungan', function ($kunjunganQuery) use ($search) {
                            $kunjunganQuery->whereHasMorph('pasien', [Balita::class], function ($pasienQuery) use ($search) {
                                $pasienQuery->where('nama_lengkap', 'like', "%{$search}%")
                                    ->orWhere('nik', 'like', "%{$search}%");
                            });
                        });
                });
            }

            $imunisasis = $query->paginate(10)->withQueryString();

            return view('bidan.imunisasi.index', compact('imunisasis', 'search'));
        } catch (\Throwable $e) {
            Log::error('BIDAN_IMUNISASI_INDEX_ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()->with('error', 'Gagal memuat data register imunisasi.');
        }
    }

    public function create(): View
    {
        $masterData = [
            'balita' => Balita::query()
                ->select('id', 'nama_lengkap as nama', 'nik')
                ->orderBy('nama_lengkap')
                ->get(),
        ];

        return view('bidan.imunisasi.create', compact('masterData'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateImunisasi($request);

        DB::beginTransaction();

        try {
            $balita = Balita::query()->findOrFail($validated['pasien_id']);

            $kunjungan = $this->buatAtauAmbilKunjungan($balita, $validated['tanggal_imunisasi']);

            Imunisasi::create($this->payloadImunisasi($validated, $kunjungan));

            DB::commit();

            return redirect()
                ->route('bidan.imunisasi.index')
                ->with('success', 'Tindakan imunisasi berhasil dicatat ke rekam medis Balita.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('BIDAN_IMUNISASI_STORE_ERROR', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data imunisasi: ' . $e->getMessage());
        }
    }

    public function show($id): View|RedirectResponse
    {
        try {
            $imunisasi = Imunisasi::query()
                ->with(['kunjungan.pasien', 'kunjungan.petugas'])
                ->findOrFail($id);

            return view('bidan.imunisasi.show', compact('imunisasi'));
        } catch (\Throwable $e) {
            Log::error('BIDAN_IMUNISASI_SHOW_ERROR', [
                'message' => $e->getMessage(),
                'imunisasi_id' => $id,
            ]);

            return redirect()
                ->route('bidan.imunisasi.index')
                ->with('error', 'Dokumen imunisasi tidak ditemukan atau sudah dihapus.');
        }
    }

    public function edit($id): RedirectResponse
    {
        return redirect()
            ->route('bidan.imunisasi.show', $id)
            ->with('info', 'Edit imunisasi belum dibuat sebagai halaman terpisah. Data dapat ditinjau melalui detail arsip imunisasi.');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $validated = $this->validateImunisasi($request);

        DB::beginTransaction();

        try {
            $imunisasi = Imunisasi::query()->findOrFail($id);
            $balita = Balita::query()->findOrFail($validated['pasien_id']);
            $kunjungan = $this->buatAtauAmbilKunjungan($balita, $validated['tanggal_imunisasi']);

            $imunisasi->update($this->payloadImunisasi($validated, $kunjungan));

            DB::commit();

            return redirect()
                ->route('bidan.imunisasi.show', $imunisasi->id)
                ->with('success', 'Data imunisasi berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('BIDAN_IMUNISASI_UPDATE_ERROR', [
                'message' => $e->getMessage(),
                'imunisasi_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data imunisasi: ' . $e->getMessage());
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            $imunisasi = Imunisasi::query()->findOrFail($id);
            $imunisasi->delete();

            return redirect()
                ->route('bidan.imunisasi.index')
                ->with('success', 'Catatan imunisasi telah dihapus dari sistem.');
        } catch (\Throwable $e) {
            Log::error('BIDAN_IMUNISASI_DESTROY_ERROR', [
                'message' => $e->getMessage(),
                'imunisasi_id' => $id,
            ]);

            return back()->with('error', 'Data imunisasi gagal dihapus karena terikat sistem.');
        }
    }

    private function validateImunisasi(Request $request): array
    {
        return $request->validate([
            'pasien_id' => ['required', 'integer', 'exists:balitas,id'],
            'kategori' => ['required', 'in:balita'],
            'jenis_imunisasi' => ['required', 'string', 'max:100'],
            'vaksin' => ['required', 'string', 'max:100'],
            'dosis' => ['required', 'string', 'max:50'],
            'tanggal_imunisasi' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],
        ], [
            'pasien_id.required' => 'Identitas Balita wajib dipilih.',
            'pasien_id.exists' => 'Data Balita tidak ditemukan.',
            'kategori.required' => 'Kategori sasaran wajib dipilih.',
            'kategori.in' => 'Imunisasi saat ini hanya digunakan untuk sasaran Balita.',
            'jenis_imunisasi.required' => 'Program imunisasi wajib dipilih.',
            'vaksin.required' => 'Jenis atau nama vaksin wajib diisi.',
            'dosis.required' => 'Dosis vaksinasi wajib diisi.',
            'tanggal_imunisasi.required' => 'Tanggal imunisasi wajib diisi.',
        ]);
    }

    private function buatAtauAmbilKunjungan(Balita $balita, string $tanggal): Kunjungan
    {
        $kunjunganTable = (new Kunjungan())->getTable();

        $keys = [
            'pasien_id' => $balita->id,
            'pasien_type' => Balita::class,
            'tanggal_kunjungan' => $tanggal,
        ];

        $defaults = [];

        if (Schema::hasColumn($kunjunganTable, 'jenis_kunjungan')) {
            $defaults['jenis_kunjungan'] = 'imunisasi';
        }

        if (Schema::hasColumn($kunjunganTable, 'petugas_id')) {
            $defaults['petugas_id'] = Auth::id();
        }

        if (Schema::hasColumn($kunjunganTable, 'kader_id')) {
            $defaults['kader_id'] = Auth::id();
        }

        if (Schema::hasColumn($kunjunganTable, 'status')) {
            $defaults['status'] = 'selesai';
        }

        if (Schema::hasColumn($kunjunganTable, 'pertemuan_ke')) {
            $defaults['pertemuan_ke'] = 1;
        }

        return Kunjungan::query()->firstOrCreate($keys, $defaults);
    }

    private function payloadImunisasi(array $validated, Kunjungan $kunjungan): array
    {
        $table = (new Imunisasi())->getTable();
        $catatan = $validated['keterangan'] ?? '-';

        $payload = [
            'kunjungan_id' => $kunjungan->id,
            'jenis_imunisasi' => $validated['jenis_imunisasi'],
            'vaksin' => $validated['vaksin'],
            'dosis' => $validated['dosis'],
            'tanggal_imunisasi' => $validated['tanggal_imunisasi'],
        ];

        if (Schema::hasColumn($table, 'keterangan')) {
            $payload['keterangan'] = $catatan;
        }

        if (Schema::hasColumn($table, 'catatan')) {
            $payload['catatan'] = $catatan;
        }

        if (Schema::hasColumn($table, 'batch_number')) {
            $payload['batch_number'] = null;
        }

        return $payload;
    }
}