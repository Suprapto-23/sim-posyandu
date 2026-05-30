<?php

namespace App\Http\Controllers\Bidan;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\JadwalPosyandu;
use App\Models\Lansia;
use App\Models\Notifikasi;
use App\Models\Remaja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class JadwalController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $status = $request->get('status', 'semua');
            $target = $request->get('target', 'semua');
            $search = trim((string) $request->get('search', ''));

            $query = JadwalPosyandu::query()
                ->latest('tanggal')
                ->latest('id');

            if (in_array($status, ['aktif', 'selesai', 'dibatalkan'], true)) {
                $query->where('status', $status);
            }

            if (in_array($target, ['balita', 'remaja', 'lansia'], true)) {
                $query->where('target_peserta', $target);
            }

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('judul', 'like', "%{$search}%")
                        ->orWhere('lokasi', 'like', "%{$search}%")
                        ->orWhere('kategori', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            }

            $jadwals = $query->paginate(10)->withQueryString();

            return view('bidan.jadwal.index', compact(
                'jadwals',
                'status',
                'target',
                'search'
            ));
        } catch (\Throwable $e) {
            Log::error('BIDAN_JADWAL_INDEX_ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()->with('error', 'Gagal memuat data jadwal Posyandu.');
        }
    }

    public function create(): View
    {
        return view('bidan.jadwal.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateJadwal($request);

        DB::beginTransaction();

        try {
            $payload = [
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'tanggal' => $validated['tanggal'],
                'waktu_mulai' => $validated['waktu_mulai'],
                'waktu_selesai' => $validated['waktu_selesai'],
                'lokasi' => $validated['lokasi'],
                'kategori' => $validated['kategori'],
                'target_peserta' => $validated['target_peserta'],
                'status' => 'aktif',
            ];

            if (Schema::hasColumn('jadwal_posyandus', 'created_by')) {
                $payload['created_by'] = Auth::id();
            }

            $jadwal = JadwalPosyandu::create($payload);

            $this->kirimNotifikasiJadwal($jadwal, 'created');

            DB::commit();

            return redirect()
                ->route('bidan.jadwal.index')
                ->with('success', 'Jadwal berhasil diterbitkan dan notifikasi telah dikirim ke sasaran terkait.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('BIDAN_JADWAL_STORE_ERROR', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Sistem gagal menerbitkan jadwal: ' . $e->getMessage());
        }
    }

    public function show(JadwalPosyandu $jadwal): View|RedirectResponse
    {
        try {
            return view('bidan.jadwal.show', compact('jadwal'));
        } catch (\Throwable $e) {
            Log::error('BIDAN_JADWAL_SHOW_ERROR', [
                'message' => $e->getMessage(),
                'jadwal_id' => $jadwal->id ?? null,
            ]);

            return redirect()
                ->route('bidan.jadwal.index')
                ->with('error', 'Detail jadwal tidak dapat ditampilkan.');
        }
    }

    public function edit(JadwalPosyandu $jadwal): View|RedirectResponse
    {
        try {
            return view('bidan.jadwal.edit', compact('jadwal'));
        } catch (\Throwable $e) {
            Log::error('BIDAN_JADWAL_EDIT_ERROR', [
                'message' => $e->getMessage(),
                'jadwal_id' => $jadwal->id ?? null,
            ]);

            return redirect()
                ->route('bidan.jadwal.index')
                ->with('error', 'Form edit jadwal tidak dapat ditampilkan.');
        }
    }

    public function update(Request $request, JadwalPosyandu $jadwal): RedirectResponse
    {
        $validated = $this->validateJadwal($request, true);

        DB::beginTransaction();

        try {
            $statusSebelum = $jadwal->status;

            $jadwal->update([
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'tanggal' => $validated['tanggal'],
                'waktu_mulai' => $validated['waktu_mulai'],
                'waktu_selesai' => $validated['waktu_selesai'],
                'lokasi' => $validated['lokasi'],
                'kategori' => $validated['kategori'],
                'target_peserta' => $validated['target_peserta'],
                'status' => $validated['status'],
            ]);

            if ($jadwal->status === 'dibatalkan' && $statusSebelum !== 'dibatalkan') {
                $this->kirimNotifikasiJadwal($jadwal->fresh(), 'cancelled');
            }

            DB::commit();

            return redirect()
                ->route('bidan.jadwal.index')
                ->with('success', 'Perubahan agenda jadwal berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('BIDAN_JADWAL_UPDATE_ERROR', [
                'message' => $e->getMessage(),
                'jadwal_id' => $jadwal->id ?? null,
                'user_id' => Auth::id(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui jadwal: ' . $e->getMessage());
        }
    }

    public function destroy(JadwalPosyandu $jadwal): RedirectResponse
    {
        try {
            $jadwal->delete();

            return redirect()
                ->route('bidan.jadwal.index')
                ->with('success', 'Agenda jadwal berhasil dihapus dari sistem.');
        } catch (\Throwable $e) {
            Log::error('BIDAN_JADWAL_DESTROY_ERROR', [
                'message' => $e->getMessage(),
                'jadwal_id' => $jadwal->id ?? null,
            ]);

            return back()->with('error', 'Gagal menghapus agenda jadwal.');
        }
    }

    private function validateJadwal(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'judul' => ['required', 'string', 'max:191'],
            'tanggal' => ['required', 'date'],
            'waktu_mulai' => ['required'],
            'waktu_selesai' => ['required', 'after:waktu_mulai'],
            'lokasi' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'in:imunisasi,pemeriksaan,posyandu,lainnya'],
            'target_peserta' => ['required', 'in:semua,balita,remaja,lansia'],
            'deskripsi' => ['nullable', 'string'],
        ];

        if ($isUpdate) {
            $rules['status'] = ['required', 'in:aktif,selesai,dibatalkan'];
        }

        return $request->validate($rules, [
            'judul.required' => 'Judul kegiatan wajib diisi.',
            'tanggal.required' => 'Tanggal pelaksanaan wajib diisi.',
            'waktu_mulai.required' => 'Jam mulai wajib diisi.',
            'waktu_selesai.required' => 'Jam selesai wajib diisi.',
            'waktu_selesai.after' => 'Jam selesai harus lebih besar dari jam mulai.',
            'lokasi.required' => 'Lokasi kegiatan wajib diisi.',
            'kategori.required' => 'Kategori layanan wajib dipilih.',
            'kategori.in' => 'Kategori layanan tidak valid.',
            'target_peserta.required' => 'Target sasaran wajib dipilih.',
            'target_peserta.in' => 'Target sasaran hanya boleh semua, balita, remaja, atau lansia.',
            'status.required' => 'Status jadwal wajib dipilih.',
            'status.in' => 'Status jadwal tidak valid.',
        ]);
    }

    private function kirimNotifikasiJadwal(JadwalPosyandu $jadwal, string $mode = 'created'): void
    {
        if (!Schema::hasTable('notifikasis')) {
            return;
        }

        $now = now();
        $tanggalFormat = Carbon::parse($jadwal->tanggal)->translatedFormat('d F Y');
        $kategoriTeks = $this->labelKategori($jadwal->kategori);
        $targetTeks = $this->labelTarget($jadwal->target_peserta);

        $notifData = [];

        $wargaUsers = $this->ambilUserWargaSesuaiTarget((string) $jadwal->target_peserta);

        if ($mode === 'cancelled') {
            $judulWarga = 'Peringatan: Jadwal Posyandu Dibatalkan';
            $pesanWarga = "Mohon maaf, agenda {$jadwal->judul} untuk {$targetTeks} pada {$tanggalFormat} di {$jadwal->lokasi} dibatalkan atau ditunda. Tunggu informasi selanjutnya dari Bidan atau Kader.";
        } else {
            $judulWarga = "Jadwal {$kategoriTeks} Baru";
            $pesanWarga = "Halo, agenda {$jadwal->judul} untuk {$targetTeks} akan dilaksanakan pada {$tanggalFormat} pukul {$this->formatJam($jadwal->waktu_mulai)} sampai {$this->formatJam($jadwal->waktu_selesai)} di {$jadwal->lokasi}.";

            if (!empty($jadwal->deskripsi)) {
                $pesanWarga .= ' ' . trim((string) $jadwal->deskripsi);
            }
        }

        foreach ($wargaUsers as $userId) {
            $notifData[] = $this->payloadNotifikasi(
                (int) $userId,
                $judulWarga,
                $pesanWarga,
                'jadwal',
                $now
            );
        }

        $judulKader = $mode === 'cancelled'
            ? 'Instruksi: Jadwal Dibatalkan'
            : 'Instruksi: Persiapan ' . $jadwal->judul;

        $pesanKader = $mode === 'cancelled'
            ? "Agenda {$jadwal->judul} pada {$tanggalFormat} di {$jadwal->lokasi} telah dibatalkan atau ditunda. Mohon informasikan kepada warga sasaran bila diperlukan."
            : "Bidan menetapkan agenda {$jadwal->judul} untuk {$targetTeks} pada {$tanggalFormat} di {$jadwal->lokasi}. Mohon Kader menyiapkan data sasaran, lokasi, dan kebutuhan pelayanan.";

        $kaderUsers = $this->ambilUserKaderAktif();

        foreach ($kaderUsers as $kaderId) {
            $notifData[] = $this->payloadNotifikasi(
                (int) $kaderId,
                $judulKader,
                $pesanKader,
                'jadwal',
                $now
            );
        }

        if (empty($notifData)) {
            return;
        }

        foreach (array_chunk($notifData, 500) as $chunk) {
            Notifikasi::insert($chunk);
        }
    }

    private function ambilUserWargaSesuaiTarget(string $target): Collection
    {
        if ($target === 'semua') {
            return User::query()
                ->where('role', 'user')
                ->whereIn('status', ['active', 'aktif'])
                ->pluck('id')
                ->unique()
                ->values();
        }

        $modelClass = match ($target) {
            'balita' => Balita::class,
            'remaja' => Remaja::class,
            'lansia' => Lansia::class,
            default => null,
        };

        if (!$modelClass) {
            return collect();
        }

        $directUserIds = collect();

        if (Schema::hasColumn((new $modelClass())->getTable(), 'user_id')) {
            $directUserIds = $modelClass::query()
                ->whereNotNull('user_id')
                ->pluck('user_id');
        }

        $userIdsByNik = collect();

        if (Schema::hasColumn((new $modelClass())->getTable(), 'nik')) {
            $nikSasaran = $modelClass::query()
                ->whereNotNull('nik')
                ->pluck('nik');

            if ($nikSasaran->isNotEmpty() && Schema::hasColumn('users', 'nik')) {
                $userIdsByNik = User::query()
                    ->where('role', 'user')
                    ->whereIn('status', ['active', 'aktif'])
                    ->whereIn('nik', $nikSasaran)
                    ->pluck('id');
            }
        }

        $parentUserIds = collect();

        if ($target === 'balita') {
            $balitaTable = (new Balita())->getTable();

            if (
                Schema::hasColumn($balitaTable, 'nik_ibu') ||
                Schema::hasColumn($balitaTable, 'nik_ayah')
            ) {
                $columns = [];

                if (Schema::hasColumn($balitaTable, 'nik_ibu')) {
                    $columns[] = 'nik_ibu';
                }

                if (Schema::hasColumn($balitaTable, 'nik_ayah')) {
                    $columns[] = 'nik_ayah';
                }

                $nikOrtu = Balita::query()
                    ->select($columns)
                    ->get()
                    ->flatMap(function ($item) use ($columns) {
                        return collect($columns)->map(fn ($column) => $item->{$column} ?? null);
                    })
                    ->filter()
                    ->unique()
                    ->values();

                if ($nikOrtu->isNotEmpty() && Schema::hasColumn('users', 'nik')) {
                    $parentUserIds = User::query()
                        ->where('role', 'user')
                        ->whereIn('status', ['active', 'aktif'])
                        ->whereIn('nik', $nikOrtu)
                        ->pluck('id');
                }
            }
        }

        return collect()
            ->merge($directUserIds)
            ->merge($userIdsByNik)
            ->merge($parentUserIds)
            ->filter()
            ->unique()
            ->values();
    }

    private function ambilUserKaderAktif(): Collection
    {
        return User::query()
            ->where('role', 'kader')
            ->whereIn('status', ['active', 'aktif'])
            ->pluck('id')
            ->unique()
            ->values();
    }

    private function payloadNotifikasi(int $userId, string $judul, string $pesan, string $tipe, $now): array
    {
        static $hasCreatedBy = null;

        if ($hasCreatedBy === null) {
            $hasCreatedBy = Schema::hasColumn('notifikasis', 'created_by');
        }

        $payload = [
            'user_id' => $userId,
            'judul' => $judul,
            'pesan' => $pesan,
            'tipe' => $tipe,
            'is_read' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if ($hasCreatedBy) {
            $payload['created_by'] = Auth::id();
        }

        return $payload;
    }

    private function labelKategori(?string $kategori): string
    {
        return match ($kategori) {
            'imunisasi' => 'Imunisasi',
            'pemeriksaan' => 'Pemeriksaan Kesehatan',
            'posyandu' => 'Posyandu',
            'lainnya' => 'Kegiatan Posyandu',
            default => 'Jadwal Posyandu',
        };
    }

    private function labelTarget(?string $target): string
    {
        return match ($target) {
            'balita' => 'Balita',
            'remaja' => 'Remaja',
            'lansia' => 'Lansia',
            default => 'Semua Warga',
        };
    }

    private function formatJam($value): string
    {
        if (!$value) {
            return '-';
        }

        try {
            return Carbon::parse($value)->format('H:i') . ' WIB';
        } catch (\Throwable) {
            return (string) $value;
        }
    }
}