<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Konsultasi extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $table = 'konsultasi';

    protected $fillable = [
        'user_id',
        'pemesanan_id',
        'nama',
        'email',
        'no_telp',
        'jenis_konsultasi',
        'jenis_ruangan',
        'budget_range',
        'timeline',
        'luas_ruangan',
        'deskripsi_kebutuhan',
        'tanggal_konsultasi',
        'waktu_konsultasi',
        'status',
        'catatan_admin',
    ];

    protected $casts = [
        'tanggal_konsultasi' => 'date',
        'waktu_konsultasi' => 'datetime:H:i',
        'luas_ruangan' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class);
    }

    // Helper methods
    public function getJenisKonsultasiLabel()
    {
        return match ($this->jenis_konsultasi) {
            'free_consultation' => 'Desain Interior Baru',
            'virtual_design' => 'Renovasi Interior',
            'in_home_visit' => 'Custom Furniture',
            'chat_support' => 'Konsultasi Desain',
            default => $this->jenis_konsultasi
        };
    }

    public function getBudgetRangeLabel()
    {
        return match ($this->budget_range) {
            'under_10m' => 'Di bawah Rp 10 Juta',
            '10m_25m' => 'Rp 10 - 25 Juta',
            '25m_50m' => 'Rp 25 - 50 Juta',
            '50m_100m' => 'Rp 50 - 100 Juta',
            'above_100m' => 'Di atas Rp 100 Juta',
            default => $this->budget_range
        };
    }

    public function getTimelineLabel()
    {
        return match ($this->timeline) {
            'immediate' => 'Segera (1-2 minggu)',
            '1_month' => '1 Bulan',
            '3_months' => '3 Bulan',
            '6_months' => '6 Bulan',
            'flexible' => 'Fleksibel',
            default => $this->timeline
        };
    }

    public function getJenisRuanganLabel(): string
    {
        return match ($this->jenis_ruangan) {
            'living_room' => 'Rumah Tinggal',
            'bedroom' => 'Apartemen',
            'kitchen' => 'Ruko',
            'bathroom' => 'Kantor',
            'office' => 'Kafe / Restoran',
            'whole_house' => 'Lainnya',
            default => (string) $this->jenis_ruangan,
        };
    }
}
