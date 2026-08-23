<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDocumentDecision extends Model
{
    use HasFactory;

    protected $fillable = [
        'pemesanan_id',
        'decided_by',
        'stage',
        'submission_round',
        'decision',
        'feedback',
    ];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
