<?php

namespace App\Models;

use Carbon\CarbonInterface;
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
        'gaya_preferensi',
        'deskripsi_kebutuhan',
        'upload_foto',
        'tanggal_konsultasi',
        'waktu_konsultasi',
        'status',
        'catatan_admin',
        'active_slot',
    ];

    protected $casts = [
        'upload_foto' => 'array',
        'tanggal_konsultasi' => 'date',
        'waktu_konsultasi' => 'datetime:H:i',
        'luas_ruangan' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (Konsultasi $consultation): void {
            $consultation->active_slot = $consultation->reservesSlot()
                ? $consultation->slotKey()
                : null;
        });
    }

    public function reservesSlot(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true);
    }

    public function slotKey(): string
    {
        $date = $this->tanggal_konsultasi;
        $time = $this->waktu_konsultasi;

        $datePart = $date instanceof CarbonInterface
            ? $date->format('Y-m-d')
            : substr((string) $date, 0, 10);
        $timePart = $time instanceof CarbonInterface
            ? $time->format('H:i')
            : substr((string) $time, 0, 5);

        return "{$datePart} {$timePart}";
    }

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
            'free_consultation' => 'Konsultasi Gratis (30 menit)',
            'virtual_design' => 'Virtual Design + 3D Mockup',
            'in_home_visit' => 'Kunjungan Designer ke Rumah',
            'chat_support' => 'Chat Support Real-time',
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
            'living_room' => 'Ruang Tamu',
            'bedroom' => 'Kamar Tidur',
            'kitchen' => 'Dapur',
            'bathroom' => 'Kamar Mandi',
            'office' => 'Ruang Kerja',
            'whole_house' => 'Seluruh Hunian',
            default => (string) $this->jenis_ruangan,
        };
    }
}
