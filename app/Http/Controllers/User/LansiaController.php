<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\Concerns\ResolvesUserHealthContext;
use App\Models\Lansia;
use App\Models\Pemeriksaan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class LansiaController extends Controller
{
    use ResolvesUserHealthContext;

    public function show(int $id): View|RedirectResponse
    {
        try {
            $context = $this->getUserContext(auth()->user());
            $lansia = $this->findAuthorizedLansia($id, $context);

            if (! $lansia) {
                return redirect()
                    ->route('user.monitoring.index')
                    ->with('error', 'Akses ditolak atau data lansia tidak ditemukan.');
            }

            $lansia->loadMissing($this->safeRelations($lansia));

            $riwayat = $this->getRiwayatPemeriksaan($lansia);
            $latestPemeriksaan = $this->resolveLatestPemeriksaan($lansia, $riwayat);

            $imt = $this->resolveImt($latestPemeriksaan, $lansia);
            $ptmAnalysis = $this->buildPtmAnalysis($latestPemeriksaan);

            return view('user.lansia.show', [
                'context' => $context,
                'lansia' => $lansia,
                'dataLansia' => $lansia, // Alias
                'riwayat' => $riwayat,
                'riwayatCards' => $this->buildRiwayatCards($riwayat),
                'latestPemeriksaan' => $latestPemeriksaan,
                'imt' => $imt,
                'ptmAnalysis' => $ptmAnalysis,
                'metrics' => $this->buildMetrics($lansia, $latestPemeriksaan),
                'healthMetrics' => $this->buildHealthMetrics($lansia, $latestPemeriksaan, $imt),
                'trend' => $this->buildTrend($riwayat),
            ]);
        } catch (\Throwable $e) {
            Log::error('User LansiaController@show error', [
                'message' => $e->getMessage(),
                'lansia_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->route('user.monitoring.index')
                ->with('error', 'Gagal memuat detail data lansia.');
        }
    }

    private function findAuthorizedLansia(int $id, array $context): ?Lansia
    {
        $fromCollection = collect($context['lansias'] ?? [])->firstWhere('id', $id);

        if ($fromCollection instanceof Lansia) {
            return $fromCollection;
        }

        if (! Schema::hasTable('lansias')) {
            return null;
        }

        $user = auth()->user();
        $nik = $context['nik'] ?? null;

        if (! $user) {
            return null;
        }

        return Lansia::query()
            ->whereKey($id)
            ->where(function (Builder $query) use ($user, $nik) {
                $hasCondition = false;
                if (Schema::hasColumn('lansias', 'user_id')) {
                    $query->where('user_id', $user->id);
                    $hasCondition = true;
                }
                if ($nik && Schema::hasColumn('lansias', 'nik')) {
                    $hasCondition ? $query->orWhere('nik', $nik) : $query->where('nik', $nik);
                }
            })
            ->first();
    }

    private function safeRelations(Lansia $lansia): array
    {
        $relations = [];
        if (method_exists($lansia, 'pemeriksaan_terakhir')) {
            $relations[] = 'pemeriksaan_terakhir';
        }
        if (method_exists($lansia, 'user')) {
            $relations[] = 'user';
        }
        return $relations;
    }

    private function getRiwayatPemeriksaan(Lansia $lansia): Collection
    {
        $fromKunjungan = $this->getRiwayatFromKunjungan($lansia);
        if ($fromKunjungan->isNotEmpty()) return $fromKunjungan;
        return $this->getRiwayatFromPemeriksaan($lansia);
    }

    private function getRiwayatFromKunjungan(Lansia $lansia): Collection
    {
        if (! method_exists($lansia, 'kunjungans')) return collect();
        try {
            return $lansia->kunjungans()
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

    private function getRiwayatFromPemeriksaan(Lansia $lansia): Collection
    {
        if (! Schema::hasTable('pemeriksaans')) return collect();
        try {
            $query = Pemeriksaan::query()->with('kunjungan')
                ->where(function (Builder $q) use ($lansia) {
                    $hasKategoriPasien = Schema::hasColumn('pemeriksaans', 'kategori_pasien') && Schema::hasColumn('pemeriksaans', 'pasien_id');
                    if ($hasKategoriPasien) {
                        $q->where(fn ($inner) => $inner->where('kategori_pasien', 'lansia')->where('pasien_id', $lansia->id));
                    }
                    if (Schema::hasColumn('pemeriksaans', 'lansia_id')) {
                        $hasKategoriPasien ? $q->orWhere('lansia_id', $lansia->id) : $q->where('lansia_id', $lansia->id);
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

    private function resolveLatestPemeriksaan(Lansia $lansia, Collection $riwayat): ?Pemeriksaan
    {
        $latest = $riwayat->first()?->pemeriksaan;
        if ($latest instanceof Pemeriksaan) return $latest;
        
        $fromRelation = $lansia->pemeriksaan_terakhir ?? null;
        if ($fromRelation instanceof Pemeriksaan) return $fromRelation;

        if (! Schema::hasTable('pemeriksaans')) return null;
        try {
            $query = Pemeriksaan::query()
                ->where(function (Builder $q) use ($lansia) {
                    $hasKategoriPasien = Schema::hasColumn('pemeriksaans', 'kategori_pasien') && Schema::hasColumn('pemeriksaans', 'pasien_id');
                    if ($hasKategoriPasien) {
                        $q->where(fn ($inner) => $inner->where('kategori_pasien', 'lansia')->where('pasien_id', $lansia->id));
                    }
                    if (Schema::hasColumn('pemeriksaans', 'lansia_id')) {
                        $hasKategoriPasien ? $q->orWhere('lansia_id', $lansia->id) : $q->where('lansia_id', $lansia->id);
                    }
                });
            $this->applyVerifiedFilter($query);
            return $query->orderByDesc('tanggal_periksa')->orderByDesc('created_at')->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function applyVerifiedFilter(Builder $query): void
    {
        if (! Schema::hasColumn('pemeriksaans', 'status_verifikasi')) return;
        $query->whereIn('status_verifikasi', ['tervalidasi', 'verified', 'approved', 'valid', 'selesai']);
    }

    private function buildMetrics(Lansia $lansia, ?Pemeriksaan $latestPemeriksaan): array {
        return [
            ['label' => 'Usia', 'value' => $this->ageText($lansia->tanggal_lahir ?? null), 'caption' => 'Kategori Lansia', 'tone' => 'emerald'],
            ['label' => 'Tensi', 'value' => $this->bloodPressureValue($latestPemeriksaan->tekanan_darah ?? $lansia->tekanan_darah ?? null), 'caption' => 'Tekanan Darah', 'tone' => 'rose'],
            ['label' => 'Gula Darah', 'value' => $this->numberValue($latestPemeriksaan->gula_darah ?? $latestPemeriksaan->gula ?? $lansia->gula_darah ?? null, 'mg/dL'), 'caption' => 'Pemeriksaan terakhir', 'tone' => 'sky'],
            ['label' => 'Kemandirian', 'value' => ucwords(str_replace('_', ' ', $lansia->tingkat_kemandirian ?: 'Belum diisi')), 'caption' => 'Status aktivitas', 'tone' => 'amber'],
        ];
    }

    private function buildHealthMetrics(Lansia $lansia, ?Pemeriksaan $latestPemeriksaan, ?float $imt): array {
        return [
            ['label' => 'Kolesterol', 'value' => $this->numberValue($latestPemeriksaan->kolesterol ?? null, 'mg/dL')],
            ['label' => 'Asam Urat', 'value' => $this->numberValue($latestPemeriksaan->asam_urat ?? null, 'mg/dL')],
            ['label' => 'L. Perut', 'value' => $this->numberValue($latestPemeriksaan->lingkar_perut ?? null, 'cm')],
            ['label' => 'IMT', 'value' => filled($imt) ? number_format($imt, 1, ',', '.') : '-'],
            ['label' => 'Berat', 'value' => $this->numberValue($latestPemeriksaan->berat_badan ?? null, 'kg')],
            ['label' => 'Tinggi', 'value' => $this->heightValue($latestPemeriksaan->tinggi_badan ?? null)],
        ];
    }

    private function buildPtmAnalysis(?Pemeriksaan $pemeriksaan): array
    {
        if (!$pemeriksaan) return ['label' => 'Belum Ada Data', 'message' => 'Data pemeriksaan PTM belum tersedia.', 'suggestion' => 'Lakukan pemeriksaan kesehatan dasar di Posyandu.', 'tone' => 'slate', 'icon' => 'fa-circle-info'];

        $tensi = $pemeriksaan->tekanan_darah ?? $pemeriksaan->tensi ?? '';
        $gula = (float) ($pemeriksaan->gula_darah ?? $pemeriksaan->gula ?? 0);
        $kolesterol = (float) ($pemeriksaan->kolesterol ?? 0);
        $asamUrat = (float) ($pemeriksaan->asam_urat ?? 0);

        $risks = [];
        if ($gula > 200) $risks[] = 'Gula Darah Tinggi';
        if ($kolesterol > 200) $risks[] = 'Kolesterol Tinggi';
        if ($asamUrat > 7) $risks[] = 'Asam Urat Tinggi';

        if (str_contains($tensi, '/')) {
            $parts = explode('/', $tensi);
            if ((int)($parts[0] ?? 0) > 140 || (int)($parts[1] ?? 0) > 90) {
                $risks[] = 'Hipertensi';
            }
        }

        if (empty($risks)) {
            return ['label' => 'Terkontrol', 'message' => 'Hasil pemeriksaan PTM dalam batas aman.', 'suggestion' => 'Pertahankan gaya hidup dan pola makan sehat.', 'tone' => 'emerald', 'icon' => 'fa-circle-check'];
        }

        return ['label' => 'Perlu Perhatian', 'message' => 'Terdeteksi indikasi: ' . implode(', ', $risks) . '.', 'suggestion' => 'Kurangi konsumsi gula/garam dan segera konsultasi ke Faskes.', 'tone' => 'rose', 'icon' => 'fa-heart-pulse'];
    }

    private function buildTrend(Collection $riwayat): array
    {
        $items = $riwayat->reverse()->values()->take(8);
        $values = $items->map(fn ($item) => (float) ($item->pemeriksaan->gula_darah ?? $item->pemeriksaan->gula ?? 0))->filter(fn ($v) => $v > 0)->values();
        $max = max((float) ($values->max() ?: 1), 1);

        return $items->map(function ($item) use ($max) {
            $pemeriksaan = $item->pemeriksaan ?? null;
            $gula = filled($pemeriksaan->gula_darah ?? $pemeriksaan->gula ?? null) ? (float) ($pemeriksaan->gula_darah ?? $pemeriksaan->gula) : null;
            return [
                'label' => $item->tanggal_kunjungan ? Carbon::parse($item->tanggal_kunjungan)->translatedFormat('d M') : '-',
                'gula' => $gula,
                'sistolik' => $this->parseBloodPressure($pemeriksaan->tekanan_darah ?? null)['sistolik'] ?? '-',
                'height' => filled($gula) ? max(18, min(100, (int) round(($gula / $max) * 100))) : 14,
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
                'tensi' => $this->bloodPressureValue($pemeriksaan->tekanan_darah ?? null),
                'gula' => $this->numberValue($pemeriksaan->gula_darah ?? $pemeriksaan->gula ?? null, 'mg/dL'),
                'kolesterol' => $this->numberValue($pemeriksaan->kolesterol ?? null, 'mg/dL'),
                'asam_urat' => $this->numberValue($pemeriksaan->asam_urat ?? null, 'mg/dL'),
                'imt' => filled($this->resolveImt($pemeriksaan)) ? number_format($this->resolveImt($pemeriksaan), 1, ',', '.') : '-',
                'lingkar_perut' => $this->numberValue($pemeriksaan->lingkar_perut ?? null, 'cm'),
                'keluhan' => $kunjungan->keluhan ?: $pemeriksaan->keluhan ?: 'Tidak ada catatan keluhan.',
                'edukasi' => $pemeriksaan->edukasi ?? null,
                'status' => $pemeriksaan->status_verifikasi_text ?? 'Tervalidasi',
            ];
        })->filter()->values()->toArray();
    }

    private function resolveImt($source = null, $fallback = null): ?float
    {
        $imt = $source->imt ?? $fallback->imt ?? null;
        if (filled($imt)) return round((float) $imt, 2);

        $berat = $source->berat_badan ?? $fallback->berat_badan ?? null;
        $tinggi = $source->tinggi_badan ?? $fallback->tinggi_badan ?? null;

        if (blank($berat) || blank($tinggi)) return null;

        $tinggiCm = $this->normalizeHeightCm($tinggi);
        if (! $tinggiCm) return null;

        $tinggiMeter = $tinggiCm / 100;
        return round(((float) $berat) / ($tinggiMeter * $tinggiMeter), 2);
    }

    private function normalizeHeightCm($value): ?float
    {
        if (blank($value)) return null;
        $height = (float) preg_replace('/[^0-9.]/', '', (string) $value);
        if ($height >= 1 && $height <= 2.5) $height *= 100;
        if ($height >= 10 && $height < 50) $height *= 10;
        if ($height < 35 || $height > 250) return null;
        return round($height, 1);
    }

    private function heightValue($value): string
    {
        $height = $this->normalizeHeightCm($value);
        if (! $height) return '-';
        return $this->numberValue($height, 'cm');
    }

    private function parseBloodPressure($value): array
    {
        if (blank($value)) return ['sistolik' => null, 'diastolik' => null];
        preg_match_all('/\d+/', (string) $value, $matches);
        $numbers = collect($matches[0] ?? [])->map(fn ($item) => (int) $item)->filter(fn ($item) => $item > 0)->values();
        return ['sistolik' => $numbers->get(0), 'diastolik' => $numbers->get(1)];
    }

    private function bloodPressureValue($value): string
    {
        $parsed = $this->parseBloodPressure($value);
        if (! $parsed['sistolik']) return '-';
        if (! $parsed['diastolik']) return (string) $parsed['sistolik'];
        return $parsed['sistolik'] . '/' . $parsed['diastolik'] . ' mmHg';
    }

    private function ageText($tanggalLahir): string
    {
        if (blank($tanggalLahir)) return '-';
        return Carbon::parse($tanggalLahir)->diff(now())->y . ' tahun';
    }

    private function numberValue($value, string $unit = ''): string
    {
        if (blank($value)) return '-';
        $formatted = rtrim(rtrim(number_format((float) $value, 1, '.', ''), '0'), '.');
        return trim($formatted . ' ' . $unit);
    }
}