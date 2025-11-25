<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'national_id', 'badge_number', 'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'guard_project')
            ->withPivot(['assigned_from', 'assigned_to'])
            ->withTimestamps();
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(Attendance::class);
    }
}
