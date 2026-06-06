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
            $imtAnalysis = $this->buildImtAnalysis($imt);
            $ptmAnalysis = $this->buildPtmAnalysis($lansia, $latestPemeriksaan);

            return view('user.lansia.show', [
                'context' => $context,
                'lansia' => $lansia,
                'dataLansia' => $lansia,

                'riwayat' => $riwayat,
                'riwayatCards' => $this->buildRiwayatCards($riwayat),

                'latestPemeriksaan' => $latestPemeriksaan,
                'pemTerakhir' => $latestPemeriksaan,

                'imt' => $imt,
                'imtAnalysis' => $imtAnalysis,
                'ptmAnalysis' => $ptmAnalysis,

                'metrics' => $this->buildMetrics($lansia, $latestPemeriksaan, $imt, $ptmAnalysis),
                'healthMetrics' => $this->buildHealthMetrics($lansia, $latestPemeriksaan, $imt, $imtAnalysis),
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

        $single = $context['lansia'] ?? null;

        if ($single instanceof Lansia && (int) $single->id === $id) {
            return $single;
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
                    $hasCondition
                        ? $query->orWhere('nik', $nik)
                        : $query->where('nik', $nik);

                    $hasCondition = true;
                }

                if (! $hasCondition) {
                    $query->whereRaw('1 = 0');
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

        if ($fromKunjungan->isNotEmpty()) {
            return $fromKunjungan;
        }

        return $this->getRiwayatFromPemeriksaan($lansia);
    }

    private function getRiwayatFromKunjungan(Lansia $lansia): Collection
    {
        if (! method_exists($lansia, 'kunjungans')) {
            return collect();
        }

        try {
            return $lansia->kunjungans()
                ->with('pemeriksaan')
                ->whereHas('pemeriksaan', function (Builder $query) {
                    $this->applyVerifiedFilter($query);
                })
                ->orderByDesc('tanggal_kunjungan')
                ->orderByDesc('created_at')
                ->limit(12)
                ->get()
                ->filter(fn ($item) => filled($item->pemeriksaan))
                ->values();
        } catch (\Throwable $e) {
            Log::warning('User lansia riwayat kunjungan skipped', [
                'lansia_id' => $lansia->id,
                'message' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    private function getRiwayatFromPemeriksaan(Lansia $lansia): Collection
    {
        if (! Schema::hasTable('pemeriksaans')) {
            return collect();
        }

        try {
            $query = Pemeriksaan::query()
                ->with('kunjungan')
                ->where(function (Builder $q) use ($lansia) {
                    $hasKategoriPasien = Schema::hasColumn('pemeriksaans', 'kategori_pasien')
                        && Schema::hasColumn('pemeriksaans', 'pasien_id');

                    if ($hasKategoriPasien) {
                        $q->where(function (Builder $inner) use ($lansia) {
                            $inner->where('kategori_pasien', 'lansia')
                                ->where('pasien_id', $lansia->id);
                        });
                    }

                    if (Schema::hasColumn('pemeriksaans', 'lansia_id')) {
                        $hasKategoriPasien
                            ? $q->orWhere('lansia_id', $lansia->id)
                            : $q->where('lansia_id', $lansia->id);
                    }
                });

            $this->applyVerifiedFilter($query);

            return $query
                ->orderByDesc('tanggal_periksa')
                ->orderByDesc('created_at')
                ->limit(12)
                ->get()
                ->map(function (Pemeriksaan $pemeriksaan) {
                    return (object) [
                        'id' => 'pemeriksaan-' . $pemeriksaan->id,
                        'tanggal_kunjungan' => $pemeriksaan->tanggal_periksa
                            ?? data_get($pemeriksaan, 'kunjungan.tanggal_kunjungan')
                            ?? $pemeriksaan->created_at,
                        'created_at' => $pemeriksaan->created_at,
                        'keluhan' => $pemeriksaan->keluhan
                            ?? data_get($pemeriksaan, 'kunjungan.keluhan')
                            ?? null,
                        'pemeriksaan' => $pemeriksaan,
                    ];
                })
                ->values();
        } catch (\Throwable $e) {
            Log::warning('User lansia riwayat pemeriksaan skipped', [
                'lansia_id' => $lansia->id,
                'message' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    private function resolveLatestPemeriksaan(Lansia $lansia, Collection $riwayat): ?Pemeriksaan
    {
        $latest = $riwayat->first()?->pemeriksaan;

        if ($latest instanceof Pemeriksaan) {
            return $latest;
        }

        $fromRelation = $lansia->pemeriksaan_terakhir ?? null;

        if ($fromRelation instanceof Pemeriksaan) {
            return $fromRelation;
        }

        if (! Schema::hasTable('pemeriksaans')) {
            return null;
        }

        try {
            $query = Pemeriksaan::query()
                ->where(function (Builder $q) use ($lansia) {
                    $hasKategoriPasien = Schema::hasColumn('pemeriksaans', 'kategori_pasien')
                        && Schema::hasColumn('pemeriksaans', 'pasien_id');

                    if ($hasKategoriPasien) {
                        $q->where(function (Builder $inner) use ($lansia) {
                            $inner->where('kategori_pasien', 'lansia')
                                ->where('pasien_id', $lansia->id);
                        });
                    }

                    if (Schema::hasColumn('pemeriksaans', 'lansia_id')) {
                        $hasKategoriPasien
                            ? $q->orWhere('lansia_id', $lansia->id)
                            : $q->where('lansia_id', $lansia->id);
                    }
                });

            $this->applyVerifiedFilter($query);

            return $query
                ->orderByDesc('tanggal_periksa')
                ->orderByDesc('created_at')
                ->first();
        } catch (\Throwable $e) {
            Log::warning('User lansia latest pemeriksaan skipped', [
                'lansia_id' => $lansia->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function applyVerifiedFilter(Builder $query): void
    {
        if (! Schema::hasColumn('pemeriksaans', 'status_verifikasi')) {
            return;
        }

        $query->whereIn('status_verifikasi', [
            'tervalidasi',
            'verified',
            'approved',
            'valid',
            'selesai',
        ]);
    }

    private function buildMetrics(
        Lansia $lansia,
        ?Pemeriksaan $latestPemeriksaan,
        ?float $imt,
        array $ptmAnalysis
    ): array {
        return [
            [
                'label' => 'Usia',
                'value' => $this->ageText($lansia->tanggal_lahir ?? null),
                'caption' => $this->kategoriUsia($lansia->tanggal_lahir ?? null),
                'tone' => 'emerald',
            ],
            [
                'label' => 'Tensi',
                'value' => $this->bloodPressureValue(
                    $latestPemeriksaan->tekanan_darah
                        ?? $lansia->tekanan_darah
                        ?? null
                ),
                'caption' => $ptmAnalysis['tensi_label'] ?? 'Belum Ada Data',
                'tone' => $ptmAnalysis['tensi_tone'] ?? 'slate',
            ],
            [
                'label' => 'Gula Darah',
                'value' => $this->numberValue(
                    $latestPemeriksaan->gula_darah
                        ?? $lansia->gula_darah
                        ?? null,
                    'mg/dL'
                ),
                'caption' => $ptmAnalysis['gula_label'] ?? 'Belum Ada Data',
                'tone' => $ptmAnalysis['gula_tone'] ?? 'slate',
            ],
            [
                'label' => 'Kemandirian',
                'value' => $this->kemandirianLabel(
                    $latestPemeriksaan->tingkat_kemandirian
                        ?? $lansia->tingkat_kemandirian
                        ?? null
                ),
                'caption' => 'Aktivitas harian',
                'tone' => 'amber',
            ],
        ];
    }

    private function buildHealthMetrics(
        Lansia $lansia,
        ?Pemeriksaan $latestPemeriksaan,
        ?float $imt,
        array $imtAnalysis
    ): array {
        return [
            [
                'label' => 'Berat',
                'value' => $this->numberValue(
                    $latestPemeriksaan->berat_badan
                        ?? $lansia->berat_badan
                        ?? null,
                    'kg'
                ),
                'caption' => 'Pemeriksaan fisik',
                'tone' => 'sky',
            ],
            [
                'label' => 'Tinggi',
                'value' => $this->heightValue(
                    $latestPemeriksaan->tinggi_badan
                        ?? $lansia->tinggi_badan
                        ?? null
                ),
                'caption' => 'Tinggi badan',
                'tone' => 'sky',
            ],
            [
                'label' => 'IMT',
                'value' => filled($imt) ? number_format($imt, 1, ',', '.') : '-',
                'caption' => $imtAnalysis['label'] ?? 'Belum Ada Data',
                'tone' => $imtAnalysis['tone'] ?? 'slate',
            ],
            [
                'label' => 'Lingkar Perut',
                'value' => $this->numberValue(
                    $latestPemeriksaan->lingkar_perut
                        ?? $lansia->lingkar_perut
                        ?? null,
                    'cm'
                ),
                'caption' => 'Risiko metabolik',
                'tone' => 'amber',
            ],
            [
                'label' => 'Kolesterol',
                'value' => $this->numberValue(
                    $latestPemeriksaan->kolesterol
                        ?? $lansia->kolesterol
                        ?? null,
                    'mg/dL'
                ),
                'caption' => 'Lab sederhana',
                'tone' => 'rose',
            ],
            [
                'label' => 'Asam Urat',
                'value' => $this->numberValue(
                    $latestPemeriksaan->asam_urat
                        ?? $lansia->asam_urat
                        ?? null,
                    'mg/dL'
                ),
                'caption' => 'Lab sederhana',
                'tone' => 'rose',
            ],
        ];
    }

    private function buildTrend(Collection $riwayat): array
    {
        $items = $riwayat
            ->reverse()
            ->values()
            ->take(8);

        $gulaValues = $items
            ->map(fn ($item) => (float) ($item->pemeriksaan->gula_darah ?? 0))
            ->filter(fn ($value) => $value > 0)
            ->values();

        $maxGula = max((float) ($gulaValues->max() ?: 1), 1);

        return $items
            ->map(function ($item) use ($maxGula) {
                $pemeriksaan = $item->pemeriksaan ?? null;
                $tensi = $this->parseBloodPressure($pemeriksaan->tekanan_darah ?? null);
                $gula = filled($pemeriksaan->gula_darah ?? null)
                    ? (float) $pemeriksaan->gula_darah
                    : null;

                return [
                    'label' => $item->tanggal_kunjungan
                        ? Carbon::parse($item->tanggal_kunjungan)->translatedFormat('d M')
                        : '-',
                    'sistolik' => $tensi['sistolik'],
                    'gula' => $gula,
                    'height' => filled($gula)
                        ? max(18, min(100, (int) round(($gula / $maxGula) * 100)))
                        : 14,
                ];
            })
            ->toArray();
    }

    private function buildRiwayatCards(Collection $riwayat): array
    {
        return $riwayat
            ->map(function ($kunjungan) {
                $pemeriksaan = $kunjungan->pemeriksaan ?? null;

                if (! $pemeriksaan) {
                    return null;
                }

                $imt = $this->resolveImt($pemeriksaan);

                return [
                    'tanggal' => $kunjungan->tanggal_kunjungan
                        ? Carbon::parse($kunjungan->tanggal_kunjungan)->translatedFormat('d M Y')
                        : '-',
                    'berat' => $this->numberValue($pemeriksaan->berat_badan ?? null, 'kg'),
                    'tinggi' => $this->heightValue($pemeriksaan->tinggi_badan ?? null),
                    'imt' => filled($imt) ? number_format($imt, 1, ',', '.') : '-',
                    'lingkar_perut' => $this->numberValue($pemeriksaan->lingkar_perut ?? null, 'cm'),
                    'tensi' => $this->bloodPressureValue($pemeriksaan->tekanan_darah ?? null),
                    'gula' => $this->numberValue($pemeriksaan->gula_darah ?? null, 'mg/dL'),
                    'kolesterol' => $this->numberValue($pemeriksaan->kolesterol ?? null, 'mg/dL'),
                    'asam_urat' => $this->numberValue($pemeriksaan->asam_urat ?? null, 'mg/dL'),
                    'keluhan' => $kunjungan->keluhan
                        ?: $pemeriksaan->keluhan
                        ?: 'Tidak ada catatan keluhan.',
                    'edukasi' => $pemeriksaan->edukasi ?? null,
                    'status' => $pemeriksaan->status_verifikasi_text
                        ?? 'Tervalidasi',
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }

    private function buildPtmAnalysis(Lansia $lansia, ?Pemeriksaan $latestPemeriksaan): array
    {
        $tensiRaw = $latestPemeriksaan->tekanan_darah
            ?? $lansia->tekanan_darah
            ?? null;

        $tensi = $this->parseBloodPressure($tensiRaw);

        $gula = $latestPemeriksaan->gula_darah
            ?? $lansia->gula_darah
            ?? null;

        $kolesterol = $latestPemeriksaan->kolesterol
            ?? $lansia->kolesterol
            ?? null;

        $asamUrat = $latestPemeriksaan->asam_urat
            ?? $lansia->asam_urat
            ?? null;

        $sistolik = $tensi['sistolik'];
        $diastolik = $tensi['diastolik'];

        $gulaValue = filled($gula) ? (float) $gula : null;
        $kolesterolValue = filled($kolesterol) ? (float) $kolesterol : null;
        $asamUratValue = filled($asamUrat) ? (float) $asamUrat : null;

        $risks = [];

        $tensiLabel = 'Belum Ada Data';
        $tensiTone = 'slate';

        if ($sistolik || $diastolik) {
            if ($sistolik >= 140 || $diastolik >= 90) {
                $tensiLabel = 'Perlu Perhatian';
                $tensiTone = 'rose';
                $risks[] = 'tekanan darah tinggi';
            } else {
                $tensiLabel = 'Terkontrol';
                $tensiTone = 'emerald';
            }
        }

        $gulaLabel = 'Belum Ada Data';
        $gulaTone = 'slate';

        if (filled($gulaValue)) {
            if ($gulaValue >= 200) {
                $gulaLabel = 'Perlu Perhatian';
                $gulaTone = 'rose';
                $risks[] = 'gula darah tinggi';
            } elseif ($gulaValue >= 140) {
                $gulaLabel = 'Pantau Berkala';
                $gulaTone = 'amber';
                $risks[] = 'gula darah perlu dipantau';
            } else {
                $gulaLabel = 'Terkontrol';
                $gulaTone = 'emerald';
            }
        }

        if (filled($kolesterolValue) && $kolesterolValue >= 240) {
            $risks[] = 'kolesterol tinggi';
        }

        if (filled($asamUratValue) && $asamUratValue >= 7) {
            $risks[] = 'asam urat perlu dipantau';
        }

        if (empty($risks)) {
            if ($sistolik || filled($gulaValue) || filled($kolesterolValue) || filled($asamUratValue)) {
                return [
                    'label' => 'Kondisi Terkontrol',
                    'message' => 'Hasil pemeriksaan dasar terakhir berada dalam rentang yang relatif terkendali.',
                    'suggestion' => 'Pertahankan pola makan seimbang, aktivitas fisik ringan, dan pemeriksaan berkala.',
                    'tone' => 'emerald',
                    'icon' => 'fa-circle-check',
                    'tensi_label' => $tensiLabel,
                    'tensi_tone' => $tensiTone,
                    'gula_label' => $gulaLabel,
                    'gula_tone' => $gulaTone,
                    'risks' => [],
                ];
            }

            return [
                'label' => 'Menunggu Data',
                'message' => 'Data tensi, gula darah, kolesterol, atau asam urat belum tersedia.',
                'suggestion' => 'Lakukan pemeriksaan kesehatan dasar di Posyandu.',
                'tone' => 'slate',
                'icon' => 'fa-circle-info',
                'tensi_label' => $tensiLabel,
                'tensi_tone' => $tensiTone,
                'gula_label' => $gulaLabel,
                'gula_tone' => $gulaTone,
                'risks' => [],
            ];
        }

        $tone = count($risks) >= 2 ? 'rose' : 'amber';

        return [
            'label' => count($risks) >= 2 ? 'Risiko Perlu Tindak Lanjut' : 'Perlu Pemantauan',
            'message' => 'Terdapat catatan: ' . implode(', ', $risks) . '.',
            'suggestion' => 'Disarankan konsultasi dengan Bidan atau tenaga kesehatan untuk pemantauan lanjutan.',
            'tone' => $tone,
            'icon' => count($risks) >= 2 ? 'fa-triangle-exclamation' : 'fa-heart-pulse',
            'tensi_label' => $tensiLabel,
            'tensi_tone' => $tensiTone,
            'gula_label' => $gulaLabel,
            'gula_tone' => $gulaTone,
            'risks' => $risks,
        ];
    }

    private function resolveImt($source = null, $fallback = null): ?float
    {
        $imt = $source->imt
            ?? $fallback->imt
            ?? null;

        if (filled($imt)) {
            return round((float) $imt, 2);
        }

        $berat = $source->berat_badan
            ?? $fallback->berat_badan
            ?? null;

        $tinggi = $source->tinggi_badan
            ?? $fallback->tinggi_badan
            ?? null;

        if (blank($berat) || blank($tinggi)) {
            return null;
        }

        $tinggiCm = $this->normalizeHeightCm($tinggi);

        if (! $tinggiCm) {
            return null;
        }

        $tinggiMeter = $tinggiCm / 100;

        return round(((float) $berat) / ($tinggiMeter * $tinggiMeter), 2);
    }

    private function buildImtAnalysis(?float $imt): array
    {
        if (! $imt) {
            return [
                'label' => 'Belum Ada Data',
                'tone' => 'slate',
            ];
        }

        if ($imt < 18.5) {
            return [
                'label' => 'Kurus',
                'tone' => 'amber',
            ];
        }

        if ($imt < 25) {
            return [
                'label' => 'Normal',
                'tone' => 'emerald',
            ];
        }

        if ($imt < 30) {
            return [
                'label' => 'Berlebih',
                'tone' => 'rose',
            ];
        }

        return [
            'label' => 'Obesitas',
            'tone' => 'rose',
        ];
    }

    private function parseBloodPressure($value): array
    {
        if (blank($value)) {
            return [
                'sistolik' => null,
                'diastolik' => null,
            ];
        }

        preg_match_all('/\d+/', (string) $value, $matches);

        $numbers = collect($matches[0] ?? [])
            ->map(fn ($item) => (int) $item)
            ->filter(fn ($item) => $item > 0)
            ->values();

        return [
            'sistolik' => $numbers->get(0),
            'diastolik' => $numbers->get(1),
        ];
    }

    private function bloodPressureValue($value): string
    {
        $parsed = $this->parseBloodPressure($value);

        if (! $parsed['sistolik']) {
            return '-';
        }

        if (! $parsed['diastolik']) {
            return (string) $parsed['sistolik'];
        }

        return $parsed['sistolik'] . '/' . $parsed['diastolik'] . ' mmHg';
    }

    private function normalizeHeightCm($value): ?float
    {
        if (blank($value)) {
            return null;
        }

        $height = (float) $value;

        if ($height >= 1 && $height <= 2.5) {
            $height *= 100;
        }

        if ($height >= 10 && $height < 50) {
            $height *= 10;
        }

        if ($height < 50 || $height > 250) {
            return null;
        }

        return round($height, 1);
    }

    private function heightValue($value): string
    {
        $height = $this->normalizeHeightCm($value);

        if (! $height) {
            return '-';
        }

        return $this->numberValue($height, 'cm');
    }

    private function kemandirianLabel($value): string
    {
        return match ($value) {
            'mandiri' => 'Mandiri',
            'bantuan_sebagian' => 'Bantuan Sebagian',
            'ketergantungan_penuh' => 'Ketergantungan Penuh',
            default => 'Belum Diisi',
        };
    }

    private function kategoriUsia($tanggalLahir): string
    {
        if (blank($tanggalLahir)) {
            return 'Belum Ada Data';
        }

        $age = Carbon::parse($tanggalLahir)->age;

        if ($age >= 70) {
            return 'Lansia Risiko Tinggi';
        }

        if ($age >= 60) {
            return 'Lansia';
        }

        if ($age >= 45) {
            return 'Pra-Lansia';
        }

        return 'Dewasa';
    }

    private function ageText($tanggalLahir): string
    {
        if (blank($tanggalLahir)) {
            return '-';
        }

        return Carbon::parse($tanggalLahir)->age . ' tahun';
    }

    private function numberValue($value, string $unit = ''): string
    {
        if (blank($value)) {
            return '-';
        }

        $number = (float) $value;
        $formatted = rtrim(rtrim(number_format($number, 1, '.', ''), '0'), '.');

        return trim($formatted . ' ' . $unit);
    }
}