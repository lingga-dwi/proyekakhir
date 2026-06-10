<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rfq extends Model
{
    use HasFactory;

    protected $table = 'rfq';

    protected $fillable = [
        'id_user',
        'id_katalog',
        'tanggal_pengajuan',
        'kebutuhan_proyek',
        'status_rfq',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function katalog()
    {
        return $this->belongsTo(Katalog::class, 'id_katalog');
    }

    public function pemesanan()
    {
        return $this->hasOne(Pemesanan::class, 'id_rfq');
    }

    // Helper methods
    public function isPending()
    {
        return $this->status_rfq === 'pending';
    }

    public function isApproved()
    {
        return $this->status_rfq === 'disetujui';
    }

    public function isRejected()
    {
        return $this->status_rfq === 'ditolak';
    }
}
