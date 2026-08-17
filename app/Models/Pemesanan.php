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
        'workflow_stage',
        'draft_round',
        'final_round',
        'progress',
        'target_selesai',
        'survey_scheduled_at',
        'survey_notes',
        'survey_result',
        'survey_completed_at',
        'catatan_progres',
        'total_harga',
        'jenis_proyek',
        'jenis_bangunan',
        'luas_area',
        'deskripsi_keinginan_desain',
    ];

    protected $casts = [
        'tanggal_pesan' => 'date',
        'target_selesai' => 'date',
        'survey_scheduled_at' => 'datetime',
        'survey_completed_at' => 'datetime',
        'draft_round' => 'integer',
        'final_round' => 'integer',
        'progress' => 'integer',
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

    public function documents()
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function documentDecisions()
    {
        return $this->hasMany(ProjectDocumentDecision::class);
    }

    public function invoices()
    {
        return $this->hasMany(ProjectInvoice::class);
    }

    public function dpInvoice()
    {
        return $this->hasOne(ProjectInvoice::class)->where('type', 'dp_20');
    }
}
