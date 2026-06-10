<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'id_invoice',
        'jumlah_bayar',
        'tanggal_bayar',
        'metode_bayar',
        'bukti_pembayaran',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'jumlah_bayar' => 'decimal:2',
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'id_invoice');
    }

    // Helper methods
    public function getFormattedJumlahAttribute()
    {
        return 'Rp ' . number_format($this->jumlah_bayar, 0, ',', '.');
    }
}
