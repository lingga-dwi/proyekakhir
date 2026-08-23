<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusTracking extends Model
{
    use HasFactory;

    protected $table = 'status_tracking';

    protected $fillable = [
        'id_pemesanan',
        'actor_id',
        'previous_status',
        'status',
        'progress',
        'tanggal_update',
        'catatan',
    ];

    protected $casts = [
        'tanggal_update' => 'date',
        'progress' => 'integer',
    ];

    // Relationships
    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
