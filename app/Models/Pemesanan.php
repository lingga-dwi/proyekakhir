<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pemesanan extends Model
{
    use HasFactory;

    protected $table = 'pemesanan';

    protected $fillable = [
        'id_rfq',
        'id_user',
        'katalog_id',
        'tanggal_pesan',
        'status_pemesanan',
        'total_harga',
        'jenis_proyek',
        'jenis_bangunan',
        'luas_area',
        'jumlah_ruangan',
        'gaya_desain_preferensi',
        'warna_dominan',
        'deskripsi_keinginan_desain',
        'upload_denah_foto',
    ];

    protected $casts = [
        'tanggal_pesan' => 'date',
        'upload_denah_foto' => 'array',
        'luas_area' => 'decimal:2',
        'total_harga' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'id_rfq');
    }

    public function katalog()
    {
        return $this->belongsTo(Katalog::class, 'katalog_id');
    }

    public function statusTrackings()
    {
        return $this->hasMany(StatusTracking::class, 'id_pemesanan');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'id_pemesanan');
    }

    // Helper methods
    public function isPending()
    {
        return $this->status_pemesanan === 'pending';
    }

    public function isDikonfirmasi()
    {
        return $this->status_pemesanan === 'dikonfirmasi';
    }

    public function getSedangDikerjakan()
    {
        return $this->status_pemesanan === 'sedang_dikerjakan';
    }

    public function isSelesai()
    {
        return $this->status_pemesanan === 'selesai';
    }

    public function isDibatalkan()
    {
        return $this->status_pemesanan === 'dibatalkan';
    }
}
