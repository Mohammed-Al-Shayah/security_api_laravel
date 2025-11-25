<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspector extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'employee_code', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Projects that this inspector is assigned to.
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'inspector_project')
            ->withTimestamps()
            ->withPivot(['assigned_from', 'assigned_to']);
    }

    /**
     * Patrols performed by this inspector.
     */
    public function patrols()
    {
        return $this->hasMany(Patrol::class);
    }

    /**
     * Incidents reported by this inspector's user account.
     * We filter incidents by reporter_id matching the inspector's user_id.
     */
    public function reportedIncidents()
    {
        return $this->hasMany(Incident::class, 'reporter_id', 'user_id');
    }
}
