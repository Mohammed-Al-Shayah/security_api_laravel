<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasActivityLogging;


class Patrol extends Model
{
    use HasFactory;
    use HasActivityLogging;

    protected $fillable = [
        'inspector_id',
        'project_id',
        'guard_id',
        'start_time',
        'end_time',
        'rating',
        'notes',
    ];

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(Inspector::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // 👇 استبدلنا guard() بـ securityGuard()
    public function securityGuard(): BelongsTo
    {
        return $this->belongsTo(Guard::class, 'guard_id');
    }
}
