<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbsensiPosyandu extends Model
{
    protected $table = 'absensi_posyandu';

    protected $fillable = [
        'kode_absensi',
        'kategori',
        'tanggal_posyandu',
        'bulan',
        'tahun',
        'nomor_pertemuan',
        'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal_posyandu' => 'date',
        'bulan' => 'integer',
        'tahun' => 'integer',
        'nomor_pertemuan' => 'integer',
        'dicatat_oleh' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const KATEGORI = [
        'balita' => 'Balita',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $absensi) {
            if (!$absensi->tanggal_posyandu) {
                $absensi->tanggal_posyandu = now('Asia/Jakarta')->toDateString();
            }

            $tanggal = Carbon::parse($absensi->tanggal_posyandu, 'Asia/Jakarta');

            $absensi->kategori = self::normalizeKategori($absensi->kategori);
            $absensi->bulan = $absensi->bulan ?: (int) $tanggal->month;
            $absensi->tahun = $absensi->tahun ?: (int) $tanggal->year;

            if (!$absensi->nomor_pertemuan) {
                $absensi->nomor_pertemuan = self::getNextNomorPertemuan(
                    $absensi->kategori,
                    (int) $tanggal->month,
                    (int) $tanggal->year
                );
            }

            if (!$absensi->kode_absensi) {
                $absensi->kode_absensi = self::generateKodeAbsensi(
                    $absensi->kategori,
                    $tanggal,
                    (int) $absensi->nomor_pertemuan
                );
            }

            if (!$absensi->dicatat_oleh && auth()->check()) {
                $absensi->dicatat_oleh = auth()->id();
            }
        });

        static::saving(function (self $absensi) {
            $absensi->kategori = self::normalizeKategori($absensi->kategori);

            if ($absensi->tanggal_posyandu) {
                $tanggal = Carbon::parse($absensi->tanggal_posyandu, 'Asia/Jakarta');

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
        return $this->hasMany(AbsensiDetail::class, 'absensi_id')
            ->where('hadir', true);
    }

    public function detailTidakHadir(): HasMany
    {
        return $this->hasMany(AbsensiDetail::class, 'absensi_id')
            ->where('hadir', false);
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

        $lastNumber = self::query()
            ->where('kategori', $kategori)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->max('nomor_pertemuan');

        return ((int) $lastNumber) + 1;
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

    $nomorPertemuan = max(1, (int) $nomorPertemuan);

    $base = 'ABS-' 
        . $prefix 
        . '-' 
        . $tanggal->format('Ymd') 
        . '-P' 
        . str_pad($nomorPertemuan, 2, '0', STR_PAD_LEFT);

    $kode = $base;
    $counter = 1;

    while (self::query()->where('kode_absensi', $kode)->exists()) {
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
        if (!$this->tanggal_posyandu) {
            return '-';
        }

        return Carbon::parse($this->tanggal_posyandu, 'Asia/Jakarta')
            ->translatedFormat('d F Y');
    }

    public function getTanggalPendekAttribute(): string
    {
        if (!$this->tanggal_posyandu) {
            return '-';
        }

        return Carbon::parse($this->tanggal_posyandu, 'Asia/Jakarta')
            ->translatedFormat('d M Y');
    }

    public function getBulanTahunAttribute(): string
    {
        if (!$this->bulan || !$this->tahun) {
            return '-';
        }

        return Carbon::createFromDate((int) $this->tahun, (int) $this->bulan, 1, 'Asia/Jakarta')
            ->translatedFormat('F Y');
    }

    public function getTotalPesertaAttribute(): int
    {
        if ($this->relationLoaded('details')) {
            return $this->details->count();
        }

        return $this->details()->count();
    }

    public function getTotalHadirAttribute(): int
    {
        if ($this->relationLoaded('details')) {
            return $this->details->where('hadir', true)->count();
        }

        return $this->detailHadir()->count();
    }

    public function getTotalTidakHadirAttribute(): int
    {
        if ($this->relationLoaded('details')) {
            return $this->details->where('hadir', false)->count();
        }

        return $this->detailTidakHadir()->count();
    }

    public function getPersentaseHadirAttribute(): float
    {
        $totalPeserta = (int) $this->total_peserta;

        if ($totalPeserta <= 0) {
            return 0;
        }

        return round(((int) $this->total_hadir / $totalPeserta) * 100, 1);
    }

    public function getPersentaseTidakHadirAttribute(): float
    {
        $totalPeserta = (int) $this->total_peserta;

        if ($totalPeserta <= 0) {
            return 0;
        }

        return round(((int) $this->total_tidak_hadir / $totalPeserta) * 100, 1);
    }

    public function getStatusRekapTextAttribute(): string
    {
        $totalPeserta = (int) $this->total_peserta;
        $totalHadir = (int) $this->total_hadir;

        if ($totalPeserta <= 0) {
            return 'Belum Ada Peserta';
        }

        if ($totalHadir <= 0) {
            return 'Belum Ada Kehadiran';
        }

        if ($totalHadir === $totalPeserta) {
            return 'Semua Hadir';
        }

        return 'Sebagian Hadir';
    }

    public function getStatusRekapBadgeAttribute(): string
    {
        $totalPeserta = (int) $this->total_peserta;
        $totalHadir = (int) $this->total_hadir;

        if ($totalPeserta <= 0) {
            return 'slate';
        }

        if ($totalHadir <= 0) {
            return 'rose';
        }

        if ($totalHadir === $totalPeserta) {
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
        return $query
            ->where('bulan', $bulan)
            ->where('tahun', $tahun);
    }

    public function scopeTahun(Builder $query, int $tahun): Builder
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeTerbaru(Builder $query): Builder
    {
        return $query
            ->orderByDesc('tanggal_posyandu')
            ->orderByDesc('created_at');
    }

    public function syncPesertaDariKategori(bool $defaultHadir = false): int
    {
        $kategori = self::normalizeKategori($this->kategori);
        $modelClass = self::getModelClassByKategori($kategori);

        if (!$modelClass || !class_exists($modelClass)) {
            return 0;
        }

        $created = 0;

        $modelClass::query()
            ->select('id')
            ->orderBy('id')
            ->chunkById(200, function ($items) use ($kategori, $defaultHadir, &$created) {
                foreach ($items as $item) {
                    $detail = $this->details()->firstOrCreate(
                        [
                            'pasien_id' => (int) $item->id,
                            'pasien_type' => $kategori,
                        ],
                        [
                            'hadir' => $defaultHadir,
                            'keterangan' => null,
                        ]
                    );

                    if ($detail->wasRecentlyCreated) {
                        $created++;
                    }
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