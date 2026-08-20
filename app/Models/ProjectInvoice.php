<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectInvoice extends Model
{
    use HasFactory;

    protected $fillable = ['pemesanan_id', 'number', 'type', 'name', 'amount', 'status', 'due_date', 'note', 'created_by', 'proof_path', 'verified_by', 'verified_at'];

    protected $casts = ['amount' => 'decimal:2', 'due_date' => 'date', 'verified_at' => 'datetime'];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
