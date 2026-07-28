<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'dikonfirmasi';

    public const STATUS_IN_PROGRESS = 'sedang_dikerjakan';

    public const STATUS_COMPLETED = 'selesai';

    public const STATUS_CANCELLED = 'dibatalkan';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $table = 'pemesanan';

    protected $fillable = [
        'id_user',
        'designer_id',
        'katalog_id',
        'tanggal_pesan',
        'sumber_masuk',
        'status_pemesanan',
        'progress',
        'target_selesai',
        'catatan_progres',
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
        'target_selesai' => 'date',
        'progress' => 'integer',
        'upload_denah_foto' => 'array',
        'luas_area' => 'decimal:2',
        'total_harga' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function designer()
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    public function katalog()
    {
        return $this->belongsTo(Katalog::class, 'katalog_id');
    }

    public function statusTrackings()
    {
        return $this->hasMany(StatusTracking::class, 'id_pemesanan');
    }

    public function konsultasi()
    {
        return $this->hasOne(Konsultasi::class, 'pemesanan_id');
    }
}
