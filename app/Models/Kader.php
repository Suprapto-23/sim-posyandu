<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kader extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jabatan',
        'tanggal_bergabung',
        'status_kader',
    ];

    protected $casts = [
        'tanggal_bergabung' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    

    public function kunjungans()
    {
        return $this->hasMany(Kunjungan::class, 'petugas_id');
    }
}