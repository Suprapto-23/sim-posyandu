<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\Imunisasi;
use App\Models\Kunjungan;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ImunisasiController extends Controller
{
    private array $columnCache = [];

    public function index(Request $request): View|RedirectResponse
    {
        try {
            $search = $this->cleanSearch($request->get('search', ''));

            $query = Imunisasi::query()
                ->with(['kunjungan.pasien', 'kunjungan.petugas']);

            $this->applyTargetBalita($query);
            $this->applySearch($query, $search);
            $this->applyLatestOrder($query);

            $imunisasis = $query
                ->paginate(10)
                ->withQueryString();

            $stats = $this->stats();
            $programOptions = $this->programOptions();

            return view('bidan.imunisasi.index', compact(
                'imunisasis',
                'search',
                'stats',
                'programOptions'
            ));
        } catch (\Throwable $e) {
            Log::error('BIDAN_IMUNISASI_INDEX_ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return redirect()
                ->route('bidan.dashboard')
                ->with('error', 'Gagal memuat data imunisasi Balita.');
        }
    }

    public function create(): View|RedirectResponse
    {
        try {
            $balitas = $this->balitaOptions();
            $programOptions = $this->programOptions();

            $mode = 'create';
            $imunisasi = null;
            $selectedBalita = null;

            return view('bidan.imunisasi.create', compact(
                'balitas',
                'programOptions',
                'mode',
                'imunisasi',
                'selectedBalita'
            ));
        } catch (\Throwable $e) {
            Log::error('BIDAN_IMUNISASI_CREATE_ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return redirect()
                ->route('bidan.imunisasi.index')
                ->with('error', 'Gagal membuka form imunisasi Balita.');
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateImunisasi($request);

        DB::beginTransaction();

        try {
            $balita = Balita::query()->findOrFail($validated['balita_id']);
            $kunjungan = $this->buatAtauAmbilKunjungan($balita, $validated['tanggal_imunisasi']);

            $imunisasi = Imunisasi::query()->create(
                $this->payloadImunisasi($validated, $kunjungan, $balita)
            );

            DB::commit();

            return redirect()
                ->route('bidan.imunisasi.show', $imunisasi->id)
                ->with('success', 'Catatan imunisasi Balita berhasil disimpan.');
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
                ->with('error', 'Gagal menyimpan catatan imunisasi Balita.');
        }
    }

    public function show(int|string $id): View|RedirectResponse
    {
        try {
            $imunisasi = Imunisasi::query()
                ->with(['kunjungan.pasien', 'kunjungan.petugas'])
                ->findOrFail($id);

            if (!$this->isBalitaImunisasi($imunisasi)) {
                return redirect()
                    ->route('bidan.imunisasi.index')
                    ->with('error', 'Data imunisasi ini bukan milik sasaran Balita.');
            }

            $balita = $this->balitaFromImunisasi($imunisasi);
            $programOptions = $this->programOptions();

            return view('bidan.imunisasi.show', compact(
                'imunisasi',
                'balita',
                'programOptions'
            ));
        } catch (\Throwable $e) {
            Log::error('BIDAN_IMUNISASI_SHOW_ERROR', [
                'message' => $e->getMessage(),
                'imunisasi_id' => $id,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return redirect()
                ->route('bidan.imunisasi.index')
                ->with('error', 'Catatan imunisasi tidak ditemukan.');
        }
    }

    public function edit(int|string $id): View|RedirectResponse
    {
        try {
            $imunisasi = Imunisasi::query()
                ->with(['kunjungan.pasien', 'kunjungan.petugas'])
                ->findOrFail($id);

            if (!$this->isBalitaImunisasi($imunisasi)) {
                return redirect()
                    ->route('bidan.imunisasi.index')
                    ->with('error', 'Data imunisasi ini bukan milik sasaran Balita.');
            }

            $balitas = $this->balitaOptions();
            $programOptions = $this->programOptions();

            $mode = 'edit';
            $selectedBalita = $this->balitaFromImunisasi($imunisasi);

            return view('bidan.imunisasi.create', compact(
                'imunisasi',
                'balitas',
                'programOptions',
                'mode',
                'selectedBalita'
            ));
        } catch (\Throwable $e) {
            Log::error('BIDAN_IMUNISASI_EDIT_ERROR', [
                'message' => $e->getMessage(),
                'imunisasi_id' => $id,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return redirect()
                ->route('bidan.imunisasi.index')
                ->with('error', 'Gagal membuka form perbaikan catatan imunisasi.');
        }
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $this->validateImunisasi($request);

        DB::beginTransaction();

        try {
            $imunisasi = Imunisasi::query()
                ->with(['kunjungan.pasien'])
                ->findOrFail($id);

            if (!$this->isBalitaImunisasi($imunisasi)) {
                DB::rollBack();

                return redirect()
                    ->route('bidan.imunisasi.index')
                    ->with('error', 'Data imunisasi ini bukan milik sasaran Balita.');
            }

            $balita = Balita::query()->findOrFail($validated['balita_id']);
            $kunjungan = $this->buatAtauAmbilKunjungan($balita, $validated['tanggal_imunisasi']);

            $imunisasi->update(
                $this->payloadImunisasi($validated, $kunjungan, $balita)
            );

            DB::commit();

            return redirect()
                ->route('bidan.imunisasi.show', $imunisasi->id)
                ->with('success', 'Catatan imunisasi Balita berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('BIDAN_IMUNISASI_UPDATE_ERROR', [
                'message' => $e->getMessage(),
                'imunisasi_id' => $id,
                'user_id' => Auth::id(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui catatan imunisasi Balita.');
        }
    }

    public function destroy(int|string $id): RedirectResponse
    {
        try {
            $imunisasi = Imunisasi::query()->findOrFail($id);
            $imunisasi->delete();

            return redirect()
                ->route('bidan.imunisasi.index')
                ->with('success', 'Catatan imunisasi Balita berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('BIDAN_IMUNISASI_DESTROY_ERROR', [
                'message' => $e->getMessage(),
                'imunisasi_id' => $id,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->with('error', 'Catatan imunisasi gagal dihapus.');
        }
    }

    private function validateImunisasi(Request $request): array
    {
        if (!$request->filled('balita_id') && $request->filled('pasien_id')) {
            $request->merge([
                'balita_id' => $request->input('pasien_id'),
            ]);
        }

        return $request->validate([
            'balita_id' => ['required', 'integer', 'exists:balitas,id'],
            'kategori' => ['nullable', 'in:balita'],
            'jenis_imunisasi' => ['required', 'string', 'max:100'],
            'vaksin' => ['required', 'string', 'max:100'],
            'dosis' => ['required', 'string', 'max:50'],
            'batch_number' => ['nullable', 'string', 'max:100'],
            'tanggal_imunisasi' => ['required', 'date', 'before_or_equal:today'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], [
            'balita_id.required' => 'Identitas Balita wajib dipilih.',
            'balita_id.exists' => 'Data Balita tidak ditemukan.',
            'kategori.in' => 'Imunisasi hanya digunakan untuk sasaran Balita.',
            'jenis_imunisasi.required' => 'Jenis imunisasi wajib diisi.',
            'vaksin.required' => 'Nama vaksin wajib diisi.',
            'dosis.required' => 'Dosis imunisasi wajib diisi.',
            'tanggal_imunisasi.required' => 'Tanggal imunisasi wajib diisi.',
            'tanggal_imunisasi.before_or_equal' => 'Tanggal imunisasi tidak boleh melebihi tanggal hari ini.',
            'batch_number.max' => 'Nomor batch maksimal 100 karakter.',
            'keterangan.max' => 'Catatan maksimal 1000 karakter.',
        ]);
    }

    private function applyTargetBalita(Builder $query): void
    {
        $query->where(function (Builder $q) {
            $q->whereHas('kunjungan', function (Builder $kunjungan) {
                $kunjungan->whereIn('pasien_type', $this->morphTypeValues(Balita::class));
            });

            if ($this->hasColumn($this->imunisasiTable(), 'balita_id')) {
                $q->orWhereNotNull('balita_id');
            }
        });
    }

    private function applySearch(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $q) use ($search) {
            foreach (['jenis_imunisasi', 'vaksin', 'dosis', 'batch_number', 'keterangan', 'catatan'] as $column) {
                if ($this->hasColumn($this->imunisasiTable(), $column)) {
                    $q->orWhere($column, 'like', '%' . $search . '%');
                }
            }

            $q->orWhereHas('kunjungan', function (Builder $kunjungan) use ($search) {
                $kunjungan->whereHasMorph('pasien', [Balita::class], function (Builder $balita) use ($search) {
                    $balita->where(function (Builder $b) use ($search) {
                        foreach ($this->balitaSearchColumns() as $column) {
                            $b->orWhere($column, 'like', '%' . $search . '%');
                        }
                    });
                });
            });

            if ($this->hasColumn($this->imunisasiTable(), 'balita_id')) {
                $matchedBalitaIds = Balita::query()
                    ->where(function (Builder $balita) use ($search) {
                        foreach ($this->balitaSearchColumns() as $column) {
                            $balita->orWhere($column, 'like', '%' . $search . '%');
                        }
                    })
                    ->limit(300)
                    ->pluck('id');

                if ($matchedBalitaIds->isNotEmpty()) {
                    $q->orWhereIn('balita_id', $matchedBalitaIds);
                }
            }
        });
    }

    private function applyLatestOrder(Builder $query): void
    {
        if ($this->hasColumn($this->imunisasiTable(), 'tanggal_imunisasi')) {
            $query->orderByDesc('tanggal_imunisasi');
        }

        if ($this->hasColumn($this->imunisasiTable(), 'created_at')) {
            $query->orderByDesc('created_at');
        }

        $query->orderByDesc('id');
    }

    private function stats(): array
    {
        $base = Imunisasi::query();
        $this->applyTargetBalita($base);

        $bulanIni = clone $base;

        if ($this->hasColumn($this->imunisasiTable(), 'tanggal_imunisasi')) {
            $bulanIni
                ->whereMonth('tanggal_imunisasi', now()->month)
                ->whereYear('tanggal_imunisasi', now()->year);
        } else {
            $bulanIni->whereRaw('1 = 0');
        }

        $vaksinTercatat = clone $base;

        $vaksinCount = $this->hasColumn($this->imunisasiTable(), 'vaksin')
            ? $vaksinTercatat
                ->whereNotNull('vaksin')
                ->where('vaksin', '!=', '')
                ->distinct('vaksin')
                ->count('vaksin')
            : 0;

        return [
            'total' => (clone $base)->count(),
            'bulan_ini' => $bulanIni->count(),
            'total_balita' => Balita::query()->count(),
            'vaksin_tercatat' => $vaksinCount,
        ];
    }

    private function balitaOptions()
    {
        $query = Balita::query();

        $columns = collect([
            'id',
            'nama_lengkap',
            'nama',
            'nik',
            'tanggal_lahir',
            'jenis_kelamin',
            'nama_ibu',
            'nama_ayah',
            'alamat',
        ])
            ->filter(fn (string $column) => $this->hasColumn($this->balitaTable(), $column))
            ->values()
            ->all();

        if (!in_array('id', $columns, true)) {
            $columns[] = 'id';
        }

        $query->select($columns);

        if ($this->hasColumn($this->balitaTable(), 'nama_lengkap')) {
            $query->orderBy('nama_lengkap');
        } elseif ($this->hasColumn($this->balitaTable(), 'nama')) {
            $query->orderBy('nama');
        } else {
            $query->orderBy('id');
        }

        return $query->get();
    }

    private function buatAtauAmbilKunjungan(Balita $balita, string $tanggal): Kunjungan
    {
        $table = $this->kunjunganTable();

        foreach (['pasien_id', 'pasien_type', 'tanggal_kunjungan'] as $requiredColumn) {
            if (!$this->hasColumn($table, $requiredColumn)) {
                throw new \RuntimeException("Kolom {$requiredColumn} tidak ditemukan pada tabel kunjungan.");
            }
        }

        $keys = [
            'pasien_id' => $balita->id,
            'pasien_type' => Balita::class,
            'tanggal_kunjungan' => $tanggal,
        ];

        $defaults = [];

        if ($this->hasColumn($table, 'jenis_kunjungan')) {
            $defaults['jenis_kunjungan'] = 'imunisasi';
        }

        if ($this->hasColumn($table, 'petugas_id')) {
            $defaults['petugas_id'] = Auth::id();
        }

        if ($this->hasColumn($table, 'bidan_id')) {
            $defaults['bidan_id'] = Auth::id();
        }

        if ($this->hasColumn($table, 'status')) {
            $defaults['status'] = 'selesai';
        }

        if ($this->hasColumn($table, 'pertemuan_ke')) {
            $defaults['pertemuan_ke'] = 1;
        }

        return Kunjungan::query()->firstOrCreate($keys, $defaults);
    }

    private function payloadImunisasi(array $validated, Kunjungan $kunjungan, Balita $balita): array
    {
        $catatan = trim((string) ($validated['keterangan'] ?? ''));
        $catatan = $catatan !== '' ? $catatan : null;

        $payload = [
            'kunjungan_id' => $kunjungan->id,
            'balita_id' => $balita->id,
            'kategori' => 'balita',
            'kategori_pasien' => 'balita',
            'jenis_imunisasi' => $validated['jenis_imunisasi'],
            'nama_imunisasi' => $validated['jenis_imunisasi'],
            'vaksin' => $validated['vaksin'],
            'dosis' => $validated['dosis'],
            'batch_number' => $validated['batch_number'] ?? null,
            'tanggal_imunisasi' => $validated['tanggal_imunisasi'],
            'keterangan' => $catatan,
            'catatan' => $catatan,
            'petugas_id' => Auth::id(),
            'bidan_id' => Auth::id(),
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ];

        return $this->onlyExistingColumns($this->imunisasiTable(), $payload);
    }

    private function balitaFromImunisasi(Imunisasi $imunisasi): ?Balita
    {
        $pasien = $imunisasi->kunjungan?->pasien;

        if ($pasien instanceof Balita) {
            return $pasien;
        }

        if ($this->hasColumn($this->imunisasiTable(), 'balita_id') && $imunisasi->balita_id) {
            return Balita::query()->find($imunisasi->balita_id);
        }

        return null;
    }

    private function isBalitaImunisasi(Imunisasi $imunisasi): bool
    {
        $pasien = $imunisasi->kunjungan?->pasien;

        if ($pasien instanceof Balita) {
            return true;
        }

        $pasienType = (string) ($imunisasi->kunjungan?->pasien_type ?? '');

        if ($pasienType !== '' && in_array($pasienType, $this->morphTypeValues(Balita::class), true)) {
            return true;
        }

        return $this->hasColumn($this->imunisasiTable(), 'balita_id') && !empty($imunisasi->balita_id);
    }

    private function balitaSearchColumns(): array
    {
        return collect([
            'nama_lengkap',
            'nama',
            'nik',
            'nama_ibu',
            'nama_ayah',
            'alamat',
        ])
            ->filter(fn (string $column) => $this->hasColumn($this->balitaTable(), $column))
            ->values()
            ->all();
    }

    private function programOptions(): array
    {
        return [
            'BCG' => 'BCG',
            'Polio' => 'Polio',
            'DPT-HB-Hib' => 'DPT-HB-Hib',
            'Hepatitis B' => 'Hepatitis B',
            'Campak / MR' => 'Campak / MR',
            'IPV' => 'IPV',
            'PCV' => 'PCV',
            'Rotavirus' => 'Rotavirus',
            'Lainnya' => 'Lainnya',
        ];
    }

    private function morphTypeValues(string $modelClass): array
    {
        $baseName = class_basename($modelClass);

        return array_values(array_unique([
            $modelClass,
            $baseName,
            strtolower($baseName),
        ]));
    }

    private function cleanSearch(mixed $value): string
    {
        if (is_array($value)) {
            return '';
        }

        return trim((string) $value);
    }

    private function onlyExistingColumns(string $table, array $payload): array
    {
        return collect($payload)
            ->filter(fn ($value, string $column) => $this->hasColumn($table, $column))
            ->all();
    }

    private function hasColumn(string $table, string $column): bool
    {
        $key = $table . '.' . $column;

        if (array_key_exists($key, $this->columnCache)) {
            return $this->columnCache[$key];
        }

        try {
            return $this->columnCache[$key] = Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return $this->columnCache[$key] = false;
        }
    }

    private function imunisasiTable(): string
    {
        return (new Imunisasi())->getTable();
    }

    private function kunjunganTable(): string
    {
        return (new Kunjungan())->getTable();
    }

    private function balitaTable(): string
    {
        return (new Balita())->getTable();
    }
}