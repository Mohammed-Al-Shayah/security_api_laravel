<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
        protected $table = 'attendance_records';


    protected $fillable = [
        'shift_id',
        'guard_id',
        'check_in_time',
        'check_out_time',
        'check_in_lat',
        'check_in_lng',
        'check_out_lat',
        'check_out_lng',
        'status',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    // ⚠️ renamed because "guard()" name conflicts with Model::guard()
    public function guardProfile()
    {
        return $this->belongsTo(Guard::class, 'guard_id');
    }
}
