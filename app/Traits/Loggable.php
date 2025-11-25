<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait Loggable
{
    public function logActivity(string $action, $model = null)
    {
        ActivityLog::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id'   => $model?->id,
            'ip'         => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
