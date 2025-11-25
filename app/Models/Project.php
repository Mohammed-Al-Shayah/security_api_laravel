<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasActivityLogging;

class Project extends Model
{
    use HasFactory;
    use HasActivityLogging;

    protected $fillable = [
        'name', 'location', 'lat', 'lng',
        'manager_id', 'start_date', 'end_date', 'status',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function guards()
    {
        return $this->belongsToMany(Guard::class, 'guard_project')
            ->withTimestamps()
            ->withPivot(['assigned_from', 'assigned_to']);
    }

    /**
     * Inspectors assigned to this project.
     */
    public function inspectors()
    {
        return $this->belongsToMany(Inspector::class, 'inspector_project')
            ->withTimestamps()
            ->withPivot(['assigned_from', 'assigned_to']);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    public function patrols()
    {
        return $this->hasMany(Patrol::class);
    }
}
