<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\Concerns\ResolvesUserHealthContext;
use App\Models\Balita;
use App\Models\Imunisasi;
use App\Models\Pemeriksaan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class BalitaController extends Controller
{
    use ResolvesUserHealthContext;

    public function show(int $id): View|RedirectResponse
    {
        try {
            $context = $this->getUserContext(auth()->user());
            $balita = $this->findAuthorizedBalita($id, $context);

            if (! $balita) {
                return redirect()
                    ->route('user.monitoring.index')
                    ->with('error', 'Akses ditolak atau data balita tidak ditemukan.');
            }

            $balita->loadMissing($this->safeRelations($balita));

            $usia = $this->buildAgeData($balita);
            $riwayat = $this->getRiwayatPemeriksaan($balita);
            $latestPemeriksaan = $this->resolveLatestPemeriksaan($balita, $riwayat);
            $riwayatImunisasi = $this->getRiwayatImunisasi($balita);

            $growthAnalysis = $this->buildGrowthAnalysis($balita, $latestPemeriksaan);

            return view('user.balita.show', [
                'context' => $context,
                'balita' => $balita,
                'dataBalita' => $balita, // Alias for view

                'usia' => $usia,
                'usia_tahun' => $usia['tahun'],
                'usia_bulan' => $usia['bulan_sisa'],
                'usia_hari' => $usia['hari'],
                'totalBulan' => $usia['total_bulan'],

                'riwayat' => $riwayat,
                'riwayatCards' => $this->buildRiwayatCards($riwayat),

                'riwayatImunisasi' => $riwayatImunisasi,
                'imunisasiCards' => $this->buildImunisasiCards($riwayatImunisasi),

                'latestPemeriksaan' => $latestPemeriksaan,

                'growthAnalysis' => $growthAnalysis,
                'metrics' => $this->buildMetrics($balita, $latestPemeriksaan, $usia, $growthAnalysis),
                'growthMetrics' => $this->buildGrowthMetrics($balita, $latestPemeriksaan),
                'trend' => $this->buildTrend($riwayat),
            ]);
        } catch (\Throwable $e) {
            Log::error('User BalitaController@show error', [
                'message' => $e->getMessage(),
                'balita_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->route('user.monitoring.index')
                ->with('error', 'Gagal memuat detail data balita.');
        }
    }

    private function findAuthorizedBalita(int $id, array $context): ?Balita
    {
        $fromCollection = collect($context['balitas'] ?? [])->firstWhere('id', $id);

        if ($fromCollection instanceof Balita) {
            return $fromCollection;
        }

        if (! Schema::hasTable('balitas')) {
            return null;
        }

        $user = auth()->user();
        $nik = $context['nik'] ?? null;

        if (! $user) {
            return null;
        }

        return Balita::query()
            ->whereKey($id)
            ->where(function (Builder $query) use ($user, $nik) {
                $hasCondition = false;

                if (Schema::hasColumn('balitas', 'user_id')) {
                    $query->where('user_id', $user->id);
                    $hasCondition = true;
                }

                if ($nik && Schema::hasColumn('balitas', 'nik')) {
                    $hasCondition ? $query->orWhere('nik', $nik) : $query->where('nik', $nik);
                    $hasCondition = true;
                }

                if ($nik && Schema::hasColumn('balitas', 'nik_ibu')) {
                    $hasCondition ? $query->orWhere('nik_ibu', $nik) : $query->where('nik_ibu', $nik);
                    $hasCondition = true;
                }

                if (! $hasCondition) {
                    $query->whereRaw('1 = 0');
                }
            })
            ->first();
    }

    private function safeRelations(Balita $balita): array
    {
        $relations = [];
        if (method_exists($balita, 'pemeriksaan_terakhir')) {
            $relations[] = 'pemeriksaan_terakhir';
        }
        if (method_exists($balita, 'user')) {
            $relations[] = 'user';
        }
        return $relations;
    }

    private function getRiwayatPemeriksaan(Balita $balita): Collection
    {
        $fromKunjungan = $this->getRiwayatFromKunjungan($balita);
        if ($fromKunjungan->isNotEmpty()) return $fromKunjungan;
        return $this->getRiwayatFromPemeriksaan($balita);
    }

    private function getRiwayatFromKunjungan(Balita $balita): Collection
    {
        if (! method_exists($balita, 'kunjungans')) return collect();
        try {
            return $balita->kunjungans()
                ->with('pemeriksaan')
                ->whereHas('pemeriksaan', fn ($query) => $this->applyVerifiedFilter($query))
                ->orderByDesc('tanggal_kunjungan')
                ->orderByDesc('created_at')
                ->limit(12)
                ->get()
                ->filter(fn ($item) => filled($item->pemeriksaan))
                ->values();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function getRiwayatFromPemeriksaan(Balita $balita): Collection
    {
        if (! Schema::hasTable('pemeriksaans')) return collect();
        try {
            $query = Pemeriksaan::query()
                ->with('kunjungan')
                ->where(function (Builder $q) use ($balita) {
                    $hasKategoriPasien = Schema::hasColumn('pemeriksaans', 'kategori_pasien') && Schema::hasColumn('pemeriksaans', 'pasien_id');
                    if ($hasKategoriPasien) {
                        $q->where(fn ($inner) => $inner->where('kategori_pasien', 'balita')->where('pasien_id', $balita->id));
                    }
                    if (Schema::hasColumn('pemeriksaans', 'balita_id')) {
                        $hasKategoriPasien ? $q->orWhere('balita_id', $balita->id) : $q->where('balita_id', $balita->id);
                    }
                });

            $this->applyVerifiedFilter($query);

            return $query->orderByDesc('tanggal_periksa')->orderByDesc('created_at')->limit(12)->get()
                ->map(function (Pemeriksaan $pemeriksaan) {
                    return (object) [
                        'id' => 'pemeriksaan-' . $pemeriksaan->id,
                        'tanggal_kunjungan' => $pemeriksaan->tanggal_periksa ?? data_get($pemeriksaan, 'kunjungan.tanggal_kunjungan') ?? $pemeriksaan->created_at,
                        'created_at' => $pemeriksaan->created_at,
                        'keluhan' => $pemeriksaan->keluhan ?? data_get($pemeriksaan, 'kunjungan.keluhan') ?? null,
                        'pemeriksaan' => $pemeriksaan,
                    ];
                })->values();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function resolveLatestPemeriksaan(Balita $balita, Collection $riwayat): ?Pemeriksaan
    {
        $latest = $riwayat->first()?->pemeriksaan;
        if ($latest instanceof Pemeriksaan) return $latest;
        
        $fromRelation = $balita->pemeriksaan_terakhir ?? null;
        if ($fromRelation instanceof Pemeriksaan) return $fromRelation;

        if (! Schema::hasTable('pemeriksaans')) return null;
        try {
            $query = Pemeriksaan::query()
                ->where(function (Builder $q) use ($balita) {
                    $hasKategoriPasien = Schema::hasColumn('pemeriksaans', 'kategori_pasien') && Schema::hasColumn('pemeriksaans', 'pasien_id');
                    if ($hasKategoriPasien) {
                        $q->where(fn ($inner) => $inner->where('kategori_pasien', 'balita')->where('pasien_id', $balita->id));
                    }
                    if (Schema::hasColumn('pemeriksaans', 'balita_id')) {
                        $hasKategoriPasien ? $q->orWhere('balita_id', $balita->id) : $q->where('balita_id', $balita->id);
                    }
                });

            $this->applyVerifiedFilter($query);
            return $query->orderByDesc('tanggal_periksa')->orderByDesc('created_at')->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getRiwayatImunisasi(Balita $balita): Collection
    {
        if (! class_exists(Imunisasi::class)) return collect();
        try {
            $table = (new Imunisasi())->getTable();
            if (! Schema::hasTable($table) || ! Schema::hasTable('kunjungans')) return collect();

            return Imunisasi::query()
                ->with('kunjungan')
                ->whereHas('kunjungan', function (Builder $query) use ($balita) {
                    if (Schema::hasColumn('kunjungans', 'pasien_id') && Schema::hasColumn('kunjungans', 'pasien_type')) {
                        $query->where('pasien_id', $balita->id)
                            ->where(function (Builder $typeQuery) {
                                $typeQuery->where('pasien_type', Balita::class)->orWhere('pasien_type', 'like', '%Balita%');
                            });
                    }
                })
                ->when(Schema::hasColumn($table, 'tanggal_imunisasi'), fn ($query) => $query->orderByDesc('tanggal_imunisasi'), fn ($query) => $query->orderByDesc('created_at'))
                ->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function applyVerifiedFilter(Builder $query): void
    {
        if (! Schema::hasColumn('pemeriksaans', 'status_verifikasi')) return;
        $query->whereIn('status_verifikasi', ['tervalidasi', 'verified', 'approved', 'valid', 'selesai']);
    }

    private function buildAgeData(Balita $balita): array
    {
        if (blank($balita->tanggal_lahir)) {
            return ['tahun' => 0, 'bulan_sisa' => 0, 'hari' => 0, 'total_bulan' => 0, 'label' => '-', 'kategori' => 'Balita'];
        }

        $diff = Carbon::parse($balita->tanggal_lahir)->diff(now());
        $totalBulan = ($diff->y * 12) + $diff->m;
        $label = $diff->y > 0 ? $diff->y . ' tahun ' . $diff->m . ' bulan' : $diff->m . ' bulan ' . $diff->d . ' hari';

        $kategori = match (true) {
            $totalBulan <= 11 => 'Bayi 0-11 Bulan',
            $totalBulan <= 23 => 'Batita 12-23 Bulan',
            $totalBulan <= 59 => 'Balita 24-59 Bulan',
            default => 'Lewat Usia Sasaran',
        };

        return ['tahun' => $diff->y, 'bulan_sisa' => $diff->m, 'hari' => $diff->d, 'total_bulan' => $totalBulan, 'label' => $label, 'kategori' => $kategori];
    }

    private function buildMetrics(Balita $balita, ?Pemeriksaan $latestPemeriksaan, array $usia, array $growthAnalysis): array {
        return [
            ['label' => 'Usia', 'value' => $usia['label'], 'caption' => $usia['kategori'], 'tone' => 'emerald'],
            ['label' => 'Berat', 'value' => $this->numberValue($latestPemeriksaan->berat_badan ?? $balita->berat_badan ?? null, 'kg'), 'caption' => 'Pemeriksaan terakhir', 'tone' => 'rose'],
            ['label' => 'Tinggi', 'value' => $this->heightValue($latestPemeriksaan->tinggi_badan ?? $balita->tinggi_badan ?? null), 'caption' => 'Pemeriksaan terakhir', 'tone' => 'sky'],
            ['label' => 'Status Gizi', 'value' => $growthAnalysis['label'] ?? 'Belum Ada', 'caption' => 'Hasil pengukuran', 'tone' => $growthAnalysis['tone'] ?? 'slate'],
        ];
    }

    private function buildGrowthMetrics(Balita $balita, ?Pemeriksaan $latestPemeriksaan): array
    {
        return [
            ['label' => 'L. Kepala', 'value' => $this->numberValue($latestPemeriksaan->lingkar_kepala ?? $balita->lingkar_kepala ?? null, 'cm')],
            ['label' => 'LILA', 'value' => $this->numberValue($latestPemeriksaan->lila ?? $latestPemeriksaan->lingkar_lengan ?? $balita->lila ?? null, 'cm')],
            ['label' => 'BB/U', 'value' => $latestPemeriksaan->status_bbu ?? $latestPemeriksaan->bb_u ?? '-'],
            ['label' => 'TB/U', 'value' => $latestPemeriksaan->status_tbu ?? $latestPemeriksaan->tb_u ?? '-'],
            ['label' => 'BB/TB', 'value' => $latestPemeriksaan->status_bbtb ?? $latestPemeriksaan->bb_tb ?? '-'],
            ['label' => 'IMT/U', 'value' => $latestPemeriksaan->status_imt_u ?? '-'],
        ];
    }

    private function buildGrowthAnalysis(Balita $balita, ?Pemeriksaan $latestPemeriksaan): array
    {
        $status = $latestPemeriksaan->status_gizi ?? $latestPemeriksaan->status_bbtb ?? $latestPemeriksaan->status_bbu ?? $balita->status_gizi ?? null;
        if (blank($status)) return ['label' => 'Belum Ada Data', 'message' => 'Data pengukuran balita belum tersedia.', 'suggestion' => 'Lakukan pengukuran BB dan TB rutin.', 'tone' => 'slate', 'icon' => 'fa-circle-info'];

        $text = strtolower((string) $status);
        if (str_contains($text, 'baik') || str_contains($text, 'normal') || str_contains($text, 'sesuai')) {
            return ['label' => $status, 'message' => 'Hasil pengukuran terakhir berada dalam kategori baik.', 'suggestion' => 'Pertahankan asupan gizi yang seimbang.', 'tone' => 'emerald', 'icon' => 'fa-circle-check'];
        }
        if (str_contains($text, 'kurang') || str_contains($text, 'pendek') || str_contains($text, 'wasting')) {
            return ['label' => $status, 'message' => 'Terdapat catatan pertumbuhan yang perlu dipantau.', 'suggestion' => 'Perbanyak asupan protein hewani.', 'tone' => 'amber', 'icon' => 'fa-triangle-exclamation'];
        }
        if (str_contains($text, 'buruk') || str_contains($text, 'stunting') || str_contains($text, 'sangat')) {
            return ['label' => $status, 'message' => 'Hasil pengukuran menunjukkan kondisi butuh intervensi.', 'suggestion' => 'Segera konsultasi ke Bidan/Puskesmas.', 'tone' => 'rose', 'icon' => 'fa-heart-pulse'];
        }

        return ['label' => $status, 'message' => 'Status pertumbuhan tercatat.', 'suggestion' => 'Lanjutkan pemantauan rutin.', 'tone' => 'sky', 'icon' => 'fa-chart-line'];
    }

    private function buildTrend(Collection $riwayat): array
    {
        $items = $riwayat->reverse()->values()->take(8);
        $values = $items->map(fn ($item) => (float) ($item->pemeriksaan->berat_badan ?? 0))->filter(fn ($v) => $v > 0)->values();
        $max = max((float) ($values->max() ?: 1), 1);

        return $items->map(function ($item) use ($max) {
            $pemeriksaan = $item->pemeriksaan ?? null;
            $berat = filled($pemeriksaan->berat_badan ?? null) ? (float) $pemeriksaan->berat_badan : null;
            return [
                'label' => $item->tanggal_kunjungan ? Carbon::parse($item->tanggal_kunjungan)->translatedFormat('d M') : '-',
                'berat' => $berat,
                'tinggi' => $this->heightValue($pemeriksaan->tinggi_badan ?? null),
                'height' => filled($berat) ? max(18, min(100, (int) round(($berat / $max) * 100))) : 14,
            ];
        })->toArray();
    }

    private function buildRiwayatCards(Collection $riwayat): array
    {
        return $riwayat->map(function ($kunjungan) {
            $pemeriksaan = $kunjungan->pemeriksaan ?? null;
            if (! $pemeriksaan) return null;

            return [
                'tanggal' => $kunjungan->tanggal_kunjungan ? Carbon::parse($kunjungan->tanggal_kunjungan)->translatedFormat('d M Y') : '-',
                'usia' => $pemeriksaan->usia_saat_periksa ? $pemeriksaan->usia_saat_periksa . ' bln' : '-',
                'berat' => $this->numberValue($pemeriksaan->berat_badan ?? null, 'kg'),
                'tinggi' => $this->heightValue($pemeriksaan->tinggi_badan ?? null),
                'lingkar_kepala' => $this->numberValue($pemeriksaan->lingkar_kepala ?? null, 'cm'),
                'status_gizi' => $pemeriksaan->status_gizi ?? $pemeriksaan->status_bbtb ?? 'Belum Dinilai',
                'keluhan' => $kunjungan->keluhan ?: $pemeriksaan->keluhan ?: 'Tidak ada catatan khusus.',
                'edukasi' => $pemeriksaan->edukasi ?? null,
                'status' => $pemeriksaan->status_verifikasi_text ?? 'Tervalidasi',
            ];
        })->filter()->values()->toArray();
    }

    private function buildImunisasiCards(Collection $items): array
    {
        return $items->map(function ($imunisasi) {
            return [
                'tanggal' => $imunisasi->tanggal_imunisasi ? Carbon::parse($imunisasi->tanggal_imunisasi)->translatedFormat('d M Y') : ($imunisasi->created_at ? Carbon::parse($imunisasi->created_at)->translatedFormat('d M Y') : '-'),
                'nama' => $imunisasi->vaksin ?? $imunisasi->jenis_imunisasi ?? $imunisasi->nama_vaksin ?? 'Imunisasi',
                'batch' => $imunisasi->batch_number ?? $imunisasi->nomor_batch ?? '-',
                'catatan' => $imunisasi->catatan ?? $imunisasi->keterangan ?? 'Tidak ada catatan tambahan.',
                'petugas' => $imunisasi->nama_petugas ?? data_get($imunisasi, 'kunjungan.petugas.name') ?? 'Petugas Posyandu',
            ];
        })->values()->toArray();
    }

    // FUNGSI NORMALISASI TINGGI BADAN KHUSUS BALITA (Mencegah typo misal ketik 0.8 atau 8 jadi 80 cm)
    private function normalizeHeightCm($value): ?float
    {
        if (blank($value)) return null;
        $height = (float) $value;

        if ($height >= 0.3 && $height <= 1.5) $height *= 100; // 0.8 -> 80cm
        elseif ($height >= 3 && $height <= 15) $height *= 10; // 8 -> 80cm

        // Balita normal range 35cm - 150cm
        if ($height < 30 || $height > 150) return null;
        return round($height, 1);
    }

    private function heightValue($value): string
    {
        $height = $this->normalizeHeightCm($value);
        if (! $height) return '-';
        return $this->numberValue($height, 'cm');
    }

    private function numberValue($value, string $unit = ''): string
    {
        if (blank($value)) return '-';
        $formatted = rtrim(rtrim(number_format((float) $value, 1, '.', ''), '0'), '.');
        return trim($formatted . ' ' . $unit);
    }
}