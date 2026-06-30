<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiPosyandu extends Model
{
    use HasFactory;

    protected $table = 'absensi_posyandu';

    protected $fillable = [
        'jadwal_id',
        'kode_absensi',
        'kategori',
        'tanggal_posyandu',
        'bulan',
        'tahun',
        'nomor_pertemuan',
        'keterangan',
        'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal_posyandu' => 'date',
        'bulan'           => 'integer',
        'tahun'           => 'integer',
        'nomor_pertemuan' => 'integer',
        'dicatat_oleh'    => 'integer',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    public const KATEGORI = [
        'balita' => 'Balita',
        'remaja' => 'Remaja',
        'lansia' => 'Lansia',
    ];

    // ── EVENT LISTENERS (LOGIKA OTOMATIS) ────────────────────────────────

    protected static function booted(): void
    {
        // Pengisian data otomatis saat data absensi baru dibuat
        static::creating(function (self $absensi) {
            if (!$absensi->tanggal_posyandu) {
                $absensi->tanggal_posyandu = now();
            }
            if (!$absensi->bulan) {
                $absensi->bulan = Carbon::parse($absensi->tanggal_posyandu)->month;
            }
            if (!$absensi->tahun) {
                $absensi->tahun = Carbon::parse($absensi->tanggal_posyandu)->year;
            }
            if (empty($absensi->kode_absensi)) {
                $prefix = strtoupper(substr($absensi->kategori, 0, 3));
                $absensi->kode_absensi = 'ABS-' . $prefix . '-' . now()->format('YmdHis');
            }
        });

        // PERBAIKAN: Mencegah Orphan Data dengan menghapus detail saat absensi dihapus
        static::deleting(function (self $absensi) {
            $absensi->details()->delete();
        });
    }

    // ── RELASI ───────────────────────────────────────────────────────────

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalPosyandu::class, 'jadwal_id');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function details(): HasMany
    {
        return $this->hasMany(AbsensiDetail::class, 'absensi_id');
    }

    // ── MUTATORS & ACCESSORS ─────────────────────────────────────────────

    public function getKategoriLabelAttribute(): string
    {
        return self::KATEGORI[strtolower($this->kategori)] ?? ucfirst($this->kategori);
    }

    // ── FUNGSI PENDUKUNG ABSENSI BATCH (BULK) ────────────────────────────

    public function generateDetailAbsensi(bool $defaultHadir = false): int
    {
        $kategori = strtolower($this->kategori);
        
        $modelClass = match ($kategori) {
            'balita' => Balita::class,
            'remaja' => Remaja::class,
            'lansia' => Lansia::class,
            default  => null,
        };

        if (!$modelClass || !class_exists($modelClass)) {
            return 0;
        }

        $created = 0;

        // Menggunakan chunk agar aplikasi tidak ngelag/crash jika data pasien mencapai ribuan
        $modelClass::query()
            ->select('id')
            ->orderBy('id')
            ->chunkById(200, function ($items) use ($kategori, $defaultHadir, &$created) {
                foreach ($items as $item) {
                    $detail = $this->details()->firstOrCreate(
                        [
                            'pasien_id'   => (int) $item->id,
                            'pasien_type' => $kategori,
                        ],
                        [
                            'hadir'      => $defaultHadir,
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
            'hadir'      => true,
            'keterangan' => $keterangan,
        ]);
    }

    public function tandaiSemuaTidakHadir(?string $keterangan = null): int
    {
        return $this->details()->update([
            'hadir'      => false,
            'keterangan' => $keterangan,
        ]);
    }

    public function resetKehadiran(): int
    {
        return $this->details()->update([
            'hadir'      => false,
            'keterangan' => null,
        ]);
    }
}