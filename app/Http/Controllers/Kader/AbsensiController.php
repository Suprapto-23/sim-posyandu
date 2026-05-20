<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\AbsensiDetail;
use App\Models\AbsensiPosyandu;
use App\Models\Balita;
use App\Models\Lansia;
use App\Models\Remaja;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AbsensiController extends Controller
{
    private array $modelMapping = [
        'balita' => Balita::class,
        'remaja' => Remaja::class,
        'lansia' => Lansia::class,
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $kategori = $request->get('kategori', 'balita');

        if (!array_key_exists($kategori, $this->modelMapping)) {
            return redirect()->route('kader.absensi.index', ['kategori' => 'balita']);
        }

        $tanggal = Carbon::today('Asia/Jakarta');
        $pasiens = $this->getPasienByKategori($kategori);

        $sesiHariIni = AbsensiPosyandu::with('details')
            ->where('kategori', $kategori)
            ->whereDate('tanggal_posyandu', $tanggal->toDateString())
            ->first();

        $absensiData = $sesiHariIni
            ? $sesiHariIni->details->keyBy('pasien_id')
            : collect();

        $pertemuanBerikutnya = $sesiHariIni
            ? $sesiHariIni->nomor_pertemuan
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
            'kategori' => 'required|in:balita,remaja,lansia',
            'kehadiran' => 'required|array',
            'kehadiran.*' => 'required|in:0,1',
            'keterangan' => 'nullable|array',
            'keterangan.*' => 'nullable|string|max:255',
        ]);

        $kategori = $validated['kategori'];
        $modelClass = $this->modelMapping[$kategori];
        $kehadiranData = $validated['kehadiran'];
        $pasienIds = array_map('intval', array_keys($kehadiranData));

        $validIds = $modelClass::whereIn('id', $pasienIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        if (count($validIds) !== count($pasienIds)) {
            return back()
                ->withInput()
                ->with('error', 'Data sasaran tidak valid. Sistem menolak proses absensi.');
        }

        DB::beginTransaction();

        try {
            $tanggal = Carbon::today('Asia/Jakarta');

            $absensi = AbsensiPosyandu::firstOrCreate(
                [
                    'kategori' => $kategori,
                    'tanggal_posyandu' => $tanggal->toDateString(),
                ],
                [
                    'kode_absensi' => $this->generateKodeAbsensi($kategori, $tanggal),
                    'nomor_pertemuan' => $this->getNomorPertemuanBerikutnya($kategori, $tanggal),
                    'bulan' => $tanggal->month,
                    'tahun' => $tanggal->year,
                    'dicatat_oleh' => auth()->id(),
                ]
            );

            foreach ($kehadiranData as $pasienId => $status) {
                $absensi->details()->updateOrCreate(
                    [
                        'pasien_id' => (int) $pasienId,
                        'pasien_type' => $modelClass,
                    ],
                    [
                        'hadir' => (bool) ((int) $status),
                        'keterangan' => $request->input("keterangan.$pasienId"),
                    ]
                );
            }

            DB::commit();

            return redirect()
                ->route('kader.absensi.success', ['id' => $absensi->id])
                ->with('success', 'Presensi warga berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal menyimpan absensi kader', [
                'message' => $e->getMessage(),
                'kategori' => $kategori,
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan absensi. Cek kembali data dan coba ulang.');
        }
    }

    public function success(Request $request): View|RedirectResponse
{
    $id = $request->query('id') ?? session('last_absensi_id');

    if ($id) {
        $absensi = AbsensiPosyandu::with(['kader', 'details.pasien'])
            ->find($id);
    } else {
        $absensi = AbsensiPosyandu::with(['kader', 'details.pasien'])
            ->where('dicatat_oleh', auth()->id())
            ->latest('updated_at')
            ->first();
    }

    if (!$absensi) {
        return redirect()
            ->route('kader.absensi.index')
            ->with('error', 'Data presensi berhasil tidak ditemukan. Silakan cek riwayat absensi.');
    }

    return view('kader.absensi.success', compact('absensi'));
}
    public function riwayat(Request $request): View
    {
        $bulan = (int) $request->get('bulan', today('Asia/Jakarta')->month);
        $tahun = (int) $request->get('tahun', today('Asia/Jakarta')->year);
        $kategori = $request->get('kategori');

        $riwayats = AbsensiPosyandu::with('kader')
            ->withCount([
                'details as total_peserta',
                'details as total_hadir' => fn ($query) => $query->where('hadir', true),
            ])
            ->when($kategori && array_key_exists($kategori, $this->modelMapping), function ($query) use ($kategori) {
                $query->where('kategori', $kategori);
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
        ]);
    }

    public function show($id): View
    {
        $absensi = AbsensiPosyandu::with(['kader', 'details.pasien'])
            ->findOrFail($id);

        $details = $absensi->details;

        $totalPasien = $details->count();
        $totalHadir = $details->where('hadir', true)->count();
        $totalAbsen = max(0, $totalPasien - $totalHadir);

        $semuaSesi = AbsensiPosyandu::where('kategori', $absensi->kategori)
            ->where('id', '!=', $absensi->id)
            ->orderByDesc('tanggal_posyandu')
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
            $absensi = AbsensiPosyandu::findOrFail($id);

            $absensi->details()->delete();
            $absensi->delete();

            DB::commit();

            return redirect()
    ->route('kader.absensi.success', ['id' => $absensi->id])
    ->with([
        'success' => 'Presensi warga berhasil disimpan.',
        'last_absensi_id' => $absensi->id,
    ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal menghapus absensi kader', [
                'message' => $e->getMessage(),
                'absensi_id' => $id,
            ]);

            return back()->with('error', 'Gagal menghapus data presensi.');
        }
    }

    private function getPasienByKategori(string $kategori)
    {
        return match ($kategori) {
            'balita' => Balita::query()
                ->select('id', 'nama_lengkap', 'nik', 'tanggal_lahir')
                ->orderBy('nama_lengkap')
                ->get(),

            'remaja' => Remaja::query()
                ->select('id', 'nama_lengkap', 'nik')
                ->orderBy('nama_lengkap')
                ->get(),

            'lansia' => Lansia::query()
                ->select('id', 'nama_lengkap', 'nik')
                ->orderBy('nama_lengkap')
                ->get(),

            default => collect(),
        };
    }

    private function getNomorPertemuanBerikutnya(string $kategori, Carbon $tanggal): int
    {
        $lastNumber = AbsensiPosyandu::where('kategori', $kategori)
            ->where('bulan', $tanggal->month)
            ->where('tahun', $tanggal->year)
            ->max('nomor_pertemuan');

        return ((int) $lastNumber) + 1;
    }

    private function generateKodeAbsensi(string $kategori, Carbon $tanggal): string
    {
        $prefix = match ($kategori) {
            'balita' => 'BAL',
            'remaja' => 'REM',
            'lansia' => 'LAN',
            default => 'POS',
        };

        return 'ABS-' . $prefix . '-' . $tanggal->format('Ymd-His');
    }
}