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

    public function getTipeColorAttribute()
    {
        return match($this->tipe) {
            'jadwal'      => 'indigo',
            'imunisasi'   => 'emerald',
            'pemeriksaan' => 'sky',
            'import'      => 'amber',
            default       => 'slate',
        };
    }

    // WAJIB ADA: Menyiapkan data JSON AJAX untuk Anti-Reload Controller
    public function toNexusFormat()
    {
        return [
            'id'         => $this->id,
            'judul'      => $this->judul,
            'pesan'      => $this->pesan,
            'waktu'      => $this->created_at->diffForHumans(),
            'is_read'    => $this->is_read,
            'icon'       => $this->tipe_icon,
            'color'      => $this->tipe_color,
            'label'      => $this->tipe_label,
            'link'       => $this->link ?? '#',
        ];
    }
}