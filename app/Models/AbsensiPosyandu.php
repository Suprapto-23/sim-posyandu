<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbsensiPosyandu extends Model
{
    use HasFactory;

    protected $table = 'absensi_posyandu';

    protected $fillable = [
        'kode_absensi',
        'nomor_pertemuan',
        'kategori',
        'tanggal_posyandu',
        'bulan',
        'tahun',
        'catatan',
        'dicatat_oleh',
    ];

    protected $casts = [
        'nomor_pertemuan' => 'integer',
        'tanggal_posyandu' => 'date',
        'bulan' => 'integer',
        'tahun' => 'integer',
        'dicatat_oleh' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const KATEGORI = [
        'balita' => 'Balita / Anak',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $absensi) {
            if (!$absensi->tanggal_posyandu) {
                $absensi->tanggal_posyandu = now('Asia/Jakarta')->toDateString();
            }

            $tanggal = Carbon::parse($absensi->tanggal_posyandu);

            $absensi->kategori = self::normalizeKategori($absensi->kategori);
            $absensi->bulan = $absensi->bulan ?: (int) $tanggal->month;
            $absensi->tahun = $absensi->tahun ?: (int) $tanggal->year;

            if (!$absensi->nomor_pertemuan) {
                $absensi->nomor_pertemuan = self::getNextNomorPertemuan(
                    $absensi->kategori,
                    $absensi->bulan,
                    $absensi->tahun
                );
            }

            if (!$absensi->kode_absensi) {
                $absensi->kode_absensi = self::generateKodeAbsensi(
                    $absensi->kategori,
                    $tanggal,
                    $absensi->nomor_pertemuan
                );
            }

            if (!$absensi->dicatat_oleh && auth()->check()) {
                $absensi->dicatat_oleh = auth()->id();
            }
        });

        static::saving(function (self $absensi) {
            $absensi->kategori = self::normalizeKategori($absensi->kategori);

            if ($absensi->tanggal_posyandu) {
                $tanggal = Carbon::parse($absensi->tanggal_posyandu);
                $absensi->bulan = (int) $tanggal->month;
                $absensi->tahun = (int) $tanggal->year;
            }
        });
    }

    public function details(): HasMany
    {
        return $this->hasMany(AbsensiDetail::class, 'absensi_id');
    }

    public function detailHadir(): HasMany
    {
        return $this->hasMany(AbsensiDetail::class, 'absensi_id')->where('hadir', true);
    }

    public function detailTidakHadir(): HasMany
    {
        return $this->hasMany(AbsensiDetail::class, 'absensi_id')->where('hadir', false);
    }

    public function kader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public static function normalizeKategori(?string $kategori): string
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

    public static function getModelClassByKategori(?string $kategori): ?string
    {
        $kategori = self::normalizeKategori($kategori);

        return AbsensiDetail::PASIEN_TYPES[$kategori] ?? null;
    }

    public static function getNextNomorPertemuan(string $kategori, int $bulan, int $tahun): int
    {
        $kategori = self::normalizeKategori($kategori);

        return ((int) self::where('kategori', $kategori)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->max('nomor_pertemuan')) + 1;
    }

    public static function generateKodeAbsensi(string $kategori, Carbon $tanggal, int $nomorPertemuan): string
    {
        $kategori = self::normalizeKategori($kategori);

        $prefix = match ($kategori) {
            'balita' => 'BAL',
            'remaja' => 'REM',
            'lansia' => 'LAN',
            default => 'POS',
        };

        $base = 'ABS-' . $prefix . '-' . $tanggal->format('Ymd') . '-' . str_pad($nomorPertemuan, 2, '0', STR_PAD_LEFT);
        $kode = $base;
        $counter = 1;

        while (self::where('kode_absensi', $kode)->exists()) {
            $kode = $base . '-' . $counter;
            $counter++;
        }

        return $kode;
    }

    public function getKategoriLabelAttribute(): string
    {
        return self::KATEGORI[self::normalizeKategori($this->kategori)] ?? 'Tidak Dikenal';
    }

    public function getTanggalFormatAttribute(): string
    {
        return $this->tanggal_posyandu
            ? Carbon::parse($this->tanggal_posyandu)->translatedFormat('d F Y')
            : '-';
    }

    public function getBulanTahunAttribute(): string
    {
        if (!$this->bulan || !$this->tahun) {
            return '-';
        }

        return Carbon::createFromDate($this->tahun, $this->bulan, 1)
            ->translatedFormat('F Y');
    }

    public function getTotalPesertaAttribute(): int
    {
        return $this->relationLoaded('details')
            ? $this->details->count()
            : $this->details()->count();
    }

    public function getTotalHadirAttribute(): int
    {
        return $this->relationLoaded('details')
            ? $this->details->where('hadir', true)->count()
            : $this->detailHadir()->count();
    }

    public function getTotalTidakHadirAttribute(): int
    {
        return $this->relationLoaded('details')
            ? $this->details->where('hadir', false)->count()
            : $this->detailTidakHadir()->count();
    }

    public function getPersentaseHadirAttribute(): float
    {
        if ($this->total_peserta <= 0) {
            return 0;
        }

        return round(($this->total_hadir / $this->total_peserta) * 100, 1);
    }

    public function getStatusRekapTextAttribute(): string
    {
        if ($this->total_peserta <= 0) {
            return 'Belum Ada Peserta';
        }

        if ($this->total_hadir <= 0) {
            return 'Belum Ada Kehadiran';
        }

        if ($this->total_hadir === $this->total_peserta) {
            return 'Semua Hadir';
        }

        return 'Sebagian Hadir';
    }

    public function getStatusRekapBadgeAttribute(): string
    {
        if ($this->total_peserta <= 0) {
            return 'slate';
        }

        if ($this->total_hadir <= 0) {
            return 'rose';
        }

        if ($this->total_hadir === $this->total_peserta) {
            return 'emerald';
        }

        return 'amber';
    }

    public function scopeKategori(Builder $query, string $kategori): Builder
    {
        return $query->where('kategori', self::normalizeKategori($kategori));
    }

    public function scopeTanggal(Builder $query, string $tanggal): Builder
    {
        return $query->whereDate('tanggal_posyandu', Carbon::parse($tanggal)->toDateString());
    }

    public function scopeHariIni(Builder $query): Builder
    {
        return $query->whereDate('tanggal_posyandu', now('Asia/Jakarta')->toDateString());
    }

    public function scopeBulan(Builder $query, int $bulan, ?int $tahun = null): Builder
    {
        $query->where('bulan', $bulan);

        if ($tahun) {
            $query->where('tahun', $tahun);
        }

        return $query;
    }

    public function scopePeriode(Builder $query, int $bulan, int $tahun): Builder
    {
        return $query->where('bulan', $bulan)->where('tahun', $tahun);
    }

    public function scopeTahun(Builder $query, int $tahun): Builder
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeTerbaru(Builder $query): Builder
    {
        return $query->orderByDesc('tanggal_posyandu')
            ->orderByDesc('created_at');
    }

    public function syncPesertaDariKategori(bool $defaultHadir = false): int
    {
        $kategori = self::normalizeKategori($this->kategori);
        $modelClass = self::getModelClassByKategori($kategori);

        if (!$modelClass || !class_exists($modelClass)) {
            return 0;
        }

        $pasienType = $kategori;
        $typeCandidates = [$pasienType, $modelClass];

        $existingIds = $this->details()
            ->whereIn('pasien_type', $typeCandidates)
            ->pluck('pasien_id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        $created = 0;

        $modelClass::query()
            ->select('id')
            ->orderBy('id')
            ->chunkById(200, function ($items) use ($existingIds, $pasienType, $defaultHadir, &$created) {
                $existingMap = array_flip($existingIds);

                foreach ($items as $item) {
                    if (isset($existingMap[(int) $item->id])) {
                        continue;
                    }

                    $this->details()->create([
                        'pasien_id' => $item->id,
                        'pasien_type' => $pasienType,
                        'hadir' => $defaultHadir,
                        'keterangan' => null,
                    ]);

                    $created++;
                }
            });

        return $created;
    }

    public function tandaiSemuaHadir(?string $keterangan = null): int
    {
        return $this->details()->update([
            'hadir' => true,
            'keterangan' => $keterangan,
        ]);
    }

    public function tandaiSemuaTidakHadir(?string $keterangan = null): int
    {
        return $this->details()->update([
            'hadir' => false,
            'keterangan' => $keterangan,
        ]);
    }

    public function resetKehadiran(): int
    {
        return $this->details()->update([
            'hadir' => false,
            'keterangan' => null,
        ]);
    }
}