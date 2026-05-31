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
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class JadwalController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $this->syncExpiredSchedules();

            $search = trim((string) $request->get('search', ''));
            $status = $this->normalizeStatus($request->get('status', 'semua'));
            $kategori = $this->normalizeKategori($request->get('kategori', 'semua'));
            $target = $this->normalizeTarget($request->get('target', 'semua'));

            $query = JadwalPosyandu::query();

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('judul', 'like', "%{$search}%")
                        ->orWhere('lokasi', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            }

            if ($status !== 'semua') {
                $query->where('status', $status);
            }

            if ($kategori !== 'semua') {
                $query->where('kategori', $kategori);
            }

            if ($target !== 'semua') {
                $query->where('target_peserta', $target);
            }

            $jadwals = $query
                ->orderByRaw("CASE WHEN tanggal >= CURDATE() THEN 0 ELSE 1 END")
                ->orderBy('tanggal')
                ->orderBy('waktu_mulai')
                ->paginate(10)
                ->withQueryString();

            $stats = [
                'total' => JadwalPosyandu::count(),
                'aktif' => JadwalPosyandu::where('status', 'aktif')->count(),
                'bulan_ini' => JadwalPosyandu::whereMonth('tanggal', now()->month)
                    ->whereYear('tanggal', now()->year)
                    ->count(),
                'mendatang' => JadwalPosyandu::whereDate('tanggal', '>=', now()->toDateString())
                    ->where('status', 'aktif')
                    ->count(),
            ];

            $kategoriOptions = $this->kategoriOptions();
            $targetOptions = $this->targetOptions();
            $statusOptions = $this->statusOptions();

            return view('bidan.jadwal.index', compact(
                'jadwals',
                'search',
                'status',
                'kategori',
                'target',
                'stats',
                'kategoriOptions',
                'targetOptions',
                'statusOptions'
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

    public function create(): View|RedirectResponse
    {
        try {
            $mode = 'create';
            $jadwal = null;

            $kategoriOptions = $this->kategoriOptions();
            $targetOptions = $this->targetOptions();
            $statusOptions = $this->statusOptions();

            return view('bidan.jadwal.create', compact(
                'mode',
                'jadwal',
                'kategoriOptions',
                'targetOptions',
                'statusOptions'
            ));
        } catch (\Throwable $e) {
            Log::error('BIDAN_JADWAL_CREATE_ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return redirect()
                ->route('bidan.jadwal.index')
                ->with('error', 'Gagal membuka form jadwal.');
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateJadwal($request);

        if (($validated['kategori'] ?? null) === 'imunisasi') {
            $validated['target_peserta'] = 'balita';
        }

        if (!$this->isFutureStartTime($validated['tanggal'], $validated['waktu_mulai'])) {
            return back()
                ->withInput()
                ->with('error', 'Jadwal baru harus dibuat sebelum waktu pelaksanaan dimulai.');
        }

        DB::beginTransaction();

        try {
            $payload = [
                'judul' => $validated['judul'],
                'deskripsi' => $this->cleanValue($validated['deskripsi'] ?? null, null),
                'tanggal' => $validated['tanggal'],
                'waktu_mulai' => $validated['waktu_mulai'],
                'waktu_selesai' => $validated['waktu_selesai'],
                'lokasi' => $validated['lokasi'],
                'kategori' => $validated['kategori'],
                'target_peserta' => $validated['target_peserta'],
                'status' => 'aktif',
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];

            $payload = $this->onlyExistingColumns($this->jadwalTable(), $payload);

            $jadwal = new JadwalPosyandu();
            $jadwal->forceFill($payload)->save();

            $this->broadcastJadwalBaru($jadwal);

            DB::commit();

            return redirect()
                ->route('bidan.jadwal.index')
                ->with('success', 'Jadwal Posyandu berhasil dibuat dan notifikasi telah dikirim.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('BIDAN_JADWAL_STORE_ERROR', [
                'message' => $e->getMessage(),
                'payload' => $validated,
                'user_id' => Auth::id(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan jadwal: ' . $e->getMessage());
        }
    }

    public function show(JadwalPosyandu $jadwal): View|RedirectResponse
    {
        try {
            $this->syncSingleScheduleStatus($jadwal);
            $jadwal->refresh();

            $kategoriOptions = $this->kategoriOptions();
            $targetOptions = $this->targetOptions();
            $statusOptions = $this->statusOptions();
            $canEdit = $this->canEditJadwal($jadwal);
            $canDelete = $this->canDeleteJadwal($jadwal);

            return view('bidan.jadwal.show', compact(
                'jadwal',
                'kategoriOptions',
                'targetOptions',
                'statusOptions',
                'canEdit',
                'canDelete'
            ));
        } catch (\Throwable $e) {
            Log::error('BIDAN_JADWAL_SHOW_ERROR', [
                'message' => $e->getMessage(),
                'jadwal_id' => $jadwal->id ?? null,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return redirect()
                ->route('bidan.jadwal.index')
                ->with('error', 'Detail jadwal tidak ditemukan.');
        }
    }

    public function edit(JadwalPosyandu $jadwal): View|RedirectResponse
    {
        try {
            $this->syncSingleScheduleStatus($jadwal);
            $jadwal->refresh();

            if (!$this->canEditJadwal($jadwal)) {
                return redirect()
                    ->route('bidan.jadwal.show', $jadwal->id)
                    ->with('error', 'Jadwal yang sudah selesai, dibatalkan, atau sudah melewati waktu pelaksanaan tidak dapat diedit.');
            }

            $mode = 'edit';

            $kategoriOptions = $this->kategoriOptions();
            $targetOptions = $this->targetOptions();
            $statusOptions = $this->statusOptions();
            $canEdit = true;

            return view('bidan.jadwal.edit', compact(
                'jadwal',
                'mode',
                'kategoriOptions',
                'targetOptions',
                'statusOptions',
                'canEdit'
            ));
        } catch (\Throwable $e) {
            Log::error('BIDAN_JADWAL_EDIT_ERROR', [
                'message' => $e->getMessage(),
                'jadwal_id' => $jadwal->id ?? null,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return redirect()
                ->route('bidan.jadwal.index')
                ->with('error', 'Data jadwal tidak ditemukan.');
        }
    }

    public function update(Request $request, JadwalPosyandu $jadwal): RedirectResponse
    {
        $this->syncSingleScheduleStatus($jadwal);
        $jadwal->refresh();

        if (!$this->canEditJadwal($jadwal)) {
            return redirect()
                ->route('bidan.jadwal.show', $jadwal->id)
                ->with('error', 'Jadwal yang sudah selesai, dibatalkan, atau sudah melewati waktu pelaksanaan tidak dapat diperbarui.');
        }

        $validated = $this->validateJadwal($request, true);

        if (($validated['kategori'] ?? null) === 'imunisasi') {
            $validated['target_peserta'] = 'balita';
        }

        if (($validated['status'] ?? 'aktif') === 'aktif' && !$this->isFutureStartTime($validated['tanggal'], $validated['waktu_mulai'])) {
            return back()
                ->withInput()
                ->with('error', 'Jadwal aktif harus memiliki waktu mulai yang belum terlewati.');
        }

        DB::beginTransaction();

        try {
            $oldStatus = $jadwal->status;
            $oldTarget = $jadwal->target_peserta;

            $payload = [
                'judul' => $validated['judul'],
                'deskripsi' => $this->cleanValue($validated['deskripsi'] ?? null, null),
                'tanggal' => $validated['tanggal'],
                'waktu_mulai' => $validated['waktu_mulai'],
                'waktu_selesai' => $validated['waktu_selesai'],
                'lokasi' => $validated['lokasi'],
                'kategori' => $validated['kategori'],
                'target_peserta' => $validated['target_peserta'],
                'status' => $validated['status'],
                'updated_by' => Auth::id(),
            ];

            $payload = $this->onlyExistingColumns($this->jadwalTable(), $payload);

            $jadwal->forceFill($payload)->save();

            $importantChanged = $jadwal->wasChanged([
                'judul',
                'tanggal',
                'waktu_mulai',
                'waktu_selesai',
                'lokasi',
                'kategori',
                'target_peserta',
                'status',
            ]);

            if ($jadwal->status === 'dibatalkan' && $oldStatus !== 'dibatalkan') {
                $this->broadcastJadwalDibatalkan($jadwal, $oldTarget);
            } elseif ($importantChanged && $jadwal->status === 'aktif') {
                $this->broadcastJadwalDiubah($jadwal);
            }

            DB::commit();

            return redirect()
                ->route('bidan.jadwal.index')
                ->with('success', 'Jadwal Posyandu berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('BIDAN_JADWAL_UPDATE_ERROR', [
                'message' => $e->getMessage(),
                'jadwal_id' => $jadwal->id ?? null,
                'payload' => $validated,
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
            $this->syncSingleScheduleStatus($jadwal);
            $jadwal->refresh();

            if (!$this->canDeleteJadwal($jadwal)) {
                return redirect()
                    ->route('bidan.jadwal.show', $jadwal->id)
                    ->with('error', 'Jadwal yang sudah selesai, dibatalkan, atau sudah melewati waktu pelaksanaan tidak dapat dihapus.');
            }

            $jadwal->delete();

            return redirect()
                ->route('bidan.jadwal.index')
                ->with('success', 'Jadwal berhasil dihapus dari sistem.');
        } catch (\Throwable $e) {
            Log::error('BIDAN_JADWAL_DESTROY_ERROR', [
                'message' => $e->getMessage(),
                'jadwal_id' => $jadwal->id ?? null,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()->with('error', 'Jadwal gagal dihapus.');
        }
    }

    private function validateJadwal(Request $request, bool $isUpdate = false): array
    {
        $kategoriKeys = implode(',', array_keys($this->kategoriOptions()));
        $targetKeys = implode(',', array_keys($this->targetOptions()));
        $statusKeys = implode(',', array_keys($this->statusOptions()));

        $rules = [
            'judul' => ['required', 'string', 'max:191'],
            'tanggal' => ['required', 'date'],
            'waktu_mulai' => ['required', 'date_format:H:i'],
            'waktu_selesai' => ['required', 'date_format:H:i', 'after:waktu_mulai'],
            'lokasi' => ['required', 'string', 'max:191'],
            'kategori' => ['required', "in:{$kategoriKeys}"],
            'target_peserta' => ['required', "in:{$targetKeys}"],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ];

        if ($isUpdate) {
            $rules['status'] = ['required', "in:{$statusKeys}"];
        }

        return $request->validate($rules, [
            'judul.required' => 'Judul kegiatan wajib diisi.',
            'tanggal.required' => 'Tanggal pelaksanaan wajib diisi.',
            'waktu_mulai.required' => 'Jam mulai wajib diisi.',
            'waktu_mulai.date_format' => 'Format jam mulai tidak valid.',
            'waktu_selesai.required' => 'Jam selesai wajib diisi.',
            'waktu_selesai.date_format' => 'Format jam selesai tidak valid.',
            'waktu_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'lokasi.required' => 'Lokasi kegiatan wajib diisi.',
            'kategori.required' => 'Kategori layanan wajib dipilih.',
            'kategori.in' => 'Kategori layanan tidak valid.',
            'target_peserta.required' => 'Target peserta wajib dipilih.',
            'target_peserta.in' => 'Target peserta tidak valid.',
            'status.required' => 'Status jadwal wajib dipilih.',
            'status.in' => 'Status jadwal tidak valid.',
        ]);
    }

    private function kategoriOptions(): array
    {
        return [
            'posyandu' => [
                'label' => 'Posyandu Rutin',
                'desc' => 'Agenda pelayanan Posyandu umum, absensi, dan pengukuran dasar.',
                'icon' => 'ph-house-line',
            ],
            'imunisasi' => [
                'label' => 'Imunisasi Balita',
                'desc' => 'Agenda pelayanan imunisasi untuk sasaran Balita.',
                'icon' => 'ph-syringe',
            ],
            'pemeriksaan' => [
                'label' => 'Pemeriksaan Klinis',
                'desc' => 'Agenda pemeriksaan lanjutan oleh Bidan.',
                'icon' => 'ph-stethoscope',
            ],
            'lainnya' => [
                'label' => 'Kegiatan Lainnya',
                'desc' => 'Agenda tambahan Posyandu di luar layanan utama.',
                'icon' => 'ph-calendar-plus',
            ],
        ];
    }

    private function targetOptions(): array
    {
        return [
            'semua' => [
                'label' => 'Semua Sasaran',
                'desc' => 'Balita, Remaja, Lansia, dan warga yang terdaftar.',
                'icon' => 'ph-users-three',
            ],
            'balita' => [
                'label' => 'Balita',
                'desc' => 'Sasaran Balita.',
                'icon' => 'ph-baby',
            ],
            'remaja' => [
                'label' => 'Remaja',
                'desc' => 'Sasaran Remaja.',
                'icon' => 'ph-user-focus',
            ],
            'lansia' => [
                'label' => 'Lansia',
                'desc' => 'Sasaran Lansia.',
                'icon' => 'ph-heartbeat',
            ],
        ];
    }

    private function statusOptions(): array
    {
        return [
            'aktif' => [
                'label' => 'Aktif',
                'desc' => 'Jadwal masih berlaku.',
                'icon' => 'ph-check-circle',
            ],
            'selesai' => [
                'label' => 'Selesai',
                'desc' => 'Jadwal sudah dilaksanakan.',
                'icon' => 'ph-flag-checkered',
            ],
            'dibatalkan' => [
                'label' => 'Dibatalkan',
                'desc' => 'Jadwal dibatalkan atau ditunda.',
                'icon' => 'ph-x-circle',
            ],
        ];
    }

    private function normalizeKategori(?string $kategori): string
    {
        $kategori = strtolower(trim((string) $kategori));

        if ($kategori === 'semua') {
            return 'semua';
        }

        return array_key_exists($kategori, $this->kategoriOptions())
            ? $kategori
            : 'semua';
    }

    private function normalizeTarget(?string $target): string
    {
        $target = strtolower(trim((string) $target));

        if ($target === 'semua') {
            return 'semua';
        }

        return array_key_exists($target, $this->targetOptions())
            ? $target
            : 'semua';
    }

    private function normalizeStatus(?string $status): string
    {
        $status = strtolower(trim((string) $status));

        if ($status === 'semua') {
            return 'semua';
        }

        return array_key_exists($status, $this->statusOptions())
            ? $status
            : 'semua';
    }

    private function canEditJadwal(JadwalPosyandu $jadwal): bool
    {
        if (($jadwal->status ?? 'aktif') !== 'aktif') {
            return false;
        }

        $start = $this->jadwalStartDateTime($jadwal);

        if (!$start) {
            return false;
        }

        return now()->lt($start);
    }

    private function canDeleteJadwal(JadwalPosyandu $jadwal): bool
    {
        return $this->canEditJadwal($jadwal);
    }

    private function isFutureStartTime(string $tanggal, string $waktuMulai): bool
    {
        try {
            $start = Carbon::parse($tanggal . ' ' . $waktuMulai);

            return now()->lt($start);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function jadwalStartDateTime(JadwalPosyandu $jadwal): ?Carbon
    {
        if (empty($jadwal->tanggal)) {
            return null;
        }

        try {
            $tanggal = Carbon::parse($jadwal->tanggal)->format('Y-m-d');
            $waktu = $jadwal->waktu_mulai
                ? Carbon::parse($jadwal->waktu_mulai)->format('H:i:s')
                : '00:00:00';

            return Carbon::parse($tanggal . ' ' . $waktu);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function jadwalEndDateTime(JadwalPosyandu $jadwal): ?Carbon
    {
        if (empty($jadwal->tanggal)) {
            return null;
        }

        try {
            $tanggal = Carbon::parse($jadwal->tanggal)->format('Y-m-d');
            $waktu = $jadwal->waktu_selesai
                ? Carbon::parse($jadwal->waktu_selesai)->format('H:i:s')
                : (
                    $jadwal->waktu_mulai
                        ? Carbon::parse($jadwal->waktu_mulai)->format('H:i:s')
                        : '23:59:59'
                );

            return Carbon::parse($tanggal . ' ' . $waktu);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function syncSingleScheduleStatus(JadwalPosyandu $jadwal): void
    {
        if (($jadwal->status ?? null) !== 'aktif') {
            return;
        }

        $end = $this->jadwalEndDateTime($jadwal);

        if (!$end) {
            return;
        }

        if (now()->gt($end)) {
            $payload = [
                'status' => 'selesai',
                'updated_by' => Auth::id(),
            ];

            $payload = $this->onlyExistingColumns($this->jadwalTable(), $payload);

            $jadwal->forceFill($payload)->save();
        }
    }

    private function syncExpiredSchedules(): void
    {
        try {
            JadwalPosyandu::query()
                ->where('status', 'aktif')
                ->where(function ($query) {
                    $query->whereDate('tanggal', '<', now()->toDateString())
                        ->orWhere(function ($q) {
                            $q->whereDate('tanggal', now()->toDateString())
                                ->whereTime('waktu_selesai', '<', now()->format('H:i:s'));
                        });
                })
                ->update(
                    $this->onlyExistingColumns($this->jadwalTable(), [
                        'status' => 'selesai',
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ])
                );
        } catch (\Throwable $e) {
            Log::warning('BIDAN_JADWAL_SYNC_EXPIRED_WARNING', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function broadcastJadwalBaru(JadwalPosyandu $jadwal): void
    {
        $tanggal = $this->formatTanggal($jadwal->tanggal);
        $waktu = $this->formatWaktu($jadwal->waktu_mulai, $jadwal->waktu_selesai);
        $kategori = $this->kategoriOptions()[$jadwal->kategori]['label'] ?? ucfirst((string) $jadwal->kategori);
        $target = $this->targetOptions()[$jadwal->target_peserta]['label'] ?? ucfirst((string) $jadwal->target_peserta);

        $wargaIds = $this->wargaIdsByTarget($jadwal->target_peserta);
        $kaderIds = $this->userIdsByRole('kader');

        $rows = [];

        foreach ($wargaIds as $userId) {
            $rows[] = $this->notificationPayload(
                userId: $userId,
                judul: "Jadwal {$kategori}",
                pesan: "Agenda {$jadwal->judul} untuk {$target} akan dilaksanakan pada {$tanggal}, pukul {$waktu}, di {$jadwal->lokasi}. {$this->cleanValue($jadwal->deskripsi, '')}",
                tipe: 'jadwal'
            );
        }

        foreach ($kaderIds as $userId) {
            $rows[] = $this->notificationPayload(
                userId: $userId,
                judul: 'Instruksi Persiapan Jadwal',
                pesan: "Bidan menetapkan agenda {$jadwal->judul} pada {$tanggal}, pukul {$waktu}, di {$jadwal->lokasi}. Mohon Kader menyiapkan layanan sesuai target {$target}.",
                tipe: 'jadwal'
            );
        }

        $this->insertNotifications($rows);
    }

    private function broadcastJadwalDiubah(JadwalPosyandu $jadwal): void
    {
        $tanggal = $this->formatTanggal($jadwal->tanggal);
        $waktu = $this->formatWaktu($jadwal->waktu_mulai, $jadwal->waktu_selesai);
        $target = $this->targetOptions()[$jadwal->target_peserta]['label'] ?? ucfirst((string) $jadwal->target_peserta);

        $userIds = $this->wargaIdsByTarget($jadwal->target_peserta)
            ->merge($this->userIdsByRole('kader'))
            ->unique()
            ->values();

        $rows = [];

        foreach ($userIds as $userId) {
            $rows[] = $this->notificationPayload(
                userId: $userId,
                judul: 'Perubahan Jadwal Posyandu',
                pesan: "Jadwal {$jadwal->judul} untuk {$target} diperbarui. Pelaksanaan terbaru: {$tanggal}, pukul {$waktu}, di {$jadwal->lokasi}.",
                tipe: 'jadwal'
            );
        }

        $this->insertNotifications($rows);
    }

    private function broadcastJadwalDibatalkan(JadwalPosyandu $jadwal, ?string $oldTarget = null): void
    {
        $tanggal = $this->formatTanggal($jadwal->tanggal);
        $target = $oldTarget ?: $jadwal->target_peserta;

        $userIds = $this->wargaIdsByTarget($target)
            ->merge($this->userIdsByRole('kader'))
            ->unique()
            ->values();

        $rows = [];

        foreach ($userIds as $userId) {
            $rows[] = $this->notificationPayload(
                userId: $userId,
                judul: 'Jadwal Posyandu Dibatalkan',
                pesan: "Agenda {$jadwal->judul} pada {$tanggal} di {$jadwal->lokasi} dibatalkan atau ditunda. Silakan menunggu informasi lanjutan dari Bidan atau Kader.",
                tipe: 'jadwal'
            );
        }

        $this->insertNotifications($rows);
    }

    private function wargaIdsByTarget(?string $target): Collection
    {
        $target = $this->normalizeTarget($target);

        if ($target === 'semua') {
            return $this->userIdsByRole('user');
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

        return $this->linkedUserIdsForModel($modelClass);
    }

    private function linkedUserIdsForModel(string $modelClass): Collection
    {
        $model = new $modelClass();
        $table = $model->getTable();

        $userIds = collect();

        if ($this->hasColumn($table, 'user_id')) {
            $userIds = $userIds->merge(
                $modelClass::query()
                    ->whereNotNull('user_id')
                    ->pluck('user_id')
            );
        }

        $nikColumns = [
            'nik',
            'nik_ibu',
            'nik_ayah',
            'nik_orang_tua',
            'nik_orangtua',
            'nik_wali',
        ];

        $nikValues = collect();

        foreach ($nikColumns as $column) {
            if ($this->hasColumn($table, $column)) {
                $nikValues = $nikValues->merge(
                    $modelClass::query()
                        ->whereNotNull($column)
                        ->pluck($column)
                );
            }
        }

        $nikValues = $nikValues
            ->filter()
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($nikValues->isNotEmpty() && $this->hasColumn('users', 'nik')) {
            $query = User::query()->whereIn('nik', $nikValues);

            if ($this->hasColumn('users', 'role')) {
                $query->where('role', 'user');
            }

            if ($this->hasColumn('users', 'status')) {
                $query->whereIn('status', ['active', 'aktif', 'Aktif', '1', 1]);
            }

            $userIds = $userIds->merge($query->pluck('id'));
        }

        return $userIds
            ->filter()
            ->unique()
            ->values();
    }

    private function userIdsByRole(string $role): Collection
    {
        $query = User::query();

        if ($this->hasColumn('users', 'role')) {
            $query->where('role', $role);
        }

        if ($this->hasColumn('users', 'status')) {
            $query->whereIn('status', ['active', 'aktif', 'Aktif', '1', 1]);
        }

        return $query
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();
    }

    private function notificationPayload(int|string $userId, string $judul, string $pesan, string $tipe = 'jadwal'): array
    {
        $now = now();

        return [
            'user_id' => $userId,
            'judul' => $judul,
            'pesan' => trim($pesan),
            'tipe' => $tipe,
            'is_read' => 0,
            'read_at' => null,
            'created_by' => Auth::id(),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function insertNotifications(array $rows): void
    {
        if (empty($rows) || !Schema::hasTable((new Notifikasi())->getTable())) {
            return;
        }

        $table = (new Notifikasi())->getTable();

        $rows = collect($rows)
            ->map(fn ($row) => $this->onlyExistingColumns($table, $row))
            ->filter(fn ($row) => !empty($row))
            ->values()
            ->all();

        if (empty($rows)) {
            return;
        }

        foreach (array_chunk($rows, 300) as $chunk) {
            Notifikasi::insert($chunk);
        }
    }

    private function formatTanggal($date): string
    {
        if (!$date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d F Y');
        } catch (\Throwable $e) {
            return '-';
        }
    }

    private function formatWaktu($mulai, $selesai): string
    {
        try {
            $mulai = $mulai ? Carbon::parse($mulai)->format('H:i') : '-';
            $selesai = $selesai ? Carbon::parse($selesai)->format('H:i') : '-';

            return "{$mulai} - {$selesai} WIB";
        } catch (\Throwable $e) {
            return '-';
        }
    }

    private function cleanValue($value, $fallback = '-')
    {
        if ($value === null) {
            return $fallback;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return $fallback;
        }

        return $value;
    }

    private function jadwalTable(): string
    {
        return (new JadwalPosyandu())->getTable();
    }

    private function onlyExistingColumns(string $table, array $payload): array
    {
        return collect($payload)
            ->filter(fn ($value, $column) => $this->hasColumn($table, $column))
            ->all();
    }

    private function hasColumn(string $table, string $column): bool
    {
        static $cache = [];

        $key = "{$table}.{$column}";

        if (!array_key_exists($key, $cache)) {
            $cache[$key] = Schema::hasColumn($table, $column);
        }

        return $cache[$key];
    }
}