<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StatusTracking extends Model
{
    use HasFactory;

    protected $table = 'status_tracking';

    protected $fillable = [
        'id_pemesanan',
        'status',
        'tanggal_update',
        'catatan',
    ];

    protected $casts = [
        'tanggal_update' => 'date',
    ];

    // Relationships
    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }
}
