<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class IncidentAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'file_path',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    // عشان نرجّع URL جاهز للواجهة
    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        // لازم تكون عامل storage:link
        return Storage::url($this->file_path);
    }
}
