<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'url',
        'payload',
        'ip',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
