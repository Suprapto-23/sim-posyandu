<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use App\Models\Pemeriksaan;
use App\Services\AnalisisBalitaService;
use App\Services\AnalisisLansiaService;
use App\Services\AnalisisRemajaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PemeriksaanController extends Controller
{
    protected AnalisisBalitaService $balitaService;
    protected AnalisisRemajaService $remajaService;
    protected AnalisisLansiaService $lansiaService;

    public function __construct(
        AnalisisBalitaService $balitaService,
        AnalisisRemajaService $remajaService,
        AnalisisLansiaService $lansiaService
    ) {
        $this->balitaService = $balitaService;
        $this->remajaService = $remajaService;
        $this->lansiaService = $lansiaService;
    }

    public function index(Request $request): View|RedirectResponse
    {
        try {
            $tab = $request->get('tab', 'pending');
            $search = trim((string) $request->get('search', ''));

            $query = Pemeriksaan::query()
                ->with(['kunjungan.pasien', 'pemeriksa', 'verifikator'])
                ->latest();

            if ($tab === 'verified') {
                $query->whereIn('status_verifikasi', ['verified', 'tervalidasi', 'approved']);
            } else {
                $query->where(function ($q) {
                    $q->where('status_verifikasi', 'pending')
                        ->orWhereNull('status_verifikasi');
                });

                $tab = 'pending';
            }

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('kunjungan.pasien', function ($pasien) use ($search) {
                        $pasien->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    })->orWhere('kategori_pasien', 'like', "%{$search}%")
                        ->orWhere('diagnosa', 'like', "%{$search}%");
                });
            }

            $pemeriksaans = $query->paginate(15)->withQueryString();

            $pendingCount = Pemeriksaan::query()
                ->where(function ($q) {
                    $q->where('status_verifikasi', 'pending')
                        ->orWhereNull('status_verifikasi');
                })
                ->count();

            return view('bidan.pemeriksaan.index', compact(
                'pemeriksaans',
                'tab',
                'pendingCount',
                'search'
            ));
        } catch (\Throwable $e) {
            Log::error('BIDAN_PEMERIKSAAN_INDEX_ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()->with('error', 'Gagal memuat ruang tunggu pemeriksaan.');
        }
    }

    public function create(): RedirectResponse
    {
        return redirect()
            ->route('bidan.pemeriksaan.index')
            ->with('info', 'Input pemeriksaan awal dilakukan oleh Kader. Bidan fokus pada validasi dan pengesahan hasil pemeriksaan.');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()
            ->route('bidan.pemeriksaan.index')
            ->with('info', 'Data pemeriksaan baru dibuat melalui modul Kader. Bidan dapat memvalidasi data yang sudah masuk ke ruang tunggu.');
    }

    public function validasi($id): View|RedirectResponse
    {
        try {
            $pemeriksaan = Pemeriksaan::query()
                ->with(['kunjungan.pasien', 'pemeriksa', 'verifikator'])
                ->findOrFail($id);

            $pasien = $pemeriksaan->kunjungan->pasien ?? null;

            if (!$pasien) {
                return redirect()
                    ->route('bidan.pemeriksaan.index')
                    ->with('error', 'Biodata pasien tidak ditemukan. Periksa relasi kunjungan dan data sasaran.');
            }

            $analisis = $this->buatAnalisisOtomatis($pemeriksaan, $pasien);

            return view('bidan.pemeriksaan.validasi', compact(
                'pemeriksaan',
                'pasien',
                'analisis'
            ));
        } catch (\Throwable $e) {
            Log::error('BIDAN_PEMERIKSAAN_VALIDASI_ERROR', [
                'message' => $e->getMessage(),
                'pemeriksaan_id' => $id,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return redirect()
                ->route('bidan.pemeriksaan.index')
                ->with('error', 'Gagal memuat panel validasi pemeriksaan.');
        }
    }

    public function simpanValidasi(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'status_gizi' => ['nullable', 'string', 'max:100'],
            'diagnosa' => ['required', 'string', 'max:255'],
            'tindakan' => ['nullable', 'string', 'max:255'],
            'catatan_edukasi' => ['nullable', 'string'],
        ], [
            'diagnosa.required' => 'Kolom kesimpulan diagnosa medis wajib diisi oleh Bidan.',
        ]);

        DB::beginTransaction();

        try {
            $pemeriksaan = Pemeriksaan::query()
                ->with(['kunjungan.pasien'])
                ->findOrFail($id);

            $imt = $this->hitungImt(
                $pemeriksaan->berat_badan,
                $pemeriksaan->tinggi_badan,
                $pemeriksaan->imt
            );

            $payload = [
                'imt' => $imt,
                'status_gizi' => $validated['status_gizi'] ?? $pemeriksaan->status_gizi,
                'diagnosa' => $validated['diagnosa'],
                'tindakan' => $validated['tindakan'] ?? '-',
                'edukasi' => $validated['catatan_edukasi'] ?? '-',
                'status_verifikasi' => 'verified',
                'tanggal_periksa' => now(),
            ];

            $payload = $this->lengkapiKolomVerifikasi($payload);

            $pemeriksaan->forceFill($payload)->save();

            DB::commit();

            return redirect()
                ->route('bidan.pemeriksaan.index', ['tab' => 'verified'])
                ->with('success', 'Rekam medis berhasil disahkan dan diterbitkan ke Buku Kesehatan Digital warga.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('BIDAN_PEMERIKSAAN_SIMPAN_VALIDASI_ERROR', [
                'message' => $e->getMessage(),
                'pemeriksaan_id' => $id,
                'user_id' => Auth::id(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal mengesahkan data: ' . $e->getMessage());
        }
    }

    public function show($id): View|RedirectResponse
    {
        try {
            $pemeriksaan = Pemeriksaan::query()
                ->with(['kunjungan.pasien', 'pemeriksa', 'verifikator'])
                ->findOrFail($id);

            $pasien = $pemeriksaan->kunjungan->pasien ?? null;

            if (!$pasien) {
                return redirect()
                    ->route('bidan.pemeriksaan.index')
                    ->with('error', 'Biodata pasien tidak ditemukan.');
            }

            return view('bidan.pemeriksaan.show', compact('pemeriksaan', 'pasien'));
        } catch (\Throwable $e) {
            Log::error('BIDAN_PEMERIKSAAN_SHOW_ERROR', [
                'message' => $e->getMessage(),
                'pemeriksaan_id' => $id,
            ]);

            return redirect()
                ->route('bidan.pemeriksaan.index')
                ->with('error', 'Data pemeriksaan tidak ditemukan.');
        }
    }

    public function edit($id): View|RedirectResponse
    {
        return $this->validasi($id);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        return $this->simpanValidasi($request, $id);
    }

    public function verifikasi(Request $request, $id): RedirectResponse
    {
        try {
            $pemeriksaan = Pemeriksaan::query()->findOrFail($id);

            $request->merge([
                'status_gizi' => $request->input('status_gizi', $pemeriksaan->status_gizi ?? 'Normal'),
                'diagnosa' => $request->input('diagnosa', $pemeriksaan->diagnosa ?? 'Pemeriksaan telah diverifikasi oleh Bidan.'),
                'tindakan' => $request->input('tindakan', $pemeriksaan->tindakan ?? '-'),
                'catatan_edukasi' => $request->input('catatan_edukasi', $pemeriksaan->edukasi ?? '-'),
            ]);

            return $this->simpanValidasi($request, $id);
        } catch (\Throwable $e) {
            Log::error('BIDAN_PEMERIKSAAN_VERIFIKASI_ERROR', [
                'message' => $e->getMessage(),
                'pemeriksaan_id' => $id,
            ]);

            return back()->with('error', 'Gagal melakukan verifikasi pemeriksaan.');
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            $pemeriksaan = Pemeriksaan::query()->findOrFail($id);
            $pemeriksaan->delete();

            return back()->with('success', 'Data antrean pemeriksaan berhasil dihapus dari sistem.');
        } catch (\Throwable $e) {
            Log::error('BIDAN_PEMERIKSAAN_DESTROY_ERROR', [
                'message' => $e->getMessage(),
                'pemeriksaan_id' => $id,
            ]);

            return back()->with('error', 'Gagal menghapus data pemeriksaan.');
        }
    }

    private function buatAnalisisOtomatis(Pemeriksaan $pemeriksaan, object $pasien): ?array
    {
        $kategori = strtolower((string) $pemeriksaan->kategori_pasien);

        try {
            if ($kategori === 'balita') {
                $usiaBulan = (int) ($pasien->usia_bulan ?? 0);
                $jenisKelamin = $pasien->jenis_kelamin ?? 'L';

                return $this->balitaService->analisisKomprehensif(
                    $usiaBulan,
                    $jenisKelamin,
                    (float) ($pemeriksaan->berat_badan ?? 0),
                    (float) ($pemeriksaan->tinggi_badan ?? 0)
                );
            }

            if ($kategori === 'remaja') {
                $usiaTahun = (int) ($pasien->usia_tahun ?? 0);
                $jenisKelamin = $pasien->jenis_kelamin ?? 'L';
                $imt = $this->hitungImt(
                    $pemeriksaan->berat_badan,
                    $pemeriksaan->tinggi_badan,
                    $pemeriksaan->imt
                );

                return $this->remajaService->analisisIMT($usiaTahun, $jenisKelamin, (float) $imt);
            }

            if ($kategori === 'lansia') {
                $jenisKelamin = $pasien->jenis_kelamin ?? 'L';

                return $this->lansiaService->analisisKomprehensif($pemeriksaan, $jenisKelamin);
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('BIDAN_PEMERIKSAAN_ANALISIS_OTOMATIS_ERROR', [
                'message' => $e->getMessage(),
                'pemeriksaan_id' => $pemeriksaan->id,
                'kategori' => $kategori,
            ]);

            return null;
        }
    }

    private function hitungImt($beratBadan, $tinggiBadan, $imtSaatIni = null): ?float
    {
        if (!empty($imtSaatIni) && (float) $imtSaatIni > 0) {
            return round((float) $imtSaatIni, 2);
        }

        $bb = (float) ($beratBadan ?? 0);
        $tb = (float) ($tinggiBadan ?? 0);

        if ($bb <= 0 || $tb <= 0) {
            return null;
        }

        return round($bb / (($tb / 100) ** 2), 2);
    }

    private function lengkapiKolomVerifikasi(array $payload): array
    {
        if (Schema::hasColumn('pemeriksaans', 'verified_by')) {
            $payload['verified_by'] = Auth::id();
        }

        if (Schema::hasColumn('pemeriksaans', 'verified_at')) {
            $payload['verified_at'] = now();
        }

        if (Schema::hasColumn('pemeriksaans', 'user_id_verifikator')) {
            $payload['user_id_verifikator'] = Auth::id();
        }

        return $payload;
    }
}