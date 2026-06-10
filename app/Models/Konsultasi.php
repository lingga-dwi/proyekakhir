<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Konsultasi extends Model
{
    use HasFactory;

    protected $table = 'konsultasi';

    protected $fillable = [
        'user_id',
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
    ];

    protected $casts = [
        'upload_foto' => 'array',
        'tanggal_konsultasi' => 'date',
        'waktu_konsultasi' => 'datetime:H:i',
        'luas_ruangan' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function getJenisKonsultasiLabel()
    {
        return match($this->jenis_konsultasi) {
            'free_consultation' => 'Konsultasi Gratis (30 menit)',
            'virtual_design' => 'Virtual Design + 3D Mockup',
            'in_home_visit' => 'Kunjungan Designer ke Rumah',
            'chat_support' => 'Chat Support Real-time',
            default => $this->jenis_konsultasi
        };
    }

    public function getBudgetRangeLabel()
    {
        return match($this->budget_range) {
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
        return match($this->timeline) {
            'immediate' => 'Segera (1-2 minggu)',
            '1_month' => '1 Bulan',
            '3_months' => '3 Bulan',
            '6_months' => '6 Bulan',
            'flexible' => 'Fleksibel',
            default => $this->timeline
        };
    }
}
