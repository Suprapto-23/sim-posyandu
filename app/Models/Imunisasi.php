<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Imunisasi extends Model
{
    use HasFactory;

    protected $table = 'imunisasis';

    protected $fillable = [
        'kunjungan_id',
        'jenis_imunisasi',
        'vaksin',
        'dosis',         // <-- Tambah ini
        'batch_number',
        'tanggal_imunisasi',
        'penyelenggara', // <-- Tambah ini
        'catatan',
    ];

    protected $casts = [
        'tanggal_imunisasi' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor Penerima & Petugas
    |--------------------------------------------------------------------------
    */

    public function getPenerimaAttribute()
    {
        return $this->kunjungan?->pasien;
    }

    public function getNamaPenerimaAttribute(): string
    {
        return $this->penerima?->nama_lengkap
            ?? $this->penerima?->nama
            ?? 'Data sasaran tidak ditemukan';
    }

    public function getNikPenerimaAttribute(): string
    {
        return $this->penerima?->nik ?? '-';
    }

    public function getNamaPetugasAttribute(): string
    {
        return $this->kunjungan?->petugas?->name
            ?? $this->kunjungan?->petugas?->nama
            ?? 'Petugas tidak diketahui';
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor Kategori Sasaran
    |--------------------------------------------------------------------------
    */

    public function getKategoriKeyAttribute(): string
    {
        $type = $this->kunjungan?->pasien_type;

        return match (true) {
            $type === Balita::class || str_contains(strtolower((string) $type), 'balita') => 'balita',
            $type === Remaja::class || str_contains(strtolower((string) $type), 'remaja') => 'remaja',
            $type === Lansia::class || str_contains(strtolower((string) $type), 'lansia') => 'lansia',
            default => 'sasaran',
        };
    }

    public function getKategoriSasaranAttribute(): string
    {
        return match ($this->kategori_key) {
            'balita' => 'Balita',
            'remaja' => 'Remaja',
            'lansia' => 'Lansia',
            default => 'Sasaran',
        };
    }

    public function getKategoriThemeAttribute(): array
    {
        return match ($this->kategori_key) {
            'balita' => [
                'label' => 'Balita',
                'desc' => 'Sasaran anak dan tumbuh kembang',
                'icon' => 'fa-child-reaching',
                'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                'solid' => 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white',
                'soft' => 'border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-white',
            ],
            'remaja' => [
                'label' => 'Remaja',
                'desc' => 'Sasaran usia remaja',
                'icon' => 'fa-user-graduate',
                'badge' => 'border-violet-200 bg-violet-50 text-violet-800',
                'solid' => 'bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white',
                'soft' => 'border-violet-200 bg-gradient-to-br from-violet-50 via-fuchsia-50 to-white',
            ],
            'lansia' => [
                'label' => 'Lansia',
                'desc' => 'Sasaran usia lanjut',
                'icon' => 'fa-person-cane',
                'badge' => 'border-sky-200 bg-sky-50 text-sky-800',
                'solid' => 'bg-gradient-to-br from-sky-500 to-cyan-500 text-white',
                'soft' => 'border-sky-200 bg-gradient-to-br from-sky-50 via-cyan-50 to-white',
            ],
            default => [
                'label' => 'Sasaran',
                'desc' => 'Data sasaran',
                'icon' => 'fa-user',
                'badge' => 'border-slate-200 bg-slate-50 text-slate-700',
                'solid' => 'bg-gradient-to-br from-slate-700 to-slate-950 text-white',
                'soft' => 'border-slate-200 bg-gradient-to-br from-slate-50 to-white',
            ],
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor Label Imunisasi
    |--------------------------------------------------------------------------
    */

    public function getTanggalLabelAttribute(): string
    {
        if (!$this->tanggal_imunisasi) {
            return '-';
        }

        return Carbon::parse($this->tanggal_imunisasi)
            ->locale('id')
            ->translatedFormat('d F Y');
    }

    public function getTanggalLengkapLabelAttribute(): string
    {
        if (!$this->tanggal_imunisasi) {
            return '-';
        }

        return Carbon::parse($this->tanggal_imunisasi)
            ->locale('id')
            ->translatedFormat('l, d F Y');
    }

    public function getJamLabelAttribute(): string
    {
        if (!$this->created_at) {
            return '-';
        }

        return Carbon::parse($this->created_at)
            ->timezone('Asia/Jakarta')
            ->format('H:i') . ' WIB';
    }

    public function getVaksinLabelAttribute(): string
    {
        return $this->vaksin ?: ($this->jenis_imunisasi ?: 'Imunisasi');
    }

    public function getJenisLabelAttribute(): string
    {
        return $this->jenis_imunisasi ?: '-';
    }

    public function getBatchLabelAttribute(): string
    {
        return $this->batch_number ?: '-';
    }

    public function getCatatanLabelAttribute(): string
    {
        return $this->catatan ?: 'Tidak ada catatan tambahan.';
    }

    public function getBadgeThemeAttribute(): array
    {
        $text = strtolower(($this->jenis_imunisasi ?? '') . ' ' . ($this->vaksin ?? ''));

        $dasar = str_contains($text, 'bcg')
            || str_contains($text, 'polio')
            || str_contains($text, 'dpt')
            || str_contains($text, 'hepatitis')
            || str_contains($text, 'hib')
            || str_contains($text, 'campak')
            || str_contains($text, 'mr')
            || str_contains($text, 'pcv')
            || str_contains($text, 'rotavirus');

        if ($dasar) {
            return [
                'label' => 'Imunisasi Dasar',
                'icon' => 'fa-syringe',
                'badge' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                'solid' => 'bg-gradient-to-br from-emerald-500 to-teal-500 text-white',
                'soft' => 'border-emerald-200 bg-gradient-to-br from-emerald-50 via-teal-50 to-white',
            ];
        }

        return [
            'label' => 'Imunisasi Tambahan',
            'icon' => 'fa-shield-heart',
            'badge' => 'border-amber-200 bg-amber-50 text-amber-700',
            'solid' => 'bg-gradient-to-br from-amber-500 to-orange-500 text-white',
            'soft' => 'border-amber-200 bg-gradient-to-br from-amber-50 via-yellow-50 to-white',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    */

    public function scopeBulanIni(Builder $query): Builder
    {
        return $query
            ->whereMonth('tanggal_imunisasi', now()->month)
            ->whereYear('tanggal_imunisasi', now()->year);
    }

    public function scopeTahunIni(Builder $query): Builder
    {
        return $query->whereYear('tanggal_imunisasi', now()->year);
    }

    public function scopePeriode(Builder $query, ?string $start, ?string $end): Builder
    {
        if ($start) {
            $query->whereDate('tanggal_imunisasi', '>=', $start);
        }

        if ($end) {
            $query->whereDate('tanggal_imunisasi', '<=', $end);
        }

        return $query;
    }

    public function scopeKategori(Builder $query, string $kategori): Builder
    {
        $model = match ($kategori) {
            'balita' => Balita::class,
            'remaja' => Remaja::class,
            'lansia' => Lansia::class,
            default => null,
        };

        if (!$model) {
            return $query;
        }

        return $query->whereHas('kunjungan', function (Builder $q) use ($model, $kategori) {
            $q->where('pasien_type', $model)
                ->orWhere('pasien_type', $kategori)
                ->orWhere('pasien_type', 'like', '%' . class_basename($model) . '%');
        });
    }

    public function scopeTargetBalita(Builder $query): Builder
    {
        return $query->kategori('balita');
    }

    public function scopeTargetRemaja(Builder $query): Builder
    {
        return $query->kategori('remaja');
    }

    public function scopeTargetLansia(Builder $query): Builder
    {
        return $query->kategori('lansia');
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        $keyword = trim((string) $keyword);

        if ($keyword === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($keyword) {
            $q->where('jenis_imunisasi', 'like', "%{$keyword}%")
                ->orWhere('vaksin', 'like', "%{$keyword}%")
                ->orWhere('batch_number', 'like', "%{$keyword}%")
                ->orWhereHas('kunjungan.pasien', function (Builder $pasien) use ($keyword) {
                    $pasien->where('nama_lengkap', 'like', "%{$keyword}%")
                        ->orWhere('nik', 'like', "%{$keyword}%");
                });
        });
    }

    public function scopeTerbaru(Builder $query): Builder
    {
        return $query
            ->latest('tanggal_imunisasi')
            ->latest('id');
    }
}