<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataImport extends Model
{
    protected $fillable = [
        'nama_file',
        'jenis_data',
        'file_path',
        'status',
        'total_data',
        'data_berhasil',
        'data_gagal',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'total_data' => 'integer',
        'data_berhasil' => 'integer',
        'data_gagal' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'info',
            'processing' => 'warning',
            'completed' => 'success',
            'failed' => 'danger',
            default => 'secondary',
        };
    }

    public function getJenisDataLabelAttribute(): string
    {
        return match ($this->jenis_data) {
            'balita' => 'Balita',
            'remaja' => 'Remaja',
            'lansia' => 'Lansia',
            default => ucfirst((string) $this->jenis_data),
        };
    }

    public function getPersentaseBerhasilAttribute(): int
    {
        $total = (int) $this->total_data;

        if ($total <= 0) {
            return 0;
        }

        return (int) round(((int) $this->data_berhasil / $total) * 100);
    }
}