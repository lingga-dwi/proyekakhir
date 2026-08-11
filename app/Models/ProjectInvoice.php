<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectInvoice extends Model
{
    use HasFactory;

    protected $fillable = ['pemesanan_id', 'number', 'type', 'amount', 'status', 'due_date', 'proof_path', 'verified_by', 'verified_at'];

    protected $casts = ['amount' => 'decimal:2', 'due_date' => 'date', 'verified_at' => 'datetime'];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class);
    }
}
