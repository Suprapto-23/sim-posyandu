<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPosyandu;
use App\Models\Balita;
use App\Models\Lansia;
use App\Models\Remaja;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AbsensiController extends Controller
{
    private array $modelMapping = [
        'balita' => Balita::class,
        'remaja' => Remaja::class,
        'lansia' => Lansia::class,
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $kategori = $this->normalizeKategori($request->get('kategori', 'balita'));

        if (!array_key_exists($kategori, $this->modelMapping)) {
            return redirect()->route('kader.absensi.index', [
                'kategori' => 'balita',
            ]);
        }

        $tanggal = $this->resolveTanggal($request->get('tanggal'));
        $pasiens = $this->getPasienByKategori($kategori);

        $sesiHariIni = AbsensiPosyandu::query()
            ->with(['details'])
            ->where('kategori', $kategori)
            ->whereDate('tanggal_posyandu', $tanggal->toDateString())
            ->first();

        $absensiData = $sesiHariIni
            ? $sesiHariIni->details->keyBy(fn ($detail) => (int) $detail->pasien_id)
            : collect();

        $pertemuanBerikutnya = $sesiHariIni
            ? (int) $sesiHariIni->nomor_pertemuan
            : $this->getNomorPertemuanBerikutnya($kategori, $tanggal);

        return view('kader.absensi.index', [
            'kategori' => $kategori,
            'tanggal' => $tanggal->toDateString(),
            'pasiens' => $pasiens,
            'sesiHariIni' => $sesiHariIni,
            'absensiData' => $absensiData,
            'pertemuanBerikutnya' => $pertemuanBerikutnya,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kategori' => ['required', 'in:balita,remaja,lansia'],
            'tanggal' => ['nullable', 'date'],
            'kehadiran' => ['required', 'array'],
            'kehadiran.*' => ['nullable', 'in:0,1'],
            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['nullable', 'string', 'max:255'],
        ], [
            'kategori.required' => 'Kategori sasaran wajib dipilih.',
            'kategori.in' => 'Kategori sasaran tidak valid.',
            'tanggal.date' => 'Tanggal absensi tidak valid.',
            'tanggal.before_or_equal' => 'Tanggal absensi tidak boleh melebihi hari ini.',
            'kehadiran.required' => 'Data kehadiran belum tersedia.',
            'kehadiran.array' => 'Format data kehadiran tidak valid.',
            'kehadiran.*.in' => 'Status kehadiran hanya boleh hadir atau tidak hadir.',
            'keterangan.*.max' => 'Keterangan maksimal 255 karakter.',
        ]);

        $kategori = $this->normalizeKategori($validated['kategori']);
        $tanggal = $this->resolveTanggal($validated['tanggal'] ?? null);

        if (!array_key_exists($kategori, $this->modelMapping)) {
            return back()
                ->withInput()
                ->with('error', 'Kategori sasaran tidak valid.');
        }

        $allPasiens = $this->getPasienByKategori($kategori);

        if ($allPasiens->isEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'Data sasaran belum tersedia. Tambahkan data sasaran terlebih dahulu.');
        }

        $allPasienIds = $allPasiens
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $submittedKehadiran = collect($validated['kehadiran'] ?? [])
            ->mapWithKeys(function ($value, $id) {
                return [
                    (int) $id => is_null($value) ? null : (string) $value,
                ];
            });

        $submittedIds = $submittedKehadiran
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->values();

        $invalidIds = $submittedIds->diff($allPasienIds);

        if ($invalidIds->isNotEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'Ada data sasaran yang tidak valid. Sistem menolak proses absensi.');
        }

        $missingIds = $allPasienIds->diff($submittedIds);

        if ($missingIds->isNotEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'Ada data sasaran yang tidak terkirim ke sistem. Muat ulang halaman lalu coba kembali.');
        }

        $pendingIds = $allPasienIds->filter(function ($pasienId) use ($submittedKehadiran) {
            $value = $submittedKehadiran->get($pasienId);

            return !in_array($value, ['0', '1'], true);
        });

        if ($pendingIds->isNotEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'Masih ada ' . $pendingIds->count() . ' data sasaran yang belum dipilih hadir atau tidak hadir.');
        }

        DB::beginTransaction();

        try {
            $absensi = AbsensiPosyandu::query()
                ->where('kategori', $kategori)
                ->whereDate('tanggal_posyandu', $tanggal->toDateString())
                ->lockForUpdate()
                ->first();

            if (!$absensi) {
                $nomorPertemuan = $this->getNomorPertemuanBerikutnya($kategori, $tanggal);

                $absensi = new AbsensiPosyandu();
                $absensi->kategori = $kategori;
                $absensi->tanggal_posyandu = $tanggal->toDateString();
                $absensi->bulan = (int) $tanggal->month;
                $absensi->tahun = (int) $tanggal->year;
                $absensi->nomor_pertemuan = $nomorPertemuan;
                $absensi->kode_absensi = $this->generateKodeAbsensi($kategori, $tanggal, $nomorPertemuan);
                $absensi->dicatat_oleh = auth()->id();
                $absensi->save();
            } else {
                $absensi->dicatat_oleh = $absensi->dicatat_oleh ?: auth()->id();
                $absensi->bulan = (int) $tanggal->month;
                $absensi->tahun = (int) $tanggal->year;
                $absensi->save();
            }

            foreach ($allPasienIds as $pasienId) {
                $status = $submittedKehadiran->get($pasienId);

                $absensi->details()->updateOrCreate(
                    [
                        'pasien_id' => $pasienId,
                        'pasien_type' => $kategori,
                    ],
                    [
                        'hadir' => $status === '1',
                        'keterangan' => $request->input("keterangan.$pasienId") ?: null,
                    ]
                );
            }

            DB::commit();

            return redirect()
                ->route('kader.absensi.success', [
                    'id' => $absensi->id,
                ])
                ->with([
                    'success' => 'Presensi Posyandu berhasil disimpan lengkap.',
                    'last_absensi_id' => $absensi->id,
                ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('KADER_ABSENSI_STORE_ERROR', [
                'message' => $e->getMessage(),
                'kategori' => $kategori,
                'tanggal' => $tanggal->toDateString(),
                'user_id' => auth()->id(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan absensi. Periksa kembali data dan coba ulang.');
        }
    }

    public function success(Request $request): View|RedirectResponse
    {
        $id = $request->query('id') ?? session('last_absensi_id');

        $absensi = null;

        if ($id) {
            $absensi = AbsensiPosyandu::query()
                ->with(['kader', 'details.pasien'])
                ->find($id);
        }

        if (!$absensi) {
            $absensi = AbsensiPosyandu::query()
                ->with(['kader', 'details.pasien'])
                ->where('dicatat_oleh', auth()->id())
                ->latest('updated_at')
                ->first();
        }

        if (!$absensi) {
            return redirect()
                ->route('kader.absensi.index')
                ->with('error', 'Data presensi tidak ditemukan. Silakan cek riwayat absensi.');
        }

        return view('kader.absensi.success', compact('absensi'));
    }

    public function riwayat(Request $request): View
    {
        $bulan = (int) $request->get('bulan', now('Asia/Jakarta')->month);
        $tahun = (int) $request->get('tahun', now('Asia/Jakarta')->year);
        $kategori = $request->get('kategori');
        $search = trim((string) $request->get('search', ''));

        if ($bulan < 1 || $bulan > 12) {
            $bulan = now('Asia/Jakarta')->month;
        }

        if ($tahun < 2020 || $tahun > ((int) now('Asia/Jakarta')->year + 1)) {
            $tahun = now('Asia/Jakarta')->year;
        }

        $kategori = $kategori ? $this->normalizeKategori($kategori) : null;

        if ($kategori && !array_key_exists($kategori, $this->modelMapping)) {
            $kategori = null;
        }

        $riwayats = AbsensiPosyandu::query()
            ->with(['kader'])
            ->withCount([
                'details as total_peserta',
                'details as total_hadir' => fn ($query) => $query->where('hadir', true),
                'details as total_tidak_hadir' => fn ($query) => $query->where('hadir', false),
            ])
            ->when($kategori, function ($query) use ($kategori) {
                $query->where('kategori', $kategori);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('kode_absensi', 'like', "%{$search}%")
                        ->orWhere('tanggal_posyandu', 'like', "%{$search}%");

                    if (Schema::hasColumn('absensi_posyandu', 'kategori')) {
                        $q->orWhere('kategori', 'like', "%{$search}%");
                    }
                });
            })
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->orderByDesc('tanggal_posyandu')
            ->orderByDesc('created_at')
            ->get();

        return view('kader.absensi.riwayat', [
            'riwayats' => $riwayats,
            'riwayat' => $riwayats,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'kategori' => $kategori,
            'search' => $search,
        ]);
    }

    public function show($id): View
    {
        $absensi = AbsensiPosyandu::query()
            ->with(['kader', 'details.pasien'])
            ->findOrFail($id);

        $details = $absensi->details ?? collect();

        $totalPasien = $details->count();
        $totalHadir = $details->where('hadir', true)->count();
        $totalAbsen = max(0, $totalPasien - $totalHadir);

        $semuaSesi = AbsensiPosyandu::query()
            ->where('kategori', $absensi->kategori)
            ->where('id', '!=', $absensi->id)
            ->orderByDesc('tanggal_posyandu')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('kader.absensi.show', [
            'absensi' => $absensi,
            'details' => $details,
            'totalPasien' => $totalPasien,
            'totalHadir' => $totalHadir,
            'totalAbsen' => $totalAbsen,
            'semuaSesi' => $semuaSesi,
        ]);
    }

    public function destroy($id): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $absensi = AbsensiPosyandu::query()
                ->withCount('details')
                ->findOrFail($id);

            $kategori = $absensi->kategori;
            $bulan = $absensi->bulan ?: now('Asia/Jakarta')->month;
            $tahun = $absensi->tahun ?: now('Asia/Jakarta')->year;
            $kode = $absensi->kode_absensi ?? 'Absensi';

            $absensi->details()->delete();
            $absensi->delete();

            DB::commit();

            return redirect()
                ->route('kader.absensi.riwayat', [
                    'kategori' => $kategori,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ])
                ->with('success', "Data {$kode} berhasil dihapus dari riwayat absensi.");
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('KADER_ABSENSI_DELETE_ERROR', [
                'message' => $e->getMessage(),
                'absensi_id' => $id,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->with('error', 'Gagal menghapus data absensi.');
        }
    }

    private function getPasienByKategori(string $kategori): Collection
    {
        $kategori = $this->normalizeKategori($kategori);

        return match ($kategori) {
            'balita' => Balita::query()
                ->select($this->existingColumns('balitas', [
                    'id',
                    'nama_lengkap',
                    'nik',
                    'tanggal_lahir',
                    'jenis_kelamin',
                    'nama_ibu',
                    'alamat',
                    'created_at',
                ]))
                ->when(
                    Schema::hasColumn('balitas', 'nama_lengkap'),
                    fn ($query) => $query->orderBy('nama_lengkap'),
                    fn ($query) => $query->orderBy('id')
                )
                ->get(),

            'remaja' => Remaja::query()
                ->select($this->existingColumns('remajas', [
                    'id',
                    'nama_lengkap',
                    'nik',
                    'tanggal_lahir',
                    'jenis_kelamin',
                    'sekolah',
                    'kelas',
                    'alamat',
                    'created_at',
                ]))
                ->when(
                    Schema::hasColumn('remajas', 'nama_lengkap'),
                    fn ($query) => $query->orderBy('nama_lengkap'),
                    fn ($query) => $query->orderBy('id')
                )
                ->get(),

            'lansia' => Lansia::query()
                ->select($this->existingColumns('lansias', [
                    'id',
                    'nama_lengkap',
                    'nik',
                    'tanggal_lahir',
                    'jenis_kelamin',
                    'tingkat_kemandirian',
                    'tekanan_darah',
                    'alamat',
                    'created_at',
                ]))
                ->when(
                    Schema::hasColumn('lansias', 'nama_lengkap'),
                    fn ($query) => $query->orderBy('nama_lengkap'),
                    fn ($query) => $query->orderBy('id')
                )
                ->get(),

            default => collect(),
        };
    }

    private function getNomorPertemuanBerikutnya(string $kategori, Carbon $tanggal): int
    {
        $kategori = $this->normalizeKategori($kategori);

        $lastNumber = AbsensiPosyandu::query()
            ->where('kategori', $kategori)
            ->where('bulan', (int) $tanggal->month)
            ->where('tahun', (int) $tanggal->year)
            ->max('nomor_pertemuan');

        return ((int) $lastNumber) + 1;
    }

    private function generateKodeAbsensi(string $kategori, Carbon $tanggal, int $nomorPertemuan): string
{
    $kategori = $this->normalizeKategori($kategori);

    if (method_exists(AbsensiPosyandu::class, 'generateKodeAbsensi')) {
        return AbsensiPosyandu::generateKodeAbsensi($kategori, $tanggal, $nomorPertemuan);
    }

    $prefix = match ($kategori) {
        'balita' => 'BAL',
        'remaja' => 'REM',
        'lansia' => 'LAN',
        default => 'POS',
    };

    $nomorPertemuan = max(1, (int) $nomorPertemuan);

    $base = 'ABS-'
        . $prefix
        . '-'
        . $tanggal->format('Ymd')
        . '-P'
        . str_pad($nomorPertemuan, 2, '0', STR_PAD_LEFT);

    $kode = $base;
    $counter = 1;

    while (AbsensiPosyandu::query()->where('kode_absensi', $kode)->exists()) {
        $kode = $base . '-' . $counter;
        $counter++;
    }

    return $kode;
}

    private function resolveTanggal(?string $tanggal): Carbon
    {
        try {
            if (filled($tanggal)) {
                $parsed = Carbon::parse($tanggal, 'Asia/Jakarta');

                if ($parsed->greaterThan(now('Asia/Jakarta'))) {
                    return today('Asia/Jakarta');
                }

                return $parsed;
            }
        } catch (\Throwable) {
            return today('Asia/Jakarta');
        }

        return today('Asia/Jakarta');
    }

    private function normalizeKategori(?string $kategori): string
    {
        $value = strtolower(trim((string) $kategori));
        $value = str_replace(['_', '-', ' '], '', $value);

        return match ($value) {
            'balita', 'balitas', 'anak' => 'balita',
            'remaja', 'remajas' => 'remaja',
            'lansia', 'lansias' => 'lansia',
            default => 'balita',
        };
    }

    private function existingColumns(string $table, array $columns): array
    {
        if (!Schema::hasTable($table)) {
            return ['id'];
        }

        $existing = array_values(array_filter($columns, function ($column) use ($table) {
            return Schema::hasColumn($table, $column);
        }));

        return !empty($existing) ? $existing : ['id'];
    }
}