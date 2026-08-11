<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDocument extends Model
{
    use HasFactory;

    protected $fillable = ['pemesanan_id', 'uploaded_by', 'stage', 'document_type', 'path', 'original_name', 'version'];

    public function pemesanan()
    {
        return $this->belongsTo(Pemesanan::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
