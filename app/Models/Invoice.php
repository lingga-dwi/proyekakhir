<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_pemesanan',
        'total_tagihan',
        'status_invoice',
        'tanggal_jatuh_tempo',
    ];

    protected $casts = [
        'tanggal_jatuh_tempo' => 'date',
        'total_tagihan' => 'decimal:2',
    ];

    // Relationships
    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class, 'id_pemesanan');
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class, 'id_invoice');
    }

    // Helper methods
    public function isPending()
    {
        return $this->status_invoice === 'pending';
    }

    public function isDibayar()
    {
        return $this->status_invoice === 'dibayar';
    }

    public function isOverdue()
    {
        return $this->status_invoice === 'overdue';
    }

    public function getFormattedTotalAttribute()
    {
        return 'Rp ' . number_format($this->total_tagihan, 0, ',', '.');
    }
}
