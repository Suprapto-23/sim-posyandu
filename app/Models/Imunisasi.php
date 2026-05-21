<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Imunisasi extends Model
{
    use HasFactory;

    protected $table = 'imunisasis';

    protected $fillable = [
        'kunjungan_id',
        'jenis_imunisasi',
        'vaksin',
        'batch_number',
        'tanggal_imunisasi',
        'catatan',
    ];

    protected $casts = [
        'tanggal_imunisasi' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function kunjungan()
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

    public function getKategoriSasaranAttribute(): string
    {
        $type = $this->kunjungan?->pasien_type;

        if (!$type) {
            return 'Tidak diketahui';
        }

        return match (class_basename($type)) {
            'Balita' => 'Balita / Anak',
            'Remaja' => 'Remaja',
            'Lansia' => 'Lansia',
            default => 'Sasaran',
        };
    }

    public function getNamaPetugasAttribute(): string
{
    return $this->kunjungan?->petugas?->name
        ?? $this->kunjungan?->petugas?->nama
        ?? 'Bidan';
}

    public function getTanggalLabelAttribute(): string
    {
        if (!$this->tanggal_imunisasi) {
            return '-';
        }

        return Carbon::parse($this->tanggal_imunisasi)
            ->locale('id')
            ->translatedFormat('d F Y');
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
        return $this->vaksin ?: $this->jenis_imunisasi ?: 'Imunisasi';
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
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                'icon' => 'fa-syringe',
            ];
        }

        return [
            'label' => 'Imunisasi Tambahan',
            'class' => 'bg-amber-50 text-amber-700 border-amber-100',
            'icon' => 'fa-shield-heart',
        ];
    }

    public function scopeBulanIni($query)
    {
        return $query
            ->whereMonth('tanggal_imunisasi', now()->month)
            ->whereYear('tanggal_imunisasi', now()->year);
    }

    public function scopeTargetBalita($query)
    {
        return $query->whereHas('kunjungan', function ($q) {
            $q->where('pasien_type', \App\Models\Balita::class)
              ->orWhere('pasien_type', 'like', '%Balita%');
        });
    }
}