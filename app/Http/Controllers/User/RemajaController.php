<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\Concerns\ResolvesUserHealthContext;
use App\Models\Pemeriksaan;
use App\Models\Remaja;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class RemajaController extends Controller
{
    use ResolvesUserHealthContext;

    public function show(int $id): View|RedirectResponse
    {
        try {
            $context = $this->getUserContext(auth()->user());
            $remaja = $this->findAuthorizedRemaja($id, $context);

            if (! $remaja) {
                return redirect()
                    ->route('user.monitoring.index')
                    ->with('error', 'Akses ditolak atau data remaja tidak ditemukan.');
            }

            $remaja->loadMissing($this->safeRelations($remaja));

            $riwayat = $this->getRiwayatPemeriksaan($remaja);
            $latestPemeriksaan = $this->resolveLatestPemeriksaan($remaja, $riwayat);

            $imt = $this->resolveImt($latestPemeriksaan, $remaja);
            $imtAnalysis = $this->buildImtAnalysis($imt);

            return view('user.remaja.show', [
                'context' => $context,
                'remaja' => $remaja,
                'riwayat' => $riwayat,
                'riwayatCards' => $this->buildRiwayatCards($riwayat),
                'latestPemeriksaan' => $latestPemeriksaan,
                'imt' => $imt,
                'imtAnalysis' => $imtAnalysis,
                'metrics' => $this->buildMetrics($remaja, $latestPemeriksaan, $imt, $imtAnalysis),
                'healthMetrics' => $this->buildHealthMetrics($remaja, $latestPemeriksaan), // TAMBAHAN METRIK KOMPLEKS
                'trend' => $this->buildTrend($riwayat),
            ]);
        } catch (\Throwable $e) {
            Log::error('User RemajaController@show error', [
                'message' => $e->getMessage(),
                'remaja_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->route('user.monitoring.index')
                ->with('error', 'Gagal memuat detail data remaja.');
        }
    }

    private function findAuthorizedRemaja(int $id, array $context): ?Remaja
    {
        $fromCollection = collect($context['remajas'] ?? [])->firstWhere('id', $id);

        if ($fromCollection instanceof Remaja) {
            return $fromCollection;
        }

        $single = $context['remaja'] ?? null;

        if ($single instanceof Remaja && (int) $single->id === $id) {
            return $single;
        }

        if (! Schema::hasTable('remajas')) {
            return null;
        }

        $user = auth()->user();
        $nik = $context['nik'] ?? null;

        if (! $user) {
            return null;
        }

        return Remaja::query()
            ->whereKey($id)
            ->where(function (Builder $query) use ($user, $nik) {
                $hasCondition = false;
                if (Schema::hasColumn('remajas', 'user_id')) {
                    $query->where('user_id', $user->id);
                    $hasCondition = true;
                }
                if ($nik && Schema::hasColumn('remajas', 'nik')) {
                    $hasCondition ? $query->orWhere('nik', $nik) : $query->where('nik', $nik);
                }
            })
            ->first();
    }

    private function safeRelations(Remaja $remaja): array
    {
        $relations = [];
        if (method_exists($remaja, 'pemeriksaan_terakhir')) {
            $relations[] = 'pemeriksaan_terakhir';
        }
        if (method_exists($remaja, 'user')) {
            $relations[] = 'user';
        }
        return $relations;
    }

    private function getRiwayatPemeriksaan(Remaja $remaja): Collection
    {
        $fromKunjungan = $this->getRiwayatFromKunjungan($remaja);
        if ($fromKunjungan->isNotEmpty()) return $fromKunjungan;
        return $this->getRiwayatFromPemeriksaan($remaja);
    }

    private function getRiwayatFromKunjungan(Remaja $remaja): Collection
    {
        if (! method_exists($remaja, 'kunjungans')) return collect();
        try {
            return $remaja->kunjungans()
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

    private function getRiwayatFromPemeriksaan(Remaja $remaja): Collection
    {
        if (! Schema::hasTable('pemeriksaans')) return collect();
        try {
            $query = Pemeriksaan::query()->with('kunjungan')
                ->where(function (Builder $q) use ($remaja) {
                    $hasKategoriPasien = Schema::hasColumn('pemeriksaans', 'kategori_pasien') && Schema::hasColumn('pemeriksaans', 'pasien_id');
                    if ($hasKategoriPasien) {
                        $q->where(fn ($inner) => $inner->where('kategori_pasien', 'remaja')->where('pasien_id', $remaja->id));
                    }
                    if (Schema::hasColumn('pemeriksaans', 'remaja_id')) {
                        $hasKategoriPasien ? $q->orWhere('remaja_id', $remaja->id) : $q->where('remaja_id', $remaja->id);
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

    private function resolveLatestPemeriksaan(Remaja $remaja, Collection $riwayat): ?Pemeriksaan
    {
        $latest = $riwayat->first()?->pemeriksaan;
        if ($latest instanceof Pemeriksaan) return $latest;
        
        $fromRelation = $remaja->pemeriksaan_terakhir ?? null;
        if ($fromRelation instanceof Pemeriksaan) return $fromRelation;

        if (! Schema::hasTable('pemeriksaans')) return null;
        try {
            $query = Pemeriksaan::query()
                ->where(function (Builder $q) use ($remaja) {
                    $hasKategoriPasien = Schema::hasColumn('pemeriksaans', 'kategori_pasien') && Schema::hasColumn('pemeriksaans', 'pasien_id');
                    if ($hasKategoriPasien) {
                        $q->where(fn ($inner) => $inner->where('kategori_pasien', 'remaja')->where('pasien_id', $remaja->id));
                    }
                    if (Schema::hasColumn('pemeriksaans', 'remaja_id')) {
                        $hasKategoriPasien ? $q->orWhere('remaja_id', $remaja->id) : $q->where('remaja_id', $remaja->id);
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

    private function buildMetrics(Remaja $remaja, ?Pemeriksaan $latestPemeriksaan, ?float $imt, array $imtAnalysis): array {
        return [
            ['label' => 'Usia', 'value' => $this->ageText($remaja->tanggal_lahir ?? null), 'caption' => 'Kategori remaja', 'tone' => 'emerald'],
            ['label' => 'IMT', 'value' => filled($imt) ? number_format($imt, 1, ',', '.') : '-', 'caption' => $imtAnalysis['label'] ?? 'Belum Ada Data', 'tone' => $imtAnalysis['tone'] ?? 'slate'],
            ['label' => 'Berat', 'value' => $this->numberValue($latestPemeriksaan->berat_badan ?? $remaja->berat_badan ?? null, 'kg'), 'caption' => 'Pemeriksaan terakhir', 'tone' => 'sky'],
            ['label' => 'Tinggi', 'value' => $this->heightValue($latestPemeriksaan->tinggi_badan ?? $remaja->tinggi_badan ?? null), 'caption' => 'Pemeriksaan terakhir', 'tone' => 'amber'],
        ];
    }

    // TAMBAHAN METRIK KESEHATAN REMAJA (Tensi, HB, LP)
    private function buildHealthMetrics(Remaja $remaja, ?Pemeriksaan $latestPemeriksaan): array {
        return [
            ['label' => 'Tensi', 'value' => $this->bloodPressureValue($latestPemeriksaan->tekanan_darah ?? null), 'caption' => 'Tekanan Darah', 'tone' => 'rose'],
            ['label' => 'Hemoglobin', 'value' => $this->numberValue($latestPemeriksaan->hemoglobin ?? $latestPemeriksaan->hb ?? null, 'g/dL'), 'caption' => 'Cek Anemia', 'tone' => 'rose'],
            ['label' => 'L. Perut', 'value' => $this->numberValue($latestPemeriksaan->lingkar_perut ?? null, 'cm'), 'caption' => 'Risiko Metabolik', 'tone' => 'amber'],
        ];
    }

    private function buildTrend(Collection $riwayat): array
    {
        $items = $riwayat->reverse()->values()->take(8);
        $values = $items->map(fn ($item) => $this->resolveImt($item->pemeriksaan ?? null))->filter(fn ($value) => filled($value))->values();
        $max = max((float) ($values->max() ?: 1), 1);

        return $items->map(function ($item) use ($max) {
            $pemeriksaan = $item->pemeriksaan ?? null;
            $imt = $this->resolveImt($pemeriksaan);
            return [
                'label' => $item->tanggal_kunjungan ? Carbon::parse($item->tanggal_kunjungan)->translatedFormat('d M') : '-',
                'imt' => filled($imt) ? round($imt, 1) : null,
                'height' => filled($imt) ? max(18, min(100, (int) round(($imt / $max) * 100))) : 14,
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
                'berat' => $this->numberValue($pemeriksaan->berat_badan ?? null, 'kg'),
                'tinggi' => $this->heightValue($pemeriksaan->tinggi_badan ?? null),
                'imt' => filled($this->resolveImt($pemeriksaan)) ? number_format($this->resolveImt($pemeriksaan), 1, ',', '.') : '-',
                'lingkar_perut' => $this->numberValue($pemeriksaan->lingkar_perut ?? null, 'cm'),
                'tensi' => $this->bloodPressureValue($pemeriksaan->tekanan_darah ?? null),
                'hb' => $this->numberValue($pemeriksaan->hemoglobin ?? $pemeriksaan->hb ?? null, 'g/dL'),
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

    private function buildImtAnalysis(?float $imt): array
    {
        if (! $imt) return ['label' => 'Belum Ada Data', 'message' => 'Data berat badan dan tinggi badan belum tersedia.', 'suggestion' => 'Lakukan pemeriksaan BB dan TB di Posyandu.', 'tone' => 'slate', 'icon' => 'fa-circle-info'];
        if ($imt < 18.5) return ['label' => 'Kurus', 'message' => 'IMT berada di bawah rentang normal.', 'suggestion' => 'Perhatikan asupan protein, karbohidrat, dan konsultasikan dengan Bidan.', 'tone' => 'amber', 'icon' => 'fa-triangle-exclamation'];
        if ($imt < 25) return ['label' => 'Normal', 'message' => 'IMT berada pada rentang normal.', 'suggestion' => 'Pertahankan pola makan seimbang dan aktivitas fisik teratur.', 'tone' => 'emerald', 'icon' => 'fa-circle-check'];
        if ($imt < 30) return ['label' => 'Berlebih', 'message' => 'IMT mulai melebihi rentang normal.', 'suggestion' => 'Kurangi makanan tinggi gula dan tingkatkan aktivitas fisik.', 'tone' => 'rose', 'icon' => 'fa-weight-scale'];
        return ['label' => 'Obesitas', 'message' => 'IMT berada jauh di atas rentang normal.', 'suggestion' => 'Segera konsultasi dengan Bidan atau tenaga kesehatan.', 'tone' => 'rose', 'icon' => 'fa-heart-pulse'];
    }

    // FUNGSI NORMALISASI TINGGI BADAN (Memperbaiki typo 16 atau 1.6 menjadi 160)
    private function normalizeHeightCm($value): ?float
    {
        if (blank($value)) return null;
        $height = (float) $value;
        if ($height >= 1 && $height <= 2.5) $height *= 100; // Ubah 1.6 m jadi 160 cm
        if ($height >= 10 && $height < 50) $height *= 10;   // Ubah 16 jadi 160 cm
        if ($height < 50 || $height > 250) return null;
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