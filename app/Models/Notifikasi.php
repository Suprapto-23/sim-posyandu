<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    use HasFactory;

    // Menetapkan tabel utama sebagai pangkalan data tunggal
    protected $table = 'notifikasis';

    protected $fillable = [
        'user_id',
        'judul',
        'pesan',
        'tipe',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    // =========================================================================
    // RELASI DATABASE
    // =========================================================================
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =========================================================================
    // LOCAL SCOPES (QUERY CEPAT)
    // =========================================================================
    
    public function scopeBelumDibaca($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeTerbaru($query)
    {
        return $query->latest()->take(10);
    }

    // =========================================================================
    // ACCESSORS: NEXUS CRM UI ENGINE
    // =========================================================================
    
    public function getTipeLabelAttribute()
    {
        return match($this->tipe) {
            'jadwal'      => 'Jadwal Posyandu',
            'imunisasi'   => 'Info Imunisasi',
            'pemeriksaan' => 'Data Pemeriksaan',
            'info'        => 'Informasi Sistem',
            'import'      => 'Status Database',
            default       => 'Pemberitahuan',
        };
    }

    public function getTipeIconAttribute()
    {
        return match($this->tipe) {
            'jadwal'      => 'fas fa-calendar-check',
            'imunisasi'   => 'fas fa-syringe',
            'pemeriksaan' => 'fas fa-stethoscope',
            'import'      => 'fas fa-file-excel',
            default       => 'fas fa-bell',
        };
    }

    public function getTipeStylesAttribute()
    {
        return match($this->tipe) {
            'jadwal'      => 'bg-indigo-100 text-indigo-600 border-indigo-200',
            'imunisasi'   => 'bg-emerald-100 text-emerald-600 border-emerald-200',
            'pemeriksaan' => 'bg-sky-100 text-sky-600 border-sky-200',
            'import'      => 'bg-amber-100 text-amber-600 border-amber-200',
            default       => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }

    public function toNexusFormat()
    {
        return [
            'id'         => $this->id,
            'title'      => $this->judul,
            'message'    => $this->pesan,
            'styles'     => $this->tipe_styles, // UBAH: dari color menjadi styles
            'is_read'    => $this->is_read,
            'created_at' => $this->created_at->diffForHumans(),
            'url'        => $this->tautan ?? '#',
            'tipe'       => $this->tipe
        ];
    }
}