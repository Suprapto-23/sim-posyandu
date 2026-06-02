<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Imunisasi extends Model
{
    protected $table = 'imunisasis';

    protected $fillable = [
        'kunjungan_id',
        'jenis_imunisasi',
        'vaksin',
        'dosis',
        'tanggal_imunisasi',
        'batch_number',
        'expiry_date',
        'penyelenggara',
        'catatan',
    ];

    protected $casts = [
        'tanggal_imunisasi' => 'date',
        'expiry_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id');
    }

    public function getNamaPenerimaAttribute(): string
    {
        return $this->kunjungan?->pasien?->nama_lengkap
            ?? $this->kunjungan?->pasien?->nama
            ?? 'Data sasaran tidak ditemukan';
    }

    public function getNikPenerimaAttribute(): string
    {
        return $this->kunjungan?->pasien?->nik ?? '-';
    }

    public function getNamaPetugasAttribute(): string
    {
        return $this->kunjungan?->petugas?->name
            ?? $this->kunjungan?->petugas?->nama
            ?? 'Bidan';
    }

    public function getKategoriKeyAttribute(): string
    {
        $type = $this->kunjungan?->pasien_type;

        if (! $type) {
            return 'umum';
        }

        return match (strtolower(class_basename($type))) {
            'balita' => 'balita',
            'remaja' => 'remaja',
            'lansia' => 'lansia',
            default => strtolower((string) $type),
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

    public function getTanggalLabelAttribute(): string
    {
        if (! $this->tanggal_imunisasi) {
            return '-';
        }

        return Carbon::parse($this->tanggal_imunisasi)
            ->locale('id')
            ->translatedFormat('d F Y');
    }

    public function getTanggalLengkapLabelAttribute(): string
    {
        if (! $this->tanggal_imunisasi) {
            return '-';
        }

        return Carbon::parse($this->tanggal_imunisasi)
            ->locale('id')
            ->translatedFormat('l, d F Y');
    }

    public function getJamLabelAttribute(): string
    {
        if (! $this->created_at) {
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

    public function getDosisLabelAttribute(): string
    {
        if ($this->dosis === null || $this->dosis === '') {
            return '-';
        }

        return 'Dosis ' . $this->dosis;
    }

    public function getBatchLabelAttribute(): string
    {
        return $this->batch_number ?: '-';
    }

    public function getExpiryLabelAttribute(): string
    {
        if (! $this->expiry_date) {
            return '-';
        }

        return Carbon::parse($this->expiry_date)
            ->locale('id')
            ->translatedFormat('d F Y');
    }

    public function getPenyelenggaraLabelAttribute(): string
    {
        return $this->penyelenggara ?: 'Posyandu / Puskesmas';
    }

    public function getCatatanLabelAttribute(): string
    {
        return $this->catatan ?: 'Tidak ada catatan tambahan.';
    }

    public function getBadgeThemeAttribute(): array
    {
        $text = strtolower(($this->jenis_imunisasi ?? '') . ' ' . ($this->vaksin ?? ''));

        if (
            str_contains($text, 'bcg') ||
            str_contains($text, 'polio') ||
            str_contains($text, 'dpt') ||
            str_contains($text, 'hepatitis') ||
            str_contains($text, 'hib') ||
            str_contains($text, 'campak') ||
            str_contains($text, 'mr') ||
            str_contains($text, 'pcv') ||
            str_contains($text, 'rotavirus')
        ) {
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

    public function scopeBulanIni(Builder $query): Builder
    {
        return $query
            ->whereMonth('tanggal_imunisasi', now()->month)
            ->whereYear('tanggal_imunisasi', now()->year);
    }

    public function scopeTargetBalita(Builder $query): Builder
    {
        return $query->whereHas('kunjungan', function (Builder $q) {
            $q->where('pasien_type', Balita::class)
                ->orWhere('pasien_type', 'like', '%Balita%')
                ->orWhere('pasien_type', 'balita');
        });
    }
}