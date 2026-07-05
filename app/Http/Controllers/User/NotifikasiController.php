<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    /**
     * Halaman utama notifikasi (Pesan Bidan)
     */
    public function index(Request $request): View
    {
        try {
            $filters = [
                'filter' => $this->normalizeFilter($request->input('filter', 'semua')),
                'search' => trim((string) $request->input('search', '')),
            ];

            if (! $this->tableReady()) {
                return view('user.notifikasi.index', $this->emptyViewData($request, $filters));
            }

            $query = $this->baseQuery();

            $this->applyReadFilter($query, $filters['filter']);
            $this->applySearchFilter($query, $filters['search']);

            $notifikasis = $query
                ->latest()
                ->paginate(5)
                ->withQueryString();

            $notifikasiCards = collect($notifikasis->items())
                ->map(fn (Notifikasi $item) => $this->buildCard($item))
                ->values();

            $counts = $this->buildCounts();

            return view('user.notifikasi.index', [
                'filters' => $filters,
                'filter' => $filters['filter'],

                'counts' => $counts,
                'allCount' => $counts['semua'],
                'unreadCount' => $counts['belum'],
                'readCount' => $counts['sudah'],

                'notifikasis' => $notifikasis,
                'notifikasiCards' => $notifikasiCards,
            ]);
        } catch (\Throwable $e) {
            Log::error('User NotifikasiController@index error', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return view('user.notifikasi.index', $this->emptyViewData($request, [
                'filter' => 'semua',
                'search' => '',
            ]))->with('error', 'Gagal memuat pesan Bidan.');
        }
    }

    /**
     * AJAX: ambil 5 notifikasi terbaru untuk dropdown (lonceng)
     */
    public function fetchRecent(): JsonResponse
    {
        try {
            if (! $this->tableReady()) {
                return response()->json([
                    'unreadCount' => 0,
                    'items' => [],
                    'latest_title' => 'Tidak ada pesan',
                    'latest_body' => 'Belum ada pesan baru.',
                ]);
            }

            $unreadCount = $this->unreadQuery()->count();

            $recentItems = $this->baseQuery()
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Notifikasi $item) => $this->buildDropdownItem($item))
                ->values();

            $latest = $recentItems->first();

            return response()->json([
                'unreadCount' => $unreadCount,
                'items' => $recentItems,
                'latest_title' => $latest['judul'] ?? 'Tidak ada pesan',
                'latest_body' => $latest['pesan'] ?? 'Belum ada pesan baru.',
            ]);
        } catch (\Throwable $e) {
            Log::warning('User NotifikasiController@fetchRecent error', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'unreadCount' => 0,
                'items' => [],
                'latest_title' => 'Gagal memuat pesan',
                'latest_body' => 'Silakan muat ulang halaman.',
            ]);
        }
    }

    /**
     * Tandai satu notifikasi sebagai dibaca (dari tombol di halaman index)
     */
    public function markRead(Request $request, int $id): RedirectResponse
    {
        try {
            if ($this->tableReady()) {
                $this->markQuery()
                    ->whereKey($id)
                    ->update($this->readPayload());
            }

            return back()->with('success', 'Pesan telah ditandai dibaca.');
        } catch (\Throwable $e) {
            Log::warning('User NotifikasiController@markRead error', [
                'message' => $e->getMessage(),
                'notifikasi_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Gagal menandai pesan.');
        }
    }

    /**
     * Tandai semua notifikasi sebagai dibaca
     */
    public function markAllRead(): RedirectResponse
    {
        try {
            if ($this->tableReady()) {
                $this->unreadQuery()->update($this->readPayload());
            }

            return back()->with('success', 'Semua pesan telah ditandai dibaca.');
        } catch (\Throwable $e) {
            Log::warning('User NotifikasiController@markAllRead error', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Gagal menandai semua pesan.');
        }
    }

    // ========== QUERY HELPERS ==========

    private function baseQuery()
    {
        return Notifikasi::query()
            ->where('user_id', auth()->id());
    }

    private function unreadQuery()
    {
        $query = $this->baseQuery();

        if (Schema::hasColumn($this->tableName(), 'is_read')) {
            return $query->where('is_read', false);
        }

        if (Schema::hasColumn($this->tableName(), 'read_at')) {
            return $query->whereNull('read_at');
        }

        return $query->whereRaw('1 = 0');
    }

    private function readQuery()
    {
        $query = $this->baseQuery();

        if (Schema::hasColumn($this->tableName(), 'is_read')) {
            return $query->where('is_read', true);
        }

        if (Schema::hasColumn($this->tableName(), 'read_at')) {
            return $query->whereNotNull('read_at');
        }

        return $query->whereRaw('1 = 0');
    }

    private function markQuery()
    {
        return Notifikasi::query()
            ->where('user_id', auth()->id());
    }

    // ========== FILTER HELPERS ==========

    private function applyReadFilter($query, string $filter): void
    {
        if ($filter === 'belum') {
            if (Schema::hasColumn($this->tableName(), 'is_read')) {
                $query->where('is_read', false);
                return;
            }

            if (Schema::hasColumn($this->tableName(), 'read_at')) {
                $query->whereNull('read_at');
                return;
            }
        }

        if ($filter === 'sudah') {
            if (Schema::hasColumn($this->tableName(), 'is_read')) {
                $query->where('is_read', true);
                return;
            }

            if (Schema::hasColumn($this->tableName(), 'read_at')) {
                $query->whereNotNull('read_at');
            }
        }
    }

    private function applySearchFilter($query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $table = $this->tableName();

        $columns = collect([
            'judul',
            'pesan',
            'tipe',
        ])->filter(fn ($column) => Schema::hasColumn($table, $column));

        if ($columns->isEmpty()) {
            return;
        }

        $query->where(function ($q) use ($columns, $search) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', '%' . $search . '%');
            }
        });
    }

    // ========== BUILDERS ==========

    private function buildCounts(): array
    {
        return [
            'semua' => $this->baseQuery()->count(),
            'belum' => $this->unreadQuery()->count(),
            'sudah' => $this->readQuery()->count(),
        ];
    }

    /**
     * Build card untuk halaman index
     */
    private function buildCard(Notifikasi $item): array
    {
        $isRead = $this->isRead($item);
        $tipe = $item->tipe ?: $this->guessType($item);

        return [
            'id' => $item->id,
            'judul' => $item->judul ?: 'Informasi Posyandu',
            'pesan' => $item->pesan ?: 'Tidak ada isi pesan.',
            'pesan_ringkas' => Str::limit((string) ($item->pesan ?: 'Tidak ada isi pesan.'), 120),
            'tipe' => $tipe,
            'label' => $this->typeLabel($tipe),
            'icon' => $this->typeIcon($tipe),
            'tone' => $this->typeTone($tipe),
            'link' => $item->link ?: route('user.notifikasi.index'),
            'is_read' => $isRead,
            'tanggal' => $item->created_at
                ? $item->created_at->translatedFormat('d F Y, H:i')
                : '-',
            'tanggal_short' => $item->created_at
                ? $item->created_at->translatedFormat('d M Y')
                : '-',
            'waktu' => $item->created_at
                ? $item->created_at->diffForHumans()
                : '-',
        ];
    }

    /**
     * Build item untuk dropdown (lonceng) – khusus untuk fetchRecent
     */
    private function buildDropdownItem(Notifikasi $item): array
    {
        $isRead = $this->isRead($item);
        $tipe = $item->tipe ?: $this->guessType($item);

        return [
            'id' => $item->id,
            'judul' => $item->judul ?: 'Informasi Posyandu',
            'pesan' => Str::limit((string) ($item->pesan ?: 'Tidak ada isi pesan.'), 80),
            'tipe' => $tipe,
            'icon' => $this->typeIcon($tipe),
            'link' => $item->link ?: route('user.notifikasi.index'),
            'is_read' => $isRead,
            'waktu' => $item->created_at
                ? $item->created_at->diffForHumans()
                : '-',
        ];
    }

    private function isRead(Notifikasi $item): bool
    {
        if (Schema::hasColumn($this->tableName(), 'is_read')) {
            return (bool) $item->is_read;
        }

        if (Schema::hasColumn($this->tableName(), 'read_at')) {
            return filled($item->read_at);
        }

        return false;
    }

    private function readPayload(): array
    {
        $payload = [];

        if (Schema::hasColumn($this->tableName(), 'is_read')) {
            $payload['is_read'] = true;
        }

        if (Schema::hasColumn($this->tableName(), 'read_at')) {
            $payload['read_at'] = now();
        }

        return $payload;
    }

    // ========== UTILITY ==========

    private function normalizeFilter(?string $value): string
    {
        return in_array($value, ['semua', 'belum', 'sudah'], true)
            ? $value
            : 'semua';
    }

    private function guessType(Notifikasi $item): string
    {
        $text = Str::lower(($item->judul ?? '') . ' ' . ($item->pesan ?? ''));

        return match (true) {
            str_contains($text, 'jadwal') || str_contains($text, 'agenda') => 'jadwal',
            str_contains($text, 'imunisasi') || str_contains($text, 'vaksin') => 'imunisasi',
            str_contains($text, 'pemeriksaan') || str_contains($text, 'rekam medis') => 'pemeriksaan',
            default => 'info',
        };
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'jadwal' => 'Jadwal Posyandu',
            'imunisasi' => 'Info Imunisasi',
            'pemeriksaan' => 'Data Pemeriksaan',
            'import' => 'Status Data',
            default => 'Informasi',
        };
    }

    private function typeIcon(string $type): string
    {
        return match ($type) {
            'jadwal' => 'fa-calendar-check',
            'imunisasi' => 'fa-syringe',
            'pemeriksaan' => 'fa-stethoscope',
            'import' => 'fa-file-excel',
            default => 'fa-bell',
        };
    }

    private function typeTone(string $type): string
    {
        return match ($type) {
            'jadwal' => 'emerald',
            'imunisasi' => 'sky',
            'pemeriksaan' => 'amber',
            'import' => 'violet',
            default => 'slate',
        };
    }

    // ========== EMPTY STATE ==========

    private function emptyViewData(Request $request, array $filters): array
    {
        return [
            'filters' => $filters,
            'filter' => $filters['filter'] ?? 'semua',

            'counts' => [
                'semua' => 0,
                'belum' => 0,
                'sudah' => 0,
            ],
            'allCount' => 0,
            'unreadCount' => 0,
            'readCount' => 0,

            'notifikasis' => $this->emptyPaginator($request),
            'notifikasiCards' => collect(),
        ];
    }

    private function emptyPaginator(Request $request): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            [],
            0,
            10,
            LengthAwarePaginator::resolveCurrentPage(),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    // ========== TABLE CHECK ==========

    private function tableReady(): bool
    {
        return class_exists(Notifikasi::class)
            && Schema::hasTable($this->tableName())
            && Schema::hasColumn($this->tableName(), 'user_id');
    }

    private function tableName(): string
    {
        return (new Notifikasi())->getTable();
    }
    public function show($id)
{
    // 1. Ambil data notifikasi berdasarkan ID dan pastikan itu milik user yang sedang login
    $notifikasi = \App\Models\Notifikasi::where('id', $id)
                    ->where('user_id', auth()->user()->id)
                    ->firstOrFail();

    // 2. Ubah status menjadi 'sudah dibaca' jika masih baru
    if (!$notifikasi->is_read) {
        $notifikasi->update(['is_read' => true]);
    }

    // 3. TAMPILKAN VIEW (JANGAN PAKAI REDIRECT)
    return view('user.notifikasi.show', compact('notifikasi'));
}
}